<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\KategoriModel;

class Kategori extends ResourceController
{
    protected $kategoriModel;

    public function __construct()
    {
        $this->kategoriModel = new KategoriModel();
    }

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $kategori = $this->kategoriModel->findAll();
        return $this->respond($kategori);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        $kategori = $this->kategoriModel->find($id);
        if (!$kategori) {
            return $this->failNotFound('Kategori not found');
        }
        return $this->respond($kategori);
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        // Retrieve data from the request body as JSON
        $data = $this->request->getJSON();

        // Check if the data is valid and contains the required fields
        if (empty ($data)) {
            return $this->fail('No data provided for creation', 400);
        }

        // Attempt to insert the new kategori data into the database
        if ($this->kategoriModel->insert($data)) {
            // If insertion is successful, return a success response
            return $this->respondCreated($data, 'Kategori created successfully');
        } else {
            // If there are errors during insertion, return a failure response with errors
            return $this->fail($this->kategoriModel->errors(), 500);
        }
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        // Retrieve updated data from the request body as JSON
        $data = $this->request->getJSON();

        // Check if the data is valid and contains the required fields
        if (empty ($data)) {
            return $this->fail('No data provided for update', 400);
        }

        // Attempt to update the kategori record in the database
        if ($this->kategoriModel->update($id, $data)) {
            // If update is successful, return a success response
            return $this->respondUpdated($data, 'Kategori updated successfully');
        } else {
            // If there are errors during update, return a failure response with errors
            return $this->fail($this->kategoriModel->errors(), 500);
        }
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        $kategori = $this->kategoriModel->find($id);
        if (!$kategori) {
            return $this->failNotFound('Kategori not found');
        }

        if ($this->kategoriModel->delete($id)) {
            return $this->respondDeleted($kategori, 'Kategori deleted successfully');
        } else {
            return $this->fail($this->kategoriModel->errors());
        }
    }
}
