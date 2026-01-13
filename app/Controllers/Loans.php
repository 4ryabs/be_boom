<?php
namespace App\Controllers;

use App\Models\BorrowerModel;
use App\Models\LoanModel;
use App\Models\ReadingProgressModel;
use CodeIgniter\RESTful\ResourceController;

class Loans extends ResourceController
{
    public function index()
    {
        $uid    = $this->request->getVar('uid');
        $status = $this->request->getVar('status');

        if (! $uid) {
            return $this->fail('User ID required');
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('loans');

        $builder->select('
            loans.loan_id,
            loans.book_id,
            loans.loan_date,
            loans.return_date,
            loans.notes,
            loans.is_returned,
            books.title as book_title,
            books.cover_image_url,
            borrowers.name as borrower_name,
            borrowers.phone_number
        ');
        $builder->join('books', 'books.book_id = loans.book_id');
        $builder->join('borrowers', 'borrowers.borrower_id = loans.borrower_id');
        $builder->where('borrowers.user_id', $uid);

        if ($status === 'history') {
            $builder->where('loans.is_returned', 1);
            $builder->orderBy('loans.return_date', 'DESC');
        } else {
            $builder->where('loans.is_returned', 0);
            $builder->orderBy('loans.loan_date', 'ASC');
        }

        $data = $builder->get()->getResult();

        return $this->respond($data);
    }

    public function create()
    {
        $loanModel     = new LoanModel();
        $borrowerModel = new BorrowerModel();
        $progressModel = new ReadingProgressModel();

        $data = $this->request->getJSON(true);

        if (! $data || ! isset($data['book_id']) || ! isset($data['name'])) {
            return $this->fail('Data tidak lengkap (book_id & name wajib ada)');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $existingBorrower = $borrowerModel->where('user_id', $data['user_id'])
                ->where('name', $data['name'])
                ->first();

            $borrowerId = null;

            if ($existingBorrower) {
                $borrowerId = is_object($existingBorrower) ? $existingBorrower->borrower_id : $existingBorrower['borrower_id'];
            } else {
                $borrowerId = $this->generateUUID();
                $borrowerModel->insert([
                    'borrower_id'  => $borrowerId,
                    'user_id'      => $data['user_id'],
                    'name'         => $data['name'],
                    'phone_number' => $data['phone'] ?? null,
                ]);
            }

            $loanId = $this->generateUUID();
            $loanModel->insert([
                'loan_id'     => $loanId,
                'book_id'     => $data['book_id'],
                'borrower_id' => $borrowerId,
                'loan_date'   => $data['loan_date'] ?? date('Y-m-d'),
                'return_date' => $data['return_date'] ?? null,
                'is_returned' => 0,
                'notes'       => $data['notes'] ?? null,
            ]);

            $progress = $progressModel->where('book_id', $data['book_id'])->first();

            if ($progress) {
                $progressId = is_object($progress) ? $progress->progress_id : $progress['progress_id'];

                $progressModel->update($progressId, [
                    'status_baca' => 'dipinjam',
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->fail('Gagal menyimpan transaksi peminjaman');
            }

            return $this->respondCreated([
                'message'       => 'Berhasil meminjamkan buku',
                'loan_id'       => $loanId,
                'borrower_name' => $data['name'],
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function returnBook()
    {
        try {
            $loanModel     = new LoanModel();
            $progressModel = new ReadingProgressModel();

            $data   = $this->request->getJSON(true);
            $loanId = $data['loan_id'] ?? null;
            $bookId = $data['book_id'] ?? null;

            if (! $loanId || ! $bookId) {
                return $this->fail("ID Peminjaman atau ID Buku tidak ditemukan.");
            }

            $loanModel->update($loanId, ['is_returned' => 1]);

            $progress = $progressModel->asArray()->where('book_id', $bookId)->first();

            if ($progress) {
                $progressId = $progress['progress_id'];

                $totalPages  = intval($progress['total_pages']);
                $currentPage = intval($progress['current_page']);

                $newStatus = 'belum_dibaca';

                if ($totalPages > 0 && $currentPage >= $totalPages) {
                    $newStatus = 'selesai';
                } elseif ($currentPage > 0) {

                    $newStatus = 'sedang_dibaca';
                }

                $progressModel->update($progressId, ['status_baca' => $newStatus]);

                return $this->respond([
                    'message' => 'Buku dikembalikan. Status diupdate jadi: ' . $newStatus,
                ]);
            }

            return $this->respond(['message' => 'Buku dikembalikan (Tidak ada history baca)']);

        } catch (\Throwable $e) {
            return $this->fail('SERVER ERROR: ' . $e->getMessage());
        }
    }

    public function update($loanId = null)
    {
        try {
            $loanModel     = new LoanModel();
            $borrowerModel = new BorrowerModel();
            $data          = $this->request->getJSON(true);

            if (! $loanId) {
                return $this->fail('Loan ID required');
            }

            $existingLoan = $loanModel->find($loanId);
            if (! $existingLoan) {
                return $this->failNotFound('Data peminjaman tidak ditemukan');
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $updateLoanData = [
                'return_date' => $data['return_date'] ?? null,
                'notes'       => $data['notes'] ?? null,
            ];
            $loanModel->update($loanId, $updateLoanData);

            if (isset($data['name']) || isset($data['phone'])) {
                $borrowerData = [];
                if (! empty($data['name'])) {
                    $borrowerData['name'] = $data['name'];
                }

                if (! empty($data['phone'])) {
                    $borrowerData['phone_number'] = $data['phone'];
                }

                $borrowerModel->update($existingLoan['borrower_id'], $borrowerData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->fail('Gagal mengupdate data');
            }

            return $this->respond(['message' => 'Data berhasil diperbarui']);

        } catch (\Throwable $e) {
            return $this->fail('SERVER ERROR: ' . $e->getMessage());
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
}
