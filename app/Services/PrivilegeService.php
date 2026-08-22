<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Privilege;
use App\Models\PrivilegeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use App\Services\TimeBankService;

class PrivilegeService
{
    public function __construct(private TimeBankService $timeBank) 
    {
        //
    }

    public function request(Child $child, Privilege $privilege): PrivilegeRequest
    {
        $balance = $this->timeBank->balance($child);

        if ($privilege->cost_minutes > $balance) {
            throw new RuntimeException('Saldo waktu tidak cukup.');
        }

        $existing = $child->privilegeRequests()
            ->where('privilege_id', $privilege->id)
            ->whereDate('created_at', today())
            ->whereIn('status', ['pending', 'approved'])
            ->latest('id')
            ->first();

        if ($existing) {
            throw new RuntimeException(
                $existing->status === 'pending'
                    ? 'Permintaan ini masih menunggu persetujuan orang tua.'
                    : 'Privilege ini sudah disetujui hari ini.'
            );
        }

        return $child->privilegeRequests()->create([
            'privilege_id' => $privilege->id,
            'cost_minutes' => $privilege->cost_minutes,
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