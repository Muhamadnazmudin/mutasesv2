<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    public function __construct(){
        parent::__construct();

        // ===== CEK DATABASE =====
        if (!$this->db->table_exists('users')) {
            if (!file_exists(FCPATH . 'install/lock.install')) {

                if ($this->router->class != 'install') {
                    redirect('install');
                }
            }
        }

        // ===== CEK LOGIN =====
        if ($this->router->class != 'install') {
            is_logged_in();
        }

        // ===== NO CACHE =====
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        $this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
        $this->output->set_header("Pragma: no-cache");
        $this->output->set_header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    }
}

class Admin_Controller extends MY_Controller {
    public function __construct(){
        parent::__construct();
        only_admin();
    }
}
