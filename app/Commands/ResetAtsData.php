<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ResetAtsData extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'ats:reset';
    protected $description = 'Kosongkan data ATS, log aktivitas, akun petugas non-admin, dan foto untuk persiapan pelatihan';
    protected $usage       = 'ats:reset [--force]';
    protected $options     = [
        '--force' => 'Jalankan reset tanpa prompt konfirmasi',
    ];

    public function run(array $params)
    {
        CLI::write("==================================================", 'yellow');
        CLI::write(" PERSIAPAN PELATIHAN: RESET DATA SISTEM PANTAU ATS", 'yellow');
        CLI::write("==================================================", 'yellow');

        $force = array_key_exists('force', $params) || CLI::getOption('force');

        if (!$force) {
            $confirm = CLI::prompt("Apakah Anda yakin ingin mengosongkan seluruh data ATS, log aktivitas, dan akun non-admin? (y/n)", ['y', 'n']);
            if (strtolower($confirm) !== 'y') {
                CLI::write("Operasi dibatalkan.", 'red');
                return;
            }
        }

        $db = \Config\Database::connect();

        try {
            // 1. Kosongkan tabel ats_data
            CLI::print("1. Mengosongkan data anak (ats_data)... ");
            $db->query("SET FOREIGN_KEY_CHECKS = 0;");
            $db->query("TRUNCATE TABLE `ats_data`;");
            CLI::write("BERHASIL!", 'green');

            // 2. Kosongkan log aktivitas
            CLI::print("2. Mengosongkan riwayat log (activity_logs)... ");
            $db->query("TRUNCATE TABLE `activity_logs`;");
            CLI::write("BERHASIL!", 'green');

            // 3. Hapus akun pengguna selain ADMIN
            CLI::print("3. Menghapus akun non-admin (hanya menyisakan akun ADMIN)... ");
            $db->query("DELETE FROM `users` WHERE UPPER(role) != 'ADMIN' AND username != 'admin';");
            $db->query("SET FOREIGN_KEY_CHECKS = 1;");
            CLI::write("BERHASIL!", 'green');

            // 4. Bersihkan foto rekonfirmasi di public/uploads/foto_rekonfirmasi/
            CLI::print("4. Membersihkan file foto dokumentasi lapangan... ");
            $photoDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'foto_rekonfirmasi';
            $deletedFiles = 0;
            if (is_dir($photoDir)) {
                $files = glob($photoDir . DIRECTORY_SEPARATOR . '*');
                foreach ($files as $file) {
                    if (is_file($file) && basename($file) !== '.gitkeep') {
                        @unlink($file);
                        $deletedFiles++;
                    }
                }
            }
            CLI::write("BERHASIL ({$deletedFiles} file dibersihkan)!", 'green');

            // 5. Catat log awal sistem bersih
            \App\Models\ActivityLogModel::record(
                'SYSTEM_RESET',
                'Database dan data ATS telah dikosongkan untuk persiapan pelatihan.',
                ['reset_by' => 'CLI Spark ats:reset']
            );

            // 6. Tampilkan daftar user admin yang tersisa
            CLI::newLine();
            CLI::write("STATUS AKUN ADMIN YANG TERSISA:", 'cyan');
            $adminUsers = $db->query("SELECT id, username, nama, role, hp FROM users ORDER BY id ASC")->getResultArray();
            $tableData = [];
            foreach ($adminUsers as $u) {
                $tableData[] = [$u['id'], $u['username'], $u['nama'], $u['role'], $u['hp'] ?? '-'];
            }
            CLI::table($tableData, ['ID', 'Username', 'Nama', 'Role', 'No. HP']);

            CLI::newLine();
            CLI::write("SUKSES: Sistem sekarang bersih dan siap digunakan untuk pelatihan!", 'green');
            CLI::write("Semua data ATS telah dikosongkan (ID reset kembali ke 1).", 'green');
        } catch (\Throwable $e) {
            CLI::error("Terjadi kesalahan saat mereset data: " . $e->getMessage());
        }
    }
}
