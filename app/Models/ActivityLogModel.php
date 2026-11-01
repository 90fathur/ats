<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'username',
        'nama_petugas',
        'role',
        'action',
        'description',
        'nik_target',
        'nama_anak',
        'kecamatan',
        'desa',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Seamlessly record an audit/activity log
     */
    public static function record(string $action, string $description, array $context = []): void
    {
        try {
            $model = new self();
            $request = service('request');

            $ip = $request ? $request->getIPAddress() : null;
            $agent = $request ? (string)$request->getUserAgent() : null;

            $model->insert([
                'user_id'      => session()->get('user_id') ?? ($context['user_id'] ?? null),
                'username'     => session()->get('username') ?? ($context['username'] ?? 'sistem'),
                'nama_petugas' => session()->get('nama') ?? ($context['nama_petugas'] ?? 'Sistem ATS'),
                'role'         => session()->get('role') ?? ($context['role'] ?? 'USER'),
                'action'       => strtoupper($action),
                'description'  => $description,
                'nik_target'   => $context['nik_target'] ?? null,
                'nama_anak'    => $context['nama_anak'] ?? null,
                'kecamatan'    => $context['kecamatan'] ?? null,
                'desa'         => $context['desa'] ?? null,
                'ip_address'   => $ip,
                'user_agent'   => substr($agent ?? '', 0, 250),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal mencatat log aktivitas: ' . $e->getMessage());
        }
    }
}
