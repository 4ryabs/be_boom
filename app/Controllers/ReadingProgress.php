<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ReadingProgressModel;
use App\Models\BookModel;
use CodeIgniter\RESTful\ResourceController;

class ReadingProgress extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */

    public function updateProgress()
    {
        $progressModel = new ReadingProgressModel();
        $bookModel = new BookModel();

        $data = $this->request->getJSON(true);

        if (! $data || !isset($data['book_id']) || !isset($data['current_page'])) {
            return $this->fail('Data tidak lengkap (book_id dan current_page diperlukan)');
        }

        $book = $bookModel->find($data['book_id']);
        if (! $book) {
            return $this->failNotFound('Buku tidak ditemukan');
        }

        if (isset($data['user_id']) && $book->user_id !== $data['user_id']) {
            return $this->failForbidden('Buku ini bukan milik anda');
        }

        $existingProgress = $progressModel->where('book_id', $data['book_id'])->first();

        if (! $existingProgress) {
            return $this->failNotFound('Data progress belum ada');
        }

        $progressId = is_object($existingProgress) ? $existingProgress->progress_id : $existingProgress['progress_id'];

        $dbTotalPages = (int) (is_object($existingProgress) ? $existingProgress->total_pages : $existingProgress['total_pages']);

        $inputPage = (int) $data['current_page'];

        if ($inputPage < 0) {
            $inputPage = 0;
        }

        if ($inputPage > $dbTotalPages) {
            $inputPage = $dbTotalPages;
        }

        $newStatus = 'sedang_dibaca';

        if ($inputPage == 0) {
            $newStatus = 'belum_dibaca';
        } elseif ($inputPage >= $dbTotalPages) {
            $newStatus = 'selesai';
        }

        $progressModel->update($progressId, [
            'current_page' => $inputPage,
            'status_baca' => $newStatus,
        ]);

        return $this->respond([
            'message' => 'Progress updated',
            'current_page' => $inputPage,
            'total_pages' => $dbTotalPages,
            'status' => $newStatus,
            'percentage' => $dbTotalPages > 0 ? round(($inputPage / $dbTotalPages) *100) . '%' : '0%',
        ]);
    }

    
}
