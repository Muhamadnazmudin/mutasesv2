<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Backup extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->dbutil();
        if (!$this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
    }
    }

    // ===========================
    // HALAMAN BACKUP DATABASE
    // ===========================
    public function index()
    {
        $data['title'] = "Backup Database";
        $data['group_setting'] = true;
        $data['active'] = 'backup_db';

        // Jika kamu pakai template header/footer
        $this->load->view('templates/header', $data);
        $this->load->view('backup/index', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/sidebar', $data);
    }

    public function do_backup()
    {
        $prefs = array(
            'format'   => 'zip',
            'filename' => 'backup-db-' . date('Y-m-d_H-i-s') . '.sql'
        );

        $backup = $this->dbutil->backup($prefs);

        $this->load->helper('download');
        force_download('backup-db-' . date('Y-m-d_H-i-s') . '.zip', $backup);
    }

    // ===========================
    // HALAMAN RESTORE DATABASE
    // ===========================
    public function restore()
    {
        $data['title'] = "Restore Database";
        $data['group_setting'] = true;
        $data['active'] = 'restore_db';

        // Template
        $this->load->view('templates/header', $data);
        $this->load->view('backup/restore', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/sidebar', $data);
    }

    public function do_restore()
{
    if (!isset($_FILES['file_sql']) || $_FILES['file_sql']['error'] !== UPLOAD_ERR_OK) {
        $this->session->set_flashdata('error', 'File SQL tidak berhasil diupload.');
        redirect('backup/restore');
        return;
    }

    $tmp = $_FILES['file_sql']['tmp_name'];

    if (!file_exists($tmp) || filesize($tmp) == 0) {
        $this->session->set_flashdata('error', 'Gagal membaca file SQL!');
        redirect('backup/restore');
        return;
    }

    $db = $this->db->conn_id; // mysqli connection

    mysqli_query($db, "SET FOREIGN_KEY_CHECKS = 0");

    $templine = '';
    $lines = file($tmp);

    foreach ($lines as $line) {

        // Lewati baris komentar / kosong
        if (substr($line, 0, 2) == '--' || trim($line) == '') {
            continue;
        }

        // Tambahkan baris SQL
        $templine .= $line;

        // Jika akhir baris ada titik koma → jalankan query
        if (substr(trim($line), -1) == ';') {
            if (!mysqli_query($db, $templine)) {
                // kalau error, lanjut saja
            }
            $templine = '';
        }
    }

    mysqli_query($db, "SET FOREIGN_KEY_CHECKS = 1");

    $this->session->set_flashdata('success', 'Database berhasil direstore!');
    redirect('backup/restore');
}

}