<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Privilege;
use App\Models\PrivilegeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PrivilegeService
{
    public function request(Child $child, Privilege $privilege): PrivilegeRequest 
    {
        $childPrivilege = $child->privileges()
            ->whereKey($privilege->id)
            ->wherePivot('is_active', true)
            ->first();

        if (!$childPrivilege) {
            throw new RuntimeException(
                'This privilege is not available for this child.'
            );
        }

        $cost = $childPrivilege->pivot->custom_cost_minutes
            ?? $privilege->cost_minutes;

        $balance = app(TimeBankService::class)->balance($child);

        if ($balance < $cost) {
            throw new RuntimeException(
                "Insufficient time balance. Current balance: {$balance} minutes."
            );
        }

        return $child->privilegeRequests()->create([
            'privilege_id' => $privilege->id,
            'cost_minutes' => $cost,
            'status' => 'pending',
            'requested_at' => now(),
        ]);
    }

    public function approve(PrivilegeRequest $request, User $parent): PrivilegeRequest 
    {
        return DB::transaction(function () use ($request, $parent) {
            if ($request->status !== 'pending') {
                throw new RuntimeException(
                    'This request has already been processed.'
                );
            }

            $bank = app(TimeBankService::class);

            // Cek ulang saldo saat approval.
            // Penting karena saldo bisa berubah
            // setelah anak membuat request.
            $bank->subtract(
                child: $request->child,
                minutes: $request->cost_minutes,
                type: 'privilege',
                description: $request->privilege->name,
                user: $parent,
                source: $request,
            );

            $request->update([
                'status' => 'approved',
                'reviewed_by' => $parent->id,
                'reviewed_at' => now(),
            ]);

            $request->child->playSessions()->create([
                'privilege_request_id' => $request->id,
                'planned_minutes' => $request->cost_minutes,
                'status' => 'pending',
            ]);

            return $request->fresh();
        });
    }

    public function reject(PrivilegeRequest $request, User $parent, ?string $note = null): PrivilegeRequest 
    {
        if ($request->status !== 'pending') {
            throw new RuntimeException(
                'This request has already been processed.'
            );
        }

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $parent->id,
            'reviewed_at' => now(),
            'note' => $note,
        ]);

        return $request->fresh();
    }
}