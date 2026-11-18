<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Models\ActivityLogModel;

class SeedUjiCoba extends BaseCommand
{
    protected $group       = 'ATS';
    protected $name        = 'ats:seed-ujicoba';
    protected $description = 'Tambah / Daftarkan 10 Akun Uji Coba (ats_1 s/d ats_10) di Kecamatan Polewali (Semua Kelurahan)';
    protected $usage       = 'ats:seed-ujicoba';

    public function run(array $params)
    {
        CLI::write("==================================================================", 'cyan');
        CLI::write(" PENDAFTARAN 10 AKUN UJI COBA ENTRI DATA ATS KAB. POLEWALI MANDAR", 'cyan');
        CLI::write(" Username : ats_1 s/d ats_10", 'yellow');
        CLI::write(" Password : polmanbaik#", 'yellow');
        CLI::write(" Wilayah  : Kecamatan Polewali (Akses Semua Kelurahan)", 'yellow');
        CLI::write("==================================================================", 'cyan');

        $db = \Config\Database::connect();
        $defaultHash = password_hash('polmanbaik#', PASSWORD_BCRYPT);
        $insertedCount = 0;
        $updatedCount = 0;

        $tableRows = [];

        for ($i = 1; $i <= 10; $i++) {
            $username = "ats_{$i}";
            $numFormatted = str_pad($i, 2, '0', STR_PAD_LEFT);
            $nama = "Petugas Uji Coba ATS {$numFormatted}";

            $existing = $db->table('users')
                ->where('username', $username)
                ->get()
                ->getRowArray();

            $userData = [
                'username'        => $username,
                'nama'            => $nama,
                'role'            => 'USER',
                'kecamatan_akses' => 'Polewali',
                'desa_akses'      => null, // Null / Empty = Semua Kelurahan di Kecamatan Polewali
                'hp'              => null,
                'updated_at'      => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $userData['password'] = $defaultHash;
                $db->table('users')->where('id', $existing['id'])->update($userData);
                $updatedCount++;
            } else {
                $userData['password']   = $defaultHash;
                $userData['created_at'] = date('Y-m-d H:i:s');
                $db->table('users')->insert($userData);
                $insertedCount++;
            }

            $tableRows[] = [
                $i,
                $nama,
                $username,
                'polmanbaik#',
                'Polewali',
                'Semua Kelurahan',
                '-'
            ];
        }

        CLI::newLine();
        CLI::table($tableRows, [
            'No', 'Nama Akun', 'Username', 'Password', 'Kecamatan', 'Kelurahan', 'No. HP'
        ]);

        ActivityLogModel::record(
            'SEED_UJICOBA',
            "Administrator menambahkan/memperbarui 10 akun petugas uji coba (ats_1 s/d ats_10) untuk Kecamatan Polewali (Akses Semua Kelurahan).",
            ['inserted' => $insertedCount, 'updated' => $updatedCount]
        );

        CLI::newLine();
        CLI::write("==================================================================", 'green');
        CLI::write(" SUKSES: 10 Akun Uji Coba Berhasil Didaftarkan!", 'green');
        CLI::write(" - Baru Ditambahkan : {$insertedCount} akun", 'green');
        CLI::write(" - Diperbarui       : {$updatedCount} akun", 'green');
        CLI::write(" - Default Password : polmanbaik#", 'white');
        CLI::write(" - Wilayah Tugas    : Kecamatan Polewali (Semua Kelurahan)", 'yellow');
        CLI::write("==================================================================", 'green');
    }
}
