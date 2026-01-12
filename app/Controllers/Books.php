<?php

namespace App\Controllers;

use App\Models\BookModel;
use App\Models\ReadingProgressModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Books extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    // Menampilkan daftar buku beserta progress membacanya berdasarkan user_id (uid)
    public function index()
    {
        $uid = $this->request->getVar('uid');
        if (!$uid) {
            return $this->fail('UID required');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('books');
        $builder->select('books.*, categories.category_name, reading_progress.status_baca, reading_progress.current_page, reading_progress.total_pages');
        $builder->join('categories', 'categories.category_id = books.category_id', 'left');
        $builder->join('reading_progress', 'reading_progress.book_id = books.book_id', 'left');
        $builder->where('books.user_id', $uid);
        $builder->orderBy('books.created_at', 'DESC');

        return $this->respond($builder->get()->getResult());
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    // Menambahkan buku baru beserta inisialisasi progress membacanya
    public function create()
    {
        $bookModel = new BookModel();
        $progressModel = new ReadingProgressModel();

        $data = null;

        try {
            $data = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $data = null;
        }

        if (!$data) {
            $data = $this->request->getPost();
        }

        if (empty($data['user_id'])) {
            return $this->fail('USER ID tidak ditemukan. Pastikan user_id dikirim.', 400);
        }

        $coverUrl = '-';
        $file = $this->request->getFile('cover_image');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/covers', $newName);
            $coverUrl = base_url('uploads/covers/' . $newName);
        } else {
            $coverUrl = $data['cover_image_url'] ?? '-';
        }

        $uuidFixed = $this->generateUUID();

        $dataBuku = [
            'book_id' => $uuidFixed,
            'user_id' => $data['user_id'],
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'author' => $data['author'],
            'publisher' => $data['publisher'] ?? '-',
            'tota_pages' => $data['total_pages'] ?? 0,
            'cover_image_url' => $coverUrl,
        ];

        try {
            $bookModel->insert($dataBuku);

            $progressModel = [
                'book_id' => $uuidFixed,
                'total_pages' => $data['total_pages'] ?? 0,
                'current_page' => 0,
                'status_baca' => 'belum_dibaca',
            ];

            return $this->respondCreated(['status' => 201,'message' => 'Buku berhasil ditambahkan', 'data' => $dataBuku]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    // Menampilkan detail buku berdasarkan book_id
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID buku diperlukan');

        $db = \Config\Database::connect();
        $builder = $db->table('books');
        $builder->select('books.*, categories.category_name, reading_progress.status_baca, reading_progress.current_page, reading_progress.total_pages, reading_progress.progress_id');

        $builder->join('categories', 'categories.category_id = books.category_id', 'left');
        $builder->join('reading_progress', 'reading_progress.book_id = books.book_id', 'left');

        $builder->where('books.book_id', $id);

        $result = $builder->get()->getRow();

        if ($result) {
            return $this->respond($result);
        } else {
            return $this->failNotFound('Buku tidak ditemukan');
        }
    }

    private function generateUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }



    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
    }

    

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function edit($id = null)
    {
        //
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
        //
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
        //
    }
}
