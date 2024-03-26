<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Barang extends ResourceController
{
    protected $modelName = 'App\Models\BarangModel';
    protected $format = 'json';

    public function index()
    {
        $model = new $this->modelName();
        $data = $model->findAllWithKategori();
        return $this->respond($data);
    }


    public function show($id = null)
    {
        $model = new $this->modelName();
        $data = $model->find($id);
        return $this->respond($data);
    }

    public function create()
    {
        $model = new $this->modelName();
        
        // Ambil data dari body permintaan
        $data = $this->request->getPost();
        
        // Cek apakah ada file gambar yang diunggah
        $gambar = $this->request->getFile('gambar');
        
        // Jika ada file gambar yang diunggah, proses penyimpanannya
        if ($gambar && $gambar->isValid() && !$gambar->hasMoved())
        {
            // Pindahkan file gambar ke direktori yang ditentukan
            $gambar->move(ROOTPATH . 'public/uploads');
        
            // Simpan nama file gambar ke dalam data barang
            $data['gambar'] = $gambar->getName();
        }
        
        // Masukkan data barang ke dalam database
        $model->insert($data);
        
        // Beri respons bahwa barang telah berhasil dibuat
        return $this->respondCreated(['message' => 'Barang created successfully']);
    }
    
    

    public function update($id = null)
    {
        $model = new $this->modelName();
        $data = $this->request->getJSON();

        // Periksa apakah ID barang yang ingin diperbarui diberikan
        if ($id === null) {
            // Jika tidak, kirim respons dengan kode 400 Bad Request
            return $this->fail('Missing ID parameter', 400);
        }

        // Periksa apakah data yang diperlukan untuk pembaruan diberikan
        if (empty ($data)) {
            // Jika tidak, kirim respons dengan kode 400 Bad Request
            return $this->fail('No data provided for update', 400);
        }

        // Lakukan pembaruan data dalam model
        $model->update($id, $data);

        // Kirim respons berhasil dengan kode 200 OK
        return $this->respond(['message' => 'Barang updated successfully'], 200);
    }

    public function delete($id = null)
    {
        $model = new $this->modelName();
        $model->delete($id);
        return $this->respond(['message' => 'Barang deleted successfully']);
    }
}
