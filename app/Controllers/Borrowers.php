<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Borrowers extends ResourceController
{
    protected $modelName = 'App\Models\BorrowerModel';
    protected $format    = 'json';

    public function searchBorrowers()
    {
        $keyword = $this->request->getVar('q');
        $uid = $this->request->getVar('uid');

        if (!$keyword || !$uid) return $this->respond([]);

        $db = \Config\Database::connect();
        $builder = $db->table('borrowers');
        
        $builder->like('name', $keyword);
        $builder->where('user_id', $uid);
        $builder->limit(5);
        
        $data = $builder->get()->getResult();
        return $this->respond($data);
    }
}
