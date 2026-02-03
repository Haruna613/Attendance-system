<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::create([
            'name' => '管理者 太郎',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 1,
            'email_verified_at' => now(),
        ]);

        $staffs = [
            ['name' => '西 伶奈', 'email' => 'reina.n@coachtech.com'],
            ['name' => '山田 太郎', 'email' => 'taro.y@coachtech.com'],
            ['name' => '増田 一世', 'email' => 'issei.m@coachtech.com'],
            ['name' => '山本 敬吉', 'email' => 'keiichi.y@coachtech.com'],
            ['name' => '秋田 朋美', 'email' => 'tomomi.a@coachtech.com'],
            ['name' => '中西 教夫', 'email' => 'norio.n@coachtech.com'],
        ];

        foreach ($staffs as $staff) {
            $user = User::create([
                'name' => $staff['name'],
                'email' => $staff['email'],
                'password' => Hash::make('password'),
                'role' => 0,
                'email_verified_at' => now(),
            ]);

            $startOfMonth = Carbon::create(2026, 1, 1);
            $endOfMonth = Carbon::create(2026, 1, 31);
            $firstSaturdayFound = false;

            for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
                if ($date->isSunday()) {
                    continue;
                }
                if ($date->isSaturday() && !$firstSaturdayFound) {
                    $firstSaturdayFound = true;
                    continue;
                }
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'punch_in' => '09:00:00',
                    'punch_out' => '18:00:00',
                    'status' => 0,
                    'remarks' => '通常出勤',
                ]);

                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => '12:00:00',
                    'end_time' => '13:00:00',
                ]);
            }
        }
    }
}
