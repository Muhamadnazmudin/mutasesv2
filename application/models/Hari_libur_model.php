<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hari_libur_model extends CI_Model {

    public function get_all() {
        return $this->db->order_by('start','ASC')->get('hari_libur')->result();
    }

    public function insert($data) {
        return $this->db->insert('hari_libur', $data);
    }

    public function delete($id) {
        return $this->db->delete('hari_libur', ['id' => $id]);
    }

    public function get_all_dates() {
        $q = $this->db->query("SELECT start FROM hari_libur")->result();
        $arr = [];

        foreach ($q as $r) {
            $arr[] = $r->start;
        }
        return $arr;
    }
}
