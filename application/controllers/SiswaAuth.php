<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SiswaAuth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(['session']);
        $this->load->helper(['url', 'form']);
        $this->load->database();
    }

    public function index() {
        $this->load->view('siswa/login');
        // $this->load->view('siswa/maintenance');
    }

    public function cek_login() {
        $nisn = $this->input->post('nisn', TRUE);
        $password = $this->input->post('password', TRUE);

        // Cek nisn
        $siswa = $this->db->get_where('siswa', [
            'nisn' => $nisn
        ])->row();

        if (!$siswa) {
            $this->session->set_flashdata('error', 'NISN tidak ditemukan!');
            redirect('SiswaAuth');
        }

        // Jika password kosong → anggap default = NISN
        $stored = $siswa->password ?: $siswa->nisn;

        if ($password != $stored) {
            $this->session->set_flashdata('error', 'Password salah!');
            redirect('SiswaAuth');
        }

        // Set session siswa
        $this->session->set_userdata([
            'siswa_login' => TRUE,
            'siswa_id' => $siswa->id,
            'siswa_nama' => $siswa->nama,
            'siswa_nisn' => $siswa->nisn
        ]);

        redirect('SiswaDashboard');
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('SiswaAuth');
    }
}
