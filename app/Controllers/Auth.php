<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Auth extends ResourceController
{
    public function sync()
    {
        $model = new UserModel();
        
        $uid = $this->request->getVar('uid');
        $email = $this->request->getVar('email');
        $name = $this->request->getVar('name');

        if (!$uid) return $this->fail('UID is required');

        if ($model->find($uid)) {
            return $this->respond(['status' => 'exists', 'message' => 'User already exists']);
        }

        $model->insert([
            'user_id'   => $uid,
            'email'     => $email,
            'full_name' => $name
        ]);

        return $this->respondCreated(['status' => 'created', 'message' => 'User registered to MySQL']);
    }

    public function show($id = null)
    {
        if (! $id) {
            return $this->fail('User ID required');
        }

        $userModel = new UserModel();
        $db        = \Config\Database::connect();

        $user = $userModel->find($id);

        if (! $user) {
            return $this->failNotFound('User tidak ditemukan di database MySQL');
        }

        $totalBooks = $db->table('books')->where('user_id', $id)->countAllResults();

        $finishedBooks = $db->table('books')
            ->join('reading_progress', 'reading_progress.book_id = books.book_id')
            ->where('books.user_id', $id)
            ->where('reading_progress.status_baca', 'selesai')
            ->countAllResults();

        $activeLoans = $db->table('loans')
            ->join('borrowers', 'borrowers.borrower_id = loans.borrower_id')
            ->where('borrowers.user_id', $id)
            ->where('loans.is_returned', 0)
            ->countAllResults();

        $response = [
            'user_id'   => $user->user_id,
            'email'     => $user->email,
            'full_name' => $user->full_name,
            'stats'     => [
                'total_books'    => $totalBooks,
                'finished_books' => $finishedBooks,
                'active_loans'   => $activeLoans,
            ],
        ];

        return $this->respond($response);
    }
}
