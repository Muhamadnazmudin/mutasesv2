<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Siswa_lulus extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Siswa_model');
    if (!$this->session->userdata('username')) redirect('auth');
  }

  public function index() {
    $data['active'] = 'siswa_lulus';
    $data['siswa'] = $this->Siswa_model->get_by_status(['lulus']);
    $this->load->view('templates/header');
    $this->load->view('templates/sidebar', $data);
    $this->load->view('siswa/lulus', $data);
    $this->load->view('templates/footer');
  }
}
