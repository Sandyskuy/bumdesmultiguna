<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SuperAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Periksa apakah pengguna memiliki peran 'super_admin'
        // Jika tidak, kembalikan respons JSON dengan pesan kesalahan
        $key = getenv("JWT_SECRET");
        $header = $request->getServer("HTTP_AUTHORIZATION");
        $session = session();
        $role = $session->get('role');

        if ($role !== 'super_admin') {
            $response = service('response');
            return $response->setJSON(['error' => 'Access denied'])->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        }

        try {
            $token = explode(' ', $header)[1];
            $decode = JWT::decode($token, new Key($key, "HS256"));
        } catch (\Exception $e) {
            // Tangani kesalahan di sini, misalnya:
            $response = service('response');
            return $response->setJSON(['error' => 'Invalid token'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
