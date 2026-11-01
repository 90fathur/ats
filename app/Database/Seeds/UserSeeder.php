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
                'kecamatan_akses' => null, // Akses seluruh se-Kabupaten
                'desa_akses'      => null, // Akses seluruh Desa/Kelurahan
                'hp'              => '08114200000',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'username'        => 'petugas_madatte',
                'password'        => password_hash('admin', PASSWORD_BCRYPT),
                'nama'            => 'Tim Entri Kelurahan Madatte',
                'role'            => 'USER',
                'kecamatan_akses' => 'Polewali',
                'desa_akses'      => 'Madate', // 1 Kelurahan/Desa spesifik
                'hp'              => '085145888881',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'username'        => 'petugas_pekkabata',
                'password'        => password_hash('admin', PASSWORD_BCRYPT),
                'nama'            => 'Tim Entri Kel. Pekkabata & Takatidung',
                'role'            => 'USER',
                'kecamatan_akses' => 'Polewali',
                'desa_akses'      => 'Pekkabata,Takatidung', // Multiple Desa
                'hp'              => '082198765432',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'username'        => 'petugas_wonomulyo',
                'password'        => password_hash('admin', PASSWORD_BCRYPT),
                'nama'            => 'Tim Entri Desa Sidorejo',
                'role'            => 'USER',
                'kecamatan_akses' => 'Wonomulyo',
                'desa_akses'      => 'Sidorejo', // 1 Desa
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
