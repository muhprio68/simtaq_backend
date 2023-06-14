<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\DonaturModel;
use App\Models\KeuanganModel;
use App\Models\SaldoModel;
use CodeIgniter\I18n\Time;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class Donatur extends ResourceController
{
    use ResponseTrait;
    // get all donatur
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
                $model = new DonaturModel();
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
 
    // get single donatur
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
                $model = new DonaturModel();
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
 
    // create a donatur
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
                $model = new DonaturModel();
                if ($model->cekData($this->request->getPost('tgl_donatur'), $this->request->getPost('wilayah_donatur'))){
                    $this->createKeuanganDonatur($lvlusr, $this->request->getPost('tgl_donatur'), 
                    $this->request->getPost('wilayah_donatur'), $this->request->getPost('petugas_donatur'), $this->request->getPost('nominal_donatur'),
                    $this->getTime(),$this->getTime() );
                    $mdlKeu = new KeuanganModel();
                    $datahh = $mdlKeu->findAll();
                    $datakeu = $mdlKeu->countAllResults();
                    $data = [
                        'id_keuangan' => $datahh[$datakeu-1]['id_keuangan'],
                        'tgl_donatur' => $this->request->getPost('tgl_donatur'),
                        'wilayah_donatur' => $this->request->getPost('wilayah_donatur'),
                        'petugas_donatur' => $this->request->getPost('petugas_donatur'),
                        'nominal_donatur' => $this->request->getPost('nominal_donatur'),
                        'create_at' => $this->getTime(),
                        'update_at' => $this->getTime()
                    ];
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
                     return $this->fail('Wilayah '.$this->request->getPost('wilayah_donatur').' sudah tercatat untuk bulan ini');
                 } 
            } else {
                return $this->fail('Hanya untuk bendahara dan superadmin');
            }
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
        
    }

    public function createKeuanganDonatur($lvlusr, $tgl_donatur, $wilayah_donatur, $petugas_donatur, $nominal_donatur, $create_at, $update_at)
    {
        try {
            if ($lvlusr == 2 || $lvlusr == 4){
                $model = new KeuanganModel();
                $data = [
                    'no_keuangan' => $model->getNomorKeuangan(),
                    'tipe_keuangan' => "Pemasukan",
                    'tgl_keuangan' => $tgl_donatur,
                    'keterangan_keuangan' => "Donatur ".$wilayah_donatur,
                    'jenis_keuangan' => "Donatur",
                    'status_keuangan' => "Selesai",
                    'nominal_keuangan' => $nominal_donatur,
                    'jml_kas_awal' =>$this->getSaldo(),
                    'jml_kas_akhir' => $this->getSaldo()+$nominal_donatur,
                    'deskripsi_keuangan' => "Donatur wilayah ".$wilayah_donatur." dengan petugas ".$petugas_donatur,
                    'create_at' => $create_at,
                    'update_at' => $update_at
                ];
                // $data = json_decode(file_get_contents("php://input"));
                // $data = $this->request->getPost();
                $model->insert($data);
                $this->updateSaldo("Pemasukan", $nominal_donatur);
                $response = [
                    'status'   => 201,
                    'error'    => null,
                    'messages' => [
                        'success' => 'Data Saved keud'
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
 
    // update donatur
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
                $model = new DonaturModel();
                $ada = $model->find($id);
                if($ada){
                    $idkeu = $ada['id_keuangan'];
                    $json = $this->request->getJSON();

                    if($json){
                        if ($model->cekDataUpdate($id, $json->tgl_donatur, $json->wilayah_donatur)){
                            $this->updateKeuangan($lvlusr, $idkeu, $json->tgl_donatur, 
                            $json->wilayah_donatur, $json->petugas_donatur, $json->nominal_donatur,
                            $this->getTime());
                            $data = [
                                'tgl_donatur' => $json->tgl_donatur,
                                'wilayah_donatur' => $json->wilayah_donatur,
                                'petugas_donatur' => $json->petugas_donatur,
                                'nominal_donatur' => $json->nominal_donatur,
                                'update_at' => $this->getTime()
                            ];
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
                            return $this->fail('Wilayah '.$json->wilayah_donatur.' sudah tercatat untuk bulan ini');
                        }
                    }else{
                        $input = $this->request->getRawInput();
                        if ($model->cekDataUpdate($id, $input['tgl_donatur'],$input['wilayah_donatur'])){
                            $this->updateKeuangan($lvlusr, $idkeu, $input['tgl_donatur'], 
                            $input['wilayah_donatur'], $input['petugas_donatur'], $input['nominal_donatur'], $this->getTime());
                            $data = [
                                'tgl_donatur' => $input['tgl_donatur'],
                                'wilayah_donatur' => $input['wilayah_donatur'],
                                'petugas_donatur' => $input['petugas_donatur'],
                                'nominal_donatur' => $input['nominal_donatur'],
                                'update_at' => $this->getTime()
                            ];
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
                            return $this->fail('Wilayah '.$input['wilayah_donatur'].' sudah tercatat untuk bulan ini');
                        }
                    }
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


    public function updateKeuangan($lvlusr, $id, $tgl_donatur, $wilayah_donatur, $petugas_donatur, $nominal_donatur, $update_at)
    {
        try {
            if ($lvlusr == 2 || $lvlusr == 4){
                $model = new KeuanganModel();
                $ada = $model->find($id);
                if ($ada){
                    $nominalawal = $ada['nominal_keuangan'];
                    $this->updateSaldo("Pengeluaran", $nominalawal);
                    $jmlkasakhir = $this->getSaldo() + $nominal_donatur;
                    $data = [
                        'tipe_keuangan' => "Pemasukan",
                        'tgl_keuangan' => $tgl_donatur,
                        'keterangan_keuangan' => "Donatur ".$wilayah_donatur,
                        'jenis_keuangan' => "Donatur",
                        'status_keuangan' => "Selesai",
                        'nominal_keuangan' => $nominal_donatur,
                        'jml_kas_awal' => $this->getSaldo(),
                        'jml_kas_akhir' => $jmlkasakhir,
                        'deskripsi_keuangan' => "Donatur wilayah ".$wilayah_donatur." dengan petugas ".$petugas_donatur,
                        'update_at' => $update_at
                    ];
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
 
    // delete donatur
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
                $model = new DonaturModel();
                $data = $model->find($id);
                if($data){
                    $model->delete($id);
                    return $this->deleteKeuangan($data['id_keuangan'], $lvlusr);
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

    public function deleteKeuangan($id, $lvlusr)
    {
        try {
            if ($lvlusr == 2 || $lvlusr == 4){
                $model = new keuanganModel();
                $data = $model->find($id);
                if($data){
                    $nominal = $data['nominal_keuangan'];
                    $tipe = $data['tipe_keuangan'];
                    $this->updateSaldo("Pengeluaran", $nominal);
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

    public function getTime(){
        $myTime = Time::now('Asia/Jakarta', 'id_ID');
        return $myTime->toDateString();
    }
 
}