<?php
namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Admin extends BaseController
{
    protected $userModel;
    use ResponseTrait;
    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Ambil semua data pengguna
        $users = $this->userModel->findAll();
    
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
    public function createSuperAdmin()
    {
        // Data super admin yang sudah ditentukan
        $superAdminData = [
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => password_hash('superadminpassword', PASSWORD_DEFAULT), // Hash password
            'role' => 'super_admin'
        ];

        // Simpan data super admin ke dalam database
        $user = $this->userModel->save($superAdminData);

        if (!$user) {
            return $this->fail('Failed to create super admin', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->respondCreated(['message' => 'Super admin created successfully']);
    }

    public function create()
    {
        // Lakukan validasi data
        $rules = [
            'email' => 'required|valid_email|is_unique[users.email]',
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'phone_number' => 'permit_empty|numeric',
            'name' => 'permit_empty',
            'address' => 'permit_empty',
            'role' => 'permit_empty|in_list[admin,super_admin,staff,buyer]' // Pastikan peran valid
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Hash password
        $password = $this->request->getVar('password');
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Set peran default ke 'staff' jika tidak diberikan
        $role = $this->request->getVar('role') ?? 'staff';

        // Simpan data pengguna
        $userData = [
            'email' => $this->request->getVar('email'),
            'username' => $this->request->getVar('username'),
            'password' => $hashedPassword,
            'phone_number' => $this->request->getVar('phone_number'),
            'name' => $this->request->getVar('name'),
            'address' => $this->request->getVar('address'),
            'role' => $role
        ];

        $user = $this->userModel->insert($userData);

        if (!$user) {
            return $this->fail('Failed to create user', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->respondCreated(['message' => 'User created successfully']);
    }

    public function update($id)
    {
        // Temukan pengguna berdasarkan ID
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        // Lakukan validasi data
        $rules = [
            'email' => 'valid_email|is_unique[users.email,id,' . $id . ']',
            'username' => 'min_length[3]|max_length[30]|is_unique[users.username,id,' . $id . ']',
            'password' => 'min_length[6]',
            'phone_number' => 'permit_empty|numeric',
            'name' => 'permit_empty',
            'address' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Hash password jika disediakan
        if ($this->request->getVar('password')) {
            $password = $this->request->getVar('password');
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }

        // Set peran default ke 'staff' jika tidak diberikan
        $role = $this->request->getVar('role') ?? $user['role'];

        // Simpan data pengguna
        $userData = [
            'email' => $this->request->getVar('email') ?? $user['email'],
            'username' => $this->request->getVar('username') ?? $user['username'],
            'password' => isset($hashedPassword) ? $hashedPassword : $user['password'],
            'phone_number' => $this->request->getVar('phone_number') ?? $user['phone_number'],
            'name' => $this->request->getVar('name') ?? $user['name'],
            'address' => $this->request->getVar('address') ?? $user['address'],
            'role' => $role
        ];

        $updated = $this->userModel->update($id, $userData);

        if (!$updated) {
            return $this->fail('Failed to update user', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->respond(['message' => 'User updated successfully']);
    }

    public function delete($id)
    {
        // Temukan pengguna berdasarkan ID
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        // Hapus pengguna
        $deleted = $this->userModel->delete($id);

        if (!$deleted) {
            return $this->fail('Failed to delete user', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->respondDeleted(['message' => 'User deleted successfully']);
    }

}
