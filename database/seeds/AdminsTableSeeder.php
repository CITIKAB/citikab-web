<?php

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->delete();
        DB::table('role_user')->delete();
        DB::table('roles')->delete();
        DB::table('permission_role')->delete();

        DB::table('admins')->insert([
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@trioangle.com', 'password' => Hash::make('123456'), 'status' => 'Active', 'created_at' => '2016-04-17 00:00:00'],
            ['id' => 2, 'username' => 'dispatcher', 'email' => 'dispatcher@trioangle.com', 'password' => Hash::make('123456'), 'status' => 'Active', 'created_at' => '2016-04-17 00:00:00'],
        ]);

         DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin', 'display_name' => 'Admin', 'description' => 'Admin User', 'created_at' => '2016-04-17 00:00:00','updated_at' => '2016-04-17 00:00:00'],
            ['id' => 2, 'name' => 'subadmin', 'display_name' => 'subadmin', 'description' => 'subadmin', 'created_at' => '2016-04-17 00:10:00','updated_at' => '2016-04-17 00:00:00'],
            ['id' => 3, 'name' => 'accountant', 'display_name' => 'accountant', 'description' => 'accountant', 'created_at' => '2016-04-17 00:10:00','updated_at' => '2016-04-17 00:00:00'],
            ['id' => 4, 'name' => 'Dispatcher', 'display_name' => 'Dispatcher', 'description' => 'Dispatcher', 'created_at' => '2016-04-17 00:10:00','updated_at' => '2016-04-17 00:00:00']
        ]);

        DB::table('role_user')->insert([
            ['user_id' => 1, 'role_id' => '1'],
            ['user_id' => 2, 'role_id' => '4'],
        ]);

        $permissions = DB::table('permissions')->get();

        $permissions_data = [];

        foreach ($permissions as $key => $value) {
            $permissions_data[] = [
                'permission_id' => $value->id,
                'role_id'       => '1'
            ];
            if ($value->name == 'manage_manual_booking') {
                $permissions_data[] = [
                    'permission_id' => $value->id,
                    'role_id'       => '4'
                ];
            }
        }

        DB::table('permission_role')->insert($permissions_data);
    }
}
