<?php
class Izin_model extends CI_Model {

    public function get_siswa_by_token($token)
{
    $this->db->select('siswa.*, kelas.nama AS kelas_nama, kelas.id AS kelas_id');
    $this->db->from('siswa');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->where('siswa.token_qr', $token);
    return $this->db->get()->row();
}


    public function izin_aktif($siswa_id)
    {
        return $this->db->order_by('id', 'DESC')
                        ->get_where('izin_keluar', ['siswa_id' => $siswa_id, 'status' => 'keluar'])
                        ->row();
    }

    public function get_izin_by_token_kembali($token)
    {
        return $this->db->get_where('izin_keluar', ['token_kembali' => $token])->row();
    }

    public function insert_izin($data)
    {
        $this->db->insert('izin_keluar', $data);
        return $this->db->insert_id();
    }

    public function set_kembali($id)
    {
        $this->db->where('id', $id)->update('izin_keluar', [
            'jam_masuk' => date('Y-m-d H:i:s'),
            'status'    => 'kembali'
        ]);
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('izin_keluar', ['id' => $id])->row();
    }

    public function all()
    {
        return $this->db->order_by('id', 'DESC')->get('izin_keluar')->result();
    }
}
