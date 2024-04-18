<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangModel extends Model
{
    protected $table = 'barang'; // Nama tabel

    protected $primaryKey = 'id'; // Primary key tabel

    protected $allowedFields = ['nama', 'deskripsi', 'harga', 'harga_kulak', 'stok', 'gambar', 'kategori_id', 'created_at'];


    // Relasi dengan tabel Kategori Barang
    public function kategori()
    {
        return $this->belongsTo(KategoriModel::class, 'kategori_id');
    }
    public function findAllWithKategori()
    {
        return $this->db->query("
        SELECT barang.*, kategori.nama AS nama_kategori
        FROM barang
        JOIN kategori ON barang.kategori_id = kategori.id
    ")->getResult();
    }

}
