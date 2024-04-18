<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\Response;
use Myth\Auth\Entities\User;
use Myth\Auth\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class Admin extends Controller
{
    use ResponseTrait;

    protected $db;
    protected $builder;
    protected $config;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('users');
        $this->config = config('Auth');
    }

    public function index()
    {
        $this->builder->select('users.id as user_id, username, email, users.name as nama_user, auth_groups.name as group_name');
        $this->builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $this->builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $this->builder->where('auth_groups.name', 'staff'); // Filter hanya grup staff
        $query = $this->builder->get();

        $data['users'] = $query->getResult();

        return $this->response->setStatusCode(200)->setJSON($data);
    }


    public function detail($id = 0)
    {
        $this->builder->select('users.id as user_id, username, email, users.name as nama_user, auth_groups.name as group_name');
        $this->builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $this->builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $this->builder->where('users.id', $id);
        $query = $this->builder->get();

        $data['user'] = $query->getRow();

        if (empty($data['user'])) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'User not found']);
        }

        return $this->response->setStatusCode(200)->setJSON($data);
    }

    public function create()
    {
        $request = service('request');
        $username = $request->getVar('username');
        $email = $request->getVar('email');
        $password = $request->getVar('password');

        if (!isset($username, $email, $password)) {
            return $this->respond(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $users = model(UserModel::class);

        // Create a new user entity
        $user = new User([
            'username' => $username,
            'email' => $email,
            'password' => $password
        ]);

        $this->config->requireActivation === null ? $user->activate() : $user->generateActivateHash();

        $userModel = $users->withGroup('staff'); // Assuming 'staff' is the group name
        $user = $userModel->insert($user);

        return $this->respond(['message' => 'User created successfully']);
    }

    public function update($id = null)
    {
        $request = service('request');
        $username = $request->getVar('username');
        $email = $request->getVar('email');
        $password = $request->getVar('password');

        if (!isset($id, $username, $email, $password)) {
            return $this->respond(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $users = model(UserModel::class);
        $user = $users->find($id);

        if (!$user) {
            return $this->respond(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $user->username = $username;
        $user->email = $email;
        $user->password = $password;

        if (!$users->save($user)) {
            return $this->respond(['message' => $users->errors()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->respond(['message' => 'User updated successfully']);
    }

    public function delete($id = null)
    {
        // Check if ID is provided
        if (!$id) {
            return $this->respond(['message' => 'Missing user ID'], Response::HTTP_BAD_REQUEST);
        }

        // Load UserModel
        $users = model(UserModel::class);

        // Attempt to find the user
        $user = $users->find($id);

        // If user not found, return 404 response
        if (!$user) {
            return $this->respond(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Attempt to delete the user
        if (!$users->delete($id)) {
            return $this->respond(['message' => 'Failed to delete user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return success response
        return $this->respond(['message' => 'User deleted successfully']);
    }


}
