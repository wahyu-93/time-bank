<?php

namespace App\Services;

use App\Models\Child;
use App\Models\TimeTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TimeBankService
{
    public function balance(Child $child): int
    {
        return (int) $child->timeTransactions()->sum('amount');
    }

    public function add(Child $child, int $minutes, string $type, string $description, ?User $user = null, ?Model $source = null): TimeTransaction 
    {
        if ($minutes <= 0) {
            throw new RuntimeException('Amount must be greater than zero.');
        }

        return $this->transaction(
            child: $child,
            amount: $minutes,
            type: $type,
            description: $description,
            user: $user,
            source: $source,
        );
    }

    public function subtract(Child $child, int $minutes, string $type, string $description, ?User $user = null, ?Model $source = null): TimeTransaction
    {
        if ($minutes <= 0) {
            throw new RuntimeException('Amount must be greater than zero.');
        }

        if ($type === 'privilege') {
            $balance = $this->balance($child);

            if ($balance < $minutes) {
                throw new RuntimeException(
                    "Insufficient time balance. Current balance: {$balance} minutes."
                );
            }
        }

        return $this->transaction(
            child: $child,
            amount: -$minutes,
            type: $type,
            description: $description,
            user: $user,
            source: $source,
        );
    }

    private function transaction(Child $child, int $amount, string $type, string $description, ?User $user, ?Model $source): TimeTransaction 
    {
        return DB::transaction(function () use (
            $child,
            $amount,
            $type,
            $description,
            $user,
            $source,
        ) {
            return $child->timeTransactions()->create([
                'amount' => $amount,
                'type' => $type,
                'source_type' => $source?->getMorphClass(),
                'source_id' => $source?->getKey(),
                'description' => $description,
                'created_by' => $user?->id,
            ]);
        });
    }
}