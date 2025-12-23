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
}
