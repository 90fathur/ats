<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKelasPutusToAtsData extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('kelas_putus', 'ats_data')) {
            $this->forge->addColumn('ats_data', [
                'kelas_putus' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'status_tdk_sekolah',
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('kelas_putus', 'ats_data')) {
            $this->forge->dropColumn('ats_data', 'kelas_putus');
        }
    }
}
