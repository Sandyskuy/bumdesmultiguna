<?php

namespace App\Models;

use CodeIgniter\Model;

class KeranjangModel extends Model
{
    protected $table = 'keranjang';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pengguna_id', 'barang_id', 'jumlah', 'harga', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'array'; // Sesuaikan dengan tipe yang Anda inginkan, misalnya 'object'

    // Relasi dengan tabel pengguna
    public function pengguna()
    {
        return $this->belongsTo(UserModel::class, 'pengguna_id');
    }

    // Relasi dengan tabel barang
    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }
}
