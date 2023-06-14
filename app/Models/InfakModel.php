<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class InfakModel extends Model
{
    protected $table = 'infak';
    protected $primaryKey = 'id_infak';
    protected $allowedFields = ['no_keuangan','id_user','keterangan_infak','nominal_infak','deskripsi_infak','status_infak'];
}