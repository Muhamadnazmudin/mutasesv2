<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

  public function __construct(){
    parent::__construct();
    $this->load->model(['User_model','Tahun_model']);
  }

  public function login(){
    if($this->input->post()){
      
      $username = $this->input->post('username');
      $password = $this->input->post('password');
      $tahun_id = $this->input->post('tahun_id');

      $user = $this->User_model->get_by_username($username);

      if($user && password_verify($password, $user->password)){

        // === Simpan session standar ===
        $this->session->set_userdata([
          'logged_in' => TRUE,
          'user_id'   => $user->id,
          'username'  => $user->username,
          'nama'      => $user->nama,
          'role_id'   => $user->role_id,
          'guru_id'   => $user->guru_id,   // penting untuk walikelas
          'role_name' => $this->User_model->role_name($user->role_id),
          'tahun_id'  => $tahun_id
        ]);

        // ======================================================
        // 🔵 REDIRECT KHUSUS WALIKELAS (role_id = 3)
        // ======================================================
        if ($user->role_id == 3) {

            // Cek kelas yang diasuh berdasarkan guru_id
            $kelas = $this->db->get_where('kelas', [
                'wali_kelas_id' => $user->guru_id
            ])->row();

            if ($kelas) {
                $this->session->set_userdata([
                    'kelas_id'   => $kelas->id,
                    'kelas_nama' => $kelas->nama
                ]);
            }

            // redirect ke dashboard walikelas
            redirect('walikelas');
        }

        // ======================================================
        // 🔴 ADMIN → ke dashboard biasa
        // ======================================================
        if ($user->role_id == 1) {
            redirect('dashboard');
        }

        // ======================================================
        // 🟠 KESISWAAN → tetap ke dashboard
        // ======================================================
        if ($user->role_id == 2) {
            redirect('dashboard');
        }

      } else {
        $this->session->set_flashdata('error','Username atau password salah.');
        redirect('auth/login');
      }

    } else {
      $data['title'] = 'Login';
      $data['tahun'] = $this->Tahun_model->get_all();
      $this->load->view('auth/login', $data);
    }
  }


  public function logout(){
    $this->session->sess_destroy();
    redirect('auth/login');
  }
}
