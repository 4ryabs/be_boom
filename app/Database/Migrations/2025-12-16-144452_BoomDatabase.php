<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BoomDatabase extends Migration
{
    public function up()
    {
        // 1. TABEL USERS
        $this->forge->addField([
            'user_id'   => ['type' => 'VARCHAR', 'constraint' => 128],
            'email'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 256, 'null' => true],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
        ]);
        $this->forge->addPrimaryKey('user_id');
        $this->forge->createTable('users');

        // 2. TABEL CATEGORIES (Kategori Buku)
        $this->forge->addField([
            'category_id'   => ['type' => 'VARCHAR', 'constraint' => 36],
            'category_name' => ['type' => 'VARCHAR', 'constraint' => 100],
        ]);
        $this->forge->addPrimaryKey('category_id');
        $this->forge->createTable('categories');

        // 3. TABEL BOOKS (Buku)
        $this->forge->addField([
            'book_id'         => ['type' => 'VARCHAR', 'constraint' => 36],
            'user_id'         => ['type' => 'VARCHAR', 'constraint' => 128],
            'category_id'     => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'author'          => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'publisher'       => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'cover_image_url' => ['type' => 'TEXT', 'null' => true],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
        ]);
        $this->forge->addPrimaryKey('book_id');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'category_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('books');

        // 4. TABEL READING PROGRESS (Progress Membaca Buku)
        $this->forge->addField([
            'progress_id'  => ['type' => 'VARCHAR', 'constraint' => 36],
            'book_id'      => ['type' => 'VARCHAR', 'constraint' => 36],
            'total_pages'  => ['type' => 'INT', 'default' => 0],
            'current_page' => ['type' => 'INT', 'default' => 0],
            'status_baca'  => ['type' => 'ENUM', 'constraint' => ['belum_dibaca', 'sedang_dibaca', 'selesai', 'dipinjam'], 'default' => 'belum_dibaca'],
            'last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);
        $this->forge->addPrimaryKey('progress_id');
        $this->forge->addForeignKey('book_id', 'books', 'book_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reading_progress');

        // 5. TABEL BORROWERS (Peminjaman)
        $this->forge->addField([
            'borrower_id'  => ['type' => 'VARCHAR', 'constraint' => 36],
            'user_id'      => ['type' => 'VARCHAR', 'constraint' => 128],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone_number' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('borrower_id');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('borrowers');

        // 6. TABEL LOANS (Transaksi)
        $this->forge->addField([
            'loan_id'     => ['type' => 'VARCHAR', 'constraint' => 36],
            'book_id'     => ['type' => 'VARCHAR', 'constraint' => 36],
            'borrower_id' => ['type' => 'VARCHAR', 'constraint' => 36],
            'loan_date'   => ['type' => 'DATE', 'null' => true],
            'return_date' => ['type' => 'DATE', 'null' => true],
            'is_returned' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'notes'       => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('loan_id');
        $this->forge->addForeignKey('book_id', 'books', 'book_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('borrower_id', 'borrowers', 'borrower_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('loans');
    }

    public function down()
    {
        $this->forge->dropTable('loans');
        $this->forge->dropTable('borrowers');
        $this->forge->dropTable('reading_progress');
        $this->forge->dropTable('books');
        $this->forge->dropTable('categories');
        $this->forge->dropTable('users');
    }
}
