<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriModel extends Model
{
    protected $table = 'kategori'; // Nama tabel

    protected $primaryKey = 'id'; // Primary key tabel

    protected $allowedFields = ['nama']; // Kolom yang diizinkan untuk diisi

    // Relasi dengan tabel Barang
    public function barang()
    {
        return $this->hasMany(BarangModel::class, 'kategori_id');
    }
}
