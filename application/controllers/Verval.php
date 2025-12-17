<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Verval extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        // WAJIB LOGIN
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
        }

        // KHUSUS ADMIN
        $role = $this->session->userdata('role_id'); // ← SAMAKAN DENGAN SISTEM
        if ($role != 1) { // 1 = admin
            show_error('Akses ditolak', 403);
        }
    }

    public function siswa()
    {
        $data['active'] = 'verval_siswa';

        $this->load->view('templates/header', $data);
        $this->load->view('verval/verval_siswa', $data);
        $this->load->view('templates/footer');
    }

    public function laporan()
    {
        $data['active'] = 'laporan_verval';

        $this->load->view('templates/header', $data);
        $this->load->view('verval/laporan_verval', $data);
        $this->load->view('templates/footer');
    }
}

