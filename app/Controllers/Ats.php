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
     * Main Admin Panel interface
     */
    public function index()
    {
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

        $summary = $this->atsModel->getAnalyticsSummary(
            $role === 'ADMIN' ? null : $allowedKecamatan,
            $role === 'ADMIN' ? null : $allowedDesa
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

        $statusSekolah       = strtoupper(trim($this->request->getPost('statusSekolah') ?? ''));
        $pendidikanTertinggi = strtoupper(trim($this->request->getPost('pendidikanTertinggi') ?? '-'));
        $keinginan           = strtoupper(trim($this->request->getPost('keinginan') ?? '-'));
        $targetKembali       = strtoupper(trim($this->request->getPost('targetKembali') ?? ''));
        $bentukPendidikan    = strtoupper(trim($this->request->getPost('bentukPendidikan') ?? '-'));
        $ukuranBaju          = strtoupper(trim($this->request->getPost('baju') ?? '-'));
        $ukuranSepatu        = trim($this->request->getPost('sepatu') ?? '-');

        if (str_contains($keinginan, 'INGIN KEMBAL')) {
            if ($bentukPendidikan === 'FORMAL') {
                if (empty($ukuranBaju) || $ukuranBaju === '-') {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => 'Untuk jalur Formal, Ukuran Baju Seragam wajib dipilih!'
                    ]);
                }
                if (empty($ukuranSepatu) || $ukuranSepatu === '-') {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => 'Untuk jalur Formal, Ukuran Sepatu wajib diisi!'
                    ]);
                }
            } else {
                // Non Formal: Tidak perlu ukuran sepatu
                $ukuranSepatu = '-';
            }
        } else {
            $ukuranBaju   = '-';
            $ukuranSepatu = '-';
        }

        if (empty($targetKembali) && str_contains($keinginan, 'INGIN KEMBAL')) {
            $sat = strtoupper(trim($this->request->getPost('satuanPendidikan') ?? ''));
            $bp  = strtoupper(trim($this->request->getPost('bentukPendidikan') ?? ''));
            $ij  = strtoupper($pendidikanTertinggi);
            $u   = (int)$umur;
            if (str_contains($sat, 'SKB')) {
                $targetKembali = 'SKB';
            } elseif (str_contains($sat, 'PAKET A') || ($bp === 'NON FORMAL' && ($u <= 12 || str_contains($ij, 'BPB') || str_contains($ij, 'TIDAK')))) {
                $targetKembali = 'PAKET A';
            } elseif (str_contains($sat, 'PAKET B') || ($bp === 'NON FORMAL' && (($u >= 13 && $u <= 15) || str_contains($ij, 'SD')))) {
                $targetKembali = 'PAKET B';
            } elseif (str_contains($sat, 'PAKET C') || ($bp === 'NON FORMAL' && ($u >= 16 || str_contains($ij, 'SMP')))) {
                $targetKembali = 'PAKET C';
            } elseif ($bp === 'NON FORMAL') {
                $targetKembali = 'PAKET B';
            } elseif (str_contains($sat, 'SMP') || ($u >= 13 && $u <= 15) || str_contains($ij, 'SD')) {
                $targetKembali = 'SMP';
            } elseif (str_contains($sat, 'SMA') || str_contains($sat, 'SMK') || ($u >= 16) || str_contains($ij, 'SMP')) {
                $targetKembali = 'SMA';
            } else {
                $targetKembali = 'SD';
            }
        }

        $data = [
            'waktu_input'          => date('Y-m-d H:i:s'),
            'jenis_input'          => strtoupper(trim($this->request->getPost('jenisInput') ?? 'DATA BARU')),
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
            'no_hp_ortu'           => trim($this->request->getPost('hpOrtu') ?? '-') ?: '-',
            'nama_ibu'             => strtoupper(trim($this->request->getPost('namaIbu') ?? '')),
            'status_sekolah'       => $statusSekolah,
            'sekolah_aktif'        => strtoupper(trim($this->request->getPost('sekolahAktif') ?? '-')) ?: '-',
            'pendidikan_tertinggi' => $pendidikanTertinggi ?: '-',
            'asal_sekolah'         => strtoupper(trim($this->request->getPost('asalSekolah') ?? '-')) ?: '-',
            'status_tdk_sekolah'   => strtoupper(trim($this->request->getPost('statusTdkSekolah') ?? '-')) ?: '-',
            'alasan'               => strtoupper(trim($this->request->getPost('alasan') ?? '-')) ?: '-',
            'alasan_lainnya'       => trim($this->request->getPost('alasanLainnya') ?? '') ?: null,
            'menerima_kip'         => strtoupper(trim($this->request->getPost('penerimaKip') ?? '-')) ?: '-',
            'keinginan'            => $keinginan ?: '-',
            'bentuk_pendidikan'    => $bentukPendidikan ?: '-',
            'target_kembali'       => $targetKembali ?: '-',
            'satuan_pendidikan'    => strtoupper(trim($this->request->getPost('satuanPendidikan') ?? '-')) ?: '-',
            'ukuran_baju'          => $ukuranBaju,
            'ukuran_sepatu'        => $ukuranSepatu,
            'keterampilan'         => strtoupper(trim($this->request->getPost('keterampilan') ?? '-')) ?: '-',
            'created_by'           => session()->get('username') ?? 'admin',
        ];

        // Foto anak (Base64 string or image upload)
        $fotoBase64 = $this->request->getPost('fotoBase64');
        if (!empty($fotoBase64) && str_starts_with($fotoBase64, 'data:image')) {
            $data['foto_anak'] = $fotoBase64;
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

            if (empty($data['foto_anak']) && !empty($target['foto_anak'])) {
                $data['foto_anak'] = $target['foto_anak'];
            }

            $data['waktu_input'] = $target['waktu_input'];

            $this->atsModel->update($target['id'], $data);

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
            $this->atsModel->insert($data);

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
                if (str_contains($keinginan, 'INGIN KEMBAL')) {
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

                $ket = strtoupper($d['keterampilan'] ?? '');
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
        }

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
            'status'  => 'success',
            'summary' => $summary,
            'charts'  => [
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
                    'labels' => ['SD', 'SMP', 'SMA/SMK', 'Paket A', 'Paket B', 'Paket C', 'SKB'],
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
            'Status Tidak Sekolah', 'Alasan Tidak Sekolah', 'Alasan Lainnya', 'Penerima KIP',
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
            $sheet->setCellValue('R' . $currentRow, $row['sekolah_aktif'] ?? '-');
            $sheet->setCellValue('S' . $currentRow, $row['pendidikan_tertinggi'] ?? '-');
            $sheet->setCellValue('T' . $currentRow, $row['asal_sekolah'] ?? '-');
            $sheet->setCellValue('U' . $currentRow, $row['status_tdk_sekolah'] ?? '-');
            $sheet->setCellValue('V' . $currentRow, $row['alasan'] ?? '-');
            $sheet->setCellValue('W' . $currentRow, $row['alasan_lainnya'] ?? '-');
            $sheet->setCellValue('X' . $currentRow, $row['menerima_kip'] ?? '-');
            $sheet->setCellValue('Y' . $currentRow, $row['keinginan'] ?? '-');
            $sheet->setCellValue('Z' . $currentRow, $row['bentuk_pendidikan'] ?? '-');
            $sheet->setCellValue('AA' . $currentRow, $row['target_kembali'] ?? '-');
            $sheet->setCellValue('AB' . $currentRow, $row['satuan_pendidikan'] ?? '-');
            $sheet->setCellValue('AC' . $currentRow, $row['ukuran_baju'] ?? '-');
            $sheet->setCellValue('AD' . $currentRow, $row['ukuran_sepatu'] ?? '-');
            $sheet->setCellValue('AE' . $currentRow, $row['keterampilan'] ?? '-');

            if ($no % 2 === 0) {
                $sheet->getStyle("A{$currentRow}:AE{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
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
        $sheet->getStyle("A6:AE" . ($currentRow - 1))->applyFromArray($styleBorder);

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
        $summary = $this->atsModel->getAnalyticsSummary($kecamatanFilterForSummary, $desaFilterForSummary);

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
     * Change Password for currently logged in user
     */
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
            $builder->groupStart()
                ->where('user_id', $currentUserId);
            if (!empty($allowedDesa)) {
                $builder->orWhereIn('desa', array_map('strtoupper', $allowedDesa));
            }
            $builder->groupEnd();
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
