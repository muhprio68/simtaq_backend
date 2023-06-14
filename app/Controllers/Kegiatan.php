<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\KegiatanModel;
use CodeIgniter\I18n\Time;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
 
class Kegiatan extends ResourceController
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
            $idusr = $decoded->uid;
            if ($idusr != null){
                $model = new KegiatanModel();
                $data = $model->findAll();
                return $this->respond($data, 200);
            } else {
                return $this->fail('User tidak terdaftar');
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
            if ($idusr != null){
                $model = new KegiatanModel();
                $data = $model->getWhere(['id_kegiatan' => $id])->getResult();
                if($data){
                    return $this->respond($data);
                }else{
                    return $this->failNotFound('No Data Found with id '.$id);
                }
            } else {
                return $this->fail('User tidak terdaftar');
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }

    // create a kegiatan
    public function create()
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
        try {
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $lvlusr = $decoded->level;
            if ($lvlusr == 3 || $lvlusr == 4){
                $model = new KegiatanModel();
                $data = [
                    'no_kegiatan' => $model->getNomorKegiatan(),
                    'nama_kegiatan' => $this->request->getPost('nama_kegiatan'),
                    'tipe_kegiatan' => $this->request->getPost('tipe_kegiatan'),
                    'tgl_kegiatan' => $this->request->getPost('tgl_kegiatan'),
                    'waktu_kegiatan' => $this->request->getPost('waktu_kegiatan'),
                    'tempat_kegiatan' => $this->request->getPost('tempat_kegiatan'),
                    'pembicara_kegiatan' => $this->request->getPost('pembicara_kegiatan'),
                    'deskripsi_kegiatan' => $this->request->getPost('deskripsi_kegiatan'),
                    'create_at' => $this->getTime(),
                    'update_at' => $this->getTime()
                ];
                //$data = json_decode(file_get_contents("php://input"));
                //$data = $this->request->getPost();
                $model->insert($data);
                $response = [
                    'status'   => 201,
                    'error'    => null,
                    'messages' => [
                        'success' => 'Data Saved'
                    ]
                ];
                
                return $this->respondCreated($response);
            } else {
                return $this->fail('Hanya untuk humas takmir dan superadmin');
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }
 
    // update kegiatan
    public function update($id = null)
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
        try {
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $lvlusr = $decoded->level;
            if ($lvlusr == 3 | $lvlusr == 4){
                $model = new KegiatanModel();
                $json = $this->request->getJSON();
                if($json){
                    $data = [
                        'nama_kegiatan' => $json->nama_kegiatan,
                        'tipe_kegiatan' => $json->tipe_kegiatan,
                        'tgl_kegiatan' => $json->tgl_kegiatan,
                        'waktu_kegiatan' => $json->waktu_kegiatan,
                        'tempat_kegiatan' => $json->tempat_kegiatan,
                        'pembicara_kegiatan' => $json->pembicara_kegiatan,
                        'deskripsi_kegiatan' => $json->deskripsi_kegiatan,
                        'update_at' => $this->getTime()
                    ];
                }else{
                    $input = $this->request->getRawInput();
                    $data = [
                        'nama_kegiatan' => $input['nama_kegiatan'],
                        'tipe_kegiatan' => $input['tipe_kegiatan'],
                        'tgl_kegiatan' => $input['tgl_kegiatan'],
                        'waktu_kegiatan' => $input['waktu_kegiatan'],
                        'tempat_kegiatan' => $input['tempat_kegiatan'],
                        'pembicara_kegiatan' => $input['pembicara_kegiatan'],
                        'deskripsi_kegiatan' => $input['deskripsi_kegiatan'],
                        'update_at' => $this->getTime()
                    ];
                }
                $ada = $model->find($id);
                if($ada){
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
                } else {
                    return $this->failNotFound('No Data Found with id '.$id);
                }
            } else {
                return $this->fail('Hanya untuk humas takmir dan superadmin');
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
            $lvlusr = $decoded->level;
            if ($lvlusr == 3 || $lvlusr == 4){
                $model = new KegiatanModel();
                $data = $model->find($id);
                if($data){
                    $model->delete($id);
                    $response = [
                        'status'   => 200,
                        'error'    => null,
                        'messages' => [
                            'success' => 'Data Deleted'
                        ]
                    ];
                    
                    return $this->respondDeleted($response);
                }else{
                    return $this->failNotFound('No Data Found with id '.$id);
                }
            } else {
                return $this->fail('Hanya untuk humas takmir dan superadmin');
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
         
    }

    public function getTime(){
        $myTime = Time::now('Asia/Jakarta', 'id_ID');
        return $myTime->toDateString();
    }
 
}