<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKecamatanAksesToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'kecamatan_akses' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'role',
                'comment'    => 'Daftar kecamatan yang dapat diakses (koma dipisahkan atau JSON, null = semua)',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'kecamatan_akses');
    }
}

