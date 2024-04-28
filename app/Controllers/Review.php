<?php

namespace App\Controllers;

use App\Models\ReviewModel;
use CodeIgniter\RESTful\ResourceController;

class Review4 extends ResourceController
{
    protected $modelName = 'App\Models\ReviewModel';
    protected $format = 'json';

    public function __construct()
    {
        helper('form');
    }

    public function index()
    {
        $reviews = $this->model->findAll();
        return $this->respond($reviews);
    }

    public function create()
    {
        // Validasi input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'barang_id' => 'required',
            'rating' => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[5]',
            'ulasan' => 'required',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            // Jika validasi gagal, kirim respons dengan status 400 (Bad Request) dan pesan error
            return $this->failValidationErrors($validation->getErrors());
        }

        // Jika validasi sukses, simpan review ke database
        $reviewModel = new ReviewModel();

        $data = [
            'barang_id' => $this->request->getVar('barang_id'),
            'pengguna_id' => session()->get('user_id'), // Ambil id user dari sesi
            'rating' => $this->request->getVar('rating'),
            'ulasan' => $this->request->getVar('ulasan'),
            'created_at' => date('Y-m-d H:i:s'), // Atau gunakan Timestamp CodeIgniter jika sudah dikonfigurasi
        ];

        $reviewModel->insert($data);

        // Kirim respons dengan status 201 (Created) dan data review yang baru dibuat
        return $this->respondCreated(['message' => 'Review berhasil disimpan.']);
    }
}
