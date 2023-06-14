<?php namespace App\Models;
 
use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

 
class DonaturModel extends Model
{
    protected $table = 'donatur';
    protected $primaryKey = 'id_donatur';
    protected $allowedFields = ['id_keuangan','tgl_donatur','wilayah_donatur','petugas_donatur','nominal_donatur','create_at','update_at'];
    
    
    function cekData($tgl, $wilayah_donatur){
        $time = Time::parse($tgl);
        $tahun = $time->getYear();
        $bulan = $time->getMonth();

        $q = $this->db->query("SELECT * FROM donatur WHERE wilayah_donatur = '$wilayah_donatur' and YEAR(tgl_donatur) = '$tahun' and MONTH(tgl_donatur) = '$bulan'");
        if($q->getNumRows()<1){
            return true;
        }else{
            return false;
        }
    }

    function cekDataUpdate($id, $tgl, $wilayah_donatur){
        $time = Time::parse($tgl);
        $tahun = $time->getYear();
        $bulan = $time->getMonth();

        $q = $this->db->query("SELECT * FROM donatur WHERE id_donatur != '$id' and wilayah_donatur = '$wilayah_donatur' and YEAR(tgl_donatur) = '$tahun' and MONTH(tgl_donatur) = '$bulan'");
        if($q->getNumRows()<1){
            return true;
        }else{
            return false;
        }
    }
} 