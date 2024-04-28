<?php

namespace App\Controllers\Transaksi;

use App\Models\DetailTransaksiModel;
use App\Models\BarangModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;

class DetailTransaksi extends BaseController
{
    use ResponseTrait;
    protected $detailTransaksiModel;
    protected $barangModel;

    public function __construct()
    {
        $this->detailTransaksiModel = new DetailTransaksiModel();
        $this->barangModel = new BarangModel();
    }

    public function index()
    {
        // Tampilkan halaman detail transaksi, atau daftar detail transaksi jika Anda mempunyai halaman dashboard admin
    }

    public function show($id)
    {
        // Ambil detail transaksi berdasarkan ID
        $detailTransaksi = $this->detailTransaksiModel->find($id);

        if (!$detailTransaksi) {
            return $this->fail(['error' => 'Detail transaction not found.'], 404);
        }

        return $this->respond($detailTransaksi);
    }

    public function getDetailTransaksiByTransaksi($transaksi_id)
    {
        $details = $this->detailTransaksiModel->where('transaksi_id', $transaksi_id)->findAll();
        $response = [];
        foreach ($details as $detail) {
            $barang = $this->barangModel->find($detail['barang_id']);
            $detail['barang'] = $barang;
            $response[] = $detail;
        }
        return $this->respond($response);
    }

}
