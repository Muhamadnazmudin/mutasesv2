<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Siswa_model extends CI_Model {

  private $table = 'siswa';

  public function get_all($limit, $offset) {
    $this->db->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left');
    $this->db->order_by('siswa.id', 'DESC');
    return $this->db->get($this->table, $limit, $offset)->result();
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

  public function get_kelas_list() {
    return $this->db->get('kelas')->result();
  }

  public function get_tahun_list() {
    return $this->db->order_by('id', 'DESC')->get('tahun_ajaran')->result();
  }
    // ==========================================================
  // 🔹 Tambahan untuk fitur Kenaikan Kelas
  // ==========================================================

  // Ambil semua siswa aktif berdasarkan kelas
  public function get_by_kelas($kelas_id) {
    $this->db->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left');
    $this->db->where('siswa.id_kelas', $kelas_id);
    $this->db->where('siswa.status', 'aktif');
    $this->db->order_by('siswa.nama', 'ASC');
    return $this->db->get($this->table)->result();
  }

  // Ambil semua siswa aktif berdasarkan pola nama kelas (misal: 'XI')
  public function get_by_kelas_pattern($pattern) {
    $this->db->select('siswa.*, kelas.nama AS nama_kelas');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->like('kelas.nama', $pattern);
    $this->db->where('siswa.status', 'aktif');
    return $this->db->get($this->table)->result();
  }

  // Hitung jumlah siswa aktif di kelas tertentu
  public function count_by_kelas($kelas_id) {
    $this->db->where('id_kelas', $kelas_id);
    $this->db->where('status', 'aktif');
    return $this->db->count_all_results($this->table);
  }

  // Ambil semua siswa aktif untuk tahun ajaran tertentu
  public function get_by_tahun($tahun_id) {
    $this->db->select('siswa.*, kelas.nama AS nama_kelas');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->where('siswa.tahun_id', $tahun_id);
    $this->db->where('siswa.status', 'aktif');
    return $this->db->get($this->table)->result();
  }
  // ==========================================================
  // 🔹 Tambahan untuk filter siswa berdasarkan status
  // ==========================================================
  public function get_by_status($status = ['aktif']) {
    $this->db->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left');

    if (is_array($status)) {
      $this->db->where_in('siswa.status', $status);
    } else {
      $this->db->where('siswa.status', $status);
    }

    $this->db->order_by('siswa.nama', 'ASC');
    return $this->db->get('siswa')->result();
  }

}
