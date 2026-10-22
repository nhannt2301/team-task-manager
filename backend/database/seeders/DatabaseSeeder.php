<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Constants\CUser;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => CUser::ROLE_ADMIN,
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Standard User',
                'password' => Hash::make('password'),
                'role' => CUser::ROLE_USER,
            ]
        );

        $users = User::factory()
            ->count(10)
            ->create(['role' => CUser::ROLE_USER, 'password' => Hash::make('password')]);

        $this->seedTasks();
    }

    private function seedTasks(): void
    {
        $total = 1000000;
        $sizeBatch = 1000;
        // Retrieve valid assignee IDs
        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command?->warn('No users found. Skipping task seeding.');
            return;
        }

        $userIdCount = count($userIds);
        $statuses = ['todo', 'in_progress', 'done'];
        $now = Carbon::now()->toDateTimeString();

        // Pre-generate
        $titlePool = [];
        for ($i = 0; $i < 50; $i++) {
            $titlePool[] = fake()->sentence(6);
        }

        $descriptionPool = [];
        for ($i = 0; $i < 50; $i++) {
            $descriptionPool[] = fake()->paragraph(3);
        }

        $totalBatches = (int) ceil($total / $sizeBatch);

        // Display console progress bar
        $progressBar = $this->command?->getOutput()->createProgressBar($total);
        $progressBar?->start();

        for ($batch = 0; $batch < $totalBatches; $batch++) {
            $records = [];
            $recordsCount = min($sizeBatch, $total - ($batch * $sizeBatch));

            for ($i = 0; $i < $recordsCount; $i++) {
                $randomDays = mt_rand(0, 180);

                $records[] = [
                    'title' => $titlePool[array_rand($titlePool)],
                    'description' => $descriptionPool[array_rand($descriptionPool)],
                    'status' => $statuses[array_rand($statuses)],
                    'assigned_to' => $userIds[mt_rand(0, $userIdCount - 1)],
                    'due_date' => Carbon::now()->addDays($randomDays)->format('Y-m-d'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Direct table insertion
            DB::table('tasks')->insert($records);

            unset($records);

            $progressBar?->advance($recordsCount);
        }

        $progressBar?->finish();
        $this->command?->newLine();
    }
}
