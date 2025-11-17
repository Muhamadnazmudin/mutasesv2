<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Izin extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Izin_model');
        $this->load->model('Guru_model');
        require_once APPPATH . 'libraries/phpqrcode/qrlib.php';
        $this->load->database();
    }

    // =======================================================================
    // 1. SCAN KARTU SISWA
    // =======================================================================
    public function scan($token = null)
{
    // Kalau hasil scanner berupa URL panjang
    if ($token !== null && strpos($token, 'http') !== false) {
        $parts = explode('/', $token);
        $token = end($parts);
    }

    // Jika token kembali
    if ($token !== null && strpos($token, 'kembali_') === 0) {
        redirect('izin/kembali/' . $token);
        return;
    }

    // Tanpa token → tampilkan QR scanner
    if ($token === null || $token == "") {
        $this->load->view('izin/scan');
        return;
    }

    // Ambil siswa + join kelas
    $siswa = $this->Izin_model->get_siswa_by_token($token);

    if (!$siswa) {
        echo "<h3>QR Code tidak valid!</h3>";
        return;
    }

    // Ambil kelas siswa
    $kelas = $this->db->get_where('kelas', ['id' => $siswa->kelas_id])->row();

    // Ambil wali kelas
    $walikelas = null;
    if ($kelas && $kelas->wali_kelas_id) {
        $walikelas = $this->db->get_where('guru', [
            'id' => $kelas->wali_kelas_id
        ])->row();
    }

    // Ambil semua guru
    $guru_list = $this->db->order_by('nama', 'ASC')->get('guru')->result();

    // Kirim ke view
    $data = [
        'siswa'      => $siswa,
        'kelas'      => $kelas,
        'walikelas'  => $walikelas,
        'guru_list'  => $guru_list,
        'token_qr'   => $token
    ];

    $this->load->view('izin/scan_form', $data);
}



    // =======================================================================
    // 2. AJUKAN IZIN KELUAR
    // =======================================================================
    public function ajukan($token_qr)
{
    $siswa = $this->Izin_model->get_siswa_by_token($token_qr);

    if (!$siswa) {
        echo "Token QR tidak valid.";
        return;
    }

    // Ambil kelas
    $kelas = $this->db->get_where('kelas', ['id' => $siswa->kelas_id])->row();

    $jenis           = $this->input->post('jenis');
    $keperluan       = $this->input->post('keperluan');
    $estimasi        = $this->input->post('estimasi');
    $id_guru_mapel   = $this->input->post('id_guru_mapel');
    $id_piket        = $this->input->post('id_piket');
    $id_walikelas    = $this->input->post('id_walikelas');
    $ditujukan       = $this->input->post('ditujukan');

    // Validasi sederhana
    if (empty($jenis) || empty($keperluan)) {
        $this->session->set_flashdata('error', 'Jenis izin dan keperluan harus diisi.');
        redirect('izin/scan/' . $token_qr);
        return;
    }

    // Token kembali hanya untuk izin keluar
    $token_kembali = null;
    if ($jenis == 'keluar') {
        $token_kembali = uniqid('kembali_');
    }

    // Jika nilai kosong harus dibuat null agar aman
    if ($id_guru_mapel == "") $id_guru_mapel = null;
    if ($id_piket == "") $id_piket = null;
    if ($id_walikelas == "") $id_walikelas = null;
    if ($ditujukan == "") $ditujukan = null;

    $data_insert = [
        'siswa_id'       => $siswa->id,
        'nis'            => isset($siswa->nis) ? $siswa->nis : null,
        'nama'           => $siswa->nama,
        'kelas_id'       => $siswa->kelas_id,
        'kelas_nama'     => $kelas ? $kelas->nama : '-',
        'keperluan'      => $keperluan,
        'estimasi_menit' => ($jenis == 'keluar') ? $estimasi : null,
        'jam_keluar'     => date('Y-m-d H:i:s'),
        'token_keluar'   => $token_qr,
        'token_kembali'  => $token_kembali,
        'status'         => ($jenis == 'pulang') ? 'pulang' : 'keluar',
        'jenis_izin'     => $jenis,

        // tambahan
        'id_guru_mapel'  => $id_guru_mapel,
        'id_piket'       => $id_piket,
        'id_walikelas'   => $id_walikelas,
        'ditujukan'      => $ditujukan,
    ];

    // Simpan
    $id = $this->Izin_model->insert_izin($data_insert);

    // Selesai → cetak
    redirect('izin/cetak/' . $id);
}


    // =======================================================================
    // 3. TANDAI SUDAH KEMBALI
    // =======================================================================
    public function kembali($token_kembali)
{
    // Cek izin berdasarkan token kembali
    $izin = $this->Izin_model->get_izin_by_token_kembali($token_kembali);

    if (!$izin) {
        echo "<h3>Token kembali tidak valid.</h3>";
        return;
    }

    // Jika sudah kembali → tampilkan pesan merah
    if ($izin->status == 'kembali') {

        $data['izin'] = $izin;
        $this->load->view('izin/kembali_duplikat', $data);
        return;
    }

    // Jika belum, set sebagai kembali
    $this->Izin_model->set_kembali($izin->id);

    $this->load->view('izin/kembali_sukses');
}


    // =======================================================================
    // 4. CETAK SURAT IZIN
    // =======================================================================
    public function cetak($id)
{
    $izin = $this->Izin_model->get_by_id($id);

    $data['izin'] = $izin;
    $data['guru_mapel'] = $this->Guru_model->get($izin->id_guru_mapel);
    $data['piket'] = $this->Guru_model->get($izin->id_piket);

    if ($izin->jenis_izin == 'pulang') {
        $data['walikelas'] = $this->Guru_model->get($izin->id_walikelas);
        $this->load->view('izin/cetak_pulang', $data);
    } else {
        $this->load->view('izin/cetak_keluar', $data);
    }
}


    // =======================================================================
    // 5. MONITOR ADMIN
    // =======================================================================
    public function index()
{
    $this->load->library('pagination');

    // Hitung total izin
    $total = $this->db->count_all('izin_keluar');

    // Pagination config
    $config['base_url'] = site_url('izin/index');
    $config['total_rows'] = $total;
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;

    // Bootstrap 5 style pagination
    $config['full_tag_open']   = '<nav><ul class="pagination pagination-sm justify-content-center">';
    $config['full_tag_close']  = '</ul></nav>';
    $config['attributes']      = ['class' => 'page-link'];

    $config['first_tag_open']  = '<li class="page-item">';
    $config['first_tag_close'] = '</li>';
    $config['last_tag_open']   = '<li class="page-item">';
    $config['last_tag_close']  = '</li>';

    $config['next_tag_open']   = '<li class="page-item">';
    $config['next_tag_close']  = '</li>';
    $config['prev_tag_open']   = '<li class="page-item">';
    $config['prev_tag_close']  = '</li>';

    $config['cur_tag_open']    = '<li class="page-item active"><a class="page-link bg-primary text-white" href="#">';
    $config['cur_tag_close']   = '</a></li>';

    $config['num_tag_open']    = '<li class="page-item">';
    $config['num_tag_close']   = '</li>';

    $this->pagination->initialize($config);

    $start = $this->uri->segment(3) ?: 0;

    // Ambil data izin sesuai halaman
    $this->db->order_by('id','DESC');
    $izin = $this->db->get('izin_keluar', $config['per_page'], $start)->result();

    // Send to view
    $data['izin'] = $izin;
    $data['active'] = 'izin';
    $data['pagination'] = $this->pagination->create_links();
    $data['start'] = $start;

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('izin/index', $data);
    $this->load->view('templates/footer');
}
public function scan_process()
{
    // HARUS dari scanner (bukan buka link manual)
    $header = $this->input->get_request_header("X-Scanner");
    if ($header !== "MUTASES") {
        echo "403";
        return;
    }

    // HARUS dari perangkat petugas (cookie)
    if (!isset($_COOKIE['petugas_scan'])) {
        echo "403";
        return;
    }

    $token = $this->input->get("token");

    // Kembalikan URL tujuan sesuai token
    if (strpos($token, "kembali_") === 0) {
        echo base_url("index.php/izin/kembali/" . $token);
    } else {
        echo base_url("index.php/izin/scan/" . $token);
    }
}


}
