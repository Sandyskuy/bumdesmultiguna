<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DetailTransaksiModel;
use App\Models\TransaksiModel;

class DetailTransaksi extends ResourceController
{
    protected $modelName = '\App\Models\DetailTransaksiModel';
    protected $format = 'json';

    public function index()
    {
        // Retrieve all detail transaksi
        $detailTransaksi = $this->model->findAll();

        return $this->respond($detailTransaksi);
    }

    public function show($id = null)
    {
        // Retrieve a single detail transaksi by ID
        $detailTransaksiModel = new DetailTransaksiModel();
        $detailTransaksi = $detailTransaksiModel
            ->select('detail_transaksi.*, barang.id AS barang_id, barang.nama AS nama_barang, barang.harga AS harga_barang')
            ->join('barang', 'barang.id = detail_transaksi.barang_id')
            ->where('detail_transaksi.id', $id)
            ->first();

        if ($detailTransaksi === null) {
            return $this->failNotFound('Detail transaksi not found.');
        }

        return $this->respond($detailTransaksi);
    }


    public function create()
    {
        // Get request data
        $data = $this->request->getJSON();

        // Get the ID of the currently logged-in user
        $loggedInUserId = session()->get('user_id');

        // Add the logged-in user's ID to the transaksi data
        $data['pengguna_id'] = $loggedInUserId;

        // Insert transaksi utama into database
        $transaksiModel = new TransaksiModel();
        $transaksiModel->insert($data);

        // Get the ID of the newly created transaksi
        $transaksiId = $transaksiModel->insertID();

        // Get detail transaksi from request data
        $detailTransaksi = $data['detail_transaksi'];

        // Insert detail transaksi into database
        $detailTransaksiModel = new DetailTransaksiModel();
        foreach ($detailTransaksi as $item) {
            // Assign transaksi_id
            $item['transaksi_id'] = $transaksiId;

            // Insert quantity directly
            $item['jumlah'] = $item['quantity'];

            // Insert detail transaksi
            $detailTransaksiModel->insert($item);
        }

        return $this->respondCreated(['message' => 'Transaksi created successfully.']);
    }



    // Implement other CRUD methods like update() and delete() similarly...
}
