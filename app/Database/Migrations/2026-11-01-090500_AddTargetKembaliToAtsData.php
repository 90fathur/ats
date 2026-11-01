<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTargetKembaliToAtsData extends Migration
{
    public function up()
    {
        // Add target_kembali column
        if (!$this->db->fieldExists('target_kembali', 'ats_data')) {
            $this->forge->addColumn('ats_data', [
                'target_kembali' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'bentuk_pendidikan',
                ]
            ]);
        }

        // Auto-classify existing data
        $rows = $this->db->table('ats_data')->where('keinginan', 'INGIN KEMBALI BERSEKOLAH')->get()->getResultArray();
        foreach ($rows as $r) {
            $id  = $r['id'];
            $sat = strtoupper($r['satuan_pendidikan'] ?? '');
            $bp  = strtoupper($r['bentuk_pendidikan'] ?? '');
            $ij  = strtoupper($r['pendidikan_tertinggi'] ?? '');
            $u   = (int)($r['umur'] ?? 0);

            $target = 'SD';
            if (str_contains($sat, 'SKB')) {
                $target = 'SKB';
            } elseif (str_contains($sat, 'PAKET A') || ($bp === 'NON FORMAL' && ($u <= 12 || str_contains($ij, 'BPB') || str_contains($ij, 'TIDAK')))) {
                $target = 'PAKET A';
            } elseif (str_contains($sat, 'PAKET B') || ($bp === 'NON FORMAL' && (($u >= 13 && $u <= 15) || str_contains($ij, 'SD')))) {
                $target = 'PAKET B';
            } elseif (str_contains($sat, 'PAKET C') || ($bp === 'NON FORMAL' && ($u >= 16 || str_contains($ij, 'SMP')))) {
                $target = 'PAKET C';
            } elseif ($bp === 'NON FORMAL') {
                $target = 'PAKET B';
            } elseif (str_contains($sat, 'SMP') || ($u >= 13 && $u <= 15) || str_contains($ij, 'SD')) {
                $target = 'SMP';
            } elseif (str_contains($sat, 'SMA') || str_contains($sat, 'SMK') || ($u >= 16) || str_contains($ij, 'SMP')) {
                $target = 'SMA';
            } else {
                $target = 'SD';
            }

            $this->db->table('ats_data')->where('id', $id)->update(['target_kembali' => $target]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('target_kembali', 'ats_data')) {
            $this->forge->dropColumn('ats_data', 'target_kembali');
        }
    }
}
