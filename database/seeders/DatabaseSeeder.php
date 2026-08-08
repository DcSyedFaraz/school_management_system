<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * LocationsAndUsersSeeder creates an admin account (admin@gmail.com),
     * two teacher accounts (user@gmail.com, user2@gmail.com — all password
     * 12345678) plus the region/district/ward/schools they belong to.
     *
     * MarksSeeder depends on LocationsAndUsersSeeder having run first — it
     * fakes marks for all 6 classes, across both schools and two exams, so
     * dashboards/reports/exports/PDF report cards have real data to render.
     */
    public function run(): void
    {
        $this->call([
            LocationsAndUsersSeeder::class,
            MarksSeeder::class,
        ]);
    }
}
