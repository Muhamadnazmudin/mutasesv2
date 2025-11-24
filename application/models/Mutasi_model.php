<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mutasi_model extends CI_Model {

    // ================================================================
    // COUNT (UNTUK PAGINATION)
    // ================================================================
    public function count_all()
    {
        $this->db->where('status_mutasi', 'aktif');
        return $this->db->count_all_results('mutasi');
    }

    // ================================================================
    // GET ALL MUTASI + JOIN SISWA, KELAS & TAHUN
    // ================================================================
    public function get_all($limit, $offset)
    {
        $this->db->select('
    mutasi.*,
    siswa.nama AS nama_siswa,
    siswa.nis,
    ktujuan.nama AS tujuan_kelas_nama,
    tahun_ajaran.tahun AS tahun_ajaran
');

        $this->db->from('mutasi');
        $this->db->join('siswa', 'siswa.id = mutasi.siswa_id', 'left');
        $this->db->join('kelas ktujuan', 'ktujuan.id = mutasi.tujuan_kelas_id', 'left');
        $this->db->join('tahun_ajaran', 'tahun_ajaran.id = mutasi.tahun_id', 'left');
        $this->db->where('mutasi.status_mutasi', 'aktif');
        $this->db->order_by('mutasi.id', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    // ================================================================
    // GET SISWA AKTIF (default)
    // ================================================================
    public function get_siswa_aktif()
    {
        return $this->db->where('status', 'aktif')->get('siswa')->result();
    }

    // ================================================================
    // GET KELAS LIST
    // ================================================================
    public function get_kelas_list()
    {
        return $this->db->order_by('nama', 'ASC')->get('kelas')->result();
    }

    // ================================================================
    // GET TAHUN LIST
    // ================================================================
    public function get_tahun_list()
    {
        return $this->db->order_by('id', 'DESC')->get('tahun_ajaran')->result();
    }

    // ================================================================
    // RESOLUSI kelas_asal (agar tidak NULL)
    // ================================================================
    protected function resolve_kelas_asal($siswa_id, $tahun_id = null, $provided_kelas_asal = null)
    {
        // 1) Jika form memberikan kelas_asal, langsung pakai
        if (!empty($provided_kelas_asal)) {
            return (int)$provided_kelas_asal;
        }

        // 2) Cari siswa_tahun berdasarkan tahun aktif dashboard (bukan POST!)
        if (!$tahun_id) {
            $tahun_id = $this->session->userdata('tahun_id');
        }

        if ($tahun_id) {
            $st = $this->db->get_where('siswa_tahun', [
                'siswa_id' => $siswa_id,
                'tahun_id' => $tahun_id,
                'status'   => 'aktif'
            ])->row();

            if ($st && $st->kelas_id) return (int)$st->kelas_id;
        }

        // 3) Cari siswa_tahun terbaru
        $st2 = $this->db->order_by('tahun_id', 'DESC')
                        ->where('siswa_id', $siswa_id)
                        ->get('siswa_tahun')->row();
        if ($st2 && $st2->kelas_id) return (int)$st2->kelas_id;

        // 4) Fallback ke siswa.id_kelas
        $siswa = $this->db->select('id_kelas')
                          ->get_where('siswa', ['id' => $siswa_id])->row();
        if ($siswa && $siswa->id_kelas) return (int)$siswa->id_kelas;

        // 5) Tidak boleh NULL
        return 0;
    }

    // ================================================================
    // MUTASI KELUAR
    // ================================================================
    public function mutasi_keluar($data)
    {
        $this->db->trans_begin();

        // Insert data mutasi
        $this->db->insert('mutasi', $data);

        // Tentukan kelas asal aman
        $kelas_asal_id = $this->resolve_kelas_asal(
            $data['siswa_id'],
            $data['tahun_id'],
            isset($data['kelas_asal_id']) ? $data['kelas_asal_id'] : null
        );

        // Tentukan status siswa
        $status_keluar = 'mutasi_keluar';
        if (isset($data['jenis_keluar'])) {
            if ($data['jenis_keluar'] === 'meninggal') {
                $status_keluar = 'meninggal';
            } elseif ($data['jenis_keluar'] === 'mengundurkan diri') {
                $status_keluar = 'keluar';
            }
        }

        // Update status siswa
        $this->db->where('id', $data['siswa_id'])
                 ->update('siswa', ['status' => $status_keluar]);

        // Update atau Insert siswa_tahun (agar tercatat)
        $st = $this->db->get_where('siswa_tahun', [
            'siswa_id' => $data['siswa_id'],
            'tahun_id' => $data['tahun_id']
        ])->row();

        if ($st) {
            $this->db->where('id', $st->id)
                     ->update('siswa_tahun', ['status' => 'mutasi_keluar']);
        } else {
            $this->db->insert('siswa_tahun', [
                'siswa_id' => $data['siswa_id'],
                'kelas_id' => $kelas_asal_id,
                'tahun_id' => $data['tahun_id'],
                'status'   => 'mutasi_keluar'
            ]);
        }

        // Insert history
        $this->db->insert('siswa_history', [
            'siswa_id' => $data['siswa_id'],
            'kelas_id' => $kelas_asal_id,
            'tahun_id' => $data['tahun_id'],
            'status'   => 'mutasi_keluar'
        ]);

        // Complete transaction
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    // ================================================================
    // MUTASI MASUK
    // ================================================================
    public function mutasi_masuk($data)
    {
        $this->db->trans_begin();

        // Insert mutasi
        $this->db->insert('mutasi', $data);

        // Update status siswa
        $this->db->where('id', $data['siswa_id'])
                 ->update('siswa', [
                     'id_kelas' => $data['tujuan_kelas_id'],
                     'status'   => 'aktif'
                 ]);

        // Update atau Insert siswa_tahun
        $st = $this->db->get_where('siswa_tahun', [
            'siswa_id' => $data['siswa_id'],
            'tahun_id' => $data['tahun_id']
        ])->row();

        if ($st) {
            $this->db->where('id', $st->id)
                     ->update('siswa_tahun', [
                         'kelas_id' => $data['tujuan_kelas_id'],
                         'status'   => 'aktif'
                     ]);
        } else {
            $this->db->insert('siswa_tahun', [
                'siswa_id' => $data['siswa_id'],
                'kelas_id' => $data['tujuan_kelas_id'],
                'tahun_id' => $data['tahun_id'],
                'status'   => 'aktif'
            ]);
        }

        // Insert history
        $this->db->insert('siswa_history', [
            'siswa_id' => $data['siswa_id'],
            'kelas_id' => $data['tujuan_kelas_id'],
            'tahun_id' => $data['tahun_id'],
            'status'   => 'mutasi_masuk'
        ]);

        // Commit
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    // ================================================================
    // BATALKAN MUTASI
    // ================================================================
    public function batalkan($id)
    {
        $mutasi = $this->db->get_where('mutasi', ['id' => $id])->row();
        if (!$mutasi) return false;

        $this->db->trans_begin();

        // Set status mutasi jadi dibatalkan
        $this->db->where('id', $id)
                 ->update('mutasi', ['status_mutasi' => 'dibatalkan']);

        // Jika mutasi keluar → kembalikan status siswa
        if ($mutasi->jenis == 'keluar') {

            $update = ['status' => 'aktif'];

            if (!empty($mutasi->kelas_asal_id)) {
                $update['id_kelas'] = $mutasi->kelas_asal_id;
            }

            $this->db->where('id', $mutasi->siswa_id)
                     ->update('siswa', $update);

            // kembalikan siswa_tahun
            $this->db->where('siswa_id', $mutasi->siswa_id)
                     ->where('tahun_id', $mutasi->tahun_id)
                     ->update('siswa_tahun', ['status' => 'aktif']);
        }

        // Jika mutasi masuk
        if ($mutasi->jenis == 'masuk') {
            $this->db->where('siswa_id', $mutasi->siswa_id)
                     ->where('tahun_id', $mutasi->tahun_id)
                     ->where('kelas_id', $mutasi->tujuan_kelas_id)
                     ->delete('siswa_tahun');

            $this->db->where('id', $mutasi->siswa_id)
                     ->update('siswa', ['status' => 'aktif']);
        }

        // Commit or rollback
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    // ================================================================
    // Search siswa (autocomplete)
    // ================================================================
    public function search_siswa($keyword, $jenis)
    {
        $this->db->group_start()
                 ->like('nama', $keyword)
                 ->or_like('nis', $keyword)
                 ->group_end();

        if ($jenis === 'masuk') {
            $this->db->where('status', 'mutasi_masuk');
        } else {
            $this->db->where('status', 'aktif');
        }

        return $this->db->limit(10)->get('siswa')->result();
    }
    public function delete($id)
{
    // Hapus file berkas jika ada
    $mutasi = $this->db->get_where('mutasi', ['id' => $id])->row();
    if ($mutasi && $mutasi->berkas) {
        $file_path = FCPATH . 'uploads/mutasi/' . $mutasi->berkas;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Hapus data mutasi
    return $this->db->delete('mutasi', ['id' => $id]);
}

}

