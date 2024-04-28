<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pengguna_id', 'tanggal', 'total', 'status'];
    protected $useTimestamps = false;
    protected $returnType = 'array';
    protected $dateFormat = 'datetime';

    // Definisikan relasi dengan tabel detail transaksi
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksiModel::class, 'transaksi_id');
    }
}
