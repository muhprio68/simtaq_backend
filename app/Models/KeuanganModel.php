<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class KeuanganModel extends Model
{
    protected $table = 'keuangan';
    protected $primaryKey = 'id_keuangan';
    protected $allowedFields = ['no_keuangan','tipe_keuangan','tgl_keuangan','keterangan_keuangan','jenis_keuangan','status_keuangan','nominal_keuangan','jml_kas_awal','jml_kas_akhir','deskripsi_keuangan','create_at','update_at'];

    function getNomorKeuangan(){
        $q = $this->db->query("SELECT * FROM nomorkeuangan WHERE tgl_keuangan=CURDATE()");
        $kd = "";
        if($q->getNumRows()>0){
            foreach($q->getResult() as $k){
                $tmp = ((int)$k->no_terakhir)+1;
                $kd = sprintf("%04s", $tmp);
                $this->db->query("UPDATE nomorkeuangan SET no_terakhir = $tmp WHERE nomorkeuangan.tgl_keuangan = CURDATE()");
            }
        }else{
            $kd = "0001";
            $this->db->query("INSERT INTO nomorkeuangan (id_nomor, tgl_keuangan, no_terakhir) VALUES (NULL, CURDATE(), '1')");
        }
        date_default_timezone_set('Asia/Jakarta');
        return "KEU-".date('ymd').$kd;
    }
}