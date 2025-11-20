<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Backup extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->dbutil();
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
        if (!isset($_FILES['file_sql'])) {
            $this->session->set_flashdata('error', 'File SQL tidak ditemukan!');
            redirect('backup/restore');
            return;
        }

        $file = $_FILES['file_sql']['tmp_name'];
        $content = file_get_contents($file);

        if ($content) {
            $sqls = explode(";", $content);

            foreach ($sqls as $query) {
                $query = trim($query);
                if ($query != "") {
                    $this->db->query($query);
                }
            }

            $this->session->set_flashdata('success', 'Database berhasil direstore!');
        } else {
            $this->session->set_flashdata('error', 'Gagal membaca file SQL!');
        }

        redirect('backup/restore');
    }
}
