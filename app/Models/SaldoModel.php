<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class SaldoModel extends Model
{
    
    protected $table = 'saldo';
    protected $primaryKey = 'id_saldo';
    protected $allowedFields = ['jml_saldo','update_at'];

}