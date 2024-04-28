<?php

namespace App\Controllers;

use App\Models\BarangModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class TotalController extends BaseController
{
    use ResponseTrait;

    protected $barangModel;

    public function __construct()
    {
        $this->barangModel = new BarangModel();
    }

    public function calculateTotal($selectedItems)
    {
        // Fetch prices of selected items from database
        $total = 0;
        foreach ($selectedItems as $itemId => $quantity) {
            $barang = $this->barangModel->find($itemId);
            if ($barang) {
                $total += $barang['harga'] * $quantity;
            }
        }

        // Return the total
        return $total;
    }
}
