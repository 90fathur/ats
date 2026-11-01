<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username',
        'password',
        'nama',
        'role',
        'kecamatan_akses',
        'desa_akses',
        'hp',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Authenticate user with password_verify or fallback to plaintext / md5 for flexibility
     */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->where('LOWER(username)', strtolower(trim($username)))->first();
        if (!$user) {
            return null;
        }

        // Support password_verify, direct hash, or plain text match
        if (password_verify($password, $user['password']) || $user['password'] === $password || $user['password'] === md5($password)) {
            return $user;
        }

        return null;
    }
}

