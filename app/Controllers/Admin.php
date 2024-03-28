<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Admin extends Controller
{
    protected $db, $builder;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('users');
    }

    public function index()
    {
        $this->builder->select('users.id as user_id, username, email, users.name as nama_user, auth_groups.name as group_name');
        $this->builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $this->builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $query = $this->builder->get();

        $data['users'] = $query->getResult();
        return view('admin/index', $data);
    }

    public function detail($id = 0)
    {
        $this->builder->select('users.id as user_id, username, email, users.name as nama_user, auth_groups.name as group_name');
        $this->builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $this->builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $this->builder->where('users.id', $id);
        $query = $this->builder->get();

        $data['user'] = $query->getRow(); // Perubahan disini, mengubah $data['users'] menjadi $data['user']

        if (empty($data['user'])) {
            return redirect()->to('/admin');
        }
        return view('admin/detail', $data);
    }
}
