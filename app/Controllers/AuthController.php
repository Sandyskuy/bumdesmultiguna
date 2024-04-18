<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Myth\Auth\Models\UserModel;

class AuthController extends BaseController
{
    use ResponseTrait;

    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function register()
    {
        // Lakukan validasi data
        $rules = [
            'email' => 'required|valid_email|is_unique[users.email]',
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'pass_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Simpan data pengguna
        $userData = [
            'email' => $this->request->getVar('email'),
            'username' => $this->request->getVar('username'),
            'password' => $this->request->getVar('password'),
        ];

        // Jika konfigurasi membutuhkan aktivasi, tambahkan langkah aktivasi di sini

        $user = $this->userModel->save($userData);
        if (!$user) {
            return $this->fail('Failed to register user', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->respondCreated(['message' => 'User registered successfully']);
    }

    public function login()
    {
        // Lakukan validasi data
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['error' => $this->validator->getErrors()], ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Lakukan proses login
        $credentials = [
            'email' => $this->request->getVar('email'),
            'password' => $this->request->getVar('password'),
        ];

        if (!$this->userModel->login($credentials)) {
            return $this->failUnauthorized('Invalid email or password');
        }

        return $this->respond(['message' => 'Login successful']);
    }

    public function logout()
    {
        $this->userModel->logout();
        return $this->respond(['message' => 'Logout successful']);
    }
}
