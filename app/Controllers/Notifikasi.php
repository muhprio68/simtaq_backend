<?php
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\KeuanganModel;
use App\Models\SaldoModel;
use App\Models\DonaturModel;
use App\Models\InfakModel;
use CodeIgniter\I18n\Time;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Notifikasi extends ResourceController
{
    public function index(){
        // header('Content-type: application/json');
        // // Set your Merchant Server Key
        // \Midtrans\Config::$serverKey = 'SB-Mid-server-Us4qHUjYvUBZPQKrSrSJr37y';
        // // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        // \Midtrans\Config::$isProduction = false;
        // // Set sanitization on (default)
        // \Midtrans\Config::$isSanitized = true;
        // // Set 3DS transaction for credit card to true
        // \Midtrans\Config::$is3ds = true;

        try {
            $json_result = file_get_contents('php://input');
            $result = json_decode($json_result, true);
    
            // $e  = $result['status_code'];
            // $a  = $result['order_id'];
            // $b  = $result['gross_amount'];
            $status_code = $result['status_code'];
    
            if ($status_code == 200){
                return $this->createKeuangan($result['order_id'], round($result['gross_amount']));
            }
        } catch(\Exception $e){
            return $this->fail('Tidak dapat memasukkan data keuangan');
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

    public function updateInfak($order_id){
        $model = new InfakModel();
                $json = $this->request->getJSON();
                $ada = $model->getWhere(['no_keuangan' => $order_id])->getResult();
                $data = json_decode($ada, true);
                $id = $data['id_infak'];
                if ($ada){
                    
                        $data = [
                            'tipe_keuangan' => $json->tipe_keuangan,
                            'tipe_keuangan' => $json->tipe_keuangan,
                            'keterangan_keuangan' => $json->keterangan_keuangan,
                            'status_keuangan' => $json->status_keuangan,
                            'nominal_keuangan' => $json->nominal_keuangan,
                            'deskripsi_keuangan' => $json->deskripsi_keuangan
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
    }

    public function createKeuangan($order_id, $nominal)
    {
        try {
            $model = new KeuanganModel();
            $jmlkasakhir = $this->getSaldo() + $nominal;
            $model->getNomorKeuangan();
                $data = [
                    'no_keuangan' => $order_id,
                    'tipe_keuangan' => "Pemasukan",
                    'tgl_keuangan' => $this->getTime(),
                    'keterangan_keuangan' => "Infaq bal bala",
                    'jenis_keuangan' => "Lain-lain",
                    'status_keuangan' => "Selesai",
                    'nominal_keuangan' => $nominal,
                    'jml_kas_awal' => $this->getSaldo(),
                    'jml_kas_akhir' => $jmlkasakhir,
                    'deskripsi_keuangan' => "",
                    'create_at' => $this->getTime(),
                    'update_at' => $this->getTime()
                ];
                // $data = json_decode(file_get_contents("php://input"));
                // $data = $this->request->getPost();
                $model->insert($data);
                $this->updateSaldo("Pemasukan", $nominal);
                $response = [
                    'status'   => 201,
                    'error'    => null,
                    'messages' => [
                        'success' => 'Data Saved'
                    ]
                ];
         
                return $this->respondCreated($response);
            } catch(\Exception $e){
                return $this->fail('Tidak dapat menyimpan data keuangan');
            }
        
    }

    public function getTime(){
        $myTime = Time::now('Asia/Jakarta', 'id_ID');
        return $myTime->toDateString();
    }
}