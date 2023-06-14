<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\NomorModel;

 
class Nomor extends ResourceController
{
    use ResponseTrait;
    // get all keuangan
    public function index()
    {
        $model = new NomorModel();
        $data = $model->findAll();
        return $this->respond($data, 200);
    }
 
    // get single keuangan
    public function show($id = null)
    {
        $model = new NomorModel();
        $data = $model->getWhere(['tgl_keuangan' => $id])->getResult();
        if($data){
            return $this->respond($data);
        }else{
            return $this->failNotFound('No Data Found with id '.$id);
        }
    }
 
    // create a keuangan
    public function create()
    {
        $model = new NomorModel();
        $data = [
            'tgl_keuangan' => $this->request->getPost('tgl_keuangan'),
            'no_terakhir' => $this->request->getPost('no_terakhir')
        ];
        // $data = json_decode(file_get_contents("php://input"));
        // $data = $this->request->getPost();
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
        $model = new NomorModel();
        $json = $this->request->getJSON();
        if($json){
            $data = [
                'tgl_keuangan' => $json->tgl_keuangan,
                'no_terakhir' => $json->no_terakhir
            ];
        }else{
            $input = $this->request->getRawInput();
            $data = [
                'tgl_keuangan' => $input['tgl_keuangan'],
                'no_terakhir' => $input['no_terakhir']
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
 
    // delete keuangan
    public function delete($id = null)
    {
        $model = new NomorModel();
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
         
    }
 
}