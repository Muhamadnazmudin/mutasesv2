<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Laporan extends CI_Controller {

  public function __construct(){
    parent::__construct();
    $this->load->model('Laporan_model');
    $this->load->library('pdf'); // library TCPDF
    $this->load->library('Spreadsheet_Lib'); // untuk export Excel
    if (!$this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
    }
  }

  public function index() {
    $tahun_aktif = date('Y');
    $kelas  = $this->input->get('kelas');
    $jenis  = $this->input->get('jenis');
    $search = $this->input->get('search');

    $data['judul'] = 'Laporan Mutasi Siswa';
    $data['active'] = 'laporan';
    $data['tahun']  = $tahun_aktif;
    $data['kelas_list'] = $this->Laporan_model->get_kelas();
    $data['mutasi'] = $this->Laporan_model->get_laporan($tahun_aktif, $kelas, $jenis, $search);

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('laporan/index', $data);
    $this->load->view('templates/footer');
  }

  // ==========================================================
  // 🔹 EXPORT PDF (Format Lama, Kompatibel PHP 5.6)
  // ==========================================================
  public function export_pdf()
  {
      $tahun_id = $this->session->userdata('tahun_id');
      $tahun_row = $this->db->get_where('tahun_ajaran', array('id' => $tahun_id))->row();
      $tahun = isset($tahun_row->tahun) ? $tahun_row->tahun : date('Y');

      $kelas  = $this->input->get('kelas');
      $jenis  = $this->input->get('jenis');
      $search = $this->input->get('search');

      $mutasi = $this->Laporan_model->get_laporan($tahun, $kelas, $jenis, $search);

      // 🔸 PDF Setup
      $this->load->library('tcpdf');
      $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
      $pdf->SetCreator('Mutases');
      $pdf->SetTitle('Laporan Mutasi Siswa Tahun ' . $tahun);
      $pdf->SetMargins(6, 8, 6);
      $pdf->AddPage('L');
      $pdf->SetFont('helvetica', '', 10);

      // Header Judul
      $pdf->SetFont('helvetica', 'B', 14);
      $pdf->Cell(0, 8, 'LAPORAN MUTASI SISWA TAHUN ' . $tahun, 0, 1, 'C');
      $pdf->Ln(3);

      // Header Tabel
      $pdf->SetFont('helvetica', 'B', 9);
      $pdf->SetFillColor(230, 230, 230);

      $headers = array(
          'No', 'Nama Siswa', 'NIS', 'NISN', 'Kelas Asal', 
          'Jenis', 'Jenis Keluar', 'Tanggal', 'Alasan', 
          'No. HP Ortu', 'Tujuan', 'Tahun Ajaran' 
      );

      // Lebar kolom disesuaikan total 297mm
      $widths = array(8, 35, 18, 22, 25, 18, 25, 20, 30, 28, 30, 25);

      foreach ($headers as $i => $header) {
          $pdf->MultiCell($widths[$i], 9, $header, 1, 'C', true, 0);
      }
      $pdf->Ln();

      // Isi Data
      $pdf->SetFont('helvetica', '', 8.5);
      $no = 1;
      if (!empty($mutasi)) {
          foreach ($mutasi as $m) {
              $pdf->MultiCell($widths[0], 8, $no++, 1, 'C', false, 0);
              $pdf->MultiCell($widths[1], 8, $m->nama_siswa, 1, 'L', false, 0);
              $pdf->MultiCell($widths[2], 8, $m->nis, 1, 'C', false, 0);
              $pdf->MultiCell($widths[3], 8, $m->nisn, 1, 'C', false, 0);
              $pdf->MultiCell($widths[4], 8, isset($m->kelas_asal) ? $m->kelas_asal : '-', 1, 'L', false, 0);
              $pdf->MultiCell($widths[5], 8, ucfirst($m->jenis), 1, 'C', false, 0);
              $pdf->MultiCell($widths[6], 8, $m->jenis == 'keluar' ? ($m->jenis_keluar ? $m->jenis_keluar : '-') : '-', 1, 'L', false, 0);
              $pdf->MultiCell($widths[7], 8, !empty($m->tanggal) ? date('d-m-Y', strtotime($m->tanggal)) : '-', 1, 'C', false, 0);
              $pdf->MultiCell($widths[8], 8, $m->alasan ? $m->alasan : '-', 1, 'L', false, 0);
              $pdf->MultiCell($widths[9], 8, $m->nohp_ortu ? $m->nohp_ortu : '-', 1, 'L', false, 0);
              $pdf->MultiCell($widths[10], 8, $m->jenis == 'keluar' ? ($m->tujuan_sekolah ? $m->tujuan_sekolah : '-') : ($m->kelas_tujuan ? $m->kelas_tujuan : '-'), 1, 'L', false, 0);
              $pdf->MultiCell($widths[11], 8, isset($m->tahun_ajaran) ? $m->tahun_ajaran : '-', 1, 'C', false, 1);
          }
      } else {
          $pdf->Cell(array_sum($widths), 10, 'Tidak ada data mutasi ditemukan.', 1, 1, 'C');
      }

      // Footer
      $pdf->Ln(5);
      $pdf->SetFont('helvetica', 'I', 9);
      $pdf->Cell(0, 7, 'Dicetak pada: ' . date('d/m/Y H:i') . ' | Sistem Mutasi Siswa', 0, 1, 'R');

      $pdf->Output('Laporan_Mutasi_' . $tahun . '.pdf', 'I');
  }

  // ==========================================================
  // 🔹 EXPORT EXCEL
  // ==========================================================
  public function export_excel()
{
    $tahun = $this->input->get('tahun');
    if (empty($tahun)) $tahun = date('Y');

    $kelas  = $this->input->get('kelas');
    $jenis  = $this->input->get('jenis');
    $search = $this->input->get('search');

    $data = $this->Laporan_model->get_laporan($tahun, $kelas, $jenis, $search);

    $this->load->library('Spreadsheet_Lib');

    // FIX: nama objek benar adalah spreadsheet_lib
    $this->spreadsheet_lib->export_laporan_mutasi($data, $tahun);
}

}
