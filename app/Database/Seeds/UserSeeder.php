<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users');

        // Truncate to clean any old accounts
        $builder->truncate();

        $users = [
            [
                'username'   => 'admin',
                'password'   => password_hash('admin', PASSWORD_BCRYPT),
                'nama'       => 'Administrator ATS',
                'role'       => 'ADMIN',
                'hp'         => '08114200000',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username'   => 'petugas',
                'password'   => password_hash('admin', PASSWORD_BCRYPT),
                'nama'       => 'Petugas Lapangan ATS',
                'role'       => 'USER',
                'hp'         => '085145888881',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($users as $user) {
            $builder->insert($user);
        }
    }
}
