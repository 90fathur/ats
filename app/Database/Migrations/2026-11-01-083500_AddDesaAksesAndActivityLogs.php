<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDesaAksesAndActivityLogs extends Migration
{
    public function up()
    {
        // 1. Tambah kolom desa_akses pada tabel users
        $fields = [
            'desa_akses' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'kecamatan_akses',
                'comment'    => 'Daftar desa/kelurahan yang dapat diakses (koma dipisahkan, null = semua desa)',
            ],
        ];
        $this->forge->addColumn('users', $fields);

        // 2. Buat tabel activity_logs untuk history aktivitas cerdas
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'nama_petugas' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'USER',
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'nik_target' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'nama_anak' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'kecamatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'desa' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['action', 'created_at']);
        $this->forge->addKey(['username', 'created_at']);
        $this->forge->createTable('activity_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs', true);
        $this->forge->dropColumn('users', 'desa_akses');
    }
}

