<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->database();

        // Ambil tahun ajaran dari session
        $session_tahun = $this->session->userdata('tahun_id');

        if ($session_tahun) {
            $this->tahun_id = $session_tahun;
        } else {
            // Untuk publik (tanpa login) ambil tahun aktif
            $t = $this->db->get_where('tahun_ajaran', ['aktif' => 1])->row();
            $this->tahun_id = $t ? $t->id : null;
        }
    }


    // ==========================================================
    //  DASHBOARD
    // ==========================================================
    public function index() {
        $data['title'] = 'Dashboard';

        // Jumlah kelas per tingkat
        $data['rombel']  = $this->get_kelas_by_tingkat();

        // siswa_tahun → status = aktif
        $data['aktif']   = $this->get_siswa_aktif_by_tingkat();
        // mutasi siswa masuk
        $data['masuk']   = $this->get_siswa_masuk_by_tingkat();
        // mutasi → jenis: keluar
        $data['keluar']  = $this->get_siswa_keluar_by_tingkat();

        // Lulus
        $q = $this->db
            ->select('tahun_ajaran.tahun, COUNT(siswa.id) AS jumlah')
            ->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left')
            ->where('siswa.status', 'lulus')
            ->where('siswa.tahun_id', $this->tahun_id)
            ->group_by('tahun_ajaran.tahun')
            ->get('siswa');

        $data['lulus'] = $q ? $q->result() : [];

        // siswa_tahun → tabel publik
        $data['per_rombel'] = $this->get_siswa_per_rombel();

        // Jika login, tampilkan versi admin
        if ($this->session->userdata('logged_in')) {
            $data['active'] = 'dashboard';
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('dashboard/index', $data);
            $this->load->view('templates/footer');
        } else {
            $this->load->view('dashboard/public', $data);
        }
    }


    // ==========================================================
    // 🔹 HITUNG KELAS PER TINGKAT
    // ==========================================================
    private function get_kelas_by_tingkat() {
        $out = ['x'=>0,'xi'=>0,'xii'=>0,'total'=>0];

        $regex = [
            'x'   => "^X( |$|[^I])",
            'xi'  => "^XI( |$)",
            'xii' => "^XII"
        ];

        foreach ($regex as $k => $r) {
            $this->db->where("nama REGEXP '$r'");
            $out[$k] = $this->db->count_all_results('kelas');
        }

        $out['total'] = $out['x'] + $out['xi'] + $out['xii'];
        return $out;
    }



    // ==========================================================
    // 🔹 SISWA AKTIF PER TINGKAT (siswa_tahun)
    // ==========================================================
    private function get_siswa_aktif_by_tingkat()
{
    $out = ['x'=>0,'xi'=>0,'xii'=>0,'total'=>0];

    $regex = [
        'x'   => "^X( |$)",
        'xi'  => "^XI( |$)",
        'xii' => "^XII( |$)"
    ];

    foreach ($regex as $k => $r) {

        $this->db->select('COUNT(siswa.id) AS jumlah');
        $this->db->from('siswa');
        $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
        $this->db->where('siswa.status', 'aktif');
        $this->db->where("kelas.nama REGEXP '$r'");

        $q = $this->db->get()->row();
        $out[$k] = $q ? (int)$q->jumlah : 0;
    }

    $out['total'] = $out['x'] + $out['xi'] + $out['xii'];
    return $out;
}


    // ==========================================================
    // 🔹 SISWA KELUAR PER TINGKAT (mutasi)
    // ==========================================================
    private function get_siswa_keluar_by_tingkat()
{
    $out = ['x'=>0,'xi'=>0,'xii'=>0,'total'=>0];

    $regex = [
        'x'   => "^X( |$)",
        'xi'  => "^XI( |$)",
        'xii' => "^XII( |$)"
    ];

    foreach ($regex as $k => $r) {

        $this->db->select('COUNT(siswa.id) AS jumlah');
        $this->db->from('siswa');
        $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');

        // STATUS KELUAR
        $this->db->where_in('siswa.status', [
            'keluar',
            'mutasi_keluar',
            'meninggal'
        ]);

        $this->db->where("kelas.nama REGEXP '$r'");

        $row = $this->db->get()->row();
        $out[$k] = $row ? (int)$row->jumlah : 0;
    }

    $out['total'] = $out['x'] + $out['xi'] + $out['xii'];
    return $out;
}


    // ==========================================================
    // 🔹 SISWA PER ROMBEL (PUBLIK) — siswa_tahun
    // ==========================================================
    private function get_siswa_per_rombel() {
        $tahun = $this->tahun_id;

        $q = $this->db
            ->select("
                k.nama AS nama_kelas,
                SUM(CASE WHEN s.jk = 'L' THEN 1 ELSE 0 END) AS laki,
                SUM(CASE WHEN s.jk = 'P' THEN 1 ELSE 0 END) AS perempuan,
                COUNT(st.id) AS total
            ")
            ->from("siswa_tahun st")
            ->join("siswa s", "s.id = st.siswa_id", "left")
            ->join("kelas k", "k.id = st.kelas_id", "left")
            ->where("st.tahun_id", $tahun)
            ->where("st.status", "aktif")
            ->group_by("k.nama")
            ->order_by("k.nama", "ASC")
            ->get();

        return $q->result();
    }


    // ==========================================================
    // 🔹 DOWNLOAD PER KELAS (pakai siswa, tetap aman)
    // ==========================================================
    public function download_excel($kelas_id = null) {
        if (!$kelas_id) show_error('Kelas tidak ditemukan.');

        $kelas = $this->db->get_where('kelas', ['id' => $kelas_id])->row();
        if (!$kelas) show_error('Data kelas tidak valid.');

        $this->db->set('download_count', 'download_count + 1', FALSE)
                 ->where('id', $kelas_id)
                 ->update('kelas');

        $siswa = $this->db
            ->where('id_kelas', $kelas_id)
            ->where('status', 'aktif')
            ->order_by('nama', 'ASC')
            ->get('siswa')
            ->result();

        if (empty($siswa)) {
            show_error('Tidak ada data siswa aktif di kelas ini.');
        }

        $this->load->library('PHPExcel_lib');
        $this->phpexcel_lib->export_siswa_per_kelas($siswa, $kelas->nama);
    }


    // ==========================================================
    // 🔹 HALAMAN PUBLIK: MUTASI
    // ==========================================================
    public function mutasi() {
        $this->load->model('Laporan_model');
        $this->load->library('pagination');

        $tahun  = date('Y');
        $kelas  = $this->input->get('kelas');
        $jenis  = $this->input->get('jenis');
        $search = $this->input->get('search');

        $config['base_url'] = site_url('dashboard/mutasi');
        $config['per_page'] = 10;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;

        $page   = ($this->input->get('page')) ? (int)$this->input->get('page') : 0;
        $offset = $page;

        $all_mutasi = $this->Laporan_model->get_laporan($tahun, $kelas, $jenis, $search);
        $config['total_rows'] = count($all_mutasi);

        $this->pagination->initialize($config);
        $data['mutasi'] = array_slice($all_mutasi, $offset, $config['per_page']);

        $data['judul']    = 'Data Siswa Mutasi';
        $data['tahun']    = $tahun;
        $data['kelas_list'] = $this->Laporan_model->get_kelas();
        $data['pagination'] = $this->pagination->create_links();

        $this->load->view('dashboard/mutasi_public', $data);
    }
// ==========================================================
// 🔹 SISWA MASUK PER TINGKAT (mutasi masuk)
// ==========================================================
private function get_siswa_masuk_by_tingkat()
{
    $out = ['x'=>0,'xi'=>0,'xii'=>0,'total'=>0];

    // regex tingkat
    $regex = [
        'x'   => "^X( |$)",
        'xi'  => "^XI( |$)",
        'xii' => "^XII( |$)"
    ];

    foreach ($regex as $k => $r) {

        $this->db->select('COUNT(m.id) AS jumlah');
        $this->db->from('mutasi m');
        $this->db->join('siswa s', 's.id = m.siswa_id', 'left');
        $this->db->join('siswa_tahun st', 'st.siswa_id = s.id AND st.tahun_id = m.tahun_id', 'left');
        $this->db->join('kelas k', 'k.id = st.kelas_id', 'left');

        $this->db->where('m.jenis', 'masuk');          // HANYA MUTASI MASUK
        $this->db->where('m.tahun_id', $this->tahun_id); // TAHUN AKTIF
        $this->db->where("k.nama REGEXP '$r'");        // FILTER TINGKAT

        $row = $this->db->get()->row();
        $out[$k] = $row ? (int)$row->jumlah : 0;
    }

    $out['total'] = $out['x'] + $out['xi'] + $out['xii'];

    return $out;
}


}
