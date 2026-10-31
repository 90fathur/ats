<?php

namespace App\Controllers;

use App\Models\UserModel;

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
            'logged_in' => true,
            'user_id'   => $user['id'],
            'username'  => $user['username'],
            'nama'      => $user['nama'],
            'role'      => strtoupper($user['role']),
            'hp'        => $user['hp'],
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
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Anda telah berhasil keluar.');
    }
}

