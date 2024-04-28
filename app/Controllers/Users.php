<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\Response;

use CodeIgniter\API\ResponseTrait;

class Users extends Controller
{
    use ResponseTrait;
    protected $userModel;
    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Ambil pengguna dengan peran 'buyer' dan pilih kolom yang diinginkan
        $users = $this->userModel->select('id, username, email, name, role')->whereIn('role', ['buyer'])->findAll();

        return $this->response->setJSON($users);
    }



    public function show($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        return $this->response->setJSON($user);
    }
}
