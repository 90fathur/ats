<?php

namespace App\Models;

use CodeIgniter\Model;

class AtsModel extends Model
{
    protected $table            = 'ats_data';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'waktu_input',
        'jenis_input',
        'kabupaten',
        'kecamatan',
        'desa',
        'nama_petugas',
        'no_hp_petugas',
        'nisn',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tanggal_lahir',
        'umur',
        'disabilitas',
        'no_hp_ortu',
        'nama_ibu',
        'status_sekolah',
        'sekolah_aktif',
        'pendidikan_tertinggi',
        'asal_sekolah',
        'status_tdk_sekolah',
        'alasan',
        'alasan_lainnya',
        'menerima_kip',
        'keinginan',
        'bentuk_pendidikan',
        'satuan_pendidikan',
        'ukuran_baju',
        'ukuran_sepatu',
        'keterampilan',
        'foto_anak',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get aggregate analytics summary filtered by Kecamatan
     */
    public function getAnalyticsSummary($kecamatan = null): array
    {
        $builder = $this->builder();
        if (!empty($kecamatan)) {
            if (is_array($kecamatan)) {
                $upper = array_map('strtoupper', $kecamatan);
                if (!empty($upper)) {
                    $builder->whereIn('kecamatan', $upper);
                } else {
                    $builder->where('1 = 0'); // No access
                }
            } else if ($kecamatan !== 'Semua') {
                $builder->where('kecamatan', strtoupper($kecamatan));
            }
        }

        $totalData = $builder->countAllResults(false);

        // ATS vs Masih Sekolah
        $totalAts = (clone $builder)->like('status_sekolah', 'TIDAK')->countAllResults(false);
        $totalMasihSekolah = (clone $builder)->like('status_sekolah', 'SEKOLAH')->countAllResults(false);

        // Gender
        $maleCount = (clone $builder)->groupStart()
            ->where('jenis_kelamin', 'LAKI-LAKI')
            ->orWhere('jenis_kelamin', 'L')
            ->groupEnd()->countAllResults(false);

        $femaleCount = (clone $builder)->groupStart()
            ->where('jenis_kelamin', 'PEREMPUAN')
            ->orWhere('jenis_kelamin', 'P')
            ->groupEnd()->countAllResults(false);

        // Avg age
        $avgAgeRow = (clone $builder)->selectAvg('umur')->get()->getRow();
        $avgAge = $avgAgeRow ? round((float)$avgAgeRow->umur, 1) : 0;

        // Disabilitas
        $disabilitasCount = (clone $builder)->where('disabilitas', 'YA')->countAllResults(false);

        // Penerima KIP
        $kipCount = (clone $builder)->where('menerima_kip', 'YA')->countAllResults(false);

        // Ijazah terbanyak
        $topIjazahRow = (clone $builder)
            ->select('pendidikan_tertinggi, COUNT(*) as total')
            ->where('pendidikan_tertinggi IS NOT NULL')
            ->where('pendidikan_tertinggi !=', '')
            ->where('pendidikan_tertinggi !=', '-')
            ->groupBy('pendidikan_tertinggi')
            ->orderBy('total', 'DESC')
            ->limit(1)
            ->get()->getRow();
        $topIjazah = $topIjazahRow ? $topIjazahRow->pendidikan_tertinggi : '-';

        return [
            'totalData'         => $totalData,
            'totalAts'          => $totalAts,
            'totalMasihSekolah' => $totalMasihSekolah,
            'maleCount'         => $maleCount,
            'femaleCount'       => $femaleCount,
            'avgAge'            => $avgAge,
            'disabilitasCount'  => $disabilitasCount,
            'kipCount'          => $kipCount,
            'topIjazah'         => $topIjazah,
        ];
    }
}
