<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class JadwalAbsensi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('AbsensiJadwal_model', 'jadwal');
        if (!$this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
    }
    }

    public function index() {
        $data['active'] = 'jadwal_absensi';
        $data['jadwal'] = $this->jadwal->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('absensiqr/jadwal', $data);
        $this->load->view('templates/footer');
    }

    public function update() {

        foreach ($_POST['id'] as $i => $id) {
            $dataUpdate = [
                'jam_masuk' => $_POST['jam_masuk'][$i],
                'jam_pulang' => $_POST['jam_pulang'][$i],
            ];

            $this->jadwal->update_jadwal($id, $dataUpdate);
        }

        $this->session->set_flashdata('success', 'Jadwal absensi berhasil diperbarui.');
        redirect('jadwalabsensi');
    }
}
