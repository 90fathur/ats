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
                'username'        => 'admin',
                'password'        => password_hash('admin', PASSWORD_BCRYPT),
                'nama'            => 'Administrator ATS Polman',
                'role'            => 'ADMIN',
                'kecamatan_akses' => null, // Akses seluruh kecamatan se-Polman
                'hp'              => '08114200000',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'username'        => 'petugas_polewali',
                'password'        => password_hash('admin', PASSWORD_BCRYPT),
                'nama'            => 'Tim Entri Kec. Polewali',
                'role'            => 'USER',
                'kecamatan_akses' => 'POLEWALI', // 1 Kecamatan
                'hp'              => '085145888881',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'username'        => 'petugas_gabungan',
                'password'        => password_hash('admin', PASSWORD_BCRYPT),
                'nama'            => 'Tim Entri Wonomulyo & Campalagian',
                'role'            => 'USER',
                'kecamatan_akses' => 'WONOMULYO,CAMPALAGIAN', // Multiple Kecamatan
                'hp'              => '082198765432',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'username'        => 'petugas',
                'password'        => password_hash('admin', PASSWORD_BCRYPT),
                'nama'            => 'Tim Entri Balanipa & Tinambung',
                'role'            => 'USER',
                'kecamatan_akses' => 'BALANIPA,TINAMBUNG', // Multiple Kecamatan
                'hp'              => '085299887711',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($users as $user) {
            $builder->insert($user);
        }
    }
}
