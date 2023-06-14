<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GantiPassword extends ResourceController
{
    use ResponseTrait;
    // get all kegiatan
    public function index()
    {

        
    }
 
    // get single kegiatan
    public function show($id = null)
    {
       
        
    }

    // create a kegiatan
    public function create()
    {
        
    }
    
 
    // update password
    public function update($id = null)
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
        $decoded = JWT::decode($token, new Key ($key, 'HS256'));
        $idusr = $decoded->uid;
        $lvl = $decoded->level;
        helper(['form']);
        if ($lvl == 4){
            $rules = [
                'passwordbaru' => 'required|min_length[6]',
                'konfirmasipassword' => 'matches[passwordbaru]'
            ];
        } else {
            $rules = [
                'passwordsaatini' => 'required|min_length[6]',
                'passwordbaru' => 'required|min_length[6]',
                'konfirmasipassword' => 'matches[passwordbaru]'
            ];
        }
        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());
        $model = new UserModel();
        $user = $model->where("id", $id)->first();
        if(!$user) return $this->failNotFound('User tidak ada');
 
        if ($lvl != 4){
        $pwsaatini = $this->request->getRawInput()['passwordsaatini'];
        $verify = password_verify($pwsaatini, $user['password']);
        if(!$verify) return $this->fail('Wrong Password');
        }
        try {
        $json = $this->request->getJSON();
        if($json){
            $data = [
                'password' => password_hash($json->passwordbaru, PASSWORD_BCRYPT)
            ];
        }else{
            $input = $this->request->getRawInput();
            $data = [
                'password' => password_hash($input['passwordbaru'], PASSWORD_BCRYPT)
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
        
    }
 
}