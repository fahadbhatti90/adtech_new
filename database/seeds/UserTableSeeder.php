<?php

use App\Models\AgencyModels\AgencyModel;

//use App\Models\User;
//use App\Models\User;
use App\Models\CustomUserModel;
use App\Models\UserRoles\UserRole;
use App\Models\UserRolesModels\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;


class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checkSuperAdmin = User::where('id', 1)->first();
        if (!empty($checkSuperAdmin)) {
            $accountUpdate = User::where('id', 1)->update([
                'name' => 'Pulse Advertising',
                'email' => 'super-admin-adtech@codeinformatics.com',
                'password' => bcrypt('123456')
            ]);
        } else {
            $superAdminRole = Role::where(['name' => 'Super Admin']);
            $adminRole = Role::where(['name' => 'Admin']);
            $managerRole = Role::where(['name' => 'Manager']);
            $superAdmin = User::create([
                'name' => 'Pulse Advertising',
                'email' => 'super-admin-adtech@codeinformatics.com',
                'password' => bcrypt('123456')
            ]);
            UserRole::create([
                'roleId' => 1,
                'userId' => 1
            ]);//end create function
        }

    }
}
