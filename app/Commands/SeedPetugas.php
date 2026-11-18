<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SeedPetugas extends BaseCommand
{
    protected $group       = 'ATS';
    protected $name        = 'ats:seed-petugas';
    protected $description = 'Seed & Daftarkan Petugas Rekonfirmasi dengan password default polmanbaik# secara cerdas';
    protected $usage       = 'ats:seed-petugas [--fresh]';
    protected $options     = [
        '--fresh' => 'Hapus semua akun petugas non-admin sebelum import (hanya menyisakan akun admin)',
    ];

    /**
     * Master data 73 Petugas Rekonfirmasi berdasarkan Dokumen Rekap Usulan Resmi
     */
    protected array $petugasList = [
        // DESA BATETANGNGA (Kec. Binuang) - 9 Petugas
        ['nama' => 'ABDUL ASIZ', 'jk' => 'L', 'desa' => 'Batetangnga', 'hp' => '085341998804'],
        ['nama' => 'IDA MULYANTI', 'jk' => 'P', 'desa' => 'Batetangnga', 'hp' => '082346499464'],
        ['nama' => 'MISBAHUDDIN', 'jk' => 'L', 'desa' => 'Batetangnga', 'hp' => '082266510945'],
        ['nama' => 'BAHRIL RAIZ', 'jk' => 'L', 'desa' => 'Batetangnga', 'hp' => '0895805005586'],
        ['nama' => 'BUSTAMIN B', 'jk' => 'L', 'desa' => 'Batetangnga', 'hp' => '081342686336'],
        ['nama' => 'MASDIANA', 'jk' => 'P', 'desa' => 'Batetangnga', 'hp' => '082188991322'],
        ['nama' => 'HASBULLAH', 'jk' => 'L', 'desa' => 'Batetangnga', 'hp' => '085397050122'],
        ['nama' => 'SITI ASTRI AYU PRATIWI', 'jk' => 'P', 'desa' => 'Batetangnga', 'hp' => '085288975162'],
        ['nama' => 'ARDIMAN', 'jk' => 'L', 'desa' => 'Batetangnga', 'hp' => null],

        // DESA AMOLA (Kec. Binuang) - 3 Petugas
        ['nama' => 'MUHAMMAD DAUD', 'jk' => 'L', 'desa' => 'Amola', 'hp' => null],
        ['nama' => 'MUHAMMAD TAUFIK SYAM', 'jk' => 'L', 'desa' => 'Amola', 'hp' => null],
        ['nama' => 'ILHAM', 'jk' => 'L', 'desa' => 'Amola', 'hp' => null],

        // DESA AMMASSANGAN (Kec. Binuang) - 6 Petugas
        ['nama' => 'MUHAMMAD ARHAM SIUDIN, S.IP', 'jk' => 'L', 'desa' => 'Amassangan', 'hp' => '085342042132'],
        ['nama' => 'HARBIYAH, S,Sos', 'jk' => 'P', 'desa' => 'Amassangan', 'hp' => '085348259454'],
        ['nama' => 'IDRIS AFFANDI', 'jk' => 'L', 'desa' => 'Amassangan', 'hp' => '081244944788'],
        ['nama' => 'MUHSIN YUSUF', 'jk' => 'L', 'desa' => 'Amassangan', 'hp' => '082396217677'],
        ['nama' => 'NELVIANA', 'jk' => 'P', 'desa' => 'Amassangan', 'hp' => '081350335094'],
        ['nama' => 'MULIANA', 'jk' => 'P', 'desa' => 'Amassangan', 'hp' => null],

        // DESA KUAJANG (Kec. Binuang) - 9 Petugas
        ['nama' => 'YERIKE', 'jk' => 'P', 'desa' => 'Kuajang', 'hp' => '081244052152'],
        ['nama' => 'NURMADINA', 'jk' => 'P', 'desa' => 'Kuajang', 'hp' => '089522639100'],
        ['nama' => 'NURHAYATI ABU', 'jk' => 'P', 'desa' => 'Kuajang', 'hp' => '089696323544'],
        ['nama' => 'SYAHARUDDIN, S.Pd', 'jk' => 'L', 'desa' => 'Kuajang', 'hp' => null],
        ['nama' => 'SRI AYU ANDRIANI S,S.Sy', 'jk' => 'P', 'desa' => 'Kuajang', 'hp' => null],
        ['nama' => 'NUR ANNISA, S.IP', 'jk' => 'P', 'desa' => 'Kuajang', 'hp' => null],
        ['nama' => 'SINAR WAHYUNI, S.Ak', 'jk' => 'P', 'desa' => 'Kuajang', 'hp' => null],
        ['nama' => 'IRAWATI,S.Pd', 'jk' => 'P', 'desa' => 'Kuajang', 'hp' => null],
        ['nama' => 'WAHYU CAHYANINGSIH', 'jk' => 'P', 'desa' => 'Kuajang', 'hp' => null],

        // DESA TONYAMAN (Kec. Binuang) - 12 Petugas
        ['nama' => 'MAHMUD', 'jk' => 'L', 'desa' => 'Tonyaman', 'hp' => '085298473988'],
        ['nama' => 'TARDI', 'jk' => 'L', 'desa' => 'Tonyaman', 'hp' => '085394255646'],
        ['nama' => 'MUHAMMAD ALBAR', 'jk' => 'L', 'desa' => 'Tonyaman', 'hp' => '085242621177'],
        ['nama' => 'WAHYUNI', 'jk' => 'P', 'desa' => 'Tonyaman', 'hp' => '082152373813'],
        ['nama' => 'SARKIYAH', 'jk' => 'P', 'desa' => 'Tonyaman', 'hp' => '085242972201'],
        ['nama' => 'DAHLIA', 'jk' => 'P', 'desa' => 'Tonyaman', 'hp' => '082348095275'],
        ['nama' => 'ANDI SUCI HANDAYANI,B S.Pd', 'jk' => 'P', 'desa' => 'Tonyaman', 'hp' => '0895374558555'],
        ['nama' => 'ANDI WINDY AYU LESTARI, S.Pd', 'jk' => 'P', 'desa' => 'Tonyaman', 'hp' => '081527309611'],
        ['nama' => 'IMRAN S,Pd', 'jk' => 'L', 'desa' => 'Tonyaman', 'hp' => '082347408068'],
        ['nama' => 'NUR ANNISA,S.Pd', 'jk' => 'P', 'desa' => 'Tonyaman', 'hp' => '085341666866'],
        ['nama' => 'MEGAWATI', 'jk' => 'P', 'desa' => 'Tonyaman', 'hp' => '085342170926'],
        ['nama' => 'NUR ANNISA AULIAH', 'jk' => 'P', 'desa' => 'Tonyaman', 'hp' => '085256362759'],

        // KELURAHAN KELAPA DUA (Kec. Anreapi) - 2 Petugas
        ['nama' => 'SATRIANI', 'jk' => 'P', 'desa' => 'Kelapa Dua', 'hp' => null],
        ['nama' => 'HERMAN SAID', 'jk' => 'L', 'desa' => 'Kelapa Dua', 'hp' => null],

        // DESA MAMMI (Kec. Binuang) - 2 Petugas
        ['nama' => 'ABD RAUF, S.Pd', 'jk' => 'L', 'desa' => 'Mammi', 'hp' => null],
        ['nama' => 'SUBAER, SH', 'jk' => 'L', 'desa' => 'Mammi', 'hp' => null],

        // DESA PAPANDANGAN (Kec. Anreapi) - 2 Petugas
        ['nama' => 'HASLINDA,S.S.Pd', 'jk' => 'P', 'desa' => 'Papandangan', 'hp' => '082348305764'],
        ['nama' => 'KIKI MAYA SARI', 'jk' => 'P', 'desa' => 'Papandangan', 'hp' => '085255353688'],

        // DESA MIRRING (Kec. Binuang) - 8 Petugas
        ['nama' => 'HASLINDA S.Pd', 'jk' => 'P', 'desa' => 'Mirring', 'hp' => '082349344695'],
        ['nama' => 'MUH ADNAN,S.Sos', 'jk' => 'L', 'desa' => 'Mirring', 'hp' => '082189486471'],
        ['nama' => 'MUH. SAPRIL', 'jk' => 'L', 'desa' => 'Mirring', 'hp' => '082153574191'],
        ['nama' => 'ABD.RASYD', 'jk' => 'P', 'desa' => 'Mirring', 'hp' => '082193233212'],
        ['nama' => 'HASRI,S.Pd', 'jk' => 'L', 'desa' => 'Mirring', 'hp' => '081240141078'],
        ['nama' => 'HERMAWAN,S.Sos', 'jk' => 'L', 'desa' => 'Mirring', 'hp' => '085397137721'],
        ['nama' => 'AHMAD AFANDI,S.Pd.I', 'jk' => 'L', 'desa' => 'Mirring', 'hp' => '085134520082'],
        ['nama' => 'PADENRENGI', 'jk' => 'L', 'desa' => 'Mirring', 'hp' => null],

        // DESA KUNYI (Kec. Anreapi) - 2 Petugas
        ['nama' => 'DARWAN', 'jk' => 'L', 'desa' => 'Kunyi', 'hp' => null],
        ['nama' => 'SURYADI', 'jk' => 'L', 'desa' => 'Kunyi', 'hp' => null],

        // DESA REA (Kec. Binuang) - 5 Petugas
        ['nama' => 'RAKHMAT ADITYA WARDHANA', 'jk' => 'L', 'desa' => 'Rea', 'hp' => '085242434748'],
        ['nama' => 'WAHDANIA', 'jk' => 'P', 'desa' => 'Rea', 'hp' => '085334576949'],
        ['nama' => 'NURENI', 'jk' => 'P', 'desa' => 'Rea', 'hp' => '0812421610022'],
        ['nama' => 'MARDIANA', 'jk' => 'P', 'desa' => 'Rea', 'hp' => '085398229985'],
        ['nama' => 'ANZHAR AHMAD', 'jk' => 'P', 'desa' => 'Rea', 'hp' => '085256718493'],

        // DESA DUAMPANUA (Kec. Anreapi) - 5 Petugas
        ['nama' => 'FUAD HASAN PALOLOANG', 'jk' => 'L', 'desa' => 'Duampanua', 'hp' => '085341531888'],
        ['nama' => 'WINDA SYARI', 'jk' => 'P', 'desa' => 'Duampanua', 'hp' => '081233415898'],
        ['nama' => 'NURHADI', 'jk' => 'L', 'desa' => 'Duampanua', 'hp' => '083123578583'],
        ['nama' => 'ABDUL RAHMAN M', 'jk' => 'L', 'desa' => 'Duampanua', 'hp' => '0895635078325'],
        ['nama' => 'MUH AS\'AD', 'jk' => 'L', 'desa' => 'Duampanua', 'hp' => '082246229610'],

        // KELURAHAN ANREAPI (Kec. Anreapi) - 2 Petugas
        ['nama' => 'KURNIA SARI J, S.IP', 'jk' => 'P', 'desa' => 'Anreapi', 'hp' => null],
        ['nama' => 'RISKAWATI, S.IP', 'jk' => 'P', 'desa' => 'Anreapi', 'hp' => null],

        // DESA KALEOK (Kec. Binuang) - 2 Petugas
        ['nama' => 'PAISAH PEBRIANI', 'jk' => 'P', 'desa' => 'Kaleok', 'hp' => null],
        ['nama' => 'KARMI', 'jk' => 'P', 'desa' => 'Kaleok', 'hp' => null],

        // DESA PAKU (Kec. Binuang) - 5 Petugas
        ['nama' => 'Amirullah, S.Sos', 'jk' => 'L', 'desa' => 'Paku', 'hp' => '081342578747'],
        ['nama' => 'ABD. Azis', 'jk' => 'L', 'desa' => 'Paku', 'hp' => '082246123326'],
        ['nama' => 'M. Ardiansyah, S.Kom', 'jk' => 'L', 'desa' => 'Paku', 'hp' => '085240624143'],
        ['nama' => 'Ratnah, S.HI', 'jk' => 'P', 'desa' => 'Paku', 'hp' => '081241098307'],
        ['nama' => 'Nurulfatanah, S.Sos', 'jk' => 'P', 'desa' => 'Paku', 'hp' => '085280228507'],

        // KECAMATAN POLEWALI (UJI COBA ATS) - 10 Akun Uji Coba (Akses Semua Kelurahan)
        ['nama' => 'Petugas Uji Coba ATS 01', 'jk' => 'L', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_1', 'hp' => null],
        ['nama' => 'Petugas Uji Coba ATS 02', 'jk' => 'P', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_2', 'hp' => null],
        ['nama' => 'Petugas Uji Coba ATS 03', 'jk' => 'L', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_3', 'hp' => null],
        ['nama' => 'Petugas Uji Coba ATS 04', 'jk' => 'P', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_4', 'hp' => null],
        ['nama' => 'Petugas Uji Coba ATS 05', 'jk' => 'L', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_5', 'hp' => null],
        ['nama' => 'Petugas Uji Coba ATS 06', 'jk' => 'P', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_6', 'hp' => null],
        ['nama' => 'Petugas Uji Coba ATS 07', 'jk' => 'L', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_7', 'hp' => null],
        ['nama' => 'Petugas Uji Coba ATS 08', 'jk' => 'P', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_8', 'hp' => null],
        ['nama' => 'Petugas Uji Coba ATS 09', 'jk' => 'L', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_9', 'hp' => null],
        ['nama' => 'Petugas Uji Coba ATS 10', 'jk' => 'P', 'kecamatan' => 'Polewali', 'desa' => null, 'username' => 'ats_10', 'hp' => null],
    ];

    public function run(array $params)
    {
        $totalPetugas = count($this->petugasList);
        CLI::write("==================================================================", 'cyan');
        CLI::write(" IMPORT CERDAS {$totalPetugas} AKUN PETUGAS REKONFIRMASI ATS KAB. POLEWALI MANDAR", 'cyan');
        CLI::write(" Password Default: polmanbaik#  |  Role: USER (Tim Entri Lapangan)", 'yellow');
        CLI::write("==================================================================", 'cyan');

        $isFresh = array_key_exists('fresh', $params) || CLI::getOption('fresh');

        $db = \Config\Database::connect();

        // 1. Muat relasi Desa -> Kecamatan dari public/data/wilayah_kecamatan.json
        $wilayahMap = $this->loadWilayahMapping();

        // 2. Handle opsi --fresh jika pengguna ingin pembersihan total
        if ($isFresh) {
            $confirm = CLI::prompt("Perhatian: Opsi --fresh akan menghapus semua akun non-admin yang ada dan menyisakan admin. Lanjutkan? (y/n)", ['y', 'n']);
            if (strtolower($confirm) !== 'y') {
                CLI::write("Operasi dibatalkan.", 'red');
                return;
            }

            CLI::print("Membersihkan akun non-admin sebelumnya... ");
            $db->query("DELETE FROM `users` WHERE UPPER(role) != 'ADMIN' AND username != 'admin';");
            CLI::write("OK!", 'green');
        }

        // 3. Bangun daftar akun & username cerdas tanpa tabrakan nama
        $generatedAccounts = $this->prepareAccounts($wilayahMap);

        // 4. Masukkan atau perbarui ke database (upsert aman)
        CLI::write("\nMenyimpan data 73 akun petugas ke database...", 'yellow');
        $defaultHash = password_hash('polmanbaik#', PASSWORD_BCRYPT);
        $insertedCount = 0;
        $updatedCount = 0;

        $tableRows = [];
        $no = 1;

        foreach ($generatedAccounts as $acc) {
            // Cek apakah akun dengan username atau nama ini sudah ada di database
            $existing = $db->table('users')
                ->where('username', $acc['username'])
                ->orWhere('nama', $acc['nama'])
                ->get()
                ->getRowArray();

            $userData = [
                'username'        => $acc['username'],
                'nama'            => $acc['nama'],
                'role'            => 'USER',
                'kecamatan_akses' => $acc['kecamatan'],
                'desa_akses'      => $acc['desa'],
                'hp'              => $acc['hp'],
                'updated_at'      => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                // Jika akun sudah ada, perbarui data profil & wilayah
                if ($isFresh) {
                    $userData['password'] = $defaultHash;
                }
                $db->table('users')->where('id', $existing['id'])->update($userData);
                $updatedCount++;
            } else {
                // Jika belum ada, insert baru dengan password default polmanbaik#
                $userData['password']   = $defaultHash;
                $userData['created_at'] = date('Y-m-d H:i:s');
                $db->table('users')->insert($userData);
                $insertedCount++;
            }

            $wilayahLabel = !empty($acc['desa']) ? ($acc['desa'] . ' (' . $acc['kecamatan'] . ')') : ('Semua Kelurahan (' . $acc['kecamatan'] . ')');
            $tableRows[] = [
                $no++,
                $acc['nama'],
                $acc['username'],
                'polmanbaik#',
                $wilayahLabel,
                $acc['hp'] ?? '-',
            ];
        }

        // 5. Tampilkan tabel ringkasan
        CLI::newLine();
        CLI::table($tableRows, [
            'No', 'Nama Petugas', 'Username', 'Password', 'Wilayah Tugas', 'No. HP'
        ]);

        // 6. Ekspor otomatis ke CSV untuk pegangan fasilitator/pelatih
        $exportPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'rekap_akun_petugas_rekonfirmasi.csv';
        $this->exportToCsv($tableRows, $exportPath);

        // 7. Catat ke Activity Log
        \App\Models\ActivityLogModel::record(
            'IMPORT_PETUGAS',
            "Administrator melakukan import/seeding {$totalPetugas} akun petugas rekonfirmasi dengan default password 'polmanbaik#'. (Inserted: {$insertedCount}, Updated: {$updatedCount})",
            ['inserted' => $insertedCount, 'updated' => $updatedCount]
        );

        CLI::newLine();
        CLI::write("==================================================================", 'green');
        CLI::write(" SUKSES: {$totalPetugas} Akun Petugas Rekonfirmasi Berhasil Didaftarkan!", 'green');
        CLI::write(" - Baru Ditambahkan : {$insertedCount} akun", 'green');
        CLI::write(" - Diperbarui       : {$updatedCount} akun", 'green');
        CLI::write(" - Password Default : polmanbaik#", 'white');
        CLI::write(" - File Rekap CSV   : " . $exportPath, 'cyan');
        CLI::write("==================================================================", 'green');
        CLI::write("Tip: File CSV di atas dapat langsung dibuka di Microsoft Excel untuk dibagikan kepada peserta pelatihan.", 'yellow');
    }

    /**
     * Mempersiapkan username cerdas dan mapping wilayah secara deterministik
     */
    protected function prepareAccounts(array $wilayahMap): array
    {
        $rawProposals = [];
        foreach ($this->petugasList as $idx => $p) {
            $customUsername = $p['username'] ?? null;
            $cleanName = $this->cleanOfficerName($p['nama']);
            $baseSlug  = $customUsername ?: $this->slugify($cleanName);
            $desaSlug  = !empty($p['desa']) ? $this->slugify($p['desa']) : 'semua';
            $kecamatan = $p['kecamatan'] ?? ($wilayahMap[$p['desa']] ?? 'Binuang');

            // Bersihkan nomor HP
            $hpClean = null;
            if (!empty($p['hp'])) {
                $hpDigits = preg_replace('/[^0-9]/', '', $p['hp']);
                if (strlen($hpDigits) >= 9) {
                    $hpClean = $hpDigits;
                }
            }

            $rawProposals[] = [
                'original_index' => $idx,
                'nama'           => trim($p['nama']),
                'clean_name'     => $cleanName,
                'base_slug'      => $baseSlug,
                'custom_username'=> $customUsername,
                'desa'           => $p['desa'] ?? null,
                'desa_slug'      => $desaSlug,
                'kecamatan'      => $kecamatan,
                'hp'             => $hpClean,
            ];
        }

        // Hitung frekuensi base_slug di antara ke-73 petugas
        $slugCounts = [];
        foreach ($rawProposals as $rp) {
            $slugCounts[$rp['base_slug']] = ($slugCounts[$rp['base_slug']] ?? 0) + 1;
        }

        $accounts = [];
        $batchUsernames = [];

        foreach ($rawProposals as $rp) {
            if (!empty($rp['custom_username'])) {
                $candidate = $rp['custom_username'];
            } else {
                $baseSlug = $rp['base_slug'];

                // Jika ada lebih dari 1 nama yang identik (misal: haslinda, nur_annisa), otomatis sematkan nama desa
                if (($slugCounts[$baseSlug] ?? 1) > 1) {
                    $candidate = 'petugas_' . $baseSlug . '_' . $rp['desa_slug'];
                } else {
                    $candidate = 'petugas_' . $baseSlug;
                }

                // Pastikan tidak tabrak nama 'admin'
                if ($candidate === 'petugas_admin' || $candidate === 'admin') {
                    $candidate .= '_ats';
                }

                // Batasi panjang jika melebihi 50 karakter (DB constraint VARCHAR(50))
                if (strlen($candidate) > 50) {
                    $candidate = substr($candidate, 0, 50);
                }
            }

            // Cek collision dalam batch 73 petugas ini
            $finalUsername = $candidate;
            $counter = 2;
            while (isset($batchUsernames[$finalUsername])) {
                $suffix = '_' . $counter;
                $maxBaseLen = 50 - strlen($suffix);
                $finalUsername = substr($candidate, 0, $maxBaseLen) . $suffix;
                $counter++;
            }
            $batchUsernames[$finalUsername] = true;

            $accounts[] = [
                'username'  => $finalUsername,
                'nama'      => $rp['nama'],
                'desa'      => $rp['desa'],
                'kecamatan' => $rp['kecamatan'],
                'hp'        => $rp['hp'],
            ];
        }

        return $accounts;
    }

    /**
     * Bersihkan gelar akademik dari nama petugas untuk slug username
     */
    protected function cleanOfficerName(string $name): string
    {
        // Hilangkan gelar-gelar umum di belakang nama
        $nameWithoutTitles = preg_replace(
            '/(,\s*|\s+)(S\.?\s*IP|S\.?,\s*Sos|S\.?\s*Sos|S\.?\s*Pd(\.I)?|S\.?\s*Ak|S\.?\s*Kom|S\.?\s*Sy|S\.?\s*H|S\.?\s*HI|S\.?\s*S\.?\s*Pd|B\s+S\.?\s*Pd).*$/i',
            '',
            $name
        );

        // Hapus karakter titik atau kutip satu di singkatan nama seperti 'M.ARDIANSYAH', 'ABD.RASYD', 'MUH. AS\'AD'
        $cleaned = str_replace(["'", '"', '.'], ' ', $nameWithoutTitles);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return trim($cleaned);
    }

    /**
     * Konversi string nama ke slug username (lowercase alphanumeric with underscore)
     */
    protected function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        return trim($text, '_');
    }

    /**
     * Muat mapping wilayah Desa -> Kecamatan
     */
    protected function loadWilayahMapping(): array
    {
        $filePath = FCPATH . 'data' . DIRECTORY_SEPARATOR . 'wilayah_kecamatan.json';
        $map = [];

        if (file_exists($filePath)) {
            $json = json_decode(file_get_contents($filePath), true);
            if (is_array($json)) {
                foreach ($json as $kec => $desas) {
                    if (is_array($desas)) {
                        foreach ($desas as $desa) {
                            $map[trim($desa)] = trim($kec);
                        }
                    }
                }
            }
        }

        // Cadangan mapping khusus untuk 15 desa penugasan rekonfirmasi
        $fallback = [
            'Batetangnga' => 'Binuang',
            'Amola'       => 'Binuang',
            'Amassangan'  => 'Binuang',
            'Kuajang'     => 'Binuang',
            'Tonyaman'    => 'Binuang',
            'Mammi'       => 'Binuang',
            'Mirring'     => 'Binuang',
            'Rea'         => 'Binuang',
            'Kaleok'      => 'Binuang',
            'Paku'        => 'Binuang',
            'Kelapa Dua'  => 'Anreapi',
            'Papandangan' => 'Anreapi',
            'Kunyi'       => 'Anreapi',
            'Duampanua'   => 'Anreapi',
            'Anreapi'     => 'Anreapi',
        ];

        return array_merge($fallback, $map);
    }

    /**
     * Ekspor daftar akun ke file CSV dengan UTF-8 BOM agar rapi di Microsoft Excel
     */
    protected function exportToCsv(array $tableRows, string $outputPath): void
    {
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $fp = fopen($outputPath, 'w');
        if ($fp) {
            // Write UTF-8 BOM so Excel opens it with proper characters
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($fp, ['No', 'Nama Petugas', 'Username', 'Password Default', 'Wilayah Tugas', 'No. HP']);
            foreach ($tableRows as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);
        }
    }
}
