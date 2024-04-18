<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TransaksiModel;

class Transaksi extends ResourceController
{
    protected $modelName = '\App\Models\TransaksiModel';
    protected $format = 'json';

    public function index()
    {
        // Retrieve all transaksi
        $transaksi = $this->model->findAll();

        return $this->respond($transaksi);
    }

    public function show($id = null)
    {
        // Retrieve a single transaksi by ID
        $transaksi = $this->model->find($id);

        if ($transaksi === null) {
            return $this->failNotFound('Transaksi not found.');
        }

        return $this->respond($transaksi);
    }

    public function create()
    {
        // Get request data
        $data = $this->request->getJSON();

        // Get the ID of the currently logged-in user
        $loggedInUserId = session()->get('user_id');

        // Add the logged-in user's ID to the transaksi data
        $data['pengguna_id'] = $loggedInUserId;

        // Set status default
        $data['status'] = 0; // Default status: belum bayar

        // Insert transaksi into database
        $transaksiModel = new TransaksiModel();
        $transaksiModel->insert($data);

        return $this->respondCreated(['message' => 'Transaksi created successfully.']);
    }
}
