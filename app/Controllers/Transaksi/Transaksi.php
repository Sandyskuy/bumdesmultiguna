<?php

namespace App\Controllers\Transaksi;

use App\Models\TransaksiModel;
use App\Models\DetailTransaksiModel;
use App\Models\BarangModel;
use App\Models\UserModel;
use App\Models\KeranjangModel;
use CodeIgniter\API\ResponseTrait;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use App\Controllers\BaseController;

class Transaksi extends BaseController
{
    use ResponseTrait;

    protected $transaksiModel;
    protected $detailTransaksiModel;
    protected $barangModel;
    protected $userModel;
    protected $keranjangModel; // Deklarasi properti $keranjangModel

    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
        $this->detailTransaksiModel = new DetailTransaksiModel();
        $this->barangModel = new BarangModel();
        $this->userModel = new UserModel();
        $this->keranjangModel = new KeranjangModel(); // Inisialisasi properti $keranjangModel
    }

    public function index()
    {
        // Ambil semua transaksi dari database
        $transactions = $this->transaksiModel->findAll();

        // Jika tidak ada transaksi, kembalikan respons kosong
        if (empty($transactions)) {
            return $this->respond([]);
        }

        // Persiapkan data transaksi untuk ditampilkan
        $formattedTransactions = [];
        foreach ($transactions as $transaction) {
            // Ambil detail transaksi untuk setiap transaksi
            $details = $this->detailTransaksiModel->where('transaksi_id', $transaction['id'])->findAll();

            // Jika tidak ada detail transaksi, lanjutkan ke transaksi berikutnya
            if (empty($details)) {
                continue;
            }

            // Persiapkan data detail transaksi untuk ditampilkan
            $formattedDetails = [];
            foreach ($details as $detail) {
                // Ambil informasi barang untuk setiap detail transaksi
                $barang = $this->barangModel->find($detail['barang_id']);

                // Jika barang tidak ditemukan, lanjutkan ke detail transaksi berikutnya
                if (!$barang) {
                    continue;
                }

                // Format detail transaksi
                $formattedDetail = [
                    'barang_id' => $detail['barang_id'],
                    'nama_barang' => $barang['nama'],
                    'jumlah' => $detail['jumlah'],
                ];

                // Tambahkan detail transaksi yang telah diformat ke dalam array
                $formattedDetails[] = $formattedDetail;
            }

            // Format transaksi
            $formattedTransaction = [
                'transaksi_id' => $transaction['id'],
                'total' => $transaction['total'],
                'status' => $transaction['status'],
                'details' => $formattedDetails,
            ];

            // Tambahkan transaksi yang telah diformat ke dalam array
            $formattedTransactions[] = $formattedTransaction;
        }

        // Kembalikan data transaksi yang telah diformat dalam respons
        return $this->respond($formattedTransactions);
    }


    public function checkout()
    {
        $key = getenv('JWT_SECRET');
        // Ambil token dari header permintaan
        $token = $this->request->getHeaderLine('Authorization');

        // Periksa apakah token ditemukan dalam header permintaan
        if (empty($token)) {
            // Tanggapi jika token tidak ditemukan
            return $this->failUnauthorized('Token not provided.');
        }

        // Buang kata "Bearer " dari token
        $token = str_replace('Bearer ', '', $token);

        // Decode token untuk mendapatkan payload
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

        } catch (\Exception $e) {
            // Tanggapi jika terjadi kesalahan dalam mendecode token
            return $this->failUnauthorized('Invalid token.');
        }

        // Ambil email dari payload token
        $email = $decoded->email;

        // Ambil pengguna dari database berdasarkan email
        $user = $this->userModel->where('email', $email)->first();

        // Periksa apakah pengguna ditemukan
        if (!$user) {
            return $this->failUnauthorized('User not found.');
        }

        // Ambil pengguna_id dari hasil pencarian
        $user_id = $user['id'];

        // Mendapatkan data keranjang belanjaan pengguna
        $cartItems = $this->getCartItems();

        // Validasi keranjang belanjaan
        if (empty($cartItems)) {
            return $this->fail('Cart is empty. Add items to cart before checkout.');
        }

        // Hitung total belanja
        $total = 0;
        foreach ($cartItems as $item) {
            // Ambil harga barang dari data barang yang sudah disimpan di keranjang
            $total += $item['harga'];
        }

        // Buat data transaksi
        $transaksiData = [
            'pengguna_id' => $user_id,
            'total' => $total,
            'status' => 0, // Status 0 untuk belum bayar
        ];

        // Simpan transaksi ke database
        $transaksi = $this->transaksiModel->save($transaksiData);
        if (!$transaksi) {
            return $this->fail('Failed to create transaction.', 500);
        }

        // Dapatkan ID transaksi yang baru saja dibuat
        $transaksi_id = $this->transaksiModel->insertID();

        // Simpan detail transaksi
        foreach ($cartItems as $item) {
            $detailTransaksiData = [
                'transaksi_id' => $transaksi_id, // Menggunakan id dari transaksi yang baru saja dibuat
                'barang_id' => $item['barang_id'],
                'jumlah' => $item['jumlah'],
            ];

            $detailTransaksi = $this->detailTransaksiModel->save($detailTransaksiData);
            if (!$detailTransaksi) {
                // Rollback transaksi jika ada kesalahan pada detail transaksi
                $this->transaksiModel->delete($transaksi_id); // Menggunakan id dari transaksi yang baru saja dibuat
                return $this->fail('Failed to create transaction detail.', 500);
            }

            // Kurangi stok barang
            $this->updateStock($item['barang_id'], $item['jumlah']);
        }

        // Kosongkan keranjang belanjaan setelah checkout
        $this->clearCart();

        return $this->respondCreated(['message' => 'Checkout successful.']);
    }


    public function addToCart()
    {
        $key = getenv('JWT_SECRET');
        // Ambil token dari header permintaan
        $token = $this->request->getHeaderLine('Authorization');

        // Periksa apakah token ditemukan dalam header permintaan
        if (empty($token)) {
            // Tanggapi jika token tidak ditemukan
            return $this->failUnauthorized('Token not provided.');
        }

        // Buang kata "Bearer " dari token
        $token = str_replace('Bearer ', '', $token);

        // Dekode token untuk mendapatkan payload
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

        } catch (\Exception $e) {
            // Tanggapi jika terjadi kesalahan dalam mendekode token
            return $this->failUnauthorized('Invalid token.');
        }

        // Ambil email dari payload token
        $email = $decoded->email;

        // Ambil pengguna dari database berdasarkan email
        $user = $this->userModel->where('email', $email)->first();

        // Periksa apakah pengguna ditemukan
        if (!$user) {
            return $this->failUnauthorized('User not found.');
        }

        // Ambil pengguna_id dari hasil pencarian
        $user_id = $user['id'];

        // Ambil data barang yang akan ditambahkan ke keranjang
        $barang_id = $this->request->getVar('barang_id');
        $jumlah = $this->request->getVar('jumlah');

        // Dapatkan data barang dari model barang berdasarkan barang_id
        $barang = $this->barangModel->find($barang_id);

        // Periksa apakah barang ditemukan
        if (!$barang) {
            return $this->failNotFound('Product not found.');
        }

        // Ambil harga barang dari data barang
        $harga_barang = $barang['harga'];

        // Cari item keranjang berdasarkan pengguna_id dan barang_id
        $existingItem = $this->keranjangModel->where('pengguna_id', $user_id)
            ->where('barang_id', $barang_id)
            ->first();

        if ($existingItem) {
            // Jika item sudah ada, tambahkan jumlahnya
            $newQuantity = $existingItem['jumlah'] + $jumlah;

            // Update jumlah dan harga barang dalam keranjang
            $this->keranjangModel->update($existingItem['id'], [
                'jumlah' => $newQuantity,
                'harga' => $harga_barang * $newQuantity // Harga total baru
            ]);
        } else {
            // Jika item belum ada, tambahkan sebagai item baru
            $cartItem = [
                'pengguna_id' => $user_id,
                'barang_id' => $barang_id,
                'jumlah' => $jumlah,
                'harga' => $harga_barang * $jumlah // Harga total
            ];

            // Simpan item ke keranjang
            $this->keranjangModel->insert($cartItem);
        }

        // Beri respons sukses
        return $this->respond(['message' => 'Product added to cart.']);
    }

    protected function updateCartItem($barang_id, $jumlah)
    {
        // Update jumlah barang di keranjang
        $this->keranjangModel->update(['jumlah' => $jumlah], ['barang_id' => $barang_id]); // Mengubah 'quantity' menjadi 'jumlah'
    }


    public function removeFromCart($barang_id)
    {
        // Hapus barang dari keranjang belanja
        $this->keranjangModel->where('barang_id', $barang_id)->delete();

        return $this->respond(['message' => 'Product removed from cart.']);
    }

    public function viewCart()
    {
        // Ambil data keranjang belanja pengguna
        $cartItems = $this->getCartItems();

        // Persiapkan array untuk menyimpan detail barang di keranjang
        $formattedCartItems = [];

        // Untuk setiap item di keranjang, ambil informasi barang yang sesuai
        foreach ($cartItems as $item) {
            // Ambil informasi barang dari model barang berdasarkan barang_id
            $barang = $this->barangModel->find($item['barang_id']);

            // Periksa apakah barang ditemukan
            if ($barang) {
                // Format data barang yang akan ditampilkan
                $formattedItem = [
                    'barang_id' => $barang['id'],
                    'nama_barang' => $barang['nama'],
                    'gambar_barang' => $barang['gambar'], // asumsikan ada kolom 'gambar' di tabel barang
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga']
                ];

                // Tambahkan data barang yang telah diformat ke dalam array
                $formattedCartItems[] = $formattedItem;
            }
        }

        // Kembalikan data keranjang belanja yang telah diformat dalam respons
        return $this->respond($formattedCartItems);
    }


    protected function getCartItems()
    {
        // Ambil data keranjang belanja dari database
        return $this->keranjangModel->findAll();
    }

    protected function clearCart()
    {
        // Kosongkan keranjang belanja pengguna
        $this->keranjangModel->truncate();
    }

    protected function updateStock($barang_id, $quantity)
    {
        $barang = $this->barangModel->find($barang_id);
        $updatedStock = $barang['stok'] - $quantity;
        $this->barangModel->update($barang_id, ['stok' => $updatedStock]);
    }

}
