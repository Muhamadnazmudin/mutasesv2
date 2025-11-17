<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SiswaDashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Siswa_model');
        $this->load->database();
    }

    private function cek_login()
    {
        if (!$this->session->userdata('siswa_login')) {
            redirect('SiswaAuth');
        }
    }

    private function getSiswa()
    {
        $siswa_id = $this->session->userdata('siswa_id');

        if (!$siswa_id) {
            return null;
        }

        return $this->db
            ->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran')
            ->join('kelas','kelas.id = siswa.id_kelas','left')
            ->join('tahun_ajaran','tahun_ajaran.id = siswa.tahun_id','left')
            ->where('siswa.id', $siswa_id)
            ->get('siswa')->row();
    }

    public function index()
    {
        $this->cek_login();

        $data['siswa'] = $this->getSiswa();
        if (!$data['siswa']) { redirect('SiswaAuth/logout'); }

        $data['active'] = 'dashboard';

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/dashboard', $data);
        $this->load->view('siswa/layout/footer');
    }

    public function biodata()
    {
        $this->cek_login();

        $data['siswa'] = $this->getSiswa();
        if (!$data['siswa']) { redirect('SiswaAuth/logout'); }

        $data['active'] = 'biodata';

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/biodata', $data);
        $this->load->view('siswa/layout/footer');
    }

    public function cetak()
    {
        $this->cek_login();

        $data['siswa'] = $this->getSiswa();
        if (!$data['siswa']) { redirect('SiswaAuth/logout'); }

        $html = $this->load->view('siswa/cetak', $data, TRUE);

        $this->load->library('pdf');
        $pdf = new Tcpdf('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->writeHTML($html);
        $pdf->Output('Biodata_'.$data['siswa']->nama.'.pdf', 'I');
    }

    public function mutasi()
    {
        $this->cek_login();

        $siswa = $this->getSiswa();
        if (!$siswa) { redirect('SiswaAuth/logout'); }

        $data['mutasi'] = $this->db
            ->where('siswa_id', $siswa->id)
            ->get('mutasi')
            ->result();

        $data['siswa']   = $siswa;
        $data['active']  = 'mutasi';

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/mutasi', $data);
        $this->load->view('siswa/layout/footer');
    }

    public function password()
    {
        $this->cek_login();

        $data['siswa'] = $this->getSiswa();
        if (!$data['siswa']) { redirect('SiswaAuth/logout'); }

        $data['active'] = 'password';

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/password', $data);
        $this->load->view('siswa/layout/footer');
    }

    public function save_password()
    {
        $this->cek_login();

        $siswa = $this->getSiswa();
        if (!$siswa) { redirect('SiswaAuth/logout'); }

        $old = $this->input->post('old');
        $new = $this->input->post('new');

        $password_now = $siswa->password ?: $siswa->nisn;

        if ($old != $password_now) {
            $this->session->set_flashdata('error', "Password lama salah!");
            redirect('SiswaDashboard/password');
        }

        $this->db->where('id', $siswa->id)
                 ->update('siswa', ['password' => $new]);

        $this->session->set_flashdata('success', "Password berhasil diubah!");
        redirect('SiswaDashboard/password');
    }

    public function kartu()
    {
        $this->cek_login();

        $data['siswa'] = $this->getSiswa();
        if (!$data['siswa']) { redirect('SiswaAuth/logout'); }

        $siswa = $data['siswa'];
        $data['active'] = 'kartu';

        require_once APPPATH . 'libraries/phpqrcode/qrlib.php';

        $qr_folder = FCPATH . 'assets/qrcodes/';
        if (!is_dir($qr_folder)) mkdir($qr_folder, 0777, true);

        if (!$siswa->token_qr) {
            $token = uniqid('qr_');
            $this->db->where('id', $siswa->id)->update('siswa', ['token_qr' => $token]);
            $siswa->token_qr = $token;
        }

        $qr_file = $qr_folder . $siswa->token_qr . '.png';

        if (!file_exists($qr_file)) {
            QRcode::png($siswa->token_qr, $qr_file, QR_ECLEVEL_M, 6, 1);
        }

        $data['qr_file'] = base_url('assets/qrcodes/'.$siswa->token_qr.'.png');

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/kartu', $data);
        $this->load->view('siswa/layout/footer');
    }
    public function idcard()
{
    $this->cek_login();

    $siswa = $this->getSiswa();
    if (!$siswa) { redirect('SiswaAuth/logout'); }

    $data['siswa'] = $siswa;
    $data['active'] = 'idcard';

    // Load library ID Card
    $this->load->library('Idcard_lib');

    // Generate PNG (binary string)
    $imgBinary = $this->idcard_lib->generate($siswa->id);

    // Convert ke Base64 untuk preview
    $data['idcard_base64'] = base64_encode($imgBinary);

    // Load view
    $this->load->view('siswa/layout/header', $data);
    $this->load->view('siswa/layout/sidebar', $data);
    $this->load->view('siswa/idcard_preview', $data);
    $this->load->view('siswa/layout/footer');
}

}

?>
