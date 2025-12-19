<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Verval extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        // WAJIB LOGIN
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
        }

        // KHUSUS ADMIN
        $role = $this->session->userdata('role_id'); // ← SAMAKAN DENGAN SISTEM
        if ($role != 1) { // 1 = admin
            show_error('Akses ditolak', 403);
        }
    }

   public function siswa()
{
    $this->load->library('pagination');

    $data['active'] = 'verval_siswa';

    // ===============================
    // Ambil filter dari GET
    // ===============================
    $kelas_id = $this->input->get('kelas');
    $keyword  = $this->input->get('q');
    $limit    = $this->input->get('limit') ?: 20;
    $page     = $this->input->get('per_page') ?: 0;

    // Query dasar (HANYA SISWA AKTIF)
$this->db->select('siswa.*, kelas.nama AS nama_kelas');
$this->db->from('siswa');
$this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
$this->db->where('siswa.status', 'aktif');

if ($kelas_id) {
    $this->db->where('siswa.id_kelas', $kelas_id);
}

if ($keyword) {
    $this->db->group_start()
             ->like('siswa.nama', $keyword)
             ->or_like('siswa.nisn', $keyword)
             ->group_end();
}


    // ===============================
    // Hitung total rows
    // ===============================
    $total_rows = $this->db->count_all_results('', false);

    // ===============================
    // Pagination config
    // ===============================
    $config['base_url'] = site_url('verval/siswa');
    $config['total_rows'] = $total_rows;
    $config['per_page'] = $limit;
    $config['page_query_string'] = true;

    // Bootstrap style
    $config['full_tag_open'] = '<ul class="pagination pagination-sm justify-content-end">';
    $config['full_tag_close'] = '</ul>';
    $config['num_tag_open'] = '<li class="page-item">';
    $config['num_tag_close'] = '</li>';
    $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
    $config['cur_tag_close'] = '</span></li>';
    $config['attributes'] = ['class' => 'page-link'];

    $this->pagination->initialize($config);

    // ===============================
    // Data siswa
    // ===============================
    $data['siswa'] = $this->db
        ->order_by('siswa.nama', 'ASC')
        ->limit($limit, $page)
        ->get()
        ->result();

    // ===============================
    // Data pendukung
    // ===============================
    $data['kelas'] = $this->db->order_by('nama','ASC')->get('kelas')->result();
    $data['pagination'] = $this->pagination->create_links();
    $data['total_rows'] = $total_rows;
    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('verval/verval_siswa', $data);
    $this->load->view('templates/footer');
}


    public function laporan()
{
    $data['active'] = 'laporan_verval';

    // filter kelas (optional)
    $kelas_id = $this->input->get('kelas');

    // ===============================
    // Ambil daftar kelas
    // ===============================
    $data['kelas'] = $this->db
        ->order_by('nama', 'ASC')
        ->get('kelas')
        ->result();

    // ===============================
    // Query laporan
    // ===============================
    $this->db->select('
        kelas.id,
        kelas.nama AS nama_kelas,
        COUNT(siswa.id) AS total_siswa,
        SUM(CASE WHEN siswa.status_verval = 1 THEN 1 ELSE 0 END) AS valid,
        SUM(CASE WHEN siswa.status_verval = 2 THEN 1 ELSE 0 END) AS perbaikan,
        SUM(CASE WHEN siswa.status_verval IN (1,2) THEN 1 ELSE 0 END) AS sudah_verval,
        SUM(CASE WHEN siswa.status_verval = 0 THEN 1 ELSE 0 END) AS belum_verval

    ');
    $this->db->from('kelas');
    $this->db->join('siswa', 'siswa.id_kelas = kelas.id AND siswa.status = "aktif"', 'left');

    if ($kelas_id) {
        $this->db->where('kelas.id', $kelas_id);
    }

    $this->db->group_by('kelas.id');
    $this->db->order_by('kelas.nama', 'ASC');

    $data['laporan'] = $this->db->get()->result();

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('verval/laporan_verval', $data);
    $this->load->view('templates/footer');
}
public function export_excel()
{
    $kelas_id = $this->input->get('kelas');

    // ===============================
    // Ambil kelas
    // ===============================
    if ($kelas_id) {
        $kelas_list = $this->db->where('id', $kelas_id)->get('kelas')->result();
    } else {
        $kelas_list = $this->db->order_by('nama', 'ASC')->get('kelas')->result();
    }

    if (empty($kelas_list)) {
        show_error('Data kelas tidak ditemukan');
    }

    $spreadsheet = new Spreadsheet();
    $sheetIndex = 0;

    foreach ($kelas_list as $kelas) {

        // ===============================
        // Ambil siswa aktif per kelas
        // ===============================
        $siswa = $this->db
            ->select('nisn, nama, status_verval')
            ->where('id_kelas', $kelas->id)
            ->where('status', 'aktif')
            ->order_by('nama', 'ASC')
            ->get('siswa')
            ->result();

        // ===============================
        // HITUNG REKAP (LOGIKA BENAR)
        // ===============================
        $total = count($siswa);
        $valid = 0;
        $perbaikan = 0;
        $sudah = 0;
        $belum = 0;

        foreach ($siswa as $s) {
            if ($s->status_verval == 1) {
                $valid++;
                $sudah++;
            } elseif ($s->status_verval == 2) {
                $perbaikan++;
                $sudah++;
            } else {
                $belum++;
            }
        }

        // ===============================
        // Sheet
        // ===============================
        $sheet = ($sheetIndex == 0)
            ? $spreadsheet->getActiveSheet()
            : $spreadsheet->createSheet();

        $sheet->setTitle(substr($kelas->nama, 0, 31));
        $sheetIndex++;

        // ===============================
        // HEADER REKAP
        // ===============================
        $sheet->setCellValue('A1', 'Kelas');
        $sheet->setCellValue('B1', $kelas->nama);

        $sheet->setCellValue('A3', 'Total Siswa');
        $sheet->setCellValue('B3', $total);

        $sheet->setCellValue('A4', 'Valid');
        $sheet->setCellValue('B4', $valid);

        $sheet->setCellValue('A5', 'Perlu Perbaikan');
        $sheet->setCellValue('B5', $perbaikan);

        $sheet->setCellValue('A6', 'Sudah Verval');
        $sheet->setCellValue('B6', $sudah);

        $sheet->setCellValue('A7', 'Belum Verval');
        $sheet->setCellValue('B7', $belum);

        $sheet->getStyle('A1:A7')->getFont()->setBold(true);

        // ===============================
        // HEADER TABEL
        // ===============================
        $rowStart = 9;
        $sheet->setCellValue("A{$rowStart}", 'No');
        $sheet->setCellValue("B{$rowStart}", 'NISN');
        $sheet->setCellValue("C{$rowStart}", 'Nama Siswa');
        $sheet->setCellValue("D{$rowStart}", 'Status');

        $sheet->getStyle("A{$rowStart}:D{$rowStart}")
              ->getFont()->setBold(true);

        // ===============================
        // DATA SISWA
        // ===============================
        $row = $rowStart + 1;
        $no = 1;

        foreach ($siswa as $s) {

            if ($s->status_verval == 1) {
                $status = 'Valid';
            } elseif ($s->status_verval == 2) {
                $status = 'Perlu Perbaikan';
            } else {
                $status = 'Belum Verval';
            }

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $s->nisn);
            $sheet->setCellValue("C{$row}", $s->nama);
            $sheet->setCellValue("D{$row}", $status);
            $row++;
        }

        foreach (range('A','D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    // ===============================
    // OUTPUT
    // ===============================
    $filename = 'Laporan_Verval_Siswa_' . date('Ymd_His') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function set($id, $status)
{
    // proteksi dasar
    if (!in_array($status, ['0','1'])) {
        show_error('Status tidak valid');
    }

    $this->db->where('id', $id)->update('siswa', [
        'status_verval' => $status,
        'verval_by'     => $this->session->userdata('user_id'),
        'verval_at'     => date('Y-m-d H:i:s')
    ]);

    redirect('verval/siswa');
}
public function berkas()
{
    echo '<pre>';
    print_r($_POST);
    exit;

    if ($this->input->method() !== 'post') {
        redirect('verval/siswa');
        exit;
    }

    $id      = $this->input->post('id', true);
    $status  = $this->input->post('status', true);
    $catatan = $this->input->post('catatan', true);

    if (empty($id) || !in_array($status, ['0','1'])) {
        show_error('Data tidak valid');
    }

    $data = [
        'status_berkas' => (int)$status,
        'verval_by'     => $this->session->userdata('user_id'),
        'verval_at'     => date('Y-m-d H:i:s')
    ];

    if ($status == '0') {
        $data['catatan_perbaikan'] = $catatan;
    } else {
        $data['catatan_perbaikan'] = null;
    }

    $this->db->where('id', $id)->update('siswa', $data);

    redirect('verval/siswa');
}
public function valid($id)
{
    $this->db->where('id', $id)
             ->update('siswa', [
                 'status_verval' => 1,
                 'catatan_verval' => null
             ]);

    $redirect = $this->input->get('redirect') ?? $this->input->server('HTTP_REFERER');

    redirect($redirect ?: 'verval/siswa');
}


public function perbaikan()
{
    $id = $this->input->post('id_siswa');

    $this->db->where('id', $id)
             ->update('siswa', [
                 'status_verval' => 2,
                 'catatan_verval' => $this->input->post('catatan')
             ]);

    $redirect = $this->input->post('redirect_url');
    redirect($redirect ?: 'verval/siswa');
}

public function reset($id)
{
    $this->db->where('id', $id)
             ->update('siswa', [
                 'status_verval' => 0,
                 'catatan_verval' => null
             ]);

    redirect($this->input->server('HTTP_REFERER'));
}


}

