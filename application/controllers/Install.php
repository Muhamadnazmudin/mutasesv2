<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Install extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index()
    {
        // Jika sudah terinstall, jangan bisa diakses lagi
        if (file_exists(FCPATH . 'install/lock.install')) {
            redirect('/');
            exit;
        }

        $this->load->view('install/index');
    }

    public function run()
    {
        // Cek apakah sudah pernah install
        if (file_exists(FCPATH . 'install/lock.install')) {
            show_error("Aplikasi sudah diinstall.");
        }

        $sql_file = FCPATH . 'install/database.sql';

        if (!file_exists($sql_file)) {
            show_error("File database.sql tidak ditemukan di folder /install/");
        }

        $sql_content = file_get_contents($sql_file);

        // Pisahkan per query dengan ';'
        $queries = array_filter(array_map('trim', explode(';', $sql_content)));

        foreach ($queries as $query) {
            if ($query != '') {
                $this->db->query($query);
            }
        }

        // Buat lock file
        file_put_contents(FCPATH . 'install/lock.install', "installed");

        // Tampilkan halaman sukses
        $this->load->view('install/success');
    }
}
