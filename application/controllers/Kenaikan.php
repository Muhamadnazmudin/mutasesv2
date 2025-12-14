<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kenaikan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Siswa_model');
        $this->load->model('Kelas_model');
        $this->load->model('Tahun_model');
        $this->load->database();

        if (!$this->session->userdata('username')) {
            redirect('auth');
        }

        // Tahun ajaran aktif
        $this->tahun_aktif = $this->Tahun_model->get_aktif()->id;
        if (!$this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
    }
    }

    public function index() {
        $data['active'] = 'kenaikan';
        $data['tahun'] = $this->Tahun_model->get_aktif();
        $data['kelas'] = $this->Kelas_model->get_all();

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


    /* =======================================================
       🔹 FUNGSI UTAMA — SIMPAN RIWAYAT & TAMBAHKAN TAHUN BARU
       ======================================================= */
    private function proses_siswa_tahun($id_siswa, $kelas_lama, $kelas_baru, $tahun_baru)
{
    // normalize nilai kelas_baru: treat empty string as NULL
    $kelas_baru_fix = ($kelas_baru === '' || $kelas_baru === null) ? null : $kelas_baru;

    // 1) Simpan riwayat tahun lama (kelas_lama bisa NULL)
    $this->db->insert('siswa_history', [
        'siswa_id' => $id_siswa,
        'kelas_id' => $kelas_lama,
        'tahun_id' => $this->tahun_aktif,
        'status'   => 'aktif'
    ]);

    // 2) Hanya tambah entri siswa_tahun JIKA ada kelas tujuan
    if ($kelas_baru_fix !== null) {
        $this->db->insert('siswa_tahun', [
            'siswa_id' => $id_siswa,
            'kelas_id' => $kelas_baru_fix,
            'tahun_id' => $tahun_baru,
            'status'   => 'aktif'
        ]);
    }

    // 3) Update tabel siswa (id_kelas boleh NULL)
    $this->db->where('id', $id_siswa)->update('siswa', [
        'id_kelas' => $kelas_baru_fix,
        'tahun_id' => $tahun_baru
    ]);
}



    /* =======================================================
       🔹 NAIKKAN SATU SISWA MANUAL
       ======================================================= */
    public function naik_manual($id_siswa)
    {
        $kelas_baru = $this->input->post('kelas_tujuan');
        $tahun_baru = $this->Tahun_model->get_aktif()->id;

        $siswa = $this->Siswa_model->get_by_id($id_siswa);
        $kelas_lama = $siswa->id_kelas;

        $this->proses_siswa_tahun($id_siswa, $kelas_lama, $kelas_baru, $tahun_baru);

        $this->session->set_flashdata('success', 'Siswa berhasil dinaikkan.');
        redirect('kenaikan');
    }


    /* =======================================================
       🔹 LULUSKAN SISWA MANUAL
       ======================================================= */
    public function luluskan($id_siswa)
    {
        $siswa = $this->Siswa_model->get_by_id($id_siswa);

        // Simpan riwayat
        $this->db->insert('siswa_history', [
            'siswa_id' => $id_siswa,
            'kelas_id' => $siswa->id_kelas,
            'tahun_id' => $this->tahun_aktif,
            'status'   => 'lulus'
        ]);

        // Update siswa
        $this->Siswa_model->update($id_siswa, ['status' => 'lulus']);

        $this->session->set_flashdata('success', 'Siswa berhasil diluluskan.');
        redirect('kenaikan');
    }


    /* =======================================================
       🔹 KENAIKAN MASSAL
       ======================================================= */
    public function simpan_massal()
    {
        $post = $this->input->post();
        $tahun_baru = $this->Tahun_model->get_aktif()->id;

        if (!empty($post['siswa_id'])) {

            foreach ($post['siswa_id'] as $i => $id_siswa) {

                $kelas_baru = $post['kelas_tujuan'][$i];

                $siswa = $this->Siswa_model->get_by_id($id_siswa);
                $kelas_lama = $siswa->id_kelas;

                if ($kelas_baru == 'lulus') {
                    // simpan riwayat
                    $this->db->insert('siswa_history', [
                        'siswa_id' => $id_siswa,
                        'kelas_id' => $kelas_lama,
                        'tahun_id' => $this->tahun_aktif,
                        'status'   => 'lulus'
                    ]);

                    $this->Siswa_model->update($id_siswa, ['status' => 'lulus']);
                } else {
                    $this->proses_siswa_tahun($id_siswa, $kelas_lama, $kelas_baru, $tahun_baru);
                }
            }

            $this->session->set_flashdata('success', 'Kenaikan massal berhasil.');
        }

        redirect('kenaikan?kelas_id=' . $this->input->post('kelas_id'));
    }


    /* =======================================================
       🔹 KENAIKAN OTOMATIS (XI → XII)
       ======================================================= */
    public function naik_otomatis()
    {
        $tahun_baru = $this->tahun_aktif;

        $siswa_xi = $this->Siswa_model->get_by_kelas_pattern('XI');
        $kelas_xii = $this->Kelas_model->get_by_pattern('XII');

        foreach ($siswa_xi as $s) {
            $this->proses_siswa_tahun($s->id, $s->id_kelas, $kelas_xii->id, $tahun_baru);
        }

        $this->session->set_flashdata('success', 'Kenaikan otomatis berhasil!');
        redirect('kenaikan');
    }

}
