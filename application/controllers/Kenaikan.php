<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kenaikan extends CI_Controller {

  public function __construct(){
    parent::__construct();
    $this->load->model('Siswa_model');
    $this->load->model('Kelas_model');
    $this->load->model('Tahun_model');

    if(!$this->session->userdata('username')){
      redirect('auth');
    }
  }

  public function index()
{
    $data['active'] = 'kenaikan';
    $data['tahun'] = $this->Tahun_model->get_aktif();
    $data['kelas'] = $this->Kelas_model->get_all(); // sudah bisa tanpa argumen
    $kelas_id = $this->input->get('kelas_id');

    if ($kelas_id) {
        $data['siswa'] = $this->Siswa_model->get_by_kelas($kelas_id);
        $data['kelas_sekarang'] = $this->Kelas_model->get_by_id($kelas_id);
    } else {
        $data['siswa'] = [];
        $data['kelas_sekarang'] = null;
    }

    $this->load->view('templates/header');
    $this->load->view('templates/sidebar', $data);
    $this->load->view('kenaikan/index', $data);
    $this->load->view('templates/footer');
}

  // 🔹 Naikkan satu siswa manual
  public function naik_manual($id_siswa){
    $kelas_tujuan = $this->input->post('kelas_tujuan');
    $tahun_baru = $this->Tahun_model->get_aktif();

    if ($kelas_tujuan && $tahun_baru) {
      $this->Siswa_model->update($id_siswa, [
        'id_kelas' => $kelas_tujuan,
        'tahun_id' => $tahun_baru->id
      ]);
      $this->session->set_flashdata('success', 'Siswa berhasil dinaikkan ke kelas baru.');
    }

    redirect('kenaikan');
  }

  // 🔹 Luluskan siswa manual
  public function luluskan($id_siswa){
    $this->Siswa_model->update($id_siswa, [
      'status' => 'lulus'
    ]);
    $this->session->set_flashdata('success', 'Siswa berhasil diluluskan.');
    redirect('kenaikan');
  }

  // 🔹 Naik otomatis XI ke XII
  public function naik_otomatis(){
    $tahun_aktif = $this->Tahun_model->get_aktif();
    if(!$tahun_aktif){
      show_error('Tahun aktif belum diatur.');
    }

    $siswa_xi = $this->Siswa_model->get_by_kelas_pattern('XI');
    $kelas_xii = $this->Kelas_model->get_by_pattern('XII');
    if (!$kelas_xii) {
      show_error('Kelas XII belum dibuat.');
    }

    foreach ($siswa_xi as $s) {
      $this->Siswa_model->update($s->id, ['id_kelas' => $kelas_xii->id]);
    }

    $this->session->set_flashdata('success', 'Semua siswa kelas XI berhasil dinaikkan ke XII.');
    redirect('kenaikan');
  }
  public function simpan_massal()
{
    $post = $this->input->post();

    if (!empty($post['siswa_id'])) {
        $total = count($post['siswa_id']);
        for ($i = 0; $i < $total; $i++) {
            $id_siswa = $post['siswa_id'][$i];
            $kelas_tujuan = $post['kelas_tujuan'][$i];

            if ($kelas_tujuan == 'lulus') {
                $this->Siswa_model->update($id_siswa, ['status' => 'lulus']);
            } else {
                $this->Siswa_model->update($id_siswa, ['id_kelas' => $kelas_tujuan]);
            }
        }

        $this->session->set_flashdata('success', 'Kenaikan kelas massal berhasil disimpan!');
    }

    redirect('kenaikan?kelas_id=' . $this->input->post('kelas_id'));
}

}
