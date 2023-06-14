<?php
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Charge extends ResourceController
{
    public function create(){
        header('Content-type: application/json');
        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = 'SB-Mid-server-Us4qHUjYvUBZPQKrSrSJr37y';
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = false;
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;

        $data = json_decode(file_get_contents("php://input"), true);
        $ppp = $data['transaction_details'];
        $json = $this->request->getJSON();
        $params = [
            'transaction_details' => array(
                'order_id' => $ppp['order_id'],
                'gross_amount' => $ppp['gross_amount']
            ),
            'custom_field1'=> "data1",
            'custom_field2'=> "data2",
            'custom_field3'=> "data3"
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        $data = [
            'token' => $snapToken,
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$snapToken
        ];
        
         
        return $this->respondCreated($data);
    }

    
}