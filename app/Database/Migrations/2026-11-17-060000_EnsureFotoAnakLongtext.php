<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureFotoAnakLongtext extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('foto_anak', 'ats_data')) {
            $this->forge->addColumn('ats_data', [
                'foto_anak' => [
                    'type'  => 'LONGTEXT',
                    'null'  => true,
                    'after' => 'keterampilan',
                ]
            ]);
        } else {
            try {
                $this->db->query("ALTER TABLE `ats_data` MODIFY `foto_anak` LONGTEXT NULL");
            } catch (\Throwable $e) {
                // Ignore if read-only or already modified
            }
        }
    }

    public function down()
    {
        // Keep column intact to prevent data loss
    }
}
