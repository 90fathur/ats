<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJenjangAktifToAtsData extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('jenjang_aktif', 'ats_data')) {
            $this->forge->addColumn('ats_data', [
                'jenjang_aktif' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'status_sekolah',
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('jenjang_aktif', 'ats_data')) {
            $this->forge->dropColumn('ats_data', 'jenjang_aktif');
        }
    }
}

