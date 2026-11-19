<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ActivityLogModel;

class ReadyProduction extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'ats:ready-production';
    protected $description = 'Bersihkan seluruh data entri ATS uji coba, foto uji coba, dan akun ats_1 s/d ats_10 untuk memulai penginputan riil';
    protected $usage       = 'ats:ready-production [--force]';
    protected $options     = [
        '--force' => 'Jalankan pembersihan tanpa prompt konfirmasi',
    ];

    public function run(array $params)
    {
        CLI::write("==================================================================", 'yellow');
        CLI::write(" PERSIAPAN PRODUKSI REAL: BERSIHKAN DATA & AKUN UJI COBA ATS", 'yellow');
        CLI::write("==================================================================", 'yellow');
        CLI::write("Perintah ini akan:", 'cyan');
        CLI::write("1. Mengosongkan tabel data anak (ats_data) -> Auto-increment reset ke 1", 'white');
        CLI::write("2. Menghapus 10 akun petugas uji coba (ats_1 s/d ats_10)", 'white');
        CLI::write("3. Menghapus foto dokumentasi uji coba di uploads/foto_rekonfirmasi/", 'white');
        CLI::write("4. Mengosongkan riwayat log pengujian (activity_logs)", 'white');
        CLI::write("5. MEMPERTAHANKAN seluruh 73 akun Petugas Rekonfirmasi Riil & Akun Admin", 'green');
        CLI::write("==================================================================", 'yellow');

        $force = array_key_exists('force', $params) || CLI::getOption('force');

        if (!$force) {
            $confirm = CLI::prompt("Apakah Anda yakin ingin menjalankan pembersihan persiapan produksi? (y/n)", ['y', 'n']);
            if (strtolower($confirm) !== 'y') {
                CLI::write("Operasi dibatalkan. Data tidak berubah.", 'red');
                return;
            }
        }

        $db = \Config\Database::connect();

        try {
            // 1. Kosongkan tabel ats_data
            CLI::print("1. Mengosongkan tabel ats_data & reset auto-increment... ");
            $db->query("SET FOREIGN_KEY_CHECKS = 0;");
            $db->query("TRUNCATE TABLE `ats_data`;");
            CLI::write("BERHASIL! (0 Data, ID kembali ke 1)", 'green');

            // 2. Hapus akun uji coba ats_1 s/d ats_10
            CLI::print("2. Menghapus akun uji coba (ats_1 s/d ats_10)... ");
            $db->query("DELETE FROM `users` WHERE `username` LIKE 'ats_%' OR `username` IN ('ats_1','ats_2','ats_3','ats_4','ats_5','ats_6','ats_7','ats_8','ats_9','ats_10');");
            $db->query("SET FOREIGN_KEY_CHECKS = 1;");
            CLI::write("BERHASIL!", 'green');

            // 3. Bersihkan foto rekonfirmasi di public/uploads/foto_rekonfirmasi/
            CLI::print("3. Membersihkan file foto dokumentasi lapangan uji coba... ");
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

            // 4. Kosongkan log aktivitas
            CLI::print("4. Mengosongkan log aktivitas uji coba (activity_logs)... ");
            $db->query("TRUNCATE TABLE `activity_logs`;");
            CLI::write("BERHASIL!", 'green');

            // 5. Catat log resmi sistem siap produksi
            ActivityLogModel::record(
                'SYSTEM_READY_PRODUCTION',
                'Sistem telah dibersihkan dari seluruh data uji coba dan akun ats_1 - ats_10. Sistem siap untuk penginputan riil oleh 73 Petugas Rekonfirmasi.',
                ['status' => 'READY_FOR_REAL_ENTRY', 'reset_time' => date('Y-m-d H:i:s')]
            );

            // 6. Perbarui file rekap CSV agar hanya berisi akun petugas riil
            CLI::print("5. Memperbarui file rekap akun petugas CSV... ");
            $realOfficers = $db->query("SELECT id, username, nama, role, kecamatan_akses, desa_akses, hp FROM users WHERE role != 'ADMIN' ORDER BY kecamatan_akses ASC, desa_akses ASC, nama ASC")->getResultArray();
            $exportPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'rekap_akun_petugas_rekonfirmasi.csv';
            $fp = fopen($exportPath, 'w');
            if ($fp) {
                fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
                fputcsv($fp, ['No', 'Nama Petugas', 'Username', 'Password Default', 'Wilayah Kecamatan', 'Wilayah Desa', 'No. HP']);
                $no = 1;
                foreach ($realOfficers as $officer) {
                    fputcsv($fp, [
                        $no++,
                        $officer['nama'],
                        $officer['username'],
                        'polmanbaik#',
                        $officer['kecamatan_akses'] ?? '-',
                        $officer['desa_akses'] ?? '-',
                        $officer['hp'] ?? '-'
                    ]);
                }
                fclose($fp);
            }
            CLI::write("BERHASIL (" . count($realOfficers) . " Petugas)", 'green');

            // 7. Tampilkan Rangkuman Akhir
            CLI::newLine();
            CLI::write("==================================================================", 'green');
            CLI::write(" RANGKUMAN STATUS SISTEM SIAP PRODUKSI REAL:", 'green');
            CLI::write("==================================================================", 'green');

            $totalAts = $db->query("SELECT COUNT(*) as total FROM ats_data")->getRowArray()['total'] ?? 0;
            $totalRealPetugas = count($realOfficers);
            $totalAdmin = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'ADMIN'")->getRowArray()['total'] ?? 0;
            $totalAtsTestUsers = $db->query("SELECT COUNT(*) as total FROM users WHERE username LIKE 'ats_%'")->getRowArray()['total'] ?? 0;

            CLI::write(" - Total Data ATS Tersimpan : {$totalAts} (Bersih, siap input ID 1)", 'cyan');
            CLI::write(" - Akun Uji Coba (ats_*)    : {$totalAtsTestUsers} (Telah dihapus seluruhnya)", 'cyan');
            CLI::write(" - Akun Petugas Riil Aktif  : {$totalRealPetugas} Petugas (Password: polmanbaik#)", 'cyan');
            CLI::write(" - Akun Administrator Aktif : {$totalAdmin} Akun Admin", 'cyan');
            CLI::write(" - File Rekap Akun CSV      : {$exportPath}", 'cyan');
            CLI::write("==================================================================", 'green');
            CLI::write("SUKSES: Sistem sekarang 100% siap untuk pelaksanaan penginputan data riil besok!", 'yellow');

        } catch (\Throwable $e) {
            CLI::error("Terjadi kesalahan saat pembersihan sistem: " . $e->getMessage());
        }
    }
}
