<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\CategoryModel;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $model = new CategoryModel();

        $categories = [
            ['category_name' => 'Novel'],
            ['category_name' => 'Komik'],
            ['category_name' => 'Teknologi'],
            ['category_name' => 'Sejarah'],
            ['category_name' => 'Bisnis'],
            ['category_name' => 'Lainnya'],
        ];

        foreach ($categories as $cat) {
            $model->insert($cat);
        }
    }
}
