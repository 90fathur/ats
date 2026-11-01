<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ActivityLogModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('ats'));
        }

        return view('auth/login');
    }

    public function login()
    {
        $username = trim($this->request->getVar('username') ?? '');
        $password = trim($this->request->getVar('password') ?? '');

        if (empty($username) || empty($password)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Silakan isi Username dan Password Anda.'
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'Silakan isi Username dan Password Anda.');
        }

        $user = $this->userModel->authenticate($username, $password);

        if (!$user) {
            ActivityLogModel::record('LOGIN_FAILED', "Percobaan login gagal untuk username: '{$username}'.", [
                'username'     => $username,
                'nama_petugas' => 'Tamu / Percobaan Login',
                'role'         => 'GUEST'
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Username atau Password salah. Silakan periksa kembali.'
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'Username atau Password salah. Silakan periksa kembali.');
        }

        // Set session
        session()->set([
            'logged_in'       => true,
            'user_id'         => $user['id'],
            'username'        => $user['username'],
            'nama'            => $user['nama'],
            'role'            => strtoupper($user['role']),
            'kecamatan_akses' => $user['kecamatan_akses'] ?? null,
            'desa_akses'      => $user['desa_akses'] ?? null,
            'hp'              => $user['hp'],
        ]);

        // Record Activity Log
        ActivityLogModel::record('LOGIN', "Petugas '{$user['nama']}' (@{$user['username']}) berhasil login ke sistem.", [
            'user_id'      => $user['id'],
            'username'     => $user['username'],
            'nama_petugas' => $user['nama'],
            'role'         => strtoupper($user['role']),
            'kecamatan'    => $user['kecamatan_akses'] ?? null,
            'desa'         => $user['desa_akses'] ?? null,
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => 'Login berhasil! Mengalihkan...',
                'redirect' => base_url('ats'),
                'data'     => [
                    'username' => $user['username'],
                    'nama'     => $user['nama'],
                    'role'     => strtoupper($user['role']),
                    'hp'       => $user['hp']
                ]
            ]);
        }

        return redirect()->to(base_url('ats'));
    }

    public function logout()
    {
        if (session()->get('logged_in')) {
            ActivityLogModel::record('LOGOUT', "Pengguna '@" . session()->get('username') . "' keluar dari sistem.", [
                'user_id'      => session()->get('user_id'),
                'username'     => session()->get('username'),
                'nama_petugas' => session()->get('nama'),
                'role'         => session()->get('role'),
                'kecamatan'    => session()->get('kecamatan_akses'),
                'desa'         => session()->get('desa_akses'),
            ]);
        }
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Anda telah berhasil keluar.');
    }
}

