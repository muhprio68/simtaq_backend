<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class User extends ResourceController
{
    use ResponseTrait;
    // get all kegiatan
    public function index()
    {

        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
 
        try {
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $lvl = $decoded->level;
            $model = new UserModel();
            $data = $model->findAll();
            $response = [
                'level' => $decoded->level,
                'message' => 'Superadmin level required'
            ];
            if ($lvl == "4"){
                return $this->respond($data, 200);
            } else{
                return $this->respond($response, 400);
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }
 
    // get single kegiatan
    public function show($id = null)
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
 
        try {
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $idusr = $decoded->uid;
            $lvl = $decoded->level;
            $model = new UserModel();
            $data = $model->getWhere(['id' => $id])->getResult();
            $response = [
                'level' => $decoded->level,
                'message' => 'Superadmin level required'
            ];

            if($data){
                if ($lvl == "4"){
                    return $this->respond($data);
                } else if ($idusr == $id) {
                    return $this->respond($data);
                } else{
                    return $this->respond($response, 400);
                }
                
            }else{
                return $this->failNotFound('No Data Found with id '.$id);
            }
            
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
        
    }

    // create a kegiatan
    public function create()
    {
        
    }
    
 
    // update kegiatan
    public function update($id = null)
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
        helper(['form']);
        $rules = [
            'nama' => 'required',
            'email' => 'required|valid_email|is_unique[users.email,id,'.$id.']',
            'level' => 'required'
        ];
        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());
        try {
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $idusr = $decoded->uid;
            $lvl = $decoded->level;
            $model = new UserModel();
        $json = $this->request->getJSON();
        if($json){
            $data = [
                'nama' => $json->nama,
                'email' => $json->email,
                'level' => $json->level
            ];
        }else{
            $input = $this->request->getRawInput();
            $data = [
                'nama' => $input['nama'],
                'email' => $input['email'],
                'level' => $input['level'],
            ];
        }

            if ($lvl == "4"){
                // Insert to Database
                if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());
                    $model->update($id, $data);
                    $responseok = [
                        'status'   => 200,
                        'error'    => null,
                        'messages' => [
                        'success' => 'Data Updated'
                        ]   
                    ];
                    return $this->respond($responseok, 200);
            } else if($idusr == $id){
                if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());
                    $model->update($id, $data);
                    $responseok = [
                        'status'   => 200,
                        'error'    => null,
                        'messages' => [
                        'success' => 'Data Updated'
                        ]   
                    ];
                    return $this->respond($responseok, 200);
            } else{
                $response = [
                    'level' => $decoded->level,
                    'message' => 'Superadmin level required'
                ];
                return $this->respond($response, 400);
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }
 
    // delete kegiatan
    public function delete($id = null)
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
 
        try {
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $lvl = $decoded->level;
            $model = new UserModel();
            $data = $model->find($id);
            $response = [
                'level' => $decoded->level,
                'message' => 'Superadmin level required'
            ];
            if($data){
                if ($lvl == "4"){
                    $model->delete($id);
                $response = [
                    'status'   => 200,
                    'error'    => null,
                    'messages' => [
                        'success' => 'Data Deleted'
                    ]
                ];
                 
                return $this->respondDeleted($response);
                } else {
                    return $this->respond($response, 400);
                }
                
            }else{
                return $this->failNotFound('No Data Found with id '.$id);
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }
 
}