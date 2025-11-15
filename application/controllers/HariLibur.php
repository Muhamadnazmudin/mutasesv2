<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HariLibur extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hari_libur_model');
    }

    public function index()
    {
        $data['judul']  = "Hari Libur Nasional & Sekolah";
        $data['active'] = "hari_libur";
        $data['libur']  = $this->Hari_libur_model->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('hari_libur/index', $data);
        $this->load->view('templates/footer');
    }

    public function tambah()
    {
        $nama  = $this->input->post('nama');
        $start = $this->input->post('start');

        $this->Hari_libur_model->insert([
            'nama'  => $nama,
            'start' => $start
        ]);

        $this->session->set_flashdata('success', "Hari libur berhasil ditambahkan.");
        redirect('HariLibur');
    }

    public function hapus($id)
    {
        $this->Hari_libur_model->delete($id);
        $this->session->set_flashdata('success', "Hari libur berhasil dihapus.");
        redirect('HariLibur');
    }
}
