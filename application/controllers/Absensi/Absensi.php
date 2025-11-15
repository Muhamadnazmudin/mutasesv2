<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Absensi/Absensi_model');
        $this->load->model('Kelas_model'); // kita pakai kelas yg sudah ada
        $this->load->model('Siswa_model'); // data siswa aktif
    }

    public function index()
{
    $this->db->select("
        ad.id_detail,
        ad.id_absensi,
        ad.status,
        ad.keterangan,
        a.tanggal,
        a.tahun_pelajaran,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        g.telp AS nohp_wali
    ");
    $this->db->from("absensi_detail ad");
    $this->db->join("absensi a", "a.id_absensi = ad.id_absensi");
    $this->db->join("siswa s", "s.id = ad.id_siswa");
    $this->db->join("kelas k", "k.id = s.id_kelas");
    $this->db->join("guru g", "g.id = k.wali_kelas_id", "left"); // 🔥 FIX DISINI
    $this->db->order_by("ad.id_detail", "DESC");

    $data["absensi"] = $this->db->get()->result();
    $data['judul'] = "Data Absensi";
    $data['active'] = 'absensi';

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensi/index', $data);
    $this->load->view('templates/footer');
}
    public function tambah() {
    $data['judul'] = "Tambah Absensi";
    $data['kelas'] = $this->Kelas_model->get_all();
    $data['siswa_all'] = $this->Siswa_model->get_all_simple();
    $data['active'] = 'absensi';

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensi/tambah', $data);
    $this->load->view('templates/footer');
}


    public function form() {
        $tanggal = $this->input->post('tanggal');
        $id_kelas = $this->input->post('id_kelas');
        $tahun = $this->input->post('tahun_pelajaran');
        $data['active'] = 'absensi';

        $data['tanggal'] = $tanggal;
        $data['tahun']   = $tahun;
        $data['kelas']   = $this->Kelas_model->get_row($id_kelas);
        $data['siswa']   = $this->Siswa_model->get_by_kelas($id_kelas);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('absensi/form', $data);
        $this->load->view('templates/footer');
    }

   public function simpan() {

    $tanggal = $this->input->post('tanggal');
    $id_kelas = $this->input->post('id_kelas'); // WAJIB
    $id_siswa = $this->input->post('id_siswa');
    $status   = $this->input->post('status');
    $alasan   = $this->input->post('keterangan');
    $tahun    = $this->input->post('tahun_pelajaran');

    // Validasi
    if (!$tanggal || !$id_kelas || !$id_siswa || !$status) {
        $this->session->set_flashdata('error', 'Lengkapi semua data!');
        return redirect(site_url('Absensi/Absensi'));

    }

    // Buat header absensi / ambil existing
    $id_absensi = $this->Absensi_model->get_or_create_absensi(
        $tanggal,
        $id_kelas,
        $tahun
    );
// Cari apakah sudah ada header absensi (tanggal + kelas)
$absensi_header = $this->db->get_where('absensi', [
    'tanggal' => $tanggal,
    'id_kelas' => $id_kelas
])->row();

// Kalau header ada, cek apakah siswa pernah absen di header itu
if ($absensi_header) {

    $cek_detail = $this->db->get_where('absensi_detail', [
        'id_absensi' => $absensi_header->id_absensi,
        'id_siswa' => $id_siswa
    ])->row();

    if ($cek_detail) {
        $this->session->set_flashdata('error', 'Siswa ini sudah memiliki catatan absensi pada tanggal tersebut.');
        redirect('Absensi/Absensi');
    }
}


    // Simpan detail absensi
    $this->Absensi_model->insert_detail([
        'id_absensi' => $id_absensi,
        'id_siswa' => $id_siswa,
        'status' => $status,
        'keterangan' => $alasan
    ]);

    $this->session->set_flashdata('success', 'Absensi berhasil disimpan.');
    return redirect(site_url('Absensi/Absensi'));

}



    public function detail($id_absensi) {
    $data['judul'] = "Detail Absensi";
    $data['active'] = 'absensi';

    // data header absensi
    $data['absen'] = $this->Absensi_model->get_by_id($id_absensi);

if (!$data['absen']) {
    $this->session->set_flashdata('error', 'Data absensi tidak ditemukan.');
    redirect('Absensi/Absensi');
}


    // ambil list siswa + status (H/I/S/A)
    $data['siswa'] = $this->Absensi_model->get_siswa_with_status(
        $data['absen']->id_kelas,
        $id_absensi
    );

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensi/detail', $data);
    $this->load->view('templates/footer');
}
// halaman input single
public function input()
{
    $data['judul'] = "Input Data Absen";
    $data['kelas'] = $this->Kelas_model->get_all();
    // ambil semua siswa untuk datalist (bisa diganti ajax jika jumlah banyak)
    $data['siswa_all'] = $this->Siswa_model->get_all_simple();
    $data['active'] = 'absensi';

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensi/input_single', $data);
    $this->load->view('templates/footer');
}

// simpan single absen
// public function save_single()
// {
//     // CSRF secara otomatis dicek karena config['csrf_protection'] = TRUE
//     $tanggal = $this->input->post('tanggal');
//     $id_kelas = $this->input->post('id_kelas');
//     $id_siswa = $this->input->post('id_siswa');
//     $status   = $this->input->post('status');
//     $keterangan = $this->input->post('keterangan');

//     if (!$tanggal || !$id_kelas || !$id_siswa || !$status) {
//         $this->session->set_flashdata('error', 'Lengkapi semua field yang wajib.');
//         redirect('index.php/Absensi/Absensi/input');
//     }

//     // dapatkan atau buat header absensi untuk tanggal+kelas+tahun (gunakan tahun dari input atau default)
//     $tahun_pelajaran = $this->input->post('tahun_pelajaran');
//     if (!$tahun_pelajaran) $tahun_pelajaran = date('Y') . '/' . (date('Y')+1); // contoh default

//     $id_absensi = $this->Absensi_model->get_or_create_absensi($tanggal, $id_kelas, $tahun_pelajaran);

//     // cek apakah sudah ada record untuk siswa ini pada header yang sama -> update atau insert
//     $exists = $this->db->get_where('absensi_detail', [
//         'id_absensi' => $id_absensi,
//         'id_siswa' => $id_siswa
//     ])->row();

//     $data_detail = [
//         'id_absensi' => $id_absensi,
//         'id_siswa' => $id_siswa,
//         'status' => $status,
//         'keterangan' => $keterangan
//     ];

//     if ($exists) {
//         // update
//         $this->db->where('id_detail', $exists->id_detail);
//         $this->db->update('absensi_detail', $data_detail);
//     } else {
//         // insert
//         $this->Absensi_model->insert_detail($data_detail);
//     }

//     $this->session->set_flashdata('success', 'Absensi tersimpan.');
//     redirect('index.php/Absensi/Absensi/input');
// }

public function update()
{
    $id_detail = $this->input->post('id_detail');
    $status    = $this->input->post('status');
    $ket       = $this->input->post('keterangan');
    $tanggal   = $this->input->post('tanggal');

    // Cek apakah detail ada
    $detail = $this->db->get_where('absensi_detail', ['id_detail' => $id_detail])->row();

    if (!$detail) {
        $this->session->set_flashdata('error', 'Data absensi tidak ditemukan.');
        redirect('Absensi/Absensi');
    }

    // Ambil header absensi
    $header = $this->db->get_where('absensi', ['id_absensi' => $detail->id_absensi])->row();

    if (!$header) {
        $this->session->set_flashdata('error', 'Header absensi tidak ditemukan.');
        redirect('Absensi/Absensi');
    }

    // CEK DUPLIKASI: siswa yang sama di tanggal yang sama kecuali record yang sedang di-edit
    $cekDuplikat = $this->db->query("
        SELECT * FROM absensi_detail d
        JOIN absensi a ON a.id_absensi = d.id_absensi
        WHERE d.id_siswa = ?
        AND a.tanggal = ?
        AND d.id_detail != ?
    ", [$detail->id_siswa, $tanggal, $id_detail])->row();

    if ($cekDuplikat) {
        $this->session->set_flashdata('error', 'Tidak bisa mengubah, siswa sudah memiliki absensi pada tanggal tersebut.');
        redirect('Absensi/Absensi');
    }

    // UPDATE HEADER tanggal JIKA tanggal berubah
    $this->db->where('id_absensi', $detail->id_absensi)->update('absensi', [
        'tanggal' => $tanggal
    ]);

    // UPDATE detail absensi
    $this->db->where('id_detail', $id_detail)->update('absensi_detail', [
        'status' => $status,
        'keterangan' => $ket
    ]);

    $this->session->set_flashdata('success', 'Data absensi berhasil diperbarui.');
    redirect('Absensi/Absensi');
}

public function ajax_siswa()
{
    $keyword = $this->input->post('keyword');

    $this->db->select('siswa.id, siswa.nisn, siswa.nama, kelas.nama AS nama_kelas, siswa.id_kelas');
    $this->db->from('siswa');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');

    // 🔥 hanya siswa aktif
    $this->db->where('siswa.status', 'aktif');

    // 🔥 perbaiki pencarian agar kondisi WHERE tidak kacau oleh OR LIKE
    $this->db->group_start();
        $this->db->like('siswa.nama', $keyword);
        $this->db->or_like('siswa.nisn', $keyword);
    $this->db->group_end();

    $this->db->limit(20);

    $result = $this->db->get()->result();

    echo json_encode($result);
}
public function hapus($id_absensi)
{
    // Hapus detail absensi
    $this->db->delete('absensi_detail', ['id_absensi' => $id_absensi]);

    // Hapus header absensi
    $this->db->delete('absensi', ['id_absensi' => $id_absensi]);

    $this->session->set_flashdata('success', 'Data absensi berhasil dihapus.');
    redirect('Absensi/Absensi');
}

}
