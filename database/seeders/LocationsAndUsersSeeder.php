<?php

namespace Database\Seeders;

use App\Models\Districts;
use App\Models\Regions;
use App\Models\Schools;
use App\Models\User;
use App\Models\Wards;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds the minimum location hierarchy (region/district/ward/schools) plus
 * one admin account and one teacher account per school, so the app can be
 * logged into and exercised end-to-end without a production database.
 *
 * None of these models declare $fillable (base Eloquent default is
 * $guarded = ['*']), so every write here uses the same
 * "new Model; $model['field'] = value; $model->save();" pattern the rest of
 * this codebase already uses (see MarksImport, UploadController) instead of
 * create()/firstOrCreate(), which would throw MassAssignmentException.
 *
 * Safe to re-run: every row is looked up by its natural key first.
 */
class LocationsAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        $region = Regions::where('regionName', 'Dodoma')->first();
        if (!$region) {
            $region = new Regions;
            $region['regionName'] = 'Dodoma';
            $region['regionCode'] = 'DDM';
            $region['isActive'] = '1';
            $region['isDeleted'] = '0';
            $region->save();
        }

        $district = Districts::where('districtName', 'Dodoma Mjini')->first();
        if (!$district) {
            $district = new Districts;
            $district['districtId'] = (int) Districts::max('districtId') + 1;
            $district['districtName'] = 'Dodoma Mjini';
            $district['districtCode'] = 'DDM01';
            $district['isActive'] = '1';
            $district['isDeleted'] = '0';
            $district->save();
        }

        $ward = Wards::where('wardName', 'Kati')->first();
        if (!$ward) {
            $ward = new Wards;
            $ward['wardName'] = 'Kati';
            $ward['wardCode'] = 'KT01';
            $ward['isActive'] = '1';
            $ward['isDeleted'] = '0';
            $ward->save();
        }

        $schoolOne = Schools::where('schoolName', 'Shule ya Msingi Mfano A')->first();
        if (!$schoolOne) {
            $schoolOne = new Schools;
            $schoolOne['schoolName'] = 'Shule ya Msingi Mfano A';
            $schoolOne['isActive'] = '1';
            $schoolOne['isDeleted'] = '0';
            $schoolOne->save();
        }

        $schoolTwo = Schools::where('schoolName', 'Shule ya Msingi Mfano B')->first();
        if (!$schoolTwo) {
            $schoolTwo = new Schools;
            $schoolTwo['schoolName'] = 'Shule ya Msingi Mfano B';
            $schoolTwo['isActive'] = '1';
            $schoolTwo['isDeleted'] = '0';
            $schoolTwo->save();
        }

        // --- Admin account ---
        $admin = User::where('email', 'admin@gmail.com')->first();
        if (!$admin) {
            $admin = new User;
            $admin['token'] = Str::random(40);
        }
        $admin['user_name'] = 'admin';
        $admin['userName'] = 'System Admin';
        $admin['email'] = 'admin@gmail.com';
        $admin['mobile'] = '0700000001';
        $admin['password'] = Hash::make('12345678');
        $admin['userType'] = 'A';
        // Admin oversees everything — not scoped to one school/location.
        $admin['schoolId'] = null;
        $admin['regionId'] = null;
        $admin['districtId'] = null;
        $admin['wardId'] = null;
        $admin['isActive'] = '1';
        $admin['isDeleted'] = '0';
        $admin->save();

        // --- Teacher accounts, one per school ---
        $teacherOne = User::where('email', 'user@gmail.com')->first();
        if (!$teacherOne) {
            $teacherOne = new User;
            $teacherOne['token'] = Str::random(40);
        }
        $teacherOne['user_name'] = 'user';
        $teacherOne['userName'] = 'Mwalimu Mkuu A';
        $teacherOne['email'] = 'user@gmail.com';
        $teacherOne['mobile'] = '0700000002';
        $teacherOne['password'] = Hash::make('12345678');
        $teacherOne['userType'] = 'T';
        $teacherOne['schoolId'] = $schoolOne->schoolId;
        $teacherOne['regionId'] = $region->regionId;
        $teacherOne['districtId'] = $district->districtId;
        $teacherOne['wardId'] = $ward->wardId;
        $teacherOne['isActive'] = '1';
        $teacherOne['isDeleted'] = '0';
        $teacherOne->save();

        $teacherTwo = User::where('email', 'user2@gmail.com')->first();
        if (!$teacherTwo) {
            $teacherTwo = new User;
            $teacherTwo['token'] = Str::random(40);
        }
        $teacherTwo['user_name'] = 'user2';
        $teacherTwo['userName'] = 'Mwalimu Mkuu B';
        $teacherTwo['email'] = 'user2@gmail.com';
        $teacherTwo['mobile'] = '0700000003';
        $teacherTwo['password'] = Hash::make('12345678');
        $teacherTwo['userType'] = 'T';
        $teacherTwo['schoolId'] = $schoolTwo->schoolId;
        $teacherTwo['regionId'] = $region->regionId;
        $teacherTwo['districtId'] = $district->districtId;
        $teacherTwo['wardId'] = $ward->wardId;
        $teacherTwo['isActive'] = '1';
        $teacherTwo['isDeleted'] = '0';
        $teacherTwo->save();

        $this->command?->info('Locations + users seeded:');
        $this->command?->info('  Admin: admin@gmail.com / 12345678');
        $this->command?->info('  Teacher (School A): user@gmail.com / 12345678');
        $this->command?->info('  Teacher (School B): user2@gmail.com / 12345678');
    }
}
