<?php
class Absensi_model extends CI_Model {

    public function get_all() {
        return $this->db->select('absensi.*, kelas.nama as nama_kelas')
            ->join('kelas', 'kelas.id = absensi.id_kelas')
            ->order_by('id_absensi', 'DESC')
            ->get('absensi')
            ->result();
    }

    public function insert_absensi($data) {
        $this->db->insert('absensi', $data);
        return $this->db->insert_id();
    }

    public function insert_detail($data) {
        $this->db->insert('absensi_detail', $data);
    }

    // untuk laporan: dapatkan siapa yang tidak hadir
    public function get_absen_detail($id_absensi) {
        return $this->db->get_where('absensi_detail', ['id_absensi' => $id_absensi])->result();
    }
    // get header absensi
public function get_by_id($id) {
    return $this->db->select('absensi.*, kelas.nama as nama_kelas')
        ->join('kelas','kelas.id = absensi.id_kelas')
        ->where('id_absensi', $id)
        ->get('absensi')
        ->row();
}

// siswa + status lengkap
public function get_siswa_with_status($id_kelas, $id_absensi) {
    $sql = "
        SELECT 
            s.id_siswa,
            s.nama_siswa,
            IFNULL(
                (SELECT UPPER(LEFT(status,1)) 
                 FROM absensi_detail 
                 WHERE id_siswa = s.id_siswa 
                   AND id_absensi = $id_absensi
                ),
                'H'
            ) AS status
        FROM siswa s
        WHERE s.id_kelas = $id_kelas
        ORDER BY s.nama_siswa ASC
    ";
    return $this->db->query($sql)->result();
}
// cari header absensi, jika tidak ada buat baru
public function get_or_create_absensi($tanggal, $id_kelas, $tahun)
{
    $this->db->where('tanggal', $tanggal);
    $this->db->where('id_kelas', $id_kelas);
    $this->db->where('tahun_pelajaran', $tahun);
    $cek = $this->db->get('absensi')->row();

    if ($cek) {
        return $cek->id_absensi;
    }

    $this->db->insert('absensi', [
        'tanggal' => $tanggal,
        'id_kelas' => $id_kelas,
        'tahun_pelajaran' => $tahun
    ]);

    return $this->db->insert_id();
}

public function laporan_filter($nama, $kelas, $dari, $sampai, $tahun, $ket)
{
    $this->db->select("
        a.tanggal,
        a.tahun_pelajaran,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        d.status,
        d.keterangan
    ");

    $this->db->from("absensi_detail d");
    $this->db->join("absensi a", "a.id_absensi = d.id_absensi");
    $this->db->join("siswa s", "s.id = d.id_siswa");
    $this->db->join("kelas k", "k.id = s.id_kelas");

    if ($nama != "") {
        $this->db->like("s.nama", $nama);
    }

    if ($kelas != "") {
        $this->db->where("k.id", $kelas);
    }

    if ($ket != "") {
        $this->db->where("d.status", $ket);
    }

    if ($tahun != "") {
        $this->db->where("a.tahun_pelajaran", $tahun);
    }

    if ($dari != "" && $sampai != "") {
        $this->db->where("a.tanggal >=", $dari);
        $this->db->where("a.tanggal <=", $sampai);
    }

    $this->db->order_by("a.tanggal", "ASC");

    return $this->db->get()->result();
}

}
