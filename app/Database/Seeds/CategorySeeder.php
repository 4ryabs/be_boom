<?php
namespace App\Database\Seeds;

use App\Models\CategoryModel;
use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $model = new CategoryModel();

        $categories = [
            ['category_name' => 'Fiksi & Sastra'],
            ['category_name' => 'Novel'],
            ['category_name' => 'Komik & Grafis'],
            ['category_name' => 'Biografi & Memoar'],
            ['category_name' => 'Bisnis & Ekonomi'],
            ['category_name' => 'Teknologi & Komputer'],
            ['category_name' => 'Sains & Alam'],
            ['category_name' => 'Sejarah'],
            ['category_name' => 'Psikologi'],
            ['category_name' => 'Pengembangan Diri'],
            ['category_name' => 'Agama & Spiritual'],
            ['category_name' => 'Pendidikan'],
            ['category_name' => 'Anak-anak'],
            ['category_name' => 'Seni & Desain'],
            ['category_name' => 'Masakan & Makanan'],
            ['category_name' => 'Kesehatan & Bugar'],
            ['category_name' => 'Travel'],
            ['category_name' => 'Lainnya'],
        ];

        foreach ($categories as $cat) {
            $model->insert($cat);
        }
    }
}
