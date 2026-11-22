<?php

namespace App\Controllers;

use App\Models\AtsModel;
use App\Models\UserModel;
use App\Models\ActivityLogModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Ats extends BaseController
{
    protected $atsModel;
    protected $userModel;

    public function __construct()
    {
        $this->atsModel = new AtsModel();
        $this->userModel = new UserModel();
    }

    /**
     * Target Resmi Kuota Rekonfirmasi ATS 2026 (Kec. Anreapi & Kec. Binuang)
     */
    public static array $MASTER_TARGET_REKONFIRMASI = [
        'ANREAPI' => [
            'target_total'  => 181,
            'petugas_total' => 13,
            'desa' => [
                'ANREAPI'      => ['target_ats' => 32,  'petugas' => 2,  'target_per_petugas' => 16.00],
                'KELAPA DUA'   => ['target_ats' => 31,  'petugas' => 2,  'target_per_petugas' => 15.50],
                'DUAMPANUA'    => ['target_ats' => 68,  'petugas' => 5,  'target_per_petugas' => 13.60],
                'PAPPANDANGAN' => ['target_ats' => 26,  'petugas' => 2,  'target_per_petugas' => 13.00],
                'KUNYI'        => ['target_ats' => 24,  'petugas' => 2,  'target_per_petugas' => 12.00],
            ]
        ],
        'BINUANG' => [
            'target_total'  => 864,
            'petugas_total' => 61,
            'desa' => [
                'MAMMI'        => ['target_ats' => 34,  'petugas' => 2,  'target_per_petugas' => 17.00],
                'KUAJANG'      => ['target_ats' => 124, 'petugas' => 9,  'target_per_petugas' => 13.78],
                'TONYAMAN'     => ['target_ats' => 172, 'petugas' => 12, 'target_per_petugas' => 14.33],
                'KALEOK'       => ['target_ats' => 29,  'petugas' => 2,  'target_per_petugas' => 14.50],
                'AMASSANGAN'   => ['target_ats' => 95,  'petugas' => 6,  'target_per_petugas' => 15.83],
                'REA'          => ['target_ats' => 68,  'petugas' => 5,  'target_per_petugas' => 13.60],
                'AMOLA'        => ['target_ats' => 48,  'petugas' => 3,  'target_per_petugas' => 16.00],
                'BATETANGNGA'  => ['target_ats' => 116, 'petugas' => 9,  'target_per_petugas' => 12.89],
                'MIRRING'      => ['target_ats' => 108, 'petugas' => 8,  'target_per_petugas' => 13.50],
                'PAKU'         => ['target_ats' => 70,  'petugas' => 5,  'target_per_petugas' => 14.00],
            ]
        ]
    ];

    /**
     * Normalisasi nama desa agar cocok dengan master target
     */
    public static function normalizeDesaKey($desa): string
    {
        $d = strtoupper(trim($desa ?? ''));
        $d = str_replace(['DESA ', 'KELURAHAN ', 'KEL. ', 'DS. '], '', $d);
        if ($d === 'PAPANDANGAN') {
            return 'PAPPANDANGAN';
        }
        return $d;
    }

    /**
     * Get list of all 16 Kecamatan in Polewali Mandar from JSON
     */
    protected function getAllPolmanKecamatan(): array
    {
        $jsonPath = FCPATH . 'data/wilayah_kecamatan.json';
        if (file_exists($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true);
            return array_keys($data);
        }
        return [];
    }

    /**
     * Get allowed kecamatan for current user based on role and assignment
     */
    protected function getUserAllowedKecamatan(): array
    {
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allKec = $this->getAllPolmanKecamatan();

        if ($role === 'ADMIN') {
            return $allKec;
        }

        // For non-admin (Tim Entri / Petugas)
        $akses = session()->get('kecamatan_akses');
        if (empty($akses) && session()->get('user_id')) {
            $user = $this->userModel->find(session()->get('user_id'));
            $akses = $user['kecamatan_akses'] ?? '';
        }

        if (empty($akses)) {
            return [];
        }

        $list = array_map('trim', explode(',', strtoupper($akses)));
        $matched = [];
        foreach ($list as $item) {
            foreach ($allKec as $k) {
                if (strtoupper($k) === strtoupper($item)) {
                    $matched[] = $k;
                    break;
                }
            }
        }
        return array_values(array_unique($matched));
    }

    /**
     * Get allowed desa/kelurahan for current user based on role and assignment
     */
    protected function getUserAllowedDesa(): array
    {
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        if ($role === 'ADMIN') {
            return []; // Empty indicates unrestricted (all desas)
        }

        $akses = session()->get('desa_akses');
        if (empty($akses) && session()->get('user_id')) {
            $user = $this->userModel->find(session()->get('user_id'));
            $akses = $user['desa_akses'] ?? '';
        }

        if (empty($akses)) {
            return [];
        }

        $list = array_map('trim', explode(',', $akses));
        return array_values(array_filter($list));
    }

    /**
     * Normalisasi penulisan kebutuhan keterampilan (mengatasi typo penulisan petugas seperti TIDK ADA, TDK ADA, dll)
     */
    protected function normalizeKeterampilan(?string $ket): string
    {
        if ($ket === null) return '-';
        $ket = strtoupper(trim($ket));
        if (empty($ket) || $ket === '-') {
            return '-';
        }
        $clean = preg_replace('/\s+/', ' ', $ket);

        // Normalisasi variasi kata "TIDAK ADA"
        if (in_array($clean, [
            'TIDK ADA', 'TDK ADA', 'TIDAK ADA', 'TIDAK', 'TDK', 'NIHIL', 
            'NONE', 'TIDAK MAU', 'TIDAK INGIN', 'TIDAK ADA KETERAMPILAN', 
            'TIDAK MEMBUTUHKAN', 'TIDK', 'TIDAK ADA.', 'TIDAK ADA/TIDAK INGIN'
        ])) {
            return 'TIDAK ADA';
        }

        // Normalisasi variasi kata "BELUM TAHU"
        if (in_array($clean, [
            'BELUM TAHU', 'BLM TAHU', 'BELUM ADA', 'BLM ADA', 'TIDAK TAHU', 
            'BINGUNG', 'BELUM TAHU / BELUM ADA', 'BELUM KETAHUAN'
        ])) {
            return 'BELUM TAHU';
        }

        return $clean;
    }

    /**
     * Ensure database schema has latest columns
     */
    private function ensureSchemaUpdated()
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->fieldExists('jenjang_aktif', 'ats_data')) {
                $forge = \Config\Database::forge();
                $forge->addColumn('ats_data', [
                    'jenjang_aktif' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 50,
                        'null'       => true,
                        'after'      => 'status_sekolah',
                    ]
                ]);
            }
            if (!$db->fieldExists('kelas_putus', 'ats_data')) {
                $forge = \Config\Database::forge();
                $forge->addColumn('ats_data', [
                    'kelas_putus' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 50,
                        'null'       => true,
                        'after'      => 'status_tdk_sekolah',
                    ]
                ]);
            }
            if (!$db->fieldExists('tahun_putus', 'ats_data')) {
                $forge = \Config\Database::forge();
                $forge->addColumn('ats_data', [
                    'tahun_putus' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 20,
                        'null'       => true,
                        'after'      => 'kelas_putus',
                    ]
                ]);
            }
            if (!$db->fieldExists('tahun_lulus', 'ats_data')) {
                $forge = \Config\Database::forge();
                $forge->addColumn('ats_data', [
                    'tahun_lulus' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 20,
                        'null'       => true,
                        'after'      => 'tahun_putus',
                    ]
                ]);
            }
            if (!$db->fieldExists('foto_anak', 'ats_data')) {
                $forge = \Config\Database::forge();
                $forge->addColumn('ats_data', [
                    'foto_anak' => [
                        'type'  => 'LONGTEXT',
                        'null'  => true,
                        'after' => 'keterampilan',
                    ]
                ]);
            } else {
                // Ensure foto_anak column is LONGTEXT to prevent data truncation errors
                try {
                    $db->query("ALTER TABLE ats_data MODIFY foto_anak LONGTEXT NULL");
                } catch (\Throwable $e) {}
            }
            if (!$db->fieldExists('created_by', 'ats_data')) {
                $forge = \Config\Database::forge();
                $forge->addColumn('ats_data', [
                    'created_by' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 50,
                        'null'       => true,
                        'after'      => 'foto_anak',
                    ]
                ]);
            }

            // Bersihkan inkonsistensi data: jika anak aktif bersekolah, bersihkan data target kembali & ATS
            try {
                $db->query("
                    UPDATE ats_data 
                    SET target_kembali = '-',
                        keinginan = '-',
                        bentuk_pendidikan = '-',
                        satuan_pendidikan = '-',
                        ukuran_baju = '-',
                        ukuran_sepatu = '-',
                        alasan = '-',
                        alasan_lainnya = NULL,
                        status_tdk_sekolah = '-',
                        kelas_putus = '-',
                        tahun_putus = '-',
                        tahun_lulus = '-'
                    WHERE (UPPER(status_sekolah) NOT LIKE '%TIDAK%' AND status_sekolah IS NOT NULL AND status_sekolah != '' AND status_sekolah != '-')
                      AND (target_kembali != '-' OR keinginan != '-' OR status_tdk_sekolah != '-');
                ");
                // Perbaiki data yang memiliki target kembali tapi keinginan tidak sengaja ter-reset menjadi '-' atau TIDAK INGIN
                $db->query("
                    UPDATE ats_data
                    SET keinginan = 'INGIN KEMBALI BERSEKOLAH'
                    WHERE target_kembali IS NOT NULL 
                      AND target_kembali != '' 
                      AND target_kembali != '-'
                      AND UPPER(status_sekolah) LIKE '%TIDAK%'
                      AND (keinginan = '-' OR keinginan IS NULL OR UPPER(keinginan) NOT LIKE '%INGIN KEMBALI%');
                ");
                // Normalisasi nilai keinginan 'YA' menjadi 'INGIN KEMBALI BERSEKOLAH'
                $db->query("
                    UPDATE ats_data
                    SET keinginan = 'INGIN KEMBALI BERSEKOLAH'
                    WHERE UPPER(TRIM(keinginan)) = 'YA' OR (UPPER(keinginan) LIKE '%INGIN%' AND UPPER(keinginan) NOT LIKE '%TIDAK%');
                ");
                $db->query("
                    UPDATE ats_data
                    SET target_kembali = '-',
                        bentuk_pendidikan = '-',
                        satuan_pendidikan = '-',
                        ukuran_baju = '-',
                        ukuran_sepatu = '-'
                    WHERE (UPPER(keinginan) NOT LIKE '%INGIN KEMBALI%' AND UPPER(keinginan) != 'YA' AND keinginan IS NOT NULL AND keinginan != '' AND keinginan != '-')
                      AND (target_kembali != '-' OR bentuk_pendidikan != '-');
                ");
                $db->query("
                    UPDATE ats_data
                    SET keterampilan = '-'
                    WHERE (UPPER(keinginan) LIKE '%INGIN KEMBALI%' OR UPPER(keinginan) = 'YA')
                      AND (keterampilan != '-');
                ");
                $db->query("
                    UPDATE ats_data
                    SET keterampilan = 'TIDAK ADA'
                    WHERE UPPER(TRIM(keterampilan)) IN ('TIDK ADA', 'TDK ADA', 'TIDAK', 'TDK', 'NIHIL', 'NONE', 'TIDAK MAU', 'TIDAK INGIN', 'TIDAK ADA KETERAMPILAN', 'TIDAK MEMBUTUHKAN', 'TIDK', 'TIDAK ADA.', 'TIDAK ADA/TIDAK INGIN');
                ");
                $db->query("
                    UPDATE ats_data
                    SET keterampilan = 'BELUM TAHU'
                    WHERE UPPER(TRIM(keterampilan)) IN ('BLM TAHU', 'BELUM ADA', 'BLM ADA', 'TIDAK TAHU', 'BINGUNG', 'BELUM TAHU / BELUM ADA', 'BELUM KETAHUAN');
                ");
            } catch (\Throwable $e) {}
        } catch (\Throwable $e) {
            // Ignore if column exists or read-only
        }
    }

    /**
     * Main Admin Panel interface
     */
    public function index()
    {
        $this->ensureSchemaUpdated();
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKecamatan = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();
        $allKecamatanList = $this->getAllPolmanKecamatan();

        $currentUser = [
            'username'         => session()->get('username') ?? 'admin',
            'nama'             => session()->get('nama') ?? 'Administrator PANTAU ATS',
            'role'             => $role,
            'kecamatan_akses'  => session()->get('kecamatan_akses') ?? '',
            'desa_akses'       => session()->get('desa_akses') ?? '',
            'hp'               => session()->get('hp') ?? '08114200000',
            'allowedKecamatan' => $allowedKecamatan,
            'allowedDesa'      => $allowedDesa,
            'isSingleDesa'     => ($role !== 'ADMIN' && count($allowedDesa) === 1),
            'singleDesaName'   => ($role !== 'ADMIN' && count($allowedDesa) === 1) ? $allowedDesa[0] : '',
            'singleKecName'    => ($role !== 'ADMIN' && count($allowedKecamatan) === 1) ? $allowedKecamatan[0] : '',
        ];

        $currentUsername = session()->get('username');
        $currentNama = session()->get('nama');

        $summary = $this->atsModel->getAnalyticsSummary(
            $role === 'ADMIN' ? null : $allowedKecamatan,
            $role === 'ADMIN' ? null : $allowedDesa,
            $role === 'ADMIN' ? null : $currentUsername,
            $role === 'ADMIN' ? null : $currentNama
        );

        return view('ats/index', [
            'currentUser'      => $currentUser,
            'summary'          => $summary,
            'kecamatanList'    => $allowedKecamatan,
            'desaList'         => $allowedDesa,
            'allKecamatanList' => $allKecamatanList,
            'isAdmin'          => ($role === 'ADMIN'),
        ]);
    }

    /**
     * API: Get Kecamatan & Desa/Kelurahan structure
     */
    public function getWilayah()
    {
        $jsonPath = FCPATH . 'data/wilayah_kecamatan.json';
        if (!file_exists($jsonPath)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data wilayah kecamatan tidak ditemukan']);
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();

        if ($role !== 'ADMIN') {
            $filtered = [];
            $upperAllowedKec = array_map('strtoupper', $allowedKec);
            $upperAllowedDesa = array_map('strtoupper', $allowedDesa);

            foreach ($data as $kec => $villages) {
                if (in_array(strtoupper($kec), $upperAllowedKec)) {
                    if (!empty($upperAllowedDesa)) {
                        $matchedVillages = array_values(array_filter($villages, function ($v) use ($upperAllowedDesa) {
                            return in_array(strtoupper($v), $upperAllowedDesa);
                        }));
                        if (!empty($matchedVillages)) {
                            $filtered[$kec] = $matchedVillages;
                        }
                    } else {
                        $filtered[$kec] = $villages;
                    }
                }
            }
            $data = $filtered;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    /**
     * API: Get Desa/Kelurahan list for a specific Kecamatan
     */
    public function getDesaByKecamatan()
    {
        $kecamatan = trim((string)$this->request->getGet('kecamatan'));
        if (empty($kecamatan) || $kecamatan === 'Semua') {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }

        $jsonPath = FCPATH . 'data/wilayah_kecamatan.json';
        if (!file_exists($jsonPath)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data wilayah tidak ditemukan']);
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $villages = [];
        foreach ($data as $k => $list) {
            if (strtoupper($k) === strtoupper($kecamatan)) {
                $villages = $list;
                break;
            }
        }

        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedDesa = $this->getUserAllowedDesa();

        if ($role !== 'ADMIN' && !empty($allowedDesa)) {
            $upperAllowedDesa = array_map('strtoupper', $allowedDesa);
            $villages = array_values(array_filter($villages, function ($v) use ($upperAllowedDesa) {
                return in_array(strtoupper($v), $upperAllowedDesa);
            }));
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => array_values($villages)
        ]);
    }

    /**
     * API: Get ATS Data list (filtered by kecamatan, desa, or search query)
     */
    public function getData()
    {
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();
        $kecamatan = $this->request->getGet('kecamatan');
        $desa      = $this->request->getGet('desa');
        $query     = $this->request->getGet('q');

        $builder = $this->atsModel->builder();

        if ($role !== 'ADMIN') {
            $currentUsername = session()->get('username');
            $currentNama = session()->get('nama');
            // Isolasi data: Petugas non-admin hanya dapat melihat data yang diinput sendiri
            $builder->groupStart()
                ->where('created_by', $currentUsername)
                ->orWhere('UPPER(nama_petugas)', strtoupper($currentNama))
                ->groupEnd();

            if (!empty($allowedKec)) {
                $builder->whereIn('kecamatan', array_map('strtoupper', $allowedKec));
            } else {
                $builder->where('1 = 0');
            }
            if (!empty($allowedDesa)) {
                $builder->whereIn('desa', array_map('strtoupper', $allowedDesa));
            }
        }

        if (!empty($kecamatan) && $kecamatan !== 'Semua') {
            if ($role === 'ADMIN' || in_array(strtoupper($kecamatan), array_map('strtoupper', $allowedKec))) {
                $builder->where('kecamatan', strtoupper($kecamatan));
            }
        }

        if (!empty($desa) && $desa !== 'Semua') {
            if ($role === 'ADMIN' || empty($allowedDesa) || in_array(strtoupper($desa), array_map('strtoupper', $allowedDesa))) {
                $builder->where('desa', strtoupper($desa));
            }
        }

        if (!empty($query)) {
            $builder->groupStart()
                ->like('nik', $query)
                ->orLike('nama_lengkap', $query)
                ->orLike('desa', $query)
                ->orLike('kecamatan', $query)
                ->groupEnd();
        }

        $builder->orderBy('id', 'DESC');
        $data = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'total'  => count($data),
            'data'   => $data
        ]);
    }

    /**
     * API: Get single record for edit / view
     */
    public function getRecord($id)
    {
        $record = $this->atsModel->where('id', $id)->orWhere('nik', $id)->first();
        if (!$record) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Data anak tidak ditemukan.'
            ]);
        }

        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();

        if ($role !== 'ADMIN') {
            $currentUsername = session()->get('username');
            $currentNama = session()->get('nama');
            $isOwner = (!empty($record['created_by']) && $record['created_by'] === $currentUsername)
                || (!empty($record['nama_petugas']) && strtoupper($record['nama_petugas']) === strtoupper($currentNama));
            if (!$isOwner) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Anda hanya berhak mengakses data yang Anda input sendiri.'
                ]);
            }

            if (!in_array(strtoupper($record['kecamatan']), array_map('strtoupper', $allowedKec))) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk melihat data di kecamatan ini.'
                ]);
            }
            if (!empty($allowedDesa) && !in_array(strtoupper($record['desa']), array_map('strtoupper', $allowedDesa))) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk melihat data di desa ini.'
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $record
        ]);
    }

    /**
     * Store or update record
     */
    public function store()
    {
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();

        $targetKec = strtoupper(trim($this->request->getPost('kecamatan') ?? '-'));
        $targetDesa = strtoupper(trim($this->request->getPost('desa') ?? '-'));

        if ($role !== 'ADMIN') {
            if (!in_array($targetKec, array_map('strtoupper', $allowedKec))) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => "Gagal: Anda tidak memiliki hak akses pendataan untuk Kecamatan {$targetKec}!"
                ]);
            }
            if (!empty($allowedDesa) && !in_array($targetDesa, array_map('strtoupper', $allowedDesa))) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => "Gagal: Anda tidak memiliki hak akses pendataan untuk Desa/Kelurahan {$targetDesa}!"
                ]);
            }
        }

        $isEditMode = filter_var($this->request->getPost('isEditMode'), FILTER_VALIDATE_BOOLEAN);
        $editId     = $this->request->getPost('editId');
        $editNik    = trim($this->request->getPost('editNik') ?? '');

        $nik = trim($this->request->getPost('nik') ?? '');
        $nik = preg_replace('/[^0-9]/', '', $nik);

        if (strlen($nik) !== 16) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal: NIK wajib diisi tepat 16 digit angka!'
            ]);
        }

        $umur = (int)$this->request->getPost('umur');
        if ($umur < 6 || $umur > 25) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal: Usia harus antara 6 hingga 25 tahun!'
            ]);
        }

        $hpOrtu = trim((string)$this->request->getPost('hpOrtu'));
        $hpOrtuDigits = preg_replace('/[^0-9]/', '', $hpOrtu);
        if (strlen($hpOrtuDigits) < 10 || strlen($hpOrtuDigits) > 13) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal: Nomor HP Orang Tua / Wali tidak boleh kurang dari 10 digit dan tidak boleh lebih dari 13 digit!'
            ]);
        }

        // Duplicate NIK check
        $existing = $this->atsModel->where('nik', $nik)->first();
        if ($existing) {
            if (!$isEditMode || ($isEditMode && $existing['nik'] !== $editNik && (int)$existing['id'] !== (int)$editId)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => "Gagal: NIK {$nik} sudah terdaftar atas nama {$existing['nama_lengkap']}!"
                ]);
            }
        }

        $this->ensureSchemaUpdated();

        $statusSekolah       = strtoupper(trim($this->request->getPost('statusSekolah') ?? ''));
        $jenjangAktif        = strtoupper(trim($this->request->getPost('jenjangAktif') ?? '-')) ?: '-';
        $sekolahAktif        = strtoupper(trim($this->request->getPost('sekolahAktif') ?? '-')) ?: '-';
        $pendidikanTertinggi = strtoupper(trim($this->request->getPost('pendidikanTertinggi') ?? '-'));
        $statusTdkSekolah    = strtoupper(trim($this->request->getPost('statusTdkSekolah') ?? '-')) ?: '-';
        $asalSekolah         = strtoupper(trim($this->request->getPost('asalSekolah') ?? '-')) ?: '-';
        $kelasPutus          = strtoupper(trim($this->request->getPost('kelasPutus') ?? '-')) ?: '-';
        $tahunPutus          = strtoupper(trim($this->request->getPost('tahunPutus') ?? '-')) ?: '-';
        $tahunLulus          = strtoupper(trim($this->request->getPost('tahunLulus') ?? '-')) ?: '-';
        $rawKeinginan        = strtoupper(trim($this->request->getPost('keinginan') ?? '-'));
        if ((str_contains($rawKeinginan, 'INGIN') && !str_contains($rawKeinginan, 'TIDAK')) || $rawKeinginan === 'YA' || str_contains($rawKeinginan, 'KEMBALI')) {
            $keinginan = 'INGIN KEMBALI BERSEKOLAH';
        } elseif (str_contains($rawKeinginan, 'TIDAK')) {
            $keinginan = 'TIDAK INGIN';
        } else {
            $keinginan = $rawKeinginan ?: '-';
        }
        $targetKembali       = strtoupper(trim($this->request->getPost('targetKembali') ?? ''));
        $bentukPendidikan    = strtoupper(trim($this->request->getPost('bentukPendidikan') ?? '-'));
        $ukuranBaju          = strtoupper(trim($this->request->getPost('baju') ?? '-'));
        $ukuranSepatu        = trim($this->request->getPost('sepatu') ?? '-');

        $isAktifSekolah = ($statusSekolah === 'YA, MASIH SEKOLAH' || (!empty($statusSekolah) && !str_contains($statusSekolah, 'TIDAK')));

        if ($isAktifSekolah) {
            if (empty($jenjangAktif) || $jenjangAktif === '-') {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Untuk anak yang masih aktif bersekolah, Jenjang Sekolah wajib dipilih!'
                ]);
            }
            if (empty($sekolahAktif) || $sekolahAktif === '-') {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Untuk anak yang masih aktif bersekolah, Nama Sekolah Aktif wajib diisi!'
                ]);
            }
            // Karena anak aktif bersekolah (bukan ATS), bersihkan seluruh atribut khusus ATS & rencana kembali
            $pendidikanTertinggi = '-';
            $statusTdkSekolah    = '-';
            $asalSekolah         = '-';
            $kelasPutus          = '-';
            $tahunPutus          = '-';
            $tahunLulus          = '-';
            $alasan              = '-';
            $alasanLainnya       = null;
            $keinginan           = '-';
            $targetKembali       = '-';
            $bentukPendidikan    = '-';
            $satuanPendidikan    = '-';
            $ukuranBaju          = '-';
            $ukuranSepatu        = '-';
            $keterampilan        = '-';
        } else {
            $jenjangAktif = '-';
            $sekolahAktif = '-';

            $isSma = (
                str_contains($pendidikanTertinggi, 'SMA') || 
                str_contains($pendidikanTertinggi, 'SMK') || 
                preg_match('/\bMA\b/i', $pendidikanTertinggi)
            );

            if ($pendidikanTertinggi === 'BPB') {
                $statusTdkSekolah = '-';
                $asalSekolah      = '-';
                $kelasPutus       = '-';
                $tahunPutus       = '-';
                $tahunLulus       = '-';
                $menerimaKip      = '-';
            } elseif ($isSma) {
                // Tamatan SMA / SMK / MA Sederajat
                if (empty($asalSekolah) || $asalSekolah === '-') {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => 'Untuk Tamatan SMA/SMK/MA Sederajat, Nama Sekolah Asal/Terakhir wajib diisi!'
                    ]);
                }
                if (empty($tahunLulus) || $tahunLulus === '-') {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => 'Untuk Tamatan SMA/SMK/MA Sederajat, Tahun Kelulusan wajib dipilih!'
                    ]);
                }
                $statusTdkSekolah = 'LULUS TIDAK MELANJUTKAN';
                $kelasPutus       = '-';
                $tahunPutus       = '-';
                $alasan           = '-';
                $alasanLainnya    = null;
                $menerimaKip      = '-';
                $keinginan        = '-';
                $targetKembali    = '-';
                $bentukPendidikan = '-';
                $satuanPendidikan = '-';
                $ukuranBaju       = '-';
                $ukuranSepatu     = '-';
                $keterampilan     = '-';
            } elseif ($pendidikanTertinggi === 'TIDAK MEMILIKI IJASAH' || $pendidikanTertinggi === 'TIDAK MEMILIKI IJAZAH' || str_contains($pendidikanTertinggi, 'TIDAK MEMILIKI') || str_contains($statusTdkSekolah, 'PUTUS') || str_contains($statusTdkSekolah, 'DO')) {
                $statusTdkSekolah = 'PUTUS SEKOLAH (DO)';
                $tahunLulus       = '-';
                if ($pendidikanTertinggi === 'TIDAK MEMILIKI IJASAH' || $pendidikanTertinggi === 'TIDAK MEMILIKI IJAZAH' || str_contains($pendidikanTertinggi, 'TIDAK MEMILIKI')) {
                    if (empty($asalSekolah) || $asalSekolah === '-') {
                        return $this->response->setJSON([
                            'status'  => 'error',
                            'message' => 'Untuk anak yang belum tamat SD/MI (Tidak Memiliki Ijazah), Nama Sekolah Asal/Sebelumnya wajib diisi!'
                        ]);
                    }
                    if (empty($kelasPutus) || $kelasPutus === '-') {
                        return $this->response->setJSON([
                            'status'  => 'error',
                            'message' => 'Untuk anak yang belum tamat SD/MI (Tidak Memiliki Ijazah), Kelas Putus Sekolah (Kelas 1 - 6) wajib dipilih!'
                        ]);
                    }
                }
            } elseif (str_contains($statusTdkSekolah, 'LULUS')) {
                $statusTdkSekolah = 'LULUS TIDAK MELANJUTKAN';
                $kelasPutus       = '-';
                $tahunPutus       = '-';
            }

            if (!$isSma) {
                if (str_contains($keinginan, 'INGIN KEMBAL')) {
                    if (empty($targetKembali) || $targetKembali === '-') {
                        return $this->response->setJSON([
                            'status'  => 'error',
                            'message' => 'Target Sekolah wajib dipilih!'
                        ]);
                    }

                    if (in_array($targetKembali, ['SD', 'SMP', 'SMA'])) {
                        $bentukPendidikan = 'FORMAL';
                        if (empty($ukuranBaju) || $ukuranBaju === '-') {
                            return $this->response->setJSON([
                                'status'  => 'error',
                                'message' => 'Untuk Target Sekolah Formal, Ukuran Baju Seragam wajib dipilih!'
                            ]);
                        }
                        if (empty($ukuranSepatu) || $ukuranSepatu === '-') {
                            return $this->response->setJSON([
                                'status'  => 'error',
                                'message' => 'Untuk Target Sekolah Formal, Ukuran Sepatu wajib diisi!'
                            ]);
                        }
                    } else {
                        $bentukPendidikan = 'NON FORMAL';
                        // Non Formal: Tidak perlu ukuran sepatu
                        $ukuranSepatu = '-';
                    }
                    // Ingin kembali bersekolah -> Kosongkan kebutuhan keterampilan kerja
                    $keterampilan = '-';
                } else {
                    // Tidak ingin kembali bersekolah -> Kosongkan seluruh rencana kembali
                    $targetKembali    = '-';
                    $bentukPendidikan = '-';
                    $satuanPendidikan = '-';
                    $ukuranBaju       = '-';
                    $ukuranSepatu     = '-';
                    $keterampilan     = $this->normalizeKeterampilan($this->request->getPost('keterampilan') ?? '-');
                }
            }
        }

        $data = [
            'waktu_input'          => date('Y-m-d H:i:s'),
            'jenis_input'          => strtoupper(trim($this->request->getPost('jenisInput') ?? 'REKONFIRMASI (DATA DASHBOARD ATS)')),
            'kabupaten'            => 'POLEWALI MANDAR',
            'kecamatan'            => $targetKec,
            'desa'                 => $targetDesa,
            'nama_petugas'         => strtoupper(trim($this->request->getPost('namaPetugas') ?? session()->get('nama'))),
            'no_hp_petugas'        => trim($this->request->getPost('hpPetugas') ?? session()->get('hp')),
            'nisn'                 => trim($this->request->getPost('nisn') ?? '-') ?: '-',
            'nik'                  => $nik,
            'nama_lengkap'         => strtoupper(trim($this->request->getPost('namaLengkap') ?? '')),
            'jenis_kelamin'        => strtoupper(trim($this->request->getPost('jk') ?? '')),
            'tanggal_lahir'        => trim($this->request->getPost('tglLahir') ?? ''),
            'umur'                 => $umur,
            'disabilitas'          => strtoupper(trim($this->request->getPost('disabilitas') ?? 'TIDAK')),
            'no_hp_ortu'           => $hpOrtuDigits,
            'nama_ibu'             => strtoupper(trim($this->request->getPost('namaIbu') ?? '')),
            'status_sekolah'       => $statusSekolah,
            'jenjang_aktif'        => $jenjangAktif,
            'sekolah_aktif'        => $sekolahAktif,
            'pendidikan_tertinggi' => $pendidikanTertinggi ?: '-',
            'status_tdk_sekolah'   => $statusTdkSekolah,
            'asal_sekolah'         => $asalSekolah,
            'kelas_putus'          => $kelasPutus,
            'tahun_putus'          => $tahunPutus,
            'tahun_lulus'          => $tahunLulus,
            'alasan'               => strtoupper(trim($this->request->getPost('alasan') ?? '-')) ?: '-',
            'alasan_lainnya'       => trim($this->request->getPost('alasanLainnya') ?? '') ?: null,
            'menerima_kip'         => strtoupper(trim($this->request->getPost('penerimaKip') ?? '-')) ?: '-',
            'keinginan'            => $keinginan ?: '-',
            'bentuk_pendidikan'    => $bentukPendidikan ?: '-',
            'target_kembali'       => $targetKembali ?: '-',
            'satuan_pendidikan'    => strtoupper(trim($this->request->getPost('satuanPendidikan') ?? '-')) ?: '-',
            'ukuran_baju'          => $ukuranBaju,
            'ukuran_sepatu'        => $ukuranSepatu,
            'keterampilan'         => $keterampilan,
            'created_by'           => session()->get('username') ?? 'admin',
        ];

        // Foto anak (Base64 string or image upload)
        $fotoBase64 = $this->request->getPost('fotoBase64');
        $isPhotoRemoved = $this->request->getPost('isPhotoRemoved') === '1';

        if ($isPhotoRemoved) {
            $data['foto_anak'] = null;
        } elseif (!empty($fotoBase64)) {
            if (str_starts_with($fotoBase64, 'data:image')) {
                // Decode and save to physical file in public/uploads/foto_rekonfirmasi/
                $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'foto_rekonfirmasi';
                if (!is_dir($uploadPath)) {
                    @mkdir($uploadPath, 0755, true);
                }

                $savedFilePath = null;
                if (preg_match('#^data:image/(\w+);base64,#i', $fotoBase64, $matches)) {
                    $ext = strtolower($matches[1]);
                    if ($ext === 'jpeg') $ext = 'jpg';
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $ext = 'jpg';
                    }
                    $base64Data = substr($fotoBase64, strpos($fotoBase64, ',') + 1);
                    $decodedImage = base64_decode($base64Data);

                    if ($decodedImage !== false && is_dir($uploadPath) && is_writable($uploadPath)) {
                        $filename = 'foto_' . preg_replace('/[^0-9]/', '', $nik) . '_' . time() . '.' . $ext;
                        $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;
                        if (@file_put_contents($fullPath, $decodedImage) !== false) {
                            $savedFilePath = 'uploads/foto_rekonfirmasi/' . $filename;
                        }
                    }
                }

                // If file saved successfully, save relative path; otherwise fallback to base64 string
                $data['foto_anak'] = $savedFilePath ?? $fotoBase64;
            } elseif (str_starts_with($fotoBase64, 'uploads/') || str_starts_with($fotoBase64, 'http')) {
                $data['foto_anak'] = $fotoBase64;
            }
        }

        if ($isEditMode) {
            $target = $this->atsModel->where('nik', $editNik)->orWhere('id', $editId)->first();
            if (!$target) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Data yang akan diedit tidak ditemukan!'
                ]);
            }

            if ($role !== 'ADMIN') {
                $currentUsername = session()->get('username');
                $currentNama = session()->get('nama');
                $isOwner = (!empty($target['created_by']) && $target['created_by'] === $currentUsername)
                    || (!empty($target['nama_petugas']) && strtoupper($target['nama_petugas']) === strtoupper($currentNama));
                if (!$isOwner) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => 'Akses ditolak: Anda hanya berhak mengubah data yang Anda input sendiri!'
                    ]);
                }

                if (!in_array(strtoupper($target['kecamatan']), array_map('strtoupper', $allowedKec))) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => "Gagal: Anda tidak memiliki hak akses untuk mengedit data di Kecamatan {$target['kecamatan']}!"
                    ]);
                }
                if (!empty($allowedDesa) && !in_array(strtoupper($target['desa']), array_map('strtoupper', $allowedDesa))) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => "Gagal: Anda tidak memiliki hak akses untuk mengedit data di Desa {$target['desa']}!"
                    ]);
                }
            }

            // Pertahankan pembuat asli data
            if (!empty($target['created_by'])) {
                $data['created_by'] = $target['created_by'];
            }

            if (!isset($data['foto_anak'])) {
                if (!$isPhotoRemoved && !empty($target['foto_anak'])) {
                    $data['foto_anak'] = $target['foto_anak'];
                } else {
                    $data['foto_anak'] = null;
                }
            }

            $data['waktu_input'] = $target['waktu_input'];

            try {
                $this->atsModel->update($target['id'], $data);
            } catch (\Throwable $e) {
                log_message('error', 'Error updating ATS data: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ]);
            }

            // Record Activity Log
            ActivityLogModel::record('UPDATE_DATA', "Memperbarui data anak: {$data['nama_lengkap']} (NIK: {$data['nik']}) di Desa/Kel. {$data['desa']}, Kec. {$data['kecamatan']}.", [
                'nik_target' => $data['nik'],
                'nama_anak'  => $data['nama_lengkap'],
                'kecamatan'  => $data['kecamatan'],
                'desa'       => $data['desa'],
            ]);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => "Data anak {$data['nama_lengkap']} berhasil diperbarui!"
            ]);
        } else {
            try {
                $this->atsModel->insert($data);
            } catch (\Throwable $e) {
                log_message('error', 'Error inserting ATS data: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ]);
            }

            // Record Activity Log
            ActivityLogModel::record('CREATE_DATA', "Menambahkan data anak baru: {$data['nama_lengkap']} (NIK: {$data['nik']}) di Desa/Kel. {$data['desa']}, Kec. {$data['kecamatan']}.", [
                'nik_target' => $data['nik'],
                'nama_anak'  => $data['nama_lengkap'],
                'kecamatan'  => $data['kecamatan'],
                'desa'       => $data['desa'],
            ]);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => "Data anak {$data['nama_lengkap']} berhasil disimpan!"
            ]);
        }
    }

    /**
     * Delete record
     */
    public function deleteRecord($id)
    {
        $record = $this->atsModel->where('id', $id)->orWhere('nik', $id)->first();
        if (!$record) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Data tidak ditemukan.'
            ]);
        }

        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();

        if ($role !== 'ADMIN') {
            $currentUsername = session()->get('username');
            $currentNama = session()->get('nama');
            $isOwner = (!empty($record['created_by']) && $record['created_by'] === $currentUsername)
                || (!empty($record['nama_petugas']) && strtoupper($record['nama_petugas']) === strtoupper($currentNama));
            if (!$isOwner) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Anda hanya berhak menghapus data yang Anda input sendiri!'
                ]);
            }

            if (!in_array(strtoupper($record['kecamatan']), array_map('strtoupper', $allowedKec))) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => "Gagal: Anda tidak memiliki hak akses untuk menghapus data di Kecamatan {$record['kecamatan']}!"
                ]);
            }
            if (!empty($allowedDesa) && !in_array(strtoupper($record['desa']), array_map('strtoupper', $allowedDesa))) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => "Gagal: Anda tidak memiliki hak akses untuk menghapus data di Desa/Kelurahan {$record['desa']}!"
                ]);
            }
        }

        $this->atsModel->delete($record['id']);

        // Record Activity Log
        ActivityLogModel::record('DELETE_DATA', "Menghapus data anak: {$record['nama_lengkap']} (NIK: {$record['nik']}) dari Desa/Kel. {$record['desa']}, Kec. {$record['kecamatan']}.", [
            'nik_target' => $record['nik'],
            'nama_anak'  => $record['nama_lengkap'],
            'kecamatan'  => $record['kecamatan'],
            'desa'       => $record['desa'],
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Data anak {$record['nama_lengkap']} berhasil dihapus!"
        ]);
    }

    /**
     * Analytics Data API for Chart.js (filtered by Kecamatan, Desa & Role Access)
     */
    public function analyticsData()
    {
        $kecamatan = $this->request->getGet('kecamatan');
        $desa      = $this->request->getGet('desa');
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();

        $builder = $this->atsModel->builder();
        if ($role !== 'ADMIN') {
            $currentUsername = session()->get('username');
            $currentNama = session()->get('nama');
            // Isolasi data: Petugas non-admin hanya melihat data yang diinput sendiri
            $builder->groupStart()
                ->where('created_by', $currentUsername)
                ->orWhere('UPPER(nama_petugas)', strtoupper($currentNama))
                ->groupEnd();

            if (empty($kecamatan) || $kecamatan === 'Semua' || !in_array(strtoupper($kecamatan), array_map('strtoupper', $allowedKec))) {
                $builder->whereIn('kecamatan', array_map('strtoupper', $allowedKec));
            } else {
                $builder->where('kecamatan', strtoupper($kecamatan));
            }

            if (!empty($allowedDesa)) {
                $builder->whereIn('desa', array_map('strtoupper', $allowedDesa));
            } elseif (!empty($desa) && $desa !== 'Semua') {
                $builder->where('desa', strtoupper($desa));
            }
        } else {
            if (!empty($kecamatan) && $kecamatan !== 'Semua') {
                $builder->where('kecamatan', strtoupper($kecamatan));
            }
            if (!empty($desa) && $desa !== 'Semua') {
                $builder->where('desa', strtoupper($desa));
            }
        }

        $data = $builder->get()->getResultArray();

        $summary = [
            'totalData'     => count($data),
            'totalAts'      => 0,
            'totalSekolah'  => 0,
            'male'          => 0,
            'female'        => 0,
            'avgAge'        => 0,
            'topIjazah'     => '-',
            'totalKip'      => 0,
            'disabilitas'   => 0,
            'targetKembali' => [
                'totalInginKembali' => 0,
                'sd'                => 0,
                'smp'               => 0,
                'sma'               => 0,
                'paketA'            => 0,
                'paketB'            => 0,
                'paketC'            => 0,
                'skb'               => 0,
                'tidakIngin'        => 0,
            ]
        ];

        $dictKec          = [];
        $dictUmur         = [];
        $dictKeinginan    = ['Ingin Kembali Bersekolah' => 0, 'Tidak Ingin' => 0];
        $dictBentuk       = ['Formal' => 0, 'Non Formal' => 0];
        $dictAlasan       = [];
        $dictKeterampilan = [];
        $dictBaju         = [];
        $dictSepatu       = [];
        $dictIjazah       = [];

        $geoStats = [];
        $allKecList = $this->getAllPolmanKecamatan();
        foreach ($allKecList as $kName) {
            $kKey = strtoupper($kName);
            $geoStats[$kKey] = [
                'name'         => $kKey,
                'total'        => 0,
                'ats'          => 0,
                'sekolah'      => 0,
                'sd'           => 0,
                'smp'          => 0,
                'sma'          => 0,
                'paketA'       => 0,
                'paketB'       => 0,
                'paketC'       => 0,
                'skb'          => 0,
                'tidakIngin'   => 0,
                'topAlasan'    => '-',
                'alasanCounts' => []
            ];
        }

        $totalAge = 0;
        $countAge = 0;

        foreach ($data as $d) {
            $status = strtoupper($d['status_sekolah'] ?? '');
            $isAts  = str_contains($status, 'TIDAK');

            if ($isAts) {
                $summary['totalAts']++;
            } else {
                $summary['totalSekolah']++;
            }

            // Gender
            $jk = strtoupper($d['jenis_kelamin'] ?? '');
            if ($jk === 'L' || $jk === 'LAKI-LAKI') {
                $summary['male']++;
            } else if ($jk === 'P' || $jk === 'PEREMPUAN') {
                $summary['female']++;
            }

            // Age
            $u = (int)($d['umur'] ?? 0);
            if ($u >= 6 && $u <= 25) {
                $dictUmur[$u] = ($dictUmur[$u] ?? 0) + 1;
                $totalAge += $u;
                $countAge++;
            }

            // Kecamatan
            $kec = strtoupper($d['kecamatan'] ?? '');
            if (!empty($kec) && $kec !== '-') {
                $dictKec[$kec] = ($dictKec[$kec] ?? 0) + 1;
            }

            if (strtoupper($d['disabilitas'] ?? '') === 'YA') $summary['disabilitas']++;
            if (strtoupper($d['menerima_kip'] ?? '') === 'YA') $summary['totalKip']++;

            if ($isAts) {
                $ij = strtoupper($d['pendidikan_tertinggi'] ?? '');
                if (!empty($ij) && $ij !== '-') {
                    $dictIjazah[$ij] = ($dictIjazah[$ij] ?? 0) + 1;
                }

                $keinginan = strtoupper($d['keinginan'] ?? '');
                $isInginKembali = ((str_contains($keinginan, 'INGIN') && !str_contains($keinginan, 'TIDAK')) || $keinginan === 'YA' || str_contains($keinginan, 'KEMBALI'));
                if ($isInginKembali) {
                    $dictKeinginan['Ingin Kembali Bersekolah']++;
                    $summary['targetKembali']['totalInginKembali']++;

                    $tk = strtoupper(trim($d['target_kembali'] ?? ''));
                    if (empty($tk) || $tk === '-') {
                        $sat = strtoupper($d['satuan_pendidikan'] ?? '');
                        $bp  = strtoupper($d['bentuk_pendidikan'] ?? '');
                        $uAge = (int)($d['umur'] ?? 0);
                        if (str_contains($sat, 'SKB')) {
                            $tk = 'SKB';
                        } elseif (str_contains($sat, 'PAKET A') || ($bp === 'NON FORMAL' && ($uAge <= 12 || str_contains($ij, 'BPB') || str_contains($ij, 'TIDAK')))) {
                            $tk = 'PAKET A';
                        } elseif (str_contains($sat, 'PAKET B') || ($bp === 'NON FORMAL' && (($uAge >= 13 && $uAge <= 15) || str_contains($ij, 'SD')))) {
                            $tk = 'PAKET B';
                        } elseif (str_contains($sat, 'PAKET C') || ($bp === 'NON FORMAL' && ($uAge >= 16 || str_contains($ij, 'SMP')))) {
                            $tk = 'PAKET C';
                        } elseif ($bp === 'NON FORMAL') {
                            $tk = 'PAKET B';
                        } elseif (str_contains($sat, 'SMP') || ($uAge >= 13 && $uAge <= 15) || str_contains($ij, 'SD')) {
                            $tk = 'SMP';
                        } elseif (str_contains($sat, 'SMA') || str_contains($sat, 'SMK') || ($uAge >= 16) || str_contains($ij, 'SMP')) {
                            $tk = 'SMA';
                        } else {
                            $tk = 'SD';
                        }
                    }

                    if ($tk === 'SD') {
                        $summary['targetKembali']['sd']++;
                    } elseif ($tk === 'SMP') {
                        $summary['targetKembali']['smp']++;
                    } elseif ($tk === 'SMA' || $tk === 'SMK') {
                        $summary['targetKembali']['sma']++;
                    } elseif ($tk === 'PAKET A') {
                        $summary['targetKembali']['paketA']++;
                    } elseif ($tk === 'PAKET B') {
                        $summary['targetKembali']['paketB']++;
                    } elseif ($tk === 'PAKET C') {
                        $summary['targetKembali']['paketC']++;
                    } elseif ($tk === 'SKB') {
                        $summary['targetKembali']['skb']++;
                    } else {
                        $summary['targetKembali']['sd']++;
                    }
                } else if (str_contains($keinginan, 'TIDAK')) {
                    $dictKeinginan['Tidak Ingin']++;
                    $summary['targetKembali']['tidakIngin']++;
                }

                $bp = strtoupper($d['bentuk_pendidikan'] ?? '');
                if (str_contains($bp, 'FORMAL') && !str_contains($bp, 'NON')) {
                    $dictBentuk['Formal']++;
                } else if (str_contains($bp, 'NON FORMAL')) {
                    $dictBentuk['Non Formal']++;
                }

                $alasan = strtoupper($d['alasan'] ?? '');
                if (!empty($alasan) && $alasan !== '-') {
                    $dictAlasan[$alasan] = ($dictAlasan[$alasan] ?? 0) + 1;
                }

                $ket = $this->normalizeKeterampilan($d['keterampilan'] ?? '');
                if (!empty($ket) && $ket !== '-') {
                    $dictKeterampilan[$ket] = ($dictKeterampilan[$ket] ?? 0) + 1;
                }

                $baju = strtoupper($d['ukuran_baju'] ?? '');
                if (!empty($baju) && $baju !== '-') {
                    $dictBaju[$baju] = ($dictBaju[$baju] ?? 0) + 1;
                }

                $sepatu = trim($d['ukuran_sepatu'] ?? '');
                if (!empty($sepatu) && $sepatu !== '-') {
                    $dictSepatu[$sepatu] = ($dictSepatu[$sepatu] ?? 0) + 1;
                }
            }

            // Update GeoStats per Kecamatan
            $kKey = strtoupper($d['kecamatan'] ?? '');
            if (!empty($kKey) && $kKey !== '-') {
                if (!isset($geoStats[$kKey])) {
                    $geoStats[$kKey] = [
                        'name'         => $kKey,
                        'total'        => 0,
                        'ats'          => 0,
                        'sekolah'      => 0,
                        'sd'           => 0,
                        'smp'          => 0,
                        'sma'          => 0,
                        'paketA'       => 0,
                        'paketB'       => 0,
                        'paketC'       => 0,
                        'skb'          => 0,
                        'tidakIngin'   => 0,
                        'topAlasan'    => '-',
                        'alasanCounts' => []
                    ];
                }
                $geoStats[$kKey]['total']++;
                if ($isAts) {
                    $geoStats[$kKey]['ats']++;
                    if (!empty($tk)) {
                        if ($tk === 'SD') $geoStats[$kKey]['sd']++;
                        elseif ($tk === 'SMP') $geoStats[$kKey]['smp']++;
                        elseif ($tk === 'SMA' || $tk === 'SMK') $geoStats[$kKey]['sma']++;
                        elseif ($tk === 'PAKET A') $geoStats[$kKey]['paketA']++;
                        elseif ($tk === 'PAKET B') $geoStats[$kKey]['paketB']++;
                        elseif ($tk === 'PAKET C') $geoStats[$kKey]['paketC']++;
                        elseif ($tk === 'SKB') $geoStats[$kKey]['skb']++;
                    }
                    if (isset($keinginan) && str_contains($keinginan, 'TIDAK')) {
                        $geoStats[$kKey]['tidakIngin']++;
                    }
                    if (isset($alasan) && !empty($alasan) && $alasan !== '-') {
                        $geoStats[$kKey]['alasanCounts'][$alasan] = ($geoStats[$kKey]['alasanCounts'][$alasan] ?? 0) + 1;
                    }
                } else {
                    $geoStats[$kKey]['sekolah']++;
                }
            }
        }

        foreach ($geoStats as &$gs) {
            if (!empty($gs['alasanCounts'])) {
                arsort($gs['alasanCounts']);
                $gs['topAlasan'] = array_key_first($gs['alasanCounts']);
            }
            unset($gs['alasanCounts']);
        }
        unset($gs);

        $summary['avgAge'] = $countAge > 0 ? round($totalAge / $countAge, 1) : 0;
        if (!empty($dictIjazah)) {
            arsort($dictIjazah);
            $summary['topIjazah'] = array_key_first($dictIjazah);
        }

        ksort($dictUmur);
        ksort($dictSepatu);
        arsort($dictKeterampilan);
        $topKeterampilan = array_slice($dictKeterampilan, 0, 7, true);
        arsort($dictAlasan);
        $topAlasan = array_slice($dictAlasan, 0, 8, true);

        return $this->response->setJSON([
            'status'   => 'success',
            'summary'  => $summary,
            'geoStats' => $geoStats,
            'charts'   => [
                'kecamatan'    => [
                    'labels' => array_keys($dictKec),
                    'values' => array_values($dictKec),
                ],
                'umur'         => [
                    'labels' => array_map(fn($k) => "{$k} Thn", array_keys($dictUmur)),
                    'values' => array_values($dictUmur),
                ],
                'keinginan'    => [
                    'labels' => array_keys($dictKeinginan),
                    'values' => array_values($dictKeinginan),
                ],
                'bentuk'       => [
                    'labels' => array_keys($dictBentuk),
                    'values' => array_values($dictBentuk),
                ],
                'alasan'       => [
                    'labels' => array_keys($topAlasan),
                    'values' => array_values($topAlasan),
                ],
                'keterampilan' => [
                    'labels' => array_keys($topKeterampilan),
                    'values' => array_values($topKeterampilan),
                ],
                'baju'         => [
                    'labels' => array_keys($dictBaju),
                    'values' => array_values($dictBaju),
                ],
                'sepatu'       => [
                    'labels' => array_keys($dictSepatu),
                    'values' => array_values($dictSepatu),
                ],
                'targetKembali' => [
                    'labels' => ['SD', 'SMP', 'SMA', 'Paket A', 'Paket B', 'Paket C', 'SKB'],
                    'values' => [
                        $summary['targetKembali']['sd'],
                        $summary['targetKembali']['smp'],
                        $summary['targetKembali']['sma'],
                        $summary['targetKembali']['paketA'],
                        $summary['targetKembali']['paketB'],
                        $summary['targetKembali']['paketC'],
                        $summary['targetKembali']['skb'],
                    ],
                ],
            ]
        ]);
    }

    /**
     * Export to Excel (.xlsx) filtered by Kecamatan, Desa and Target
     */
    public function exportExcel()
    {
        $target    = $this->request->getGet('target') ?? 'Semua';
        $kecamatan = $this->request->getGet('kecamatan') ?? 'Semua';
        $desa      = $this->request->getGet('desa') ?? 'Semua';

        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();

        $builder = $this->atsModel->builder();
        if ($role !== 'ADMIN') {
            $currentUsername = session()->get('username');
            $currentNama = session()->get('nama');
            // Isolasi data: Petugas non-admin hanya mengekspor data yang diinput sendiri
            $builder->groupStart()
                ->where('created_by', $currentUsername)
                ->orWhere('UPPER(nama_petugas)', strtoupper($currentNama))
                ->groupEnd();

            if ($kecamatan === 'Semua' || !in_array(strtoupper($kecamatan), array_map('strtoupper', $allowedKec))) {
                $builder->whereIn('kecamatan', array_map('strtoupper', $allowedKec));
                $kecamatanLabel = implode(', ', $allowedKec);
            } else {
                $builder->where('kecamatan', strtoupper($kecamatan));
                $kecamatanLabel = $kecamatan;
            }

            if (!empty($allowedDesa)) {
                $builder->whereIn('desa', array_map('strtoupper', $allowedDesa));
                $desaLabel = implode(', ', $allowedDesa);
            } elseif ($desa !== 'Semua') {
                $builder->where('desa', strtoupper($desa));
                $desaLabel = $desa;
            } else {
                $desaLabel = 'Semua Desa';
            }
        } else {
            if ($kecamatan !== 'Semua') {
                $builder->where('kecamatan', strtoupper($kecamatan));
                $kecamatanLabel = $kecamatan;
            } else {
                $kecamatanLabel = 'Semua (16 Kecamatan)';
            }

            if ($desa !== 'Semua') {
                $builder->where('desa', strtoupper($desa));
                $desaLabel = $desa;
            } else {
                $desaLabel = 'Semua Desa/Kelurahan';
            }
        }

        $rows = $builder->get()->getResultArray();

        // Record Activity Log
        ActivityLogModel::record('EXPORT_EXCEL', "Mengunduh file Excel ATS kategori: {$target} (Wilayah: {$kecamatanLabel} / {$desaLabel}).", [
            'kecamatan' => $kecamatanLabel,
            'desa'      => $desaLabel,
        ]);

        $filtered = array_filter($rows, function ($d) use ($target) {
            if ($target === 'Semua') return true;

            $u = (int)$d['umur'];
            $ijazah = strtoupper($d['pendidikan_tertinggi'] ?? '');
            $tPend = strtoupper($d['bentuk_pendidikan'] ?? '');
            $nSat = strtoupper($d['satuan_pendidikan'] ?? '');
            $tk = strtoupper($d['target_kembali'] ?? '');

            if ($target === 'SD') {
                return $tk === 'SD' || ($u >= 7 && $u <= 12) || str_contains($ijazah, 'TIDAK') || $ijazah === 'BPB';
            }
            if ($target === 'SMP') {
                return $tk === 'SMP' || ($u >= 13 && $u <= 15) || str_contains($ijazah, 'SD');
            }
            if ($target === 'SMA') {
                return $tk === 'SMA' || $tk === 'SMK' || ($u >= 16 && $u <= 18) || str_contains($ijazah, 'SMP');
            }
            if ($target === 'PAKET A') {
                return $tk === 'PAKET A' || ($tPend === 'NON FORMAL' && (str_contains($nSat, 'PAKET A') || str_contains($nSat, 'PKBM') || ($u >= 7 && $u <= 12)));
            }
            if ($target === 'PAKET B') {
                return $tk === 'PAKET B' || ($tPend === 'NON FORMAL' && (str_contains($nSat, 'PAKET B') || ($u >= 13 && $u <= 15)));
            }
            if ($target === 'PAKET C') {
                return $tk === 'PAKET C' || ($tPend === 'NON FORMAL' && (str_contains($nSat, 'PAKET C') || ($u >= 16 && $u <= 25)));
            }
            if ($target === 'SKB') {
                return $tk === 'SKB' || str_contains($nSat, 'SKB');
            }
            return true;
        });

        // Generate Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data PANTAU ATS');

        // Header Title
        $sheet->setCellValue('A1', 'PEMERINTAH KABUPATEN POLEWALI MANDAR');
        $sheet->setCellValue('A2', 'DINAS PENDIDIKAN');
        $sheet->setCellValue('A3', 'PANTAU ATS - PEMANTAUAN DAN PEMUTAKHIRAN DATA ANAK TIDAK SEKOLAH');
        $sheet->setCellValue('A4', "Wilayah: Kab. Polewali Mandar | Kec: {$kecamatanLabel} | Desa/Kel: {$desaLabel} | Kategori: {$target} | Tanggal: " . date('d-m-Y H:i'));

        $sheet->getStyle('A1:A4')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2:A3')->getFont()->setSize(12);
        $sheet->getStyle('A4')->getFont()->setSize(10)->setItalic(true);

        $headers = [
            'No', 'Waktu Input', 'Jenis Input', 'Kecamatan', 'Desa/Kelurahan',
            'Petugas Pendata', 'No HP Petugas', 'NISN', 'NIK', 'Nama Lengkap Anak',
            'Jenis Kelamin', 'Tanggal Lahir', 'Umur', 'Disabilitas', 'No HP Ortu', 'Nama Ibu Kandung',
            'Status Bersekolah', 'Sekolah Aktif', 'Pendidikan Tertinggi', 'Asal Sekolah',
            'Status Tidak Sekolah', 'Kelas Putus Sekolah', 'Alasan Tidak Sekolah', 'Alasan Lainnya', 'Penerima Program PIP/KIP',
            'Minat Kembali Sekolah', 'Bentuk Pendidikan', 'Target Kembali', 'Satuan Pendidikan Tujuan',
            'Ukuran Baju', 'Ukuran Sepatu', 'Keterampilan Dibutuhkan'
        ];

        $col = 'A';
        $headerRow = 6;
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $headerRow, $h);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($col . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
            $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $col++;
        }

        $currentRow = 7;
        $no = 1;
        foreach ($filtered as $row) {
            $sheet->setCellValue('A' . $currentRow, $no++);
            $sheet->setCellValue('B' . $currentRow, $row['waktu_input']);
            $sheet->setCellValue('C' . $currentRow, $row['jenis_input']);
            $sheet->setCellValue('D' . $currentRow, $row['kecamatan']);
            $sheet->setCellValue('E' . $currentRow, $row['desa']);
            $sheet->setCellValue('F' . $currentRow, $row['nama_petugas']);
            $sheet->setCellValueExplicit('G' . $currentRow, $row['no_hp_petugas'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('H' . $currentRow, $row['nisn'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('I' . $currentRow, $row['nik'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('J' . $currentRow, $row['nama_lengkap']);
            $sheet->setCellValue('K' . $currentRow, $row['jenis_kelamin']);
            $sheet->setCellValue('L' . $currentRow, $row['tanggal_lahir']);
            $sheet->setCellValue('M' . $currentRow, $row['umur']);
            $sheet->setCellValue('N' . $currentRow, $row['disabilitas']);
            $sheet->setCellValueExplicit('O' . $currentRow, $row['no_hp_ortu'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('P' . $currentRow, $row['nama_ibu']);
            $sheet->setCellValue('Q' . $currentRow, $row['status_sekolah']);
            $sekolahDisplay = !empty($row['jenjang_aktif']) && $row['jenjang_aktif'] !== '-' 
                ? '[' . $row['jenjang_aktif'] . '] ' . ($row['sekolah_aktif'] ?? '') 
                : ($row['sekolah_aktif'] ?? '-');
            $sheet->setCellValue('R' . $currentRow, $sekolahDisplay);
            $sheet->setCellValue('S' . $currentRow, $row['pendidikan_tertinggi'] ?? '-');
            $sheet->setCellValue('T' . $currentRow, $row['asal_sekolah'] ?? '-');
            $sheet->setCellValue('U' . $currentRow, $row['status_tdk_sekolah'] ?? '-');
            $sheet->setCellValue('V' . $currentRow, $row['kelas_putus'] ?? '-');
            $sheet->setCellValue('W' . $currentRow, $row['alasan'] ?? '-');
            $sheet->setCellValue('X' . $currentRow, $row['alasan_lainnya'] ?? '-');
            $sheet->setCellValue('Y' . $currentRow, $row['menerima_kip'] ?? '-');
            $sheet->setCellValue('Z' . $currentRow, $row['keinginan'] ?? '-');
            $sheet->setCellValue('AA' . $currentRow, $row['bentuk_pendidikan'] ?? '-');
            $sheet->setCellValue('AB' . $currentRow, $row['target_kembali'] ?? '-');
            $sheet->setCellValue('AC' . $currentRow, $row['satuan_pendidikan'] ?? '-');
            $sheet->setCellValue('AD' . $currentRow, $row['ukuran_baju'] ?? '-');
            $sheet->setCellValue('AE' . $currentRow, $row['ukuran_sepatu'] ?? '-');
            $sheet->setCellValue('AF' . $currentRow, $row['keterampilan'] ?? '-');

            if ($no % 2 === 0) {
                $sheet->getStyle("A{$currentRow}:AF{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $currentRow++;
        }

        for ($colIndex = 1; $colIndex <= count($headers); $colIndex++) {
            $columnID = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $styleBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle("A6:AF" . ($currentRow - 1))->applyFromArray($styleBorder);

        $filename = "PANTAU_ATS_" . str_replace(' ', '_', $target) . "_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Print Individual ATS Verification Card
     */
    public function printCard($id)
    {
        $record = $this->atsModel->where('id', $id)->orWhere('nik', $id)->first();
        if (!$record) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();

        if ($role !== 'ADMIN') {
            $currentUsername = session()->get('username');
            $currentNama = session()->get('nama');
            $isOwner = (!empty($record['created_by']) && $record['created_by'] === $currentUsername)
                || (!empty($record['nama_petugas']) && strtoupper($record['nama_petugas']) === strtoupper($currentNama));
            if (!$isOwner) {
                return redirect()->to(base_url('ats'))->with('error', 'Akses ditolak: Anda hanya berhak mencetak kartu data yang Anda input sendiri.');
            }

            if (!in_array(strtoupper($record['kecamatan']), array_map('strtoupper', $allowedKec))) {
                return redirect()->to(base_url())->with('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk melihat data di luar wilayah tugas Anda.');
            }
            if (!empty($allowedDesa) && !in_array(strtoupper($record['desa']), array_map('strtoupper', $allowedDesa))) {
                return redirect()->to(base_url())->with('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk melihat data di luar wilayah desa Anda.');
            }
        }

        ActivityLogModel::record('PRINT_CARD', "Mencetak kartu verifikasi ATS anak: {$record['nama_lengkap']} (NIK: {$record['nik']}).", [
            'nik_target' => $record['nik'],
            'nama_anak'  => $record['nama_lengkap'],
            'kecamatan'  => $record['kecamatan'],
            'desa'       => $record['desa'],
        ]);

        return view('ats/print_card', ['data' => $record]);
    }

    /**
     * Print Full Rekapitulasi Report
     */
    public function printReport()
    {
        $kecamatan = $this->request->getGet('kecamatan') ?? 'Semua';
        $desa      = $this->request->getGet('desa') ?? 'Semua';
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();

        $builder = $this->atsModel->builder();
        if ($role !== 'ADMIN') {
            $currentUsername = session()->get('username');
            $currentNama = session()->get('nama');
            // Isolasi data: Petugas non-admin hanya mengekspor data yang diinput sendiri
            $builder->groupStart()
                ->where('created_by', $currentUsername)
                ->orWhere('UPPER(nama_petugas)', strtoupper($currentNama))
                ->groupEnd();

            if ($kecamatan === 'Semua' || !in_array(strtoupper($kecamatan), array_map('strtoupper', $allowedKec))) {
                $builder->whereIn('kecamatan', array_map('strtoupper', $allowedKec));
                $kecamatanFilterForSummary = $allowedKec;
                $displayKecamatan = implode(', ', $allowedKec);
            } else {
                $builder->where('kecamatan', strtoupper($kecamatan));
                $kecamatanFilterForSummary = $kecamatan;
                $displayKecamatan = $kecamatan;
            }

            if (!empty($allowedDesa)) {
                $builder->whereIn('desa', array_map('strtoupper', $allowedDesa));
                $desaFilterForSummary = $allowedDesa;
            } elseif ($desa !== 'Semua') {
                $builder->where('desa', strtoupper($desa));
                $desaFilterForSummary = $desa;
            } else {
                $desaFilterForSummary = null;
            }
        } else {
            if ($kecamatan !== 'Semua') {
                $builder->where('kecamatan', strtoupper($kecamatan));
                $kecamatanFilterForSummary = $kecamatan;
                $displayKecamatan = $kecamatan;
            } else {
                $kecamatanFilterForSummary = null;
                $displayKecamatan = 'Semua (16 Kecamatan)';
            }

            if ($desa !== 'Semua') {
                $builder->where('desa', strtoupper($desa));
                $desaFilterForSummary = $desa;
            } else {
                $desaFilterForSummary = null;
            }
        }

        $data = $builder->orderBy('kecamatan', 'ASC')->orderBy('desa', 'ASC')->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();
        $summary = $this->atsModel->getAnalyticsSummary(
            $kecamatanFilterForSummary,
            $desaFilterForSummary,
            $role === 'ADMIN' ? null : $currentUsername,
            $role === 'ADMIN' ? null : $currentNama
        );

        ActivityLogModel::record('PRINT_REPORT', "Mencetak dokumen resmi rekapitulasi ATS (Kecamatan: {$displayKecamatan}).", [
            'kecamatan' => $displayKecamatan,
        ]);

        return view('ats/print_report', [
            'data'      => $data,
            'summary'   => $summary,
            'kecamatan' => $displayKecamatan,
        ]);
    }

    /**
     * Print View / Dokumen PDF: Cetak Rekap Akun Petugas Rekonfirmasi
     * Dipisahkan per Kecamatan sesuai kebutuhan distribusi & tanda terima pelatihan
     */
    public function printAkun()
    {
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        if ($role !== 'ADMIN') {
            return redirect()->to(base_url('ats'))->with('error', 'Akses ditolak: Hanya Administrator yang berhak mencetak rekap akun.');
        }

        $selectedKec = trim($this->request->getGet('kecamatan') ?? 'Semua');

        $builder = $this->userModel->builder();
        $builder->orderBy('role', 'ASC')
            ->orderBy('kecamatan_akses', 'ASC')
            ->orderBy('desa_akses', 'ASC')
            ->orderBy('nama', 'ASC');

        if (!empty($selectedKec) && $selectedKec !== 'Semua') {
            if (strtoupper($selectedKec) === 'ADMIN') {
                $builder->where('UPPER(role)', 'ADMIN');
            } else {
                $builder->where('kecamatan_akses', $selectedKec);
            }
        }

        $users = $builder->get()->getResultArray();

        // Ambil daftar kecamatan unik dari seluruh user untuk filter
        $allKecamatan = $this->userModel
            ->select('kecamatan_akses')
            ->where('kecamatan_akses IS NOT NULL')
            ->where('kecamatan_akses !=', '')
            ->groupBy('kecamatan_akses')
            ->orderBy('kecamatan_akses', 'ASC')
            ->findAll();
        $kecamatanList = array_values(array_filter(array_column($allKecamatan, 'kecamatan_akses')));

        // Kelompokkan user per Kecamatan
        $grouped = [];
        foreach ($users as $u) {
            $isAdminUser = strtoupper($u['role'] ?? '') === 'ADMIN';
            $key = $isAdminUser ? 'ADMIN' : (trim($u['kecamatan_akses'] ?? '') ?: 'LAINNYA');
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'key'       => $key,
                    'is_admin'  => $isAdminUser,
                    'title'     => $isAdminUser ? 'ADMINISTRATOR SISTEM (TINGKAT KABUPATEN)' : 'KECAMATAN ' . strtoupper($key),
                    'subtitle'  => $isAdminUser ? 'Akses Penuh Seluruh 16 Kecamatan & Desa se-Kabupaten Polewali Mandar' : "Wilayah Penugasan Tim Petugas Rekonfirmasi Lapangan Kecamatan {$key}",
                    'users'     => []
                ];
            }
            $grouped[$key]['users'][] = $u;
        }

        ActivityLogModel::record(
            'PRINT_AKUN',
            "Administrator mencetak dokumen Rekap Akun Petugas Rekonfirmasi (Filter: {$selectedKec}, Total: " . count($users) . " akun)."
        );

        return view('ats/print_akun', [
            'users'         => $users,
            'grouped'       => $grouped,
            'selectedKec'   => $selectedKec,
            'kecamatanList' => $kecamatanList,
        ]);
    }

    /**
     * Change Password for currently logged in user
     */
    /**
     * Map database row to standard 27-column Matriks Rekonfirmasi Data ATS
     */
    protected function mapRowToMatriks(array $row): array
    {
        $nisn = (!empty($row['nisn']) && $row['nisn'] !== '-') ? $row['nisn'] : '-';
        $nik  = $row['nik'] ?? '-';
        $nama = strtoupper($row['nama_lengkap'] ?? '-');
        
        $jkRaw = strtoupper($row['jenis_kelamin'] ?? '');
        $jk = str_starts_with($jkRaw, 'L') ? 'L' : (str_starts_with($jkRaw, 'P') ? 'P' : '-');

        $tglLahir = (!empty($row['tanggal_lahir']) && $row['tanggal_lahir'] !== '0000-00-00') ? $row['tanggal_lahir'] : '-';
        $umur = $row['umur'] ?? '-';
        $pendTertinggi = (!empty($row['pendidikan_tertinggi']) && $row['pendidikan_tertinggi'] !== '-') ? $row['pendidikan_tertinggi'] : '-';

        $statusSekolahRaw = strtoupper($row['status_sekolah'] ?? '');
        $isBersekolah = str_contains($statusSekolahRaw, 'MASIH SEKOLAH') || str_contains($statusSekolahRaw, 'YA');
        $bersekolah = $isBersekolah ? '√' : '-';
        $tidakBersekolah = !$isBersekolah ? '√' : '-';

        $statusTdkSekolahRaw = strtoupper($row['status_tdk_sekolah'] ?? '');
        $putusJenjang = '-';
        $putusKelas   = '-';
        $putusTahun   = '-';
        $ltmJenjang   = '-';
        $ltmTahun     = '-';

        $isDO  = str_contains($statusTdkSekolahRaw, 'PUTUS') || str_contains($statusTdkSekolahRaw, 'DO');
        $isLTM = str_contains($statusTdkSekolahRaw, 'LULUS') || str_contains($statusTdkSekolahRaw, 'LTM') || str_contains($statusTdkSekolahRaw, 'TIDAK MELANJUTKAN');

        if ($isDO) {
            if (str_contains($pendTertinggi, 'SD')) {
                $putusJenjang = 'SD/MI';
            } elseif (str_contains($pendTertinggi, 'SMP')) {
                $putusJenjang = 'SMP/MTs';
            } elseif (str_contains($pendTertinggi, 'SMA') || str_contains($pendTertinggi, 'SMK')) {
                $putusJenjang = 'SMA/SMK';
            } else {
                $putusJenjang = $pendTertinggi !== '-' ? $pendTertinggi : 'SD/MI';
            }
            $putusKelas = (!empty($row['kelas_putus']) && $row['kelas_putus'] !== '-') ? str_ireplace(['kelas', 'kls', ' '], '', $row['kelas_putus']) : '-';
            if (empty($putusKelas)) $putusKelas = '-';
            $putusTahun = (!empty($row['tahun_putus']) && $row['tahun_putus'] !== '-') ? $row['tahun_putus'] : '-';
        } elseif ($isLTM) {
            if (str_contains($pendTertinggi, 'SD')) {
                $ltmJenjang = 'SD/MI';
            } elseif (str_contains($pendTertinggi, 'SMP')) {
                $ltmJenjang = 'SMP/MTs';
            } elseif (str_contains($pendTertinggi, 'SMA') || str_contains($pendTertinggi, 'SMK') || str_contains($pendTertinggi, 'MA')) {
                $ltmJenjang = 'SMA/SMK/MA';
            } else {
                $ltmJenjang = $pendTertinggi !== '-' ? $pendTertinggi : '-';
            }
            $ltmTahun = (!empty($row['tahun_lulus']) && $row['tahun_lulus'] !== '-') ? $row['tahun_lulus'] : '-';
        }

        $alasan = $row['alasan'] ?? '-';
        if (!empty($row['alasan_lainnya'])) {
            $alasan = ($alasan !== '-' ? $alasan . ' (' . $row['alasan_lainnya'] . ')' : $row['alasan_lainnya']);
        }

        $sekolahAsal = (!empty($row['asal_sekolah']) && $row['asal_sekolah'] !== '-') 
            ? $row['asal_sekolah'] 
            : ((!empty($row['sekolah_aktif']) && $row['sekolah_aktif'] !== '-') ? $row['sekolah_aktif'] : '-');

        $kipRaw = strtoupper($row['menerima_kip'] ?? '');
        $menerimaKip = (str_contains($kipRaw, 'YA')) ? 'Ya' : 'Tidak';

        $namaOrtu = (!empty($row['nama_ibu']) && $row['nama_ibu'] !== '-') ? $row['nama_ibu'] : '-';

        $keinginanRaw = strtoupper($row['keinginan'] ?? '');
        $keinginan = ($isBersekolah) ? '-' : (((str_contains($keinginanRaw, 'INGIN') && !str_contains($keinginanRaw, 'TIDAK')) || $keinginanRaw === 'YA' || str_contains($keinginanRaw, 'KEMBALI')) ? 'Ya' : 'Tidak');

        $tujuanFormal = '-';
        $tujuanNonFormal = '-';
        $bentukPend = strtoupper($row['bentuk_pendidikan'] ?? '');
        $satuanPend = (!empty($row['satuan_pendidikan']) && $row['satuan_pendidikan'] !== '-') 
            ? $row['satuan_pendidikan'] 
            : ((!empty($row['target_kembali']) && $row['target_kembali'] !== '-') ? $row['target_kembali'] : '-');

        if (!$isBersekolah && $keinginan === 'Ya') {
            if (str_contains($bentukPend, 'NON') || str_contains($satuanPend, 'PAKET') || str_contains($satuanPend, 'PKBM') || str_contains($satuanPend, 'SKB')) {
                $tujuanNonFormal = $satuanPend !== '-' ? $satuanPend : 'Non Formal';
            } else {
                $tujuanFormal = $satuanPend !== '-' ? $satuanPend : 'Formal';
            }
        }

        if ($isBersekolah) {
            $putusJenjang = '-';
            $putusKelas   = '-';
            $putusTahun   = '-';
            $ltmJenjang   = '-';
            $ltmTahun     = '-';
            $alasan       = '-';
            $tujuanFormal = '-';
            $tujuanNonFormal = '-';
            $ukuranBaju   = '-';
            $ukuranSepatu = '-';
            $keterampilan = '-';
        }

        $disabilitasRaw = strtoupper($row['disabilitas'] ?? '');
        $disabilitas = (str_contains($disabilitasRaw, 'YA')) ? 'Ya' : 'Tidak';

        $noHp = (!empty($row['no_hp_ortu']) && $row['no_hp_ortu'] !== '-') ? $row['no_hp_ortu'] : '-';
        $ukuranBaju = ($isBersekolah) ? '-' : ((!empty($row['ukuran_baju']) && $row['ukuran_baju'] !== '-') ? $row['ukuran_baju'] : '-');
        $ukuranSepatu = ($isBersekolah) ? '-' : ((!empty($row['ukuran_sepatu']) && $row['ukuran_sepatu'] !== '-') ? $row['ukuran_sepatu'] : '-');
        $keterampilan = ($isBersekolah) ? '-' : ((!empty($row['keterampilan']) && $row['keterampilan'] !== '-') ? $row['keterampilan'] : '-');
        $keterangan = (!empty($row['jenis_input']) && $row['jenis_input'] !== '-') ? $row['jenis_input'] : '-';

        return [
            'id'                   => $row['id'] ?? null,
            'petugas'              => $row['nama_petugas'] ?? '-',
            'no_hp_petugas'        => $row['no_hp_petugas'] ?? '-',
            'kecamatan'            => $row['kecamatan'] ?? '-',
            'desa'                 => $row['desa'] ?? '-',
            'nisn'                 => $nisn,
            'nik'                  => $nik,
            'nama_lengkap'         => $nama,
            'jk'                   => $jk,
            'tanggal_lahir'        => $tglLahir,
            'umur'                 => $umur,
            'pendidikan_tertinggi' => $pendTertinggi,
            'bersekolah'           => $bersekolah,
            'tidak_bersekolah'     => $tidakBersekolah,
            'putus_jenjang'        => $putusJenjang,
            'putus_kelas'          => $putusKelas,
            'putus_tahun'          => $putusTahun,
            'ltm_jenjang'          => $ltmJenjang,
            'ltm_tahun'            => $ltmTahun,
            'alasan'               => $alasan,
            'sekolah_asal'         => $sekolahAsal,
            'menerima_kip'         => $menerimaKip,
            'nama_ortu'            => $namaOrtu,
            'keinginan'            => $keinginan,
            'tujuan_formal'        => $tujuanFormal,
            'tujuan_non_formal'    => $tujuanNonFormal,
            'disabilitas'          => $disabilitas,
            'no_hp'                => $noHp,
            'ukuran_baju'          => $ukuranBaju,
            'ukuran_sepatu'        => $ukuranSepatu,
            'keterampilan'         => $keterampilan,
            'keterangan'           => $keterangan,
        ];
    }

    /**
     * Helper to prepare query and metadata for Matriks Rekonfirmasi
     */
    protected function getMatriksQueryData()
    {
        $petugas   = trim($this->request->getGet('petugas') ?? 'Semua');
        $kecamatan = trim($this->request->getGet('kecamatan') ?? 'Semua');
        $desa      = trim($this->request->getGet('desa') ?? 'Semua');
        $search    = trim($this->request->getGet('search') ?? '');

        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $allowedKec = $this->getUserAllowedKecamatan();
        $allowedDesa = $this->getUserAllowedDesa();
        $currentUsername = session()->get('username');
        $currentNama = session()->get('nama');

        $db = \Config\Database::connect();
        $builder = $db->table('ats_data');

        if ($role !== 'ADMIN') {
            // Isolasi data: Petugas non-admin hanya melihat data yang diinput sendiri
            $builder->groupStart()
                ->where('LOWER(TRIM(created_by))', strtolower(trim($currentUsername)))
                ->orWhere('UPPER(TRIM(nama_petugas))', strtoupper(trim($currentNama)))
                ->groupEnd();

            if ($kecamatan === 'Semua' || !in_array(strtoupper(trim($kecamatan)), array_map('strtoupper', array_map('trim', $allowedKec)))) {
                if (!empty($allowedKec)) {
                    $builder->whereIn('UPPER(TRIM(kecamatan))', array_map('strtoupper', array_map('trim', $allowedKec)));
                    $displayKecamatan = implode(', ', $allowedKec);
                } else {
                    $displayKecamatan = 'Semua Kecamatan Tugas';
                }
            } else {
                $builder->where('UPPER(TRIM(kecamatan))', strtoupper(trim($kecamatan)));
                $displayKecamatan = $kecamatan;
            }

            if (!empty($allowedDesa)) {
                $builder->whereIn('UPPER(TRIM(desa))', array_map('strtoupper', array_map('trim', $allowedDesa)));
                $displayDesa = implode(', ', $allowedDesa);
            } elseif ($desa !== 'Semua' && !empty($desa)) {
                $builder->where('UPPER(TRIM(desa))', strtoupper(trim($desa)));
                $displayDesa = $desa;
            } else {
                $displayDesa = 'Semua Desa/Kelurahan Tugas';
            }
        } else {
            if ($kecamatan !== 'Semua' && !empty($kecamatan)) {
                $builder->where('UPPER(TRIM(kecamatan))', strtoupper(trim($kecamatan)));
                $displayKecamatan = $kecamatan;
            } else {
                $displayKecamatan = 'Semua (16 Kecamatan)';
            }

            if ($desa !== 'Semua' && !empty($desa)) {
                $builder->where('UPPER(TRIM(desa))', strtoupper(trim($desa)));
                $displayDesa = $desa;
            } else {
                $displayDesa = 'Semua Desa/Kelurahan';
            }
        }

        if ($role !== 'ADMIN') {
            $displayPetugas = !empty($currentNama) ? $currentNama : $currentUsername;
            $userHp = session()->get('hp') ?? '-';
            if ($userHp === '-') {
                $uRow = $this->userModel->where('username', $currentUsername)->first();
                if ($uRow && !empty($uRow['hp'])) {
                    $userHp = $uRow['hp'];
                }
            }
            $displayPetugasHp = $userHp;
            $petugasList = [
                [
                    'name' => $displayPetugas,
                    'hp'   => $displayPetugasHp,
                    'user' => $currentUsername
                ]
            ];
        } else {
            $displayPetugas = 'Semua Petugas / Tim Entri';
            $displayPetugasHp = '-';
            if ($petugas !== 'Semua' && !empty($petugas)) {
                $builder->groupStart()
                    ->where('UPPER(TRIM(nama_petugas))', strtoupper(trim($petugas)))
                    ->orWhere('LOWER(TRIM(created_by))', strtolower(trim($petugas)))
                ->groupEnd();
                $displayPetugas = $petugas;

                // Lookup nomor HP petugas dari tabel ats_data atau users tanpa mengganggu builder utama
                $pRow = $db->table('ats_data')
                    ->select('no_hp_petugas')
                    ->groupStart()
                        ->where('UPPER(TRIM(nama_petugas))', strtoupper(trim($petugas)))
                        ->orWhere('LOWER(TRIM(created_by))', strtolower(trim($petugas)))
                    ->groupEnd()
                    ->where('no_hp_petugas IS NOT NULL')
                    ->where('no_hp_petugas !=', '')
                    ->get(1)->getRowArray();

                if ($pRow && !empty($pRow['no_hp_petugas'])) {
                    $displayPetugasHp = $pRow['no_hp_petugas'];
                } else {
                    $uRow = $this->userModel->where('nama', $petugas)->orWhere('username', $petugas)->first();
                    if ($uRow && !empty($uRow['hp'])) {
                        $displayPetugasHp = $uRow['hp'];
                    }
                }
            }
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('nama_lengkap', $search)
                ->orLike('nik', $search)
                ->orLike('nisn', $search)
                ->orLike('asal_sekolah', $search)
            ->groupEnd();
        }

        $rows = $builder->orderBy('kecamatan', 'ASC')
                        ->orderBy('desa', 'ASC')
                        ->orderBy('nama_lengkap', 'ASC')
                        ->get()->getResultArray();

        $mapped = array_map(fn($r) => $this->mapRowToMatriks($r), $rows);

        if ($role === 'ADMIN') {
            // Filter daftar petugas pendata berdasarkan Kecamatan dan Desa yang dipilih
            $pBuilder = $db->table('ats_data')
                ->select('nama_petugas, MAX(no_hp_petugas) as no_hp_petugas, MAX(created_by) as created_by')
                ->where('nama_petugas IS NOT NULL')
                ->where('nama_petugas !=', '');

            if ($kecamatan !== 'Semua' && !empty($kecamatan)) {
                $pBuilder->where('UPPER(TRIM(kecamatan))', strtoupper(trim($kecamatan)));
            }
            if ($desa !== 'Semua' && !empty($desa)) {
                $pBuilder->where('UPPER(TRIM(desa))', strtoupper(trim($desa)));
            }

            $petugasQuery = $pBuilder->groupBy('nama_petugas')->orderBy('nama_petugas', 'ASC')->get()->getResultArray();

            $uBuilder = $this->userModel->select('nama, hp, username, kecamatan_akses, desa_akses')->orderBy('nama', 'ASC');
            $usersList = $uBuilder->findAll();

            $petugasMap = [];
            foreach ($petugasQuery as $p) {
                $name = trim($p['nama_petugas']);
                if (!empty($name)) {
                    $petugasMap[$name] = [
                        'name' => $name,
                        'hp'   => $p['no_hp_petugas'] ?? '-',
                        'user' => $p['created_by'] ?? ''
                    ];
                }
            }
            foreach ($usersList as $u) {
                $name = trim($u['nama']);
                if (empty($name)) continue;

                $matchKec = true;
                $matchDesa = true;

                if ($kecamatan !== 'Semua' && !empty($kecamatan)) {
                    if (!empty($u['kecamatan_akses'])) {
                        $uKecs = array_map('trim', explode(',', strtoupper($u['kecamatan_akses'])));
                        if (!in_array(strtoupper($kecamatan), $uKecs)) {
                            $matchKec = false;
                        }
                    }
                }

                if ($desa !== 'Semua' && !empty($desa)) {
                    if (!empty($u['desa_akses'])) {
                        $uDesas = array_map('trim', explode(',', strtoupper($u['desa_akses'])));
                        if (!in_array(strtoupper($desa), $uDesas)) {
                            $matchDesa = false;
                        }
                    }
                }

                if ($matchKec && $matchDesa) {
                    if (!isset($petugasMap[$name])) {
                        $petugasMap[$name] = [
                            'name' => $name,
                            'hp'   => $u['hp'] ?? '-',
                            'user' => $u['username'] ?? ''
                        ];
                    }
                }
            }
            ksort($petugasMap);
            $petugasList = array_values($petugasMap);
        }

        return [
            'mapped'           => $mapped,
            'petugasList'      => $petugasList,
            'displayPetugas'   => $displayPetugas,
            'displayPetugasHp' => $displayPetugasHp,
            'displayKecamatan' => $displayKecamatan,
            'displayDesa'      => $displayDesa,
        ];
    }

    /**
     * AJAX Endpoint: Get Matriks Rekonfirmasi Data ATS (JSON)
     */
    public function getMatriksData()
    {
        $data = $this->getMatriksQueryData();

        return $this->response->setJSON([
            'status'       => 'success',
            'total'        => count($data['mapped']),
            'header_info'  => [
                'petugas'    => $data['displayPetugas'],
                'petugas_hp' => $data['displayPetugasHp'],
                'kecamatan'  => $data['displayKecamatan'],
                'desa'       => $data['displayDesa'],
            ],
            'petugas_list' => $data['petugasList'],
            'data'         => $data['mapped'],
        ]);
    }

    /**
     * Print View: Cetak Matriks Rekonfirmasi Data ATS
     */
    public function printMatriks()
    {
        $res = $this->getMatriksQueryData();

        ActivityLogModel::record('PRINT_MATRIKS', "Mencetak Matriks Rekonfirmasi Data ATS (Petugas: {$res['displayPetugas']}, Wilayah: {$res['displayKecamatan']} / {$res['displayDesa']}).", [
            'petugas'   => $res['displayPetugas'],
            'kecamatan' => $res['displayKecamatan'],
            'desa'      => $res['displayDesa'],
        ]);

        return view('ats/print_matriks', [
            'data'             => $res['mapped'],
            'displayPetugas'   => $res['displayPetugas'],
            'displayPetugasHp' => $res['displayPetugasHp'],
            'displayKecamatan' => $res['displayKecamatan'],
            'displayDesa'      => $res['displayDesa'],
        ]);
    }

    /**
     * Export to Excel: Matriks Rekonfirmasi Data ATS (Official 27-Column Format)
     */
    public function exportMatriks()
    {
        $res = $this->getMatriksQueryData();
        $mapped = $res['mapped'];
        $displayPetugas   = $res['displayPetugas'];
        $displayPetugasHp = $res['displayPetugasHp'];
        $displayKecamatan = $res['displayKecamatan'];
        $displayDesa      = $res['displayDesa'];

        ActivityLogModel::record('EXPORT_MATRIKS', "Mengunduh Excel Matriks Rekonfirmasi Data ATS (Petugas: {$displayPetugas}, Wilayah: {$displayKecamatan} / {$displayDesa}).", [
            'petugas'   => $displayPetugas,
            'kecamatan' => $displayKecamatan,
            'desa'      => $displayDesa,
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matriks Rekonfirmasi ATS');

        // Judul Dokumen
        $sheet->setCellValue('A1', 'PEMERINTAH KABUPATEN POLEWALI MANDAR');
        $sheet->setCellValue('A2', 'DINAS PENDIDIKAN');
        $sheet->setCellValue('A3', 'MATRIKS REKONFIRMASI DATA ANAK TIDAK SEKOLAH (ATS) TAHUN 2026');

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(13);
        $sheet->getStyle('A2')->getFont()->setSize(11);
        $sheet->getStyle('A3')->getFont()->setSize(12);

        // Info Wilayah & Petugas
        $sheet->setCellValue('A5', 'Kabupaten: Polewali Mandar');
        $sheet->setCellValue('A6', 'Kecamatan: ' . $displayKecamatan);
        $sheet->setCellValue('A7', 'Desa/Kelurahan: ' . $displayDesa);

        $sheet->setCellValue('H5', 'Petugas Rekonfirmasi: ' . $displayPetugas);
        $sheet->setCellValue('H6', 'No. HP Petugas: ' . ($displayPetugasHp ?: '-'));
        $sheet->setCellValue('H7', 'Total Terdata: ' . count($mapped) . ' Anak');

        $sheet->getStyle('A5:A7')->getFont()->setBold(true)->setSize(9.5);
        $sheet->getStyle('H5:H7')->getFont()->setBold(true)->setSize(9.5);

        // Header Matriks 3 Baris (Baris 9, 10, 11)
        // Baris 9
        $sheet->setCellValue('A9', "No");
        $sheet->setCellValue('B9', "NISN
(1)");
        $sheet->setCellValue('C9', "NIK
(2)");
        $sheet->setCellValue('D9', "Nama ATS
(3)");
        $sheet->setCellValue('E9', "JK
(L/P)
(4)");
        $sheet->setCellValue('F9', "Tgl Lahir
(5)");
        $sheet->setCellValue('G9', "Umur
(6)");
        $sheet->setCellValue('H9', "Pendidikan Tertinggi*
(7)");
        $sheet->setCellValue('I9', "Kegiatan Saat Ini
(8)");
        $sheet->setCellValue('K9', "Status Tidak Sekolah
(9)");
        $sheet->setCellValue('P9', "Alasan Tidak Sekolah
(Alasan DO atau LTM)
(10)");
        $sheet->setCellValue('Q9', "Sekolah Asal
(11)");
        $sheet->setCellValue('R9', "Penerima KIP
(Ya/Tidak)
(12)");
        $sheet->setCellValue('S9', "Nama Orang Tua / Wali
(13)");
        $sheet->setCellValue('T9', "Keinginan Kembali Sekolah**
(Ya/Tidak)
(14)");
        $sheet->setCellValue('U9', "Jika Ya, Tuliskan Satuan Pendidikan Tujuan
(15)");
        $sheet->setCellValue('W9', "Disabilitas / Cacat
(16)");
        $sheet->setCellValue('X9', "Nomor HP
(17)");
        $sheet->setCellValue('Y9', "Ukuran Baju
(18)");
        $sheet->setCellValue('Z9', "Ukuran Sepatu
(19)");
        $sheet->setCellValue('AA9', "Jenis Keterampilan Dibutuhkan***
(20)");
        $sheet->setCellValue('AB9', "Keterangan****
(21)");

        // Merge Header Baris 9 ke 11 untuk kolom tunggal
        $sheet->mergeCells('A9:A11');
        $sheet->mergeCells('B9:B11');
        $sheet->mergeCells('C9:C11');
        $sheet->mergeCells('D9:D11');
        $sheet->mergeCells('E9:E11');
        $sheet->mergeCells('F9:F11');
        $sheet->mergeCells('G9:G11');
        $sheet->mergeCells('H9:H11');
        $sheet->mergeCells('I9:J9');
        $sheet->mergeCells('K9:O9');
        $sheet->mergeCells('P9:P11');
        $sheet->mergeCells('Q9:Q11');
        $sheet->mergeCells('R9:R11');
        $sheet->mergeCells('S9:S11');
        $sheet->mergeCells('T9:T11');
        $sheet->mergeCells('U9:V9');
        $sheet->mergeCells('W9:W11');
        $sheet->mergeCells('X9:X11');
        $sheet->mergeCells('Y9:Y11');
        $sheet->mergeCells('Z9:Z11');
        $sheet->mergeCells('AA9:AA11');
        $sheet->mergeCells('AB9:AB11');

        // Baris 10: Sub-Header
        $sheet->setCellValue('I10', "Bersekolah
(8a)");
        $sheet->setCellValue('J10', "Tidak Bersekolah
(8b)");
        $sheet->setCellValue('K10', "Putus Sekolah (DO)
(9a)");
        $sheet->setCellValue('N10', "Lulus Tdk Lanjut (LTM)
(9b)");
        $sheet->setCellValue('U10', "Formal
(15a)");
        $sheet->setCellValue('V10', "Non Formal
(15b)");

        $sheet->mergeCells('I10:I11');
        $sheet->mergeCells('J10:J11');
        $sheet->mergeCells('K10:M10');
        $sheet->mergeCells('N10:O10');
        $sheet->mergeCells('U10:U11');
        $sheet->mergeCells('V10:V11');

        // Baris 11: Sub-Header Level 3
        $sheet->setCellValue('K11', "Jenjang
(9a1)");
        $sheet->setCellValue('L11', "Kelas
(9a2)");
        $sheet->setCellValue('M11', "Tahun
(9a3)");
        $sheet->setCellValue('N11', "Jenjang
(9b1)");
        $sheet->setCellValue('O11', "Tahun
(9b2)");

        // Styling Header (A9:AB11)
        $sheet->getStyle('A9:AB11')->getFont()->setBold(true)->setSize(8.5)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A9:AB11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet->getStyle('A9:AB11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                                      ->setVertical(Alignment::VERTICAL_CENTER)
                                                      ->setWrapText(true);

        // Isi Data dari Baris 12
        $currentRow = 12;
        $no = 1;
        foreach ($mapped as $r) {
            $sheet->setCellValue('A' . $currentRow, $no++);
            $sheet->setCellValueExplicit('B' . $currentRow, $r['nisn'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $currentRow, $r['nik'], DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $currentRow, $r['nama_lengkap']);
            $sheet->setCellValue('E' . $currentRow, $r['jk']);
            $sheet->setCellValue('F' . $currentRow, $r['tanggal_lahir']);
            $sheet->setCellValue('G' . $currentRow, $r['umur']);
            $sheet->setCellValue('H' . $currentRow, $r['pendidikan_tertinggi']);
            $sheet->setCellValue('I' . $currentRow, $r['bersekolah']);
            $sheet->setCellValue('J' . $currentRow, $r['tidak_bersekolah']);
            $sheet->setCellValue('K' . $currentRow, $r['putus_jenjang']);
            $sheet->setCellValue('L' . $currentRow, $r['putus_kelas']);
            $sheet->setCellValue('M' . $currentRow, $r['putus_tahun']);
            $sheet->setCellValue('N' . $currentRow, $r['ltm_jenjang']);
            $sheet->setCellValue('O' . $currentRow, $r['ltm_tahun']);
            $sheet->setCellValue('P' . $currentRow, $r['alasan']);
            $sheet->setCellValue('Q' . $currentRow, $r['sekolah_asal']);
            $sheet->setCellValue('R' . $currentRow, $r['menerima_kip']);
            $sheet->setCellValue('S' . $currentRow, $r['nama_ortu']);
            $sheet->setCellValue('T' . $currentRow, $r['keinginan']);
            $sheet->setCellValue('U' . $currentRow, $r['tujuan_formal']);
            $sheet->setCellValue('V' . $currentRow, $r['tujuan_non_formal']);
            $sheet->setCellValue('W' . $currentRow, $r['disabilitas']);
            $sheet->setCellValueExplicit('X' . $currentRow, $r['no_hp'], DataType::TYPE_STRING);
            $sheet->setCellValue('Y' . $currentRow, $r['ukuran_baju']);
            $sheet->setCellValue('Z' . $currentRow, $r['ukuran_sepatu']);
            $sheet->setCellValue('AA' . $currentRow, $r['keterampilan']);
            $sheet->setCellValue('AB' . $currentRow, $r['keterangan']);

            // Alignments per baris
            $sheet->getStyle("A{$currentRow}:C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$currentRow}:J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$currentRow}:O{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("R{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("T{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("W{$currentRow}:Z{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($no % 2 === 1) {
                $sheet->getStyle("A{$currentRow}:AB{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $currentRow++;
        }

        // Border Tabel
        $styleBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '64748B'],
                ],
            ],
        ];
        $sheet->getStyle('A9:AB' . ($currentRow - 1))->applyFromArray($styleBorder);

        // Auto width untuk kolom
        for ($colIndex = 1; $colIndex <= 28; $colIndex++) {
            $columnID = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $cleanPetugas = preg_replace('/[^A-Za-z0-9_-]/', '_', $displayPetugas);
        $filename = "MATRIKS_REKONFIRMASI_ATS_" . $cleanPetugas . "_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function changePassword()
    {
        $userId   = session()->get('user_id');
        $username = session()->get('username');

        if (!$userId && !$username) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Sesi login tidak valid. Silakan login kembali.'
            ]);
        }

        $user = null;
        if ($userId) {
            $user = $this->userModel->find($userId);
        }
        if (!$user && $username) {
            $user = $this->userModel->where('username', $username)->first();
        }

        if (!$user) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Akun pengguna tidak ditemukan di database.'
            ]);
        }

        $oldPassword     = (string)$this->request->getPost('oldPassword');
        $newPassword     = (string)$this->request->getPost('newPassword');
        $confirmPassword = (string)$this->request->getPost('confirmPassword');

        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Semua kolom password wajib diisi!'
            ]);
        }

        // Verify old password (supports bcrypt, plaintext fallback)
        if (!password_verify($oldPassword, $user['password']) && $user['password'] !== $oldPassword && $user['password'] !== md5($oldPassword)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Password saat ini (lama) tidak sesuai!'
            ]);
        }

        if (strlen($newPassword) < 4) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Password baru minimal harus 4 karakter!'
            ]);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Konfirmasi password baru tidak cocok!'
            ]);
        }

        // Update to new hashed password
        $this->userModel->update($user['id'], [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);

        ActivityLogModel::record('CHANGE_PASSWORD', "Pengguna '@{$user['username']}' ({$user['nama']}) berhasil memperbarui password akun.");

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Password berhasil diperbarui! Silakan gunakan password baru ini untuk login berikutnya.'
        ]);
    }

    /**
     * Update Profil Petugas / Admin (Nama, Nomor HP/WhatsApp, dan Password)
     */
    public function updateProfile()
    {
        $userId   = session()->get('user_id');
        $username = session()->get('username');

        if (!$userId && !$username) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Sesi login tidak valid. Silakan login kembali.'
            ]);
        }

        $user = null;
        if ($userId) {
            $user = $this->userModel->find($userId);
        }
        if (!$user && $username) {
            $user = $this->userModel->where('username', $username)->first();
        }

        if (!$user) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Akun pengguna tidak ditemukan di database.'
            ]);
        }

        $nama            = trim((string)$this->request->getPost('nama'));
        $hp              = trim((string)$this->request->getPost('hp'));
        $oldPassword     = (string)$this->request->getPost('oldPassword');
        $newPassword     = (string)$this->request->getPost('newPassword');
        $confirmPassword = (string)$this->request->getPost('confirmPassword');

        if (empty($nama)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Nama lengkap petugas tidak boleh kosong!'
            ]);
        }

        $hpClean = null;
        if (!empty($hp)) {
            $hpClean = preg_replace('/[^0-9]/', '', $hp);
            if (strlen($hpClean) < 10 || strlen($hpClean) > 13) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Nomor HP tidak valid! Harus terdiri dari 10 hingga 13 digit angka.'
                ]);
            }
        }

        $updateData = [
            'nama'       => $nama,
            'hp'         => $hpClean,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Jika petugas ingin mengganti password
        if (!empty($newPassword) || !empty($oldPassword)) {
            if (empty($oldPassword)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Silakan masukkan Password Saat Ini (Lama) untuk konfirmasi ganti password!'
                ]);
            }

            // Verify old password
            if (!password_verify($oldPassword, $user['password']) && $user['password'] !== $oldPassword && $user['password'] !== md5($oldPassword)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Password saat ini (lama) tidak sesuai!'
                ]);
            }

            if (strlen($newPassword) < 4) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Password baru minimal harus 4 karakter!'
                ]);
            }

            if ($newPassword !== $confirmPassword) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Konfirmasi password baru tidak cocok!'
                ]);
            }

            $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $this->userModel->update($user['id'], $updateData);

        // Perbarui data sesi aktif
        session()->set('nama', $nama);
        session()->set('hp', $hpClean);

        // Siapkan link WhatsApp resmi
        $waLink = '';
        if (!empty($hpClean)) {
            $waDigits = $hpClean;
            if (str_starts_with($waDigits, '08')) {
                $waDigits = '62' . substr($waDigits, 1);
            } elseif (str_starts_with($waDigits, '8')) {
                $waDigits = '62' . $waDigits;
            }
            $waLink = 'https://wa.me/' . $waDigits;
        }

        ActivityLogModel::record(
            'UPDATE_PROFILE',
            "Pengguna '@{$user['username']}' berhasil memperbarui profil (Nama: '{$nama}', No. HP/WA: '{$hpClean}')" . (!empty($updateData['password']) ? " dan mengganti password akun." : ".")
        );

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Profil berhasil diperbarui!' . (!empty($updateData['password']) ? ' Password baru Anda telah aktif.' : ''),
            'data'    => [
                'nama'    => $nama,
                'hp'      => $hpClean ?? '',
                'wa_link' => $waLink,
            ]
        ]);
    }

    /**
     * Get list of users (Admin only)
     */
    public function getUsers()
    {
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        if ($role !== 'ADMIN') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak: Hanya Administrator yang berhak mengelola akun.'
            ])->setStatusCode(403);
        }

        $users = $this->userModel->orderBy('role', 'ASC')->orderBy('username', 'ASC')->findAll();
        // Remove password hash from array for safety
        foreach ($users as &$u) {
            unset($u['password']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $users,
        ]);
    }

    /**
     * API: Tabulasi Rekap Kinerja & Jumlah Data yang Diinput Petugas / User
     */
    public function getTabulasiPetugas()
    {
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $currentUserId = session()->get('user_id');

        $userBuilder = $this->userModel->builder();
        if ($role !== 'ADMIN') {
            $userBuilder->where('id', $currentUserId);
        }
        $users = $userBuilder->orderBy('nama', 'ASC')->get()->getResultArray();

        $db = \Config\Database::connect();

        // 1. Agregasi per created_by (username)
        $qUser = $db->query("
            SELECT 
                created_by,
                COUNT(*) as total,
                SUM(CASE WHEN UPPER(status_sekolah) LIKE '%TIDAK%' THEN 1 ELSE 0 END) as total_ats,
                SUM(CASE WHEN UPPER(status_sekolah) NOT LIKE '%TIDAK%' THEN 1 ELSE 0 END) as total_sekolah,
                SUM(CASE WHEN UPPER(jenis_kelamin) IN ('L', 'LAKI-LAKI') THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN UPPER(jenis_kelamin) IN ('P', 'PEREMPUAN') THEN 1 ELSE 0 END) as female,
                MAX(created_at) as last_input
            FROM ats_data
            WHERE created_by IS NOT NULL AND created_by != ''
            GROUP BY created_by
        ")->getResultArray();

        $statsByUsername = [];
        foreach ($qUser as $row) {
            $statsByUsername[strtolower(trim($row['created_by']))] = $row;
        }

        // 2. Agregasi per nama_petugas
        $qNama = $db->query("
            SELECT 
                UPPER(nama_petugas) as nama_key,
                COUNT(*) as total,
                SUM(CASE WHEN UPPER(status_sekolah) LIKE '%TIDAK%' THEN 1 ELSE 0 END) as total_ats,
                SUM(CASE WHEN UPPER(status_sekolah) NOT LIKE '%TIDAK%' THEN 1 ELSE 0 END) as total_sekolah,
                SUM(CASE WHEN UPPER(jenis_kelamin) IN ('L', 'LAKI-LAKI') THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN UPPER(jenis_kelamin) IN ('P', 'PEREMPUAN') THEN 1 ELSE 0 END) as female,
                MAX(created_at) as last_input
            FROM ats_data
            WHERE (created_by IS NULL OR created_by = '') AND nama_petugas IS NOT NULL AND nama_petugas != ''
            GROUP BY UPPER(nama_petugas)
        ")->getResultArray();

        $statsByNama = [];
        foreach ($qNama as $row) {
            $statsByNama[strtoupper(trim($row['nama_key']))] = $row;
        }

        // 3. Agregasi real-time per Desa & Kecamatan dari tabel ats_data
        $qDesa = $db->query("
            SELECT 
                UPPER(TRIM(kecamatan)) as kec_key,
                UPPER(TRIM(desa)) as desa_key,
                COUNT(*) as total_input,
                SUM(CASE WHEN UPPER(status_sekolah) LIKE '%TIDAK%' THEN 1 ELSE 0 END) as total_ats,
                SUM(CASE WHEN UPPER(status_sekolah) NOT LIKE '%TIDAK%' THEN 1 ELSE 0 END) as total_sekolah
            FROM ats_data
            WHERE kecamatan IS NOT NULL AND kecamatan != '' AND desa IS NOT NULL AND desa != ''
            GROUP BY UPPER(TRIM(kecamatan)), UPPER(TRIM(desa))
        ")->getResultArray();

        $realisasiDesa = [];
        foreach ($qDesa as $rd) {
            $kKey = strtoupper(trim($rd['kec_key']));
            $dKey = self::normalizeDesaKey($rd['desa_key']);
            $realisasiDesa[$kKey][$dKey] = [
                'total_input'   => (int)$rd['total_input'],
                'total_ats'     => (int)$rd['total_ats'],
                'total_sekolah' => (int)$rd['total_sekolah'],
            ];
        }

        $result = [];
        $totalInputAll = 0;
        $totalAtsAll = 0;
        $totalSekolahAll = 0;
        $totalPetugasAktif = 0;
        $totalPetugasBelum = 0;

        foreach ($users as $u) {
            $uname = strtolower(trim($u['username']));
            $namaKey = strtoupper(trim($u['nama']));

            $uStats = $statsByUsername[$uname] ?? null;
            $nStats = $statsByNama[$namaKey] ?? null;

            $total = (int)($uStats['total'] ?? 0) + (int)($nStats['total'] ?? 0);
            $totalAts = (int)($uStats['total_ats'] ?? 0) + (int)($nStats['total_ats'] ?? 0);
            $totalSekolah = (int)($uStats['total_sekolah'] ?? 0) + (int)($nStats['total_sekolah'] ?? 0);
            $male = (int)($uStats['male'] ?? 0) + (int)($nStats['male'] ?? 0);
            $female = (int)($uStats['female'] ?? 0) + (int)($nStats['female'] ?? 0);

            $dates = array_filter([$uStats['last_input'] ?? null, $nStats['last_input'] ?? null]);
            $lastInput = !empty($dates) ? max($dates) : null;

            if ($total > 0) {
                $totalPetugasAktif++;
                $totalInputAll += $total;
                $totalAtsAll += $totalAts;
                $totalSekolahAll += $totalSekolah;
            } else {
                $totalPetugasBelum++;
            }

            // WhatsApp url
            $hpClean = preg_replace('/[^0-9]/', '', $u['hp'] ?? '');
            $waUrl = '';
            if (!empty($hpClean)) {
                $waDigits = $hpClean;
                if (str_starts_with($waDigits, '08')) {
                    $waDigits = '62' . substr($waDigits, 1);
                } elseif (str_starts_with($waDigits, '8')) {
                    $waDigits = '62' . $waDigits;
                }
                $waUrl = 'https://wa.me/' . $waDigits;
            }

            // Target individu berdasarkan penugasan desa
            $userKec = strtoupper(trim($u['kecamatan_akses'] ?? ''));
            $userDesa = self::normalizeDesaKey($u['desa_akses'] ?? '');
            $targetIndividu = 0;
            $targetDesaTotal = 0;
            if (isset(self::$MASTER_TARGET_REKONFIRMASI[$userKec]['desa'][$userDesa])) {
                $mInfo = self::$MASTER_TARGET_REKONFIRMASI[$userKec]['desa'][$userDesa];
                $targetIndividu = (float)$mInfo['target_per_petugas'];
                $targetDesaTotal = (int)$mInfo['target_ats'];
            }
            $pctIndividu = $targetIndividu > 0 ? round(($total / $targetIndividu) * 100, 1) : 0;
            $sisaIndividu = $targetIndividu > 0 ? max(0, round($targetIndividu - $total, 1)) : 0;

            $result[] = [
                'id'                 => (int)$u['id'],
                'username'           => $u['username'],
                'nama'               => $u['nama'],
                'role'               => strtoupper($u['role']),
                'hp'                 => $u['hp'] ?: '-',
                'wa_url'             => $waUrl,
                'kecamatan_akses'    => $u['kecamatan_akses'] ?: 'Semua (16 Kecamatan)',
                'desa_akses'         => $u['desa_akses'] ?: 'Semua Desa/Kelurahan',
                'target_individu'    => $targetIndividu,
                'target_desa'        => $targetDesaTotal,
                'pct_individu'       => $pctIndividu,
                'sisa_individu'      => $sisaIndividu,
                'total_input'        => $total,
                'total_ats'          => $totalAts,
                'total_sekolah'      => $totalSekolah,
                'male'               => $male,
                'female'             => $female,
                'last_input'         => $lastInput,
                'status_kinerja'     => $total > 0 ? 'AKTIF' : 'BELUM_ADA_DATA'
            ];
        }

        // Leaderboard order: Terbanyak input
        usort($result, function($a, $b) {
            if ($b['total_input'] === $a['total_input']) {
                return strcmp($a['nama'], $b['nama']);
            }
            return $b['total_input'] <=> $a['total_input'];
        });

        // 4. Bangun Rekapitulasi Target & Capaian Desa (Binuang & Anreapi)
        $usersByKecDesa = [];
        foreach ($result as $petugasItem) {
            $pk = strtoupper(trim($petugasItem['kecamatan_akses']));
            $pd = self::normalizeDesaKey($petugasItem['desa_akses']);
            $usersByKecDesa[$pk][$pd][] = $petugasItem;
        }

        $rekapDesa = [];
        $grandTargetAts = 0;
        $grandPetugas = 0;
        $grandRealisasiInput = 0;
        $grandRealisasiAts = 0;
        $grandRealisasiSekolah = 0;

        foreach (self::$MASTER_TARGET_REKONFIRMASI as $kecKey => $kData) {
            $subTargetAts = 0;
            $subPetugas = 0;
            $subRealisasiInput = 0;
            $subRealisasiAts = 0;
            $subRealisasiSekolah = 0;
            $desaList = [];

            foreach ($kData['desa'] as $desaName => $dMaster) {
                $real = $realisasiDesa[$kecKey][$desaName] ?? [
                    'total_input'   => 0,
                    'total_ats'     => 0,
                    'total_sekolah' => 0
                ];

                $tInput = $real['total_input'];
                $tAts = $real['total_ats'];
                $tSekolah = $real['total_sekolah'];
                $targetAts = (int)$dMaster['target_ats'];
                $jmlPetugas = (int)$dMaster['petugas'];
                $targetPerPetugas = (float)$dMaster['target_per_petugas'];

                $sisa = max(0, $targetAts - $tInput);
                $pct = $targetAts > 0 ? round(($tInput / $targetAts) * 100, 1) : 0;

                $petugasDesa = $usersByKecDesa[$kecKey][$desaName] ?? [];

                $desaList[] = [
                    'desa'               => $desaName,
                    'target_ats'         => $targetAts,
                    'petugas'            => $jmlPetugas,
                    'target_per_petugas' => $targetPerPetugas,
                    'realisasi_input'    => $tInput,
                    'realisasi_ats'      => $tAts,
                    'realisasi_sekolah'  => $tSekolah,
                    'sisa'               => $sisa,
                    'persentase'         => $pct,
                    'petugas_list'       => $petugasDesa
                ];

                $subTargetAts += $targetAts;
                $subPetugas += $jmlPetugas;
                $subRealisasiInput += $tInput;
                $subRealisasiAts += $tAts;
                $subRealisasiSekolah += $tSekolah;
            }

            $subSisa = max(0, $subTargetAts - $subRealisasiInput);
            $subPct = $subTargetAts > 0 ? round(($subRealisasiInput / $subTargetAts) * 100, 1) : 0;

            $rekapDesa[$kecKey] = [
                'kecamatan'         => $kecKey,
                'target_total'      => $subTargetAts,
                'petugas_total'     => $subPetugas,
                'realisasi_input'   => $subRealisasiInput,
                'realisasi_ats'     => $subRealisasiAts,
                'realisasi_sekolah' => $subRealisasiSekolah,
                'sisa'              => $subSisa,
                'persentase'        => $subPct,
                'items'             => $desaList
            ];

            $grandTargetAts += $subTargetAts;
            $grandPetugas += $subPetugas;
            $grandRealisasiInput += $subRealisasiInput;
            $grandRealisasiAts += $subRealisasiAts;
            $grandRealisasiSekolah += $subRealisasiSekolah;
        }

        $grandSisa = max(0, $grandTargetAts - $grandRealisasiInput);
        $grandPct = $grandTargetAts > 0 ? round(($grandRealisasiInput / $grandTargetAts) * 100, 1) : 0;

        $topKontributor = !empty($result) && $result[0]['total_input'] > 0 ? $result[0] : null;
        $avgInput = $totalPetugasAktif > 0 ? round($totalInputAll / $totalPetugasAktif, 1) : 0;

        return $this->response->setJSON([
            'status'            => 'success',
            'summary'           => [
                'total_petugas'             => count($users),
                'petugas_aktif'             => $totalPetugasAktif,
                'petugas_belum'             => $totalPetugasBelum,
                'total_data_terinput'       => $totalInputAll,
                'total_ats'                 => $totalAtsAll,
                'total_sekolah'             => $totalSekolahAll,
                'avg_input'                 => $avgInput,
                'target_rekonfirmasi_total' => $grandTargetAts, // 1045
                'target_petugas_total'      => $grandPetugas,   // 74
                'realisasi_input_total'     => $grandRealisasiInput,
                'realisasi_ats_total'       => $grandRealisasiAts,
                'realisasi_sekolah_total'   => $grandRealisasiSekolah,
                'sisa_target_total'         => $grandSisa,
                'persentase_total'          => $grandPct,
                'anreapi'                   => [
                    'target'     => $rekapDesa['ANREAPI']['target_total'] ?? 181,
                    'petugas'    => $rekapDesa['ANREAPI']['petugas_total'] ?? 13,
                    'realisasi'  => $rekapDesa['ANREAPI']['realisasi_input'] ?? 0,
                    'sisa'       => $rekapDesa['ANREAPI']['sisa'] ?? 181,
                    'persentase' => $rekapDesa['ANREAPI']['persentase'] ?? 0,
                ],
                'binuang'                   => [
                    'target'     => $rekapDesa['BINUANG']['target_total'] ?? 864,
                    'petugas'    => $rekapDesa['BINUANG']['petugas_total'] ?? 61,
                    'realisasi'  => $rekapDesa['BINUANG']['realisasi_input'] ?? 0,
                    'sisa'       => $rekapDesa['BINUANG']['sisa'] ?? 864,
                    'persentase' => $rekapDesa['BINUANG']['persentase'] ?? 0,
                ],
                'top_kontributor'           => $topKontributor ? [
                    'nama'     => $topKontributor['nama'],
                    'username' => $topKontributor['username'],
                    'total'    => $topKontributor['total_input'],
                    'wilayah'  => $topKontributor['kecamatan_akses']
                ] : null
            ],
            'rekap_target_desa' => $rekapDesa,
            'data'              => $result
        ]);
    }

    /**
     * Save user (Insert or Update) - Admin only (Configured by Desa/Kelurahan)
     */
    public function saveUser()
    {
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        if ($role !== 'ADMIN') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak: Hanya Administrator yang berhak mengelola akun.'
            ])->setStatusCode(403);
        }

        $id        = $this->request->getPost('id');
        $username  = strtolower(trim((string)$this->request->getPost('username')));
        $nama      = trim((string)$this->request->getPost('nama'));
        $userRole  = strtoupper(trim((string)$this->request->getPost('role')));
        $hp        = trim((string)$this->request->getPost('hp'));
        if (!empty($hp)) {
            $hpClean = preg_replace('/[^0-9]/', '', $hp);
            if (strlen($hpClean) < 10 || strlen($hpClean) > 13) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal: Nomor WhatsApp / HP Petugas tidak boleh kurang dari 10 digit dan tidak boleh lebih dari 13 digit!'
                ]);
            }
            $hp = $hpClean;
        }
        $password  = (string)$this->request->getPost('password');
        $kecAkses  = trim((string)$this->request->getPost('kecamatan_akses'));
        $desaAkses = $this->request->getPost('desa_akses');

        if (empty($username) || empty($nama)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Username dan Nama Petugas wajib diisi!'
            ]);
        }

        if (!in_array($userRole, ['ADMIN', 'USER'])) {
            $userRole = 'USER';
        }

        // Process kecamatan_akses & desa_akses
        $kecamatanString = null;
        $desaString = null;

        if ($userRole === 'USER') {
            if (empty($kecAkses)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Kecamatan wilayah tugas wajib dipilih!'
                ]);
            }
            $kecamatanString = $kecAkses;

            if (is_array($desaAkses)) {
                $filteredDesa = array_filter(array_map('trim', $desaAkses));
                $desaString = !empty($filteredDesa) ? implode(',', $filteredDesa) : null;
            } elseif (is_string($desaAkses)) {
                $desaString = trim($desaAkses);
            }

            if (empty($desaString)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Tim Entri wajib dipilihkan minimal 1 Desa / Kelurahan wilayah tugas!'
                ]);
            }
        }

        // Check unique username
        $existing = $this->userModel->where('username', $username)->first();
        if ($existing && (!empty($id) && (int)$existing['id'] !== (int)$id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Username '{$username}' sudah digunakan oleh pengguna lain!"
            ]);
        }
        if ($existing && empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Username '{$username}' sudah terdaftar!"
            ]);
        }

        $userData = [
            'username'        => $username,
            'nama'            => $nama,
            'role'            => $userRole,
            'hp'              => $hp,
            'kecamatan_akses' => $kecamatanString,
            'desa_akses'      => $desaString,
        ];

        if (!empty($password)) {
            $userData['password'] = password_hash($password, PASSWORD_BCRYPT);
        } elseif (empty($id)) {
            // Default password for new user if left blank
            $userData['password'] = password_hash('admin', PASSWORD_BCRYPT);
        }

        if (!empty($id)) {
            $this->userModel->update($id, $userData);
            $msg = "Akun pengguna '{$nama}' berhasil diperbarui!";
            ActivityLogModel::record('MANAGE_USER', "Administrator memperbarui akun pengguna: '{$nama}' (@{$username}) [Kec: {$kecamatanString}, Desa: {$desaString}].");
        } else {
            $this->userModel->insert($userData);
            $msg = "Akun baru '{$nama}' berhasil ditambahkan!";
            ActivityLogModel::record('MANAGE_USER', "Administrator menambahkan akun pengguna baru: '{$nama}' (@{$username}) [Kec: {$kecamatanString}, Desa: {$desaString}].");
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Delete user - Admin only
     */
    public function deleteUser($id)
    {
        $currentRole = strtoupper(session()->get('role') ?? 'ADMIN');
        $currentUserId = session()->get('user_id');

        if ($currentRole !== 'ADMIN') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak: Hanya Administrator yang berhak menghapus akun.'
            ])->setStatusCode(403);
        }

        if ((int)$currentUserId === (int)$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal: Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!'
            ]);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Akun pengguna tidak ditemukan.'
            ]);
        }

        $this->userModel->delete($id);

        ActivityLogModel::record('MANAGE_USER', "Administrator menghapus akun pengguna: '{$user['nama']}' (@{$user['username']}).");

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Akun '{$user['nama']}' ({$user['username']}) berhasil dihapus!"
        ]);
    }

    /**
     * Get Activity Logs & History (Intelligent Audit Trail)
     */
    public function getLogs()
    {
        $role = strtoupper(session()->get('role') ?? 'ADMIN');
        $currentUserId = session()->get('user_id');
        $allowedDesa = $this->getUserAllowedDesa();

        $logModel = new ActivityLogModel();
        $builder = $logModel->builder();

        if ($role !== 'ADMIN') {
            // Isolasi log: Petugas non-admin hanya melihat riwayat aktivitasnya sendiri
            $builder->where('user_id', $currentUserId);
        }

        $action = $this->request->getGet('action');
        if (!empty($action) && $action !== 'Semua') {
            $builder->where('action', strtoupper($action));
        }

        $query = $this->request->getGet('q');
        if (!empty($query)) {
            $builder->groupStart()
                ->like('description', $query)
                ->orLike('nama_petugas', $query)
                ->orLike('username', $query)
                ->orLike('nik_target', $query)
                ->orLike('nama_anak', $query)
                ->orLike('desa', $query)
                ->orLike('kecamatan', $query)
                ->groupEnd();
        }

        $logs = $builder->orderBy('id', 'DESC')->limit(150)->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $logs,
        ]);
    }
}
