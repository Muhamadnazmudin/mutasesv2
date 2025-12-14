<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class DashboardMBG extends CI_Controller {
    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->model('Kelas_model');
        $this->load->model('Siswa_model');
        if (!$this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
    }
    }

    public function index() 
    {
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');

        // ambil seluruh kelas
        $kelas = $this->db
    ->select('*')
    ->from('kelas')
    ->order_by('nama', 'ASC')
    ->get()
    ->result();

        $rekap = [];

        foreach ($kelas as $k) {

            // ============================
            // 1. AMBIL LIST NIS DALAM KELAS
            // ============================
            $siswa = $this->db->select('nis')
                              ->from('siswa')
                              ->where('id_kelas', $k->id)
                              ->where('status', 'aktif')
                              ->get()->result();

            $nis_list = [];
            foreach ($siswa as $s) {
                $nis_list[] = $s->nis;
            }

            if (empty($nis_list)) {
                $nis_list = ['0']; // cegah error IN()
            }

            $total_siswa = count($nis_list);

            // ============================
            // 2. HITUNG YANG HADIR
            // ============================
            $hadir = $this->db->where('tanggal', $tanggal)
                              ->where_in('nis', $nis_list)
                              ->count_all_results('absensi_qr');

            // ============================
            // 3. TIDAK HADIR = TOTAL - HADIR
            // ============================
            $tidak_hadir = $total_siswa - $hadir;

            // SIMPAN REKAP
            $rekap[] = [
                'kelas_id'    => $k->id,
                'kelas_nama'  => $k->nama,
                'total'       => $total_siswa,
                'hadir'       => $hadir,
                'tidak_hadir' => $tidak_hadir
            ];
        }

        $data['tanggal'] = $tanggal;
        $data['rekap'] = $rekap;

        $this->load->view('dashboard_mbg/index', $data);
    }

    public function detail($kelas_id)
    {
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');

        // 1. ambil seluruh siswa kelas ini
        $siswa = $this->db
            ->select('id, nis, nama')
            ->from('siswa')
            ->where('id_kelas', $kelas_id)
            ->where('status', 'aktif')
            ->get()->result();

        $nis_list = array_column($siswa, 'nis');

        if (empty($nis_list)) {
            $nis_list = ['0'];
        }

        // 2. siswa hadir
        $hadir = $this->db->select('siswa.nama, siswa.nis')
    ->from('absensi_qr')
    ->join('siswa', 'siswa.nis = absensi_qr.nis', 'left')
    ->where('absensi_qr.tanggal', $tanggal)
    ->where_in('absensi_qr.nis', $nis_list)
    ->get()->result();


        // 3. siswa tidak hadir = siswa - hadir
        $hadir_nis = array_column($hadir, 'nis');

// Jika tidak ada yang hadir
if (empty($hadir_nis)) {
    // semua siswa dianggap tidak hadir
    $tidak_hadir = $this->db
        ->select('nama')
        ->from('siswa')
        ->where('id_kelas', $kelas_id)
        ->where('status', 'aktif')
        ->get()->result();
} else {
    // ambil siswa yang tidak hadir
    $tidak_hadir = $this->db
        ->select('nama')
        ->from('siswa')
        ->where('id_kelas', $kelas_id)
        ->where('status', 'aktif')
        ->where_not_in('nis', $hadir_nis)
        ->get()->result();
}


        $data['kelas']       = $this->Kelas_model->get_by_id($kelas_id);
        $data['tanggal']     = $tanggal;
        $data['hadir']       = $hadir;
        $data['tidak_hadir'] = $tidak_hadir;

        $this->load->view('dashboard_mbg/detail', $data);
    }
    public function export_pdf()
{
    $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');

    // Ambil data seperti index()
    $kelas = $this->db->order_by('nama','ASC')->get('kelas')->result();
    $rekap = [];

    foreach ($kelas as $k) {

        $siswa = $this->db->select('nis')
                          ->from('siswa')
                          ->where('id_kelas', $k->id)
                          ->where('status', 'aktif')
                          ->get()->result();

        $nis_list = array_column($siswa, 'nis');
        if (empty($nis_list)) $nis_list = ['0'];

        $total_siswa = count($nis_list);

        $hadir = $this->db->where('tanggal', $tanggal)
                          ->where_in('nis', $nis_list)
                          ->count_all_results('absensi_qr');

        $tidak_hadir = $total_siswa - $hadir;

        $rekap[] = [
            'kelas' => $k->nama,
            'total' => $total_siswa,
            'hadir' => $hadir,
            'tidak_hadir' => $tidak_hadir
        ];
    }

    // LOAD VIEW HTML
    $data['tanggal'] = $tanggal;
    $data['rekap'] = $rekap;
    $html = $this->load->view('dashboard_mbg/pdf', $data, TRUE);

    // PDF
    $this->load->library('pdf');
    $this->pdf->setPrintHeader(false);
    $this->pdf->setPrintFooter(false);
    $this->pdf->AddPage('L', 'A4');
    $this->pdf->writeHTML($html);
    $this->pdf->Output("Rekap_MBG_{$tanggal}.pdf", "I");
}

public function export_excel()
{
    @ob_end_clean();
    ob_start();
    error_reporting(0);

    $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');

    // Ambil semua kelas
    $kelas = $this->db->order_by('nama', 'ASC')->get('kelas')->result();

    $rekap = [];

    foreach ($kelas as $k) {

        // ambil siswa kelas ini
        $siswa = $this->db->select('nis')
                          ->from('siswa')
                          ->where('id_kelas', $k->id)
                          ->where('status', 'aktif')
                          ->get()->result();

        $nis_list = array_column($siswa, 'nis');
        if (empty($nis_list)) $nis_list = ['0'];

        $total_siswa = count($nis_list);

        $hadir = $this->db->where('tanggal', $tanggal)
                          ->where_in('nis', $nis_list)
                          ->count_all_results('absensi_qr');

        $tidak_hadir = $total_siswa - $hadir;

        $rekap[] = [
            'kelas' => $k->nama,
            'total' => $total_siswa,
            'hadir' => $hadir,
            'tidak_hadir' => $tidak_hadir
        ];
    }

    // Buat Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // =============================
    // HEADER
    // =============================
    $sheet->setCellValue('A1', 'No')
          ->setCellValue('B1', 'Kelas')
          ->setCellValue('C1', 'Total')
          ->setCellValue('D1', 'Hadir')
          ->setCellValue('E1', 'Tidak Hadir');

    // Style header
    $sheet->getStyle('A1:E1')->applyFromArray([
        'font' => ['bold' => true],
        'borders' => ['allBorders' => ['style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
    ]);

    // =============================
    // ISI DATA
    // =============================
    $row = 2;
    $no = 1;

    foreach ($rekap as $r) {

        // Explicit string untuk menghindari auto-format Excel
        $sheet->setCellValue("A$row", $no++)
              ->setCellValueExplicit("B$row", $r['kelas'], DataType::TYPE_STRING)
              ->setCellValue("C$row", $r['total'])
              ->setCellValue("D$row", $r['hadir'])
              ->setCellValue("E$row", $r['tidak_hadir']);

        $row++;
    }

    // Auto column width
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // =============================
    // OUTPUT
    $filename = "Rekap_MBG_{$tanggal}.xls";

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Cache-Control: max-age=0');

    $writer = new Xls($spreadsheet);
    $writer->save('php://output');

    ob_end_flush();
    exit;
}

}
