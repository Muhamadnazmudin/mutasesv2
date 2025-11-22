<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kelas_model extends CI_Model {

    private $table = 'kelas';

    // ==========================================================
    // 🔹 Ambil semua kelas + nama wali kelas
    // ==========================================================
    public function get_all($limit = null, $offset = null) {
        $this->db->select('kelas.*, guru.nama AS wali_nama');
        $this->db->join('guru', 'guru.id = kelas.wali_kelas_id', 'left');
        $this->db->order_by('kelas.id', 'DESC');

        if ($limit !== null) {
            return $this->db->get($this->table, $limit, $offset)->result();
        } else {
            return $this->db->get($this->table)->result();
        }
    }

    public function count_all() {
        return $this->db->count_all($this->table);
    }

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    // ==========================================================
    // 🔥 Untuk Form TAMBAH → hanya guru yang BELUM jadi wali kelas
    // ==========================================================
    public function get_guru_list() {
        $sql = "
            SELECT g.*
            FROM guru g
            LEFT JOIN kelas k ON k.wali_kelas_id = g.id
            WHERE k.wali_kelas_id IS NULL
            GROUP BY g.id
            ORDER BY g.nama ASC
        ";
        return $this->db->query($sql)->result();
    }

    // ==========================================================
    // 🔥 Untuk Form EDIT → guru available + wali kelas saat ini
    // ==========================================================
    public function get_guru_list_edit($id_kelas)
    {
        $kelas = $this->get_by_id($id_kelas);
        $wali_now = $kelas ? $kelas->wali_kelas_id : 0;

        $sql = "
            SELECT g.*
            FROM guru g
            LEFT JOIN kelas k ON k.wali_kelas_id = g.id
            WHERE k.wali_kelas_id IS NULL 
               OR g.id = ?
            GROUP BY g.id
            ORDER BY g.nama ASC
        ";

        return $this->db->query($sql, [$wali_now])->result();
    }

    // ==========================================================
    // 🔹 Kenaikan Kelas (Fungsi Lama, tetap dipertahankan)
    // ==========================================================

    // Ambil semua kelas tanpa limit
    public function get_all_simple() {
        return $this->db->order_by('nama', 'ASC')->get($this->table)->result();
    }

    // Ambil kelas berdasarkan pola nama (misal: 'XI', 'XII')
    public function get_by_pattern($pattern) {
        return $this->db->like('nama', $pattern)
                        ->order_by('id', 'ASC')
                        ->get($this->table)
                        ->row();
    }

    // Kelas berikutnya berdasarkan nama kelas sekarang
    public function next_level($id_kelas) {
        $kelas = $this->db->get_where($this->table, ['id' => $id_kelas])->row();
        if (!$kelas) return null;

        $nama = strtoupper($kelas->nama);

        if (strpos($nama, 'XII') !== false) return null;
        elseif (strpos($nama, 'XI') !== false) $next = 'XII';
        elseif (strpos($nama, 'X ') !== false || strpos($nama, 'X-') !== false) $next = 'XI';
        elseif (strpos($nama, 'IX') !== false) $next = 'X';
        elseif (strpos($nama, 'VIII') !== false) $next = 'IX';
        elseif (strpos($nama, 'VII') !== false) $next = 'VIII';
        else return null;

        return $this->db->like('nama', $next)
                        ->order_by('id', 'ASC')
                        ->get($this->table)
                        ->row();
    }

}
