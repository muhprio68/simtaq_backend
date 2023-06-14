<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\SaldoModel;
use PHPUnit\Util\Json;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Saldo extends ResourceController
{
    use ResponseTrait;
    // get all keuangan
    public function index()
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
        try{
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $idusr = $decoded->uid;
            if ($idusr != null){
                $model = new SaldoModel();
                $data = $model->findAll();
                return $this->respond($data, 200);
            } else {
                return $this->fail('User tidak terdaftar');
            }
        }catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }
 

    // get single keuangan
    // public function show($id = null)
    // {
    //     $model = new KeuanganModel();
    //     $data = $model->getWhere(['id_keuangan' => $id])->getResult();
    //     if($data){
    //         return $this->respond($data);
    //     }else{
    //         return $this->failNotFound('No Data Found with id '.$id);
    //     }
    // }
 
    // create a keuangan
    public function create()
    {
        $model = new SaldoModel();
        $data = [
            'jml_saldo' => $this->request->getPost('jml_saldo'),
            'update_at' => $this->request->getPost('update_at')
        ];
        $data = json_decode(file_get_contents("php://input"));
        $data = $this->request->getPost();
        $model->insert($data);
        $response = [
            'status'   => 201,
            'error'    => null,
            'messages' => [
                'success' => 'Data Saved'
            ]
        ];
         
        return $this->respondCreated($response);
    }
 
    // update keuangan
    public function update($id = null)
    {
        $model = new SaldoModel();
        $json = $this->request->getJSON();
        if($json){
            $data = [
                'jml_saldo' => $json->jml_saldo,
                'update_at' => $json->update_at
            ];
        }else{
            $input = $this->request->getRawInput();
            $data = [
                'jml_saldo' => $input['jml_saldo'],
                'update_at' => $input['update_at']
            ];
        }
        // Insert to Database
        $model->update($id, $data);
        $response = [
            'status'   => 200,
            'error'    => null,
            'messages' => [
                'success' => 'Data Updated'
            ]
        ];
        return $this->respond($response);
    }
}