<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAtsTables extends Migration
{
    public function up()
    {
        // 1. Table users
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'USER', // 'ADMIN' or 'USER'
            ],
            'hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);

        // 2. Table ats_data
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'waktu_input' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'jenis_input' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'DATA BARU',
            ],
            'kabupaten' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'kecamatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'desa' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'nama_petugas' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'no_hp_petugas' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'nisn' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'unique'     => true,
            ],
            'nama_lengkap' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'jenis_kelamin' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'tanggal_lahir' => [
                'type' => 'DATE',
            ],
            'umur' => [
                'type'       => 'INT',
                'constraint' => 3,
            ],
            'disabilitas' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'TIDAK',
            ],
            'no_hp_ortu' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nama_ibu' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'status_sekolah' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'sekolah_aktif' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'pendidikan_tertinggi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'asal_sekolah' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'status_tdk_sekolah' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'alasan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'alasan_lainnya' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'menerima_kip' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'keinginan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'bentuk_pendidikan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'satuan_pendidikan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'ukuran_baju' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'ukuran_sepatu' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'keterampilan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'foto_anak' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kabupaten');
        $this->forge->addKey('status_sekolah');
        $this->forge->createTable('ats_data', true);
    }

    public function down()
    {
        $this->forge->dropTable('ats_data', true);
        $this->forge->dropTable('users', true);
    }
}
