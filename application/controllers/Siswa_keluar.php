<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Siswa_keluar extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Siswa_model');
    if (!$this->session->userdata('username')) redirect('auth');
  }

  public function index() {
    $data['active'] = 'siswa_keluar';
    $data['siswa'] = $this->Siswa_model->get_by_status(['mutasi_keluar', 'keluar']);
    $this->load->view('templates/header');
    $this->load->view('templates/sidebar', $data);
    $this->load->view('siswa/keluar', $data);
    $this->load->view('templates/footer');
  }
}
