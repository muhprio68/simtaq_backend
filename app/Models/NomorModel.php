<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class NomorModel extends Model
{
    protected $table = 'nomorkeuangan';
    protected $primaryKey = 'id_nomor';
    protected $allowedFields = ['tgl_keuangan','no_terakhir'];
}