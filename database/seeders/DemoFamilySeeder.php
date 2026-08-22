<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Child;
use App\Models\Family;
use App\Models\Privilege;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoFamilySeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Parent
        |--------------------------------------------------------------------------
        */

        $parent = User::updateOrCreate(
            [
                'email' => 'parent@example.com',
            ],
            [
                'name' => 'Mama',
                'password' => Hash::make('password'),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Family
        |--------------------------------------------------------------------------
        */

        $family = Family::firstOrCreate([
            'name' => 'Keluarga Demo',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Parent ↔ Family
        |--------------------------------------------------------------------------
        */

        $family->parents()->syncWithoutDetaching([
            $parent->id => [
                'role' => 'parent',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Children
        |--------------------------------------------------------------------------
        */

        $budi = $family->children()->updateOrCreate(
            [
                'name' => 'Budi',
            ],
            [
                'birth_date' => '2019-01-01',
                'daily_limit_minutes' => 180,
                'max_debt_minutes' => 60,
                'is_active' => true,
            ]
        );

        $aisyah = $family->children()->updateOrCreate(
            [
                'name' => 'Aisyah',
            ],
            [
                'birth_date' => '2022-01-01',
                'daily_limit_minutes' => 60,
                'max_debt_minutes' => 30,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | RFID
        |--------------------------------------------------------------------------
        */

        $budi->rfidCards()->updateOrCreate(
            [
                'uid' => 'DEMO-BUDI-001',
            ],
            [
                'name' => 'Kartu Budi',
                'is_active' => true,
            ]
        );

        $aisyah->rfidCards()->updateOrCreate(
            [
                'uid' => 'DEMO-AISYAH-001',
            ],
            [
                'name' => 'Kartu Aisyah',
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Activities
        |--------------------------------------------------------------------------
        */

        $sholat = $family->activities()->updateOrCreate(
            [
                'name' => 'Sholat',
            ],
            [
                'description' => 'Melaksanakan sholat sesuai jadwal.',
                'icon' => 'mosque',
                'type' => 'required',
                'reward_minutes' => 5,
                'penalty_minutes' => 5,
                'requires_approval' => true,
                'is_active' => true,
            ]
        );

        $pr = $family->activities()->updateOrCreate(
            [
                'name' => 'PR',
            ],
            [
                'description' => 'Mengerjakan pekerjaan rumah.',
                'icon' => 'book',
                'type' => 'required',
                'reward_minutes' => 5,
                'penalty_minutes' => 5,
                'requires_approval' => true,
                'is_active' => true,
            ]
        );

        $ngaji = $family->activities()->updateOrCreate(
            [
                'name' => 'Ngaji',
            ],
            [
                'description' => 'Mengaji.',
                'icon' => 'book-open',
                'type' => 'required',
                'reward_minutes' => 5,
                'penalty_minutes' => 5,
                'requires_approval' => true,
                'is_active' => true,
            ]
        );

        $rapikanKamar = $family->activities()->updateOrCreate(
            [
                'name' => 'Rapikan kamar',
            ],
            [
                'description' => 'Merapikan kamar sendiri.',
                'icon' => 'bed',
                'type' => 'bonus',
                'reward_minutes' => 5,
                'penalty_minutes' => 0,
                'requires_approval' => true,
                'is_active' => true,
            ]
        );

        $sholat->schedules()->updateOrCreate(
            ['frequency' => 'daily'],
            [
                'days_of_week' => null,
                'is_active' => true,
            ]
        );

        $ngaji->schedules()->updateOrCreate(
            ['frequency' => 'daily'],
            [
                'days_of_week' => null,
                'is_active' => true,
            ]
        );

        $pr->schedules()->updateOrCreate(
            ['frequency' => 'weekly'],
            [
                'days_of_week' => [1, 2, 3, 4, 5],
                'is_active' => true,
            ]
        );

        $rapikanKamar->schedules()->updateOrCreate(
            ['frequency' => 'weekly'],
            [
                'days_of_week' => [6, 7],
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Assign Activities
        |--------------------------------------------------------------------------
        */

        $budi->activities()->syncWithoutDetaching([
            $sholat->id,
            $pr->id,
            $ngaji->id,
            $rapikanKamar->id,
        ]);

        $aisyah->activities()->syncWithoutDetaching([
            $sholat->id,
            $ngaji->id,
            $rapikanKamar->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Privileges
        |--------------------------------------------------------------------------
        */

        $mainHp = $family->privileges()->updateOrCreate(
            [
                'name' => 'Main HP 20 menit',
            ],
            [
                'description' => 'Waktu bermain HP selama 20 menit.',
                'icon' => 'phone',
                'cost_minutes' => 20,
                'requires_approval' => true,
                'is_active' => true,
            ]
        );

        $jajan = $family->privileges()->updateOrCreate(
            [
                'name' => 'Jajan',
            ],
            [
                'description' => 'Tukar saldo untuk mendapatkan jajan.',
                'icon' => 'candy',
                'cost_minutes' => 10,
                'requires_approval' => true,
                'is_active' => true,
            ]
        );

        $nonton = $family->privileges()->updateOrCreate(
            [
                'name' => 'Nonton',
            ],
            [
                'description' => 'Menonton selama waktu yang disepakati.',
                'icon' => 'tv',
                'cost_minutes' => 20,
                'requires_approval' => true,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Assign Privileges
        |--------------------------------------------------------------------------
        */

        $budi->privileges()->syncWithoutDetaching([
            $mainHp->id,
            $jajan->id,
            $nonton->id,
        ]);

        $aisyah->privileges()->syncWithoutDetaching([
            $jajan->id,
            $nonton->id,
        ]);
    }
}