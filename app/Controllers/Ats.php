<?php

namespace App\Controllers;

use App\Models\AtsModel;
use App\Models\UserModel;
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
     * Main Admin Panel interface
     */
    public function index()
    {
        $currentUser = [
            'username' => session()->get('username') ?? 'ari',
            'nama'     => session()->get('nama') ?? 'Arifuddin',
            'role'     => session()->get('role') ?? 'USER',
            'hp'       => session()->get('hp') ?? '085145888881',
        ];

        $summary = $this->atsModel->getAnalyticsSummary();

        // Load all kecamatan
        $jsonPath = FCPATH . 'data/wilayah_kecamatan.json';
        $kecamatanList = [];
        if (file_exists($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true);
            $kecamatanList = array_keys($data);
        }

        return view('ats/index', [
            'currentUser'   => $currentUser,
            'summary'       => $summary,
            'kecamatanList' => $kecamatanList,
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
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    /**
     * API: Get ATS Data list (filtered by kecamatan or search query)
     */
    public function getData()
    {
        $kecamatan = $this->request->getGet('kecamatan');
        $query     = $this->request->getGet('q');

        $builder = $this->atsModel->builder();

        if (!empty($kecamatan) && $kecamatan !== 'Semua') {
            $builder->where('kecamatan', strtoupper($kecamatan));
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

        $data = [
            'waktu_input'          => date('Y-m-d H:i:s'),
            'jenis_input'          => strtoupper(trim($this->request->getPost('jenisInput') ?? 'DATA BARU')),
            'kabupaten'            => 'POLEWALI MANDAR',
            'kecamatan'            => strtoupper(trim($this->request->getPost('kecamatan') ?? '-')),
            'desa'                 => strtoupper(trim($this->request->getPost('desa') ?? '-')),
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
            'bentuk_pendidikan'    => strtoupper(trim($this->request->getPost('bentukPendidikan') ?? '-')) ?: '-',
            'satuan_pendidikan'    => strtoupper(trim($this->request->getPost('satuanPendidikan') ?? '-')) ?: '-',
            'ukuran_baju'          => strtoupper(trim($this->request->getPost('baju') ?? '-')) ?: '-',
            'ukuran_sepatu'        => trim($this->request->getPost('sepatu') ?? '-') ?: '-',
            'keterampilan'         => strtoupper(trim($this->request->getPost('keterampilan') ?? '-')) ?: '-',
            'created_by'           => session()->get('username') ?? 'ari',
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

            if (empty($data['foto_anak']) && !empty($target['foto_anak'])) {
                $data['foto_anak'] = $target['foto_anak'];
            }

            $data['waktu_input'] = $target['waktu_input'];

            $this->atsModel->update($target['id'], $data);
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => "Data anak {$data['nama_lengkap']} berhasil diperbarui!"
            ]);
        } else {
            $this->atsModel->insert($data);
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

        $this->atsModel->delete($record['id']);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Data anak {$record['nama_lengkap']} berhasil dihapus!"
        ]);
    }

    /**
     * Analytics Data API for Chart.js (filtered by Kecamatan)
     */
    public function analyticsData()
    {
        $kecamatan = $this->request->getGet('kecamatan');

        $builder = $this->atsModel->builder();
        if (!empty($kecamatan) && $kecamatan !== 'Semua') {
            $builder->where('kecamatan', strtoupper($kecamatan));
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
            'disabilitas'   => 0
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
                } else if (str_contains($keinginan, 'TIDAK')) {
                    $dictKeinginan['Tidak Ingin']++;
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
            ]
        ]);
    }

    /**
     * Export to Excel (.xlsx) filtered by Kecamatan and Target
     */
    public function exportExcel()
    {
        $target    = $this->request->getGet('target') ?? 'Semua';
        $kecamatan = $this->request->getGet('kecamatan') ?? 'Semua';

        $builder = $this->atsModel->builder();
        if ($kecamatan !== 'Semua') {
            $builder->where('kecamatan', strtoupper($kecamatan));
        }

        $rows = $builder->get()->getResultArray();

        $filtered = array_filter($rows, function ($d) use ($target) {
            if ($target === 'Semua') return true;

            $u = (int)$d['umur'];
            $ijazah = strtoupper($d['pendidikan_tertinggi'] ?? '');
            $tPend = strtoupper($d['bentuk_pendidikan'] ?? '');
            $nSat = strtoupper($d['satuan_pendidikan'] ?? '');

            if ($target === 'SD') {
                return ($u >= 7 && $u <= 12) || str_contains($ijazah, 'TIDAK') || $ijazah === 'BPB';
            }
            if ($target === 'SMP') {
                return ($u >= 13 && $u <= 15) || str_contains($ijazah, 'SD');
            }
            if ($target === 'SMA') {
                return ($u >= 16 && $u <= 18) || str_contains($ijazah, 'SMP');
            }
            if ($target === 'PAKET A') {
                return $tPend === 'NON FORMAL' && (str_contains($nSat, 'PAKET A') || str_contains($nSat, 'PKBM') || ($u >= 7 && $u <= 12));
            }
            if ($target === 'PAKET B') {
                return $tPend === 'NON FORMAL' && (str_contains($nSat, 'PAKET B') || ($u >= 13 && $u <= 15));
            }
            if ($target === 'PAKET C') {
                return $tPend === 'NON FORMAL' && (str_contains($nSat, 'PAKET C') || ($u >= 16 && $u <= 25));
            }
            return true;
        });

        // Generate Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Rekonfirmasi ATS');

        // Header Title
        $sheet->setCellValue('A1', 'PEMERINTAH KABUPATEN POLEWALI MANDAR');
        $sheet->setCellValue('A2', 'DINAS PENDIDIKAN DAN KEBUDAYAAN');
        $sheet->setCellValue('A3', 'SISTEM INFORMASI REKAPITULASI ANAK TIDAK SEKOLAH (ATS)');
        $sheet->setCellValue('A4', "Wilayah: Kabupaten Polewali Mandar | Kecamatan: {$kecamatan} | Kategori: {$target} | Tanggal Unduh: " . date('d-m-Y H:i'));

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
            'Minat Kembali Sekolah', 'Bentuk Pendidikan', 'Satuan Pendidikan Tujuan',
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
            $sheet->setCellValue('AA' . $currentRow, $row['satuan_pendidikan'] ?? '-');
            $sheet->setCellValue('AB' . $currentRow, $row['ukuran_baju'] ?? '-');
            $sheet->setCellValue('AC' . $currentRow, $row['ukuran_sepatu'] ?? '-');
            $sheet->setCellValue('AD' . $currentRow, $row['keterampilan'] ?? '-');

            if ($no % 2 === 0) {
                $sheet->getStyle("A{$currentRow}:AD{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
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
        $sheet->getStyle("A6:AD" . ($currentRow - 1))->applyFromArray($styleBorder);

        $filename = "Laporan_ATS_" . str_replace(' ', '_', $target) . "_" . date('Ymd_His') . ".xlsx";

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

        return view('ats/print_card', ['data' => $record]);
    }

    /**
     * Print Full Rekapitulasi Report
     */
    public function printReport()
    {
        $kecamatan = $this->request->getGet('kecamatan') ?? 'Semua';
        $builder = $this->atsModel->builder();
        if ($kecamatan !== 'Semua') {
            $builder->where('kecamatan', strtoupper($kecamatan));
        }

        $data = $builder->orderBy('kecamatan', 'ASC')->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();
        $summary = $this->atsModel->getAnalyticsSummary($kecamatan);

        return view('ats/print_report', [
            'data'      => $data,
            'summary'   => $summary,
            'kecamatan' => $kecamatan,
        ]);
    }
}
