<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class KegiatanModel extends Model
{
    protected $table = 'kegiatan';
    protected $primaryKey = 'id_kegiatan';
    protected $allowedFields = ['no_kegiatan','nama_kegiatan','tipe_kegiatan','tgl_kegiatan','waktu_kegiatan','tempat_kegiatan','pembicara_kegiatan','deskripsi_kegiatan','create_at','update_at'];
    
    function getNomorKegiatan(){
        $q = $this->db->query("SELECT * FROM nomorkegiatan WHERE tgl_kegiatan=CURDATE()");
        $kd = "";
        if($q->getNumRows()>0){
            foreach($q->getResult() as $k){
                $tmp = ((int)$k->no_terakhir)+1;
                $kd = sprintf("%03s", $tmp);
                $this->db->query("UPDATE nomorkegiatan SET no_terakhir = $tmp WHERE nomorkegiatan.tgl_kegiatan = CURDATE()");
                
            }
        }else{
            $kd = "001";
            $this->db->query("INSERT INTO nomorkegiatan (id_nomor, tgl_kegiatan, no_terakhir) VALUES (NULL, CURDATE(), '1')");
        }
        date_default_timezone_set('Asia/Jakarta');
        return "KG-".date('ymd').$kd;
    }

}