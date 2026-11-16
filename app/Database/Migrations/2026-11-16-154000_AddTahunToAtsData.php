<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTahunToAtsData extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('tahun_putus', 'ats_data')) {
            $this->forge->addColumn('ats_data', [
                'tahun_putus' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'kelas_putus',
                ]
            ]);
        }

        if (!$this->db->fieldExists('tahun_lulus', 'ats_data')) {
            $this->forge->addColumn('ats_data', [
                'tahun_lulus' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'tahun_putus',
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('tahun_lulus', 'ats_data')) {
            $this->forge->dropColumn('ats_data', 'tahun_lulus');
        }
        if ($this->db->fieldExists('tahun_putus', 'ats_data')) {
            $this->forge->dropColumn('ats_data', 'tahun_putus');
        }
    }
}
