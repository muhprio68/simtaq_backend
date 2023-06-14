<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\KeuanganModel;
use App\Models\SaldoModel;
use App\Models\DonaturModel;
use CodeIgniter\I18n\Time;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
 
class Keuangan extends ResourceController
{
    use ResponseTrait;
    // get all keuangan
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
                $model = new KeuanganModel();
                $data = $model->findAll();
                return $this->respond($data, 200);
            } else {
                return $this->fail('User tidak terdaftar');
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }

    public function getSaldo()
    {
        $saldomodel = new SaldoModel();
        $datasaldo = $saldomodel->find(1);
        return $datasaldo['jml_saldo'];
    }

    public function updateSaldo($tipe, $nominal)
    {
        $model = new SaldoModel();
        if($tipe == "Pemasukan"){
            $data = [
                'jml_saldo' => $this->getSaldo()+$nominal,
                'update_at' => $this->getTime()
            ];
        }else{
            $data = [
                'jml_saldo' => $this->getSaldo()-$nominal,
                'update_at' => $this->getTime()
            ];
        }
        // Insert to Database
        $model->update(1, $data);
        $response = [
            'status'   => 200,
            'error'    => null,
            'messages' => [
                'success' => 'Data Updated'
            ]
        ];
        return $this->respond($response);
    }
 
    // get single keuangan
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
                $model = new KeuanganModel();
                $data = $model->getWhere(['id_keuangan' => $id])->getResult();
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
 
    // create a keuangan
    public function create()
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
        try {
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $lvlusr = $decoded->level;
            if ($lvlusr == 2 || $lvlusr == 4){
                $model = new KeuanganModel();
                if ($this->request->getPost('tipe_keuangan') == "Pemasukan"){
                    $jmlkasakhir = $this->getSaldo() + $this->request->getPost('nominal_keuangan');
                } else {
                    $jmlkasakhir = $this->getSaldo() - $this->request->getPost('nominal_keuangan');
                }
                
                $data = [
                    'no_keuangan' => $model->getNomorKeuangan(),
                    'tipe_keuangan' => $this->request->getPost('tipe_keuangan'),
                    'tgl_keuangan' => $this->request->getPost('tgl_keuangan'),
                    'keterangan_keuangan' => $this->request->getPost('keterangan_keuangan'),
                    'jenis_keuangan' => $this->request->getPost('jenis_keuangan'),
                    'status_keuangan' => $this->request->getPost('status_keuangan'),
                    'nominal_keuangan' => $this->request->getPost('nominal_keuangan'),
                    'jml_kas_awal' => $this->getSaldo(),
                    'jml_kas_akhir' => $jmlkasakhir,
                    'deskripsi_keuangan' => $this->request->getPost('deskripsi_keuangan'),
                    'create_at' => $this->getTime(),
                    'update_at' => $this->getTime()
                ];
                // $data = json_decode(file_get_contents("php://input"));
                // $data = $this->request->getPost();
                $model->insert($data);
                $this->updateSaldo($this->request->getPost('tipe_keuangan'), $this->request->getPost('nominal_keuangan'));
                $response = [
                    'status'   => 201,
                    'error'    => null,
                    'messages' => [
                        'success' => 'Data Saved'
                    ]
                ];
         
                return $this->respondCreated($response);
            } else {
                return $this->fail('Hanya untuk bendahara dan superadmin');
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }

   
 
    // update keuangan
    public function update($id = null)
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
        try {
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $lvlusr = $decoded->level;
            if ($lvlusr == 2 || $lvlusr == 4){
                $model = new KeuanganModel();
                $json = $this->request->getJSON();
                $ada = $model->find($id);
                if ($ada){
                    $nominal = $ada['nominal_keuangan'];
                    $tipe = $ada['tipe_keuangan'];
                    $jmlkasakhir = null;
                    if ($tipe == "Pemasukan"){
                        $this->updateSaldo("Pengeluaran", $nominal);
                    } else {
                        $this->updateSaldo("Pemasukan", $nominal);
                    }
                    if($json){
                        if ($json->tipe_keuangan == "Pemasukan"){
                            $jmlkasakhir = $this->getSaldo() + $json->nominal_keuangan;
                        } else {
                            $jmlkasakhir = $this->getSaldo() - $json->nominal_keuangan;
                        }
                        $data = [
                            'tipe_keuangan' => $json->tipe_keuangan,
                            'tgl_keuangan' => $json->tgl_keuangan,
                            'keterangan_keuangan' => $json->keterangan_keuangan,
                            'jenis_keuangan' => $json->jenis_keuangan,
                            'status_keuangan' => $json->status_keuangan,
                            'nominal_keuangan' => $json->nominal_keuangan,
                            'jml_kas_awal' => $this->getSaldo(),
                            'jml_kas_akhir' => $jmlkasakhir,
                            'deskripsi_keuangan' => $json->deskripsi_keuangan,
                            'update_at' => $this->getTime()
                        ];
                    }else{
                        $input = $this->request->getRawInput();
                        if ($input['tipe_keuangan'] == "Pemasukan"){
                            $jmlkasakhir = $this->getSaldo() + $input['nominal_keuangan'];
                        } else {
                            $jmlkasakhir = $this->getSaldo() - $input['nominal_keuangan'];
                        }
                        $data = [
                            'tipe_keuangan' => $input['tipe_keuangan'],
                            'tgl_keuangan' => $input['tgl_keuangan'],
                            'keterangan_keuangan' => $input['keterangan_keuangan'],
                            'jenis_keuangan' => $input['jenis_keuangan'],
                            'status_keuangan' => $input['status_keuangan'],
                            'nominal_keuangan' => $input['nominal_keuangan'],
                            'jml_kas_awal' => $this->getSaldo(),
                            'jml_kas_akhir' => $jmlkasakhir,
                            'deskripsi_keuangan' => $input['deskripsi_keuangan'],
                            'update_at' => $this->getTime()
                        ];
                    }
                    // Insert to Database
                    $model->update($id, $data);
                    $this->updateSaldo($data['tipe_keuangan'], $data['nominal_keuangan']);
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
                return $this->fail('Hanya untuk bendahara dan superadmin');
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }
 
    // delete keuangan
    public function delete($id = null)
    {
        $key = getenv('TOKEN_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
        try {
            $decoded = JWT::decode($token, new Key ($key, 'HS256'));
            $lvlusr = $decoded->level;
            if ($lvlusr == 2 || $lvlusr == 4){
                $model = new keuanganModel();
                $data = $model->find($id);
                if($data){
                    $nominal = $data['nominal_keuangan'];
                    $tipe = $data['tipe_keuangan'];
                    if ($tipe == "Pemasukan"){
                        $this->updateSaldo("Pengeluaran", $nominal);
                    } else {
                        $this->updateSaldo("Pemasukan", $nominal);
                    }
                    $this->deleteDonatur($lvlusr, $id);
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
                return $this->fail('Hanya untuk bendahara dan superadmin');
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }

    // delete donatur
    public function deleteDonatur($lvlusr, $id)
    {
        try {
            if ($lvlusr == 2 || $lvlusr == 4){
                $model = new DonaturModel();
                $data = $model->getWhere(['id_keuangan' => $id])->getResult();;
                if($data){
                    $model->where('id_keuangan', $id)->delete();
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
                return $this->fail('Hanya untuk bendahara dan superadmin');
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }

    public function getTime(){
        $myTime = Time::now('Asia/Jakarta', 'id_ID');
        return $myTime->toDateString();
    }

    // public function getTimeCoba($tgl){
    //     $time = Time::parse($tgl);
    //     $tahun = $time->getYear();
    //     $bulan = $time->getMonth();
    //     return $tahun;
    // }
    
}