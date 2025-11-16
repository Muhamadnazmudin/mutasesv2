<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Izin extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Izin_model');
        require_once APPPATH . 'libraries/phpqrcode/qrlib.php';
        $this->load->database();
    }

    // =======================================================================
    // 1. SCAN KARTU SISWA
    // =======================================================================
    public function scan($token = null)
{
    // Kalau scanner mengirim URL panjang → ambil token terakhir
    if ($token !== null && strpos($token, 'http') !== false) {
        $parts = explode('/', $token);
        $token = end($parts);
    }

    // Jika ini token kembali (kembali_xxxxxx)
    if ($token !== null && strpos($token, 'kembali_') === 0) {
        redirect('izin/kembali/' . $token);
        return;
    }

    // Jika belum ada token → tampilkan scanner
    if ($token === null || $token == "") {
        $this->load->view('izin/scan');
        return;
    }

    // TOKEN MASUK (scan kartu siswa)
    $siswa = $this->db->get_where('siswa', ['token_qr' => $token])->row();

    if (!$siswa) {
        echo "<h3>QR Code tidak valid!</h3>";
        return;
    }

    $data['siswa'] = $siswa;
    $data['token_qr'] = $token;

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

    $kelas = $this->db->get_where('kelas', ['id' => $siswa->id_kelas])->row();

    $jenis = $this->input->post('jenis'); // keluar / pulang

    // Token kembali hanya untuk izin keluar
    $token_kembali = ($jenis == 'keluar') ? uniqid('kembali_') : null;

    $data_insert = [
        'siswa_id'      => $siswa->id,
        'nis'           => $siswa->nis,
        'nama'          => $siswa->nama,
        'kelas_id'      => $siswa->id_kelas,
        'kelas_nama'    => $kelas ? $kelas->nama : '-',
        'keperluan'     => $this->input->post('keperluan'),
        'estimasi_menit'=> $jenis == 'keluar' ? $this->input->post('estimasi') : null,
        'jam_keluar'    => date('Y-m-d H:i:s'),
        'token_keluar'  => $token_qr,
        'token_kembali' => $token_kembali,
        'status'        => ($jenis == 'pulang') ? 'pulang' : 'keluar',
        'jenis_izin'    => $jenis
    ];

    $id = $this->Izin_model->insert_izin($data_insert);

    // Jika pulang → tidak ada QR kembali
    if ($jenis == 'pulang') {
        redirect('izin/cetak/' . $id);
        return;
    }

    // Izin keluar → tetap cetak QR kembali
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
        $data['izin'] = $this->Izin_model->get_by_id($id);
        $this->load->view('izin/cetak', $data);
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


}
