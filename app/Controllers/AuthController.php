<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use \Firebase\JWT\JWT;

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

        // Hash password
        $password = $this->request->getVar('password');
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Simpan data pengguna
        $userData = [
            'email' => $this->request->getVar('email'),
            'username' => $this->request->getVar('username'),
            'password' => $hashedPassword,
            'role' => 'buyer' // Set default role to 'buyer'
        ];

        $user = $this->userModel->save($userData);
        if (!$user) {
            return $this->fail('Failed to register user', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->respondCreated(['message' => 'User registered successfully']);
    }

    public function login()
    {
        $email = $this->request->getVar('email');
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        // Cari user berdasarkan email
        $user = $this->userModel->where('email', $email)->first();

        // Jika tidak ditemukan, cari user berdasarkan username
        if (is_null($user) && !empty($username)) {
            $user = $this->userModel->where('username', $username)->first();
        }

        // Jika user tidak ditemukan atau password tidak cocok, kirim respon error
        if (is_null($user) || !password_verify($password, $user['password'])) {
            return $this->respond(['error' => 'Invalid email/username or password.'], 401);
        }

        // Pembuatan token JWT dan respon berhasil
        $key = getenv('JWT_SECRET');
        $iat = time(); // current timestamp value
        $exp = $iat + 3600;

        $payload = array(
            "iss" => "Issuer of the JWT",
            "aud" => "Audience that the JWT",
            "sub" => "Subject of the JWT",
            "iat" => $iat, //Time the JWT issued at
            "exp" => $exp, // Expiration time of token
            "id" => $user['id'], // Add user ID to payload
            "email" => $user['email'],
        );

        $token = JWT::encode($payload, $key, 'HS256');

        $response = [
            'message' => 'Login Succesful',
            'token' => $token
        ];

        return $this->respond($response, 200);
    }

    public function logout()
    {
        $this->userModel->logout();
        return $this->respond(['message' => 'Logout successful']);
    }
}
