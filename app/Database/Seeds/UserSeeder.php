<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users');

        $users = [
            [
                'username'   => 'ari',
                'password'   => password_hash('ari', PASSWORD_BCRYPT),
                'nama'       => 'Arifuddin',
                'role'       => 'USER',
                'hp'         => '085145888881',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username'   => 'admin',
                'password'   => password_hash('ari', PASSWORD_BCRYPT),
                'nama'       => 'Arifuddin (Admin Utama)',
                'role'       => 'ADMIN',
                'hp'         => '08114200000',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username'   => 'peserta',
                'password'   => password_hash('edudigital', PASSWORD_BCRYPT),
                'nama'       => 'Petugas Lapangan Sulbar',
                'role'       => 'USER',
                'hp'         => '082212345678',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($users as $user) {
            $existing = $builder->where('username', $user['username'])->get()->getRow();
            if (!$existing) {
                $builder->insert($user);
            } else {
                $builder->where('username', $user['username'])->update($user);
            }
        }
    }
}
