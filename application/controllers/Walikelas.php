<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Walikelas extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // pastikan session login wali kelas
        if (!$this->session->userdata('logged_in') || 
            $this->session->userdata('role_id') != 3) {
            redirect('auth/login');
        }

        $this->load->database();
        $this->load->model('Kelas_model');
        $this->load->model('Siswa_model');
    }

    // ===========================
    // DASHBOARD WALIKELAS
    // ===========================
    public function index() {

        $kelas_id   = $this->session->userdata('kelas_id');
        $kelas_nama = $this->session->userdata('kelas_nama');

        // Ambil NIS siswa dalam kelas
        $siswa = $this->db->select('nis')
                          ->from('siswa')
                          ->where('id_kelas', $kelas_id)
                          ->where('status', 'aktif')
                          ->get()
                          ->result();

        $nis_list = [];
        foreach ($siswa as $s) {
            $nis_list[] = $s->nis;
        }

        // cegah error IN()
        if (empty($nis_list)) {
            $nis_list = ['0'];
        }

        // ===========================
        // DATA SISWA L / P / TOTAL
        // ===========================
        $data['laki'] = $this->db
            ->where('id_kelas', $kelas_id)
            ->where('jk', 'L')
            ->where('status', 'aktif')
            ->count_all_results('siswa');

        $data['perempuan'] = $this->db
            ->where('id_kelas', $kelas_id)
            ->where('jk', 'P')
            ->where('status', 'aktif')
            ->count_all_results('siswa');

        $data['total_siswa'] = $data['laki'] + $data['perempuan'];

        // ===========================
        // KEHADIRAN HARI INI
        // ===========================
        $tanggal = date('Y-m-d');
        $kode = ['H','I','S','A'];

        foreach ($kode as $k) {
            $data['hari_'.$k] = $this->db
                ->where('tanggal', $tanggal)
                ->where('kehadiran', $k)
                ->where_in('nis', $nis_list)
                ->count_all_results('absensi_qr');
        }

        // ===========================
        // KEHADIRAN BULAN INI
        // ===========================
        $bulan = date('m');
        $tahun = date('Y');

        foreach ($kode as $k) {
            $data['bulan_'.$k] = $this->db
                ->where('kehadiran', $k)
                ->where_in('nis', $nis_list)
                ->where('MONTH(tanggal)', $bulan)
                ->where('YEAR(tanggal)', $tahun)
                ->count_all_results('absensi_qr');
        }

        // ===========================
        // IZIN HARI INI
        // ===========================
        // Pastikan tabel izin_keluar memiliki: nis, jenis, created_at
        $data['izin_keluar_hari_ini'] = $this->db
            ->where_in('nis', $nis_list)
            ->where('DATE(created_at)', $tanggal)
            ->where('jenis_izin', 'keluar')
            ->count_all_results('izin_keluar');

        $data['izin_pulang_hari_ini'] = $this->db
            ->where_in('nis', $nis_list)
            ->where('DATE(created_at)', $tanggal)
            ->where('jenis_izin', 'pulang')
            ->count_all_results('izin_keluar');

        // ===========================
        // INFO TAMBAHAN
        // ===========================
        $data['kelas_nama'] = $kelas_nama;
        $data['title']      = "Dashboard Wali Kelas";
        $data['active']     = "walikelas_dashboard";

        // load view
        $this->load->view('walikelas/templates/header', $data);
        $this->load->view('walikelas/templates/sidebar', $data);
        $this->load->view('walikelas/dashboard', $data);
        $this->load->view('walikelas/templates/footer');
    }
    public function siswa()
{
    $kelas_id = $this->session->userdata('kelas_id');
    $kelas_nama = $this->session->userdata('kelas_nama');

    // Search
    $search = $this->input->get('search');

    $this->db->select("siswa.*, kelas.nama AS nama_kelas");
    $this->db->from("siswa");
    $this->db->join("kelas", "kelas.id = siswa.id_kelas", "left");
    $this->db->where("siswa.id_kelas", $kelas_id);
    $this->db->where("siswa.status", "aktif");

    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('siswa.nama', $search);
        $this->db->or_like('siswa.nis', $search);
        $this->db->or_like('siswa.nisn', $search);
        $this->db->group_end();
    }

    $data['siswa'] = $this->db->get()->result();

    // info tampilan
    $data['kelas_nama'] = $kelas_nama;
    $data['active'] = "wk_siswa";
    $data['title']  = "Data Siswa Kelas " . $kelas_nama;
    $data['search'] = $search;

    $this->load->view('walikelas/templates/header', $data);
    $this->load->view('walikelas/templates/sidebar', $data);
    $this->load->view('walikelas/siswa/index', $data);
    $this->load->view('walikelas/templates/footer');
}
public function siswa_export_excel()
{
    $kelas_id = $this->session->userdata('kelas_id');

    if (!$kelas_id) {
        echo "Akses ditolak: Anda tidak memiliki kelas.";
        exit;
    }

    // Load PHPExcel
    $this->load->library('PHPExcel_lib');
    $excel = new PHPExcel();
    $sheet = $excel->setActiveSheetIndex(0);

    // ===========================
    // HEADER STYLE
    // ===========================
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'type'   => PHPExcel_Style_Fill::FILL_SOLID,
            'color'  => ['rgb' => '0073CC']
        ],
        'alignment' => [
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ],
        'borders' => [
            'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
        ]
    ];

    $borderStyle = [
        'borders' => [
            'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
        ]
    ];

    // ===========================
    // SEMUA KOLOM DALAM TABEL SISWA
    // ===========================
    $fields_all = $this->db->list_fields('siswa');

    // ========================================
    // HAPUS KOLOM YANG TIDAK BOLEH DITAMPILKAN
    // ========================================
    $remove_fields = [
        'id',
        'id_kelas',
        'tahun_id',
        'created_at',
        'password',
        'token_qr',
        'foto'
    ];

    $fields = array_values(array_diff($fields_all, $remove_fields));

    // Tambahkan kolom: no, nama_kelas, tahun_ajaran
    $final_fields = array_merge(['no'], $fields, ['nama_kelas', 'tahun_ajaran']);

    // ===========================
    // AMBIL DATA SISWA + JOIN
    // ===========================
    $this->db->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left');
    $this->db->where('siswa.id_kelas', $kelas_id);
    $this->db->order_by('siswa.nama', 'ASC');
    $siswa = $this->db->get('siswa')->result();

    if (!$siswa) {
        echo "Tidak ada siswa.";
        exit;
    }

    // ===========================
    // TULIS HEADER
    // ===========================
    $col = 'A';
    foreach ($final_fields as $f) {
        $sheet->setCellValue($col.'1', strtoupper($f));
        $sheet->getStyle($col.'1')->applyFromArray($headerStyle);
        $col++;
    }

    // ===========================
    // ISI DATA
    // ===========================
    $row = 2;
    $no = 1;

    foreach ($siswa as $s) {

        $col = 'A';

        // === kolom NO ===
        $sheet->setCellValue($col.$row, $no++);
        $sheet->getStyle($col.$row)->applyFromArray($borderStyle);
        $col++;

        // === kolom selain yang dibuang ===
        foreach ($fields as $f) {
            $sheet->setCellValue($col.$row, $s->$f);
            $sheet->getStyle($col.$row)->applyFromArray($borderStyle);
            $col++;
        }

        // === nama kelas ===
        $sheet->setCellValue($col.$row, $s->nama_kelas);
        $sheet->getStyle($col.$row)->applyFromArray($borderStyle);
        $col++;

        // === tahun ajaran ===
        $sheet->setCellValue($col.$row, $s->tahun_ajaran);
        $sheet->getStyle($col.$row)->applyFromArray($borderStyle);

        $row++;
    }

    // AUTO SIZE
    $lastCol = PHPExcel_Cell::stringFromColumnIndex(count($final_fields) - 1);
    foreach (range('A', $lastCol) as $c) {
        $sheet->getColumnDimension($c)->setAutoSize(true);
    }

    // ===========================
    // OUTPUT
    // ===========================
    $fileName = "Data_Siswa_Kelas_" . str_replace(" ", "_", $this->session->userdata('kelas_nama')) . ".xls";

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$fileName.'"');
    header('Cache-Control: max-age=0');

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    $writer->save('php://output');
}


public function cetak($id)
{
    // ambil siswa
    $data['siswa'] = $this->db
        ->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran')
        ->join('kelas', 'kelas.id = siswa.id_kelas', 'left')
        ->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left')
        ->where('siswa.id', $id)
        ->get('siswa')
        ->row();

    // 🔥 AMBIL DATA WALI KELAS (dari session user login guru)
    $guru_id = $this->session->userdata('user_id');   // pastikan ini ID guru
    $data['walikelas'] = $this->db->get_where('guru', ['id' => $guru_id])->row();

    if (!$data['walikelas']) {
        // fallback biar tidak error
        $data['walikelas'] = (object)[
            'nama' => 'Tidak ditemukan',
            'nip'  => '-'
        ];
    }

    // render html
    $html = $this->load->view('walikelas/cetak_biodata', $data, TRUE);

    // PDF
    $this->load->library('pdf');
    $pdf = new Tcpdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();
    $pdf->writeHTML($html);

    $pdf->Output("Biodata_".$data['siswa']->nama.".pdf", "I");
}

public function cetak_biodata_all()
{
    $kelas_id = $this->session->userdata('kelas_id');

    $data['siswa'] = $this->db
        ->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran')
        ->from('siswa')
        ->join('kelas', 'kelas.id = siswa.id_kelas', 'left')
        ->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left')
        ->where('siswa.id_kelas', $kelas_id)
        ->where('siswa.status', 'aktif')
        ->order_by('siswa.nama', 'ASC')
        ->get()
        ->result();

    $this->load->view('walikelas/cetak_biodata_all', $data);
}

public function cetak_biodata($id)
{
    // Ambil data siswa
    $data['siswa'] = $this->db
        ->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran, kelas.wali_kelas_id')
        ->join('kelas', 'kelas.id = siswa.id_kelas', 'left')
        ->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left')
        ->where('siswa.id', $id)
        ->get('siswa')
        ->row();

    if (!$data['siswa']) {
        echo "Data siswa tidak ditemukan.";
        return;
    }

    // ====== AMBIL DATA WALI KELAS ======
    if (!empty($data['siswa']->wali_kelas_id)) {
        $data['walikelas'] = $this->db
            ->get_where('guru', ['id' => $data['siswa']->wali_kelas_id])
            ->row();
    } else {
        // jika NULL
        $data['walikelas'] = (object)[
            'nama' => '-',
            'nip'  => '-'
        ];
    }

    // Generate HTML
    $html = $this->load->view('walikelas/cetak_biodata', $data, TRUE);

    // Load PDF
    $this->load->library('pdf');
    $pdf = new Tcpdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();
    $pdf->writeHTML($html);

    $fileName = 'Biodata_' . str_replace(' ', '_', $data['siswa']->nama) . '.pdf';

    $pdf->Output($fileName, 'I');
}
public function absensi() {
    $kelas_id   = $this->session->userdata('kelas_id');
    $kelas_nama = $this->session->userdata('kelas_nama');

    $data['kelas']      = $this->Kelas_model->get_by_id($kelas_id) ? [$this->Kelas_model->get_by_id($kelas_id)] : [];
    $data['kelas_nama'] = $kelas_nama;
    $data['title']      = 'Laporan Absensi QR - Wali Kelas';
    $data['active']     = 'walikelas_absensi';

    $this->load->view('walikelas/templates/header', $data);
    $this->load->view('walikelas/templates/sidebar', $data);
    $this->load->view('walikelas/absensi/index', $data);
    $this->load->view('walikelas/templates/footer', $data);
}


    /**
     * AJAX: ambil data absensi (filter) — hanya untuk kelas wali
     * POST: nama, dari, sampai, status (kehadiran code H/I/S/A)
     */
    public function absensi_data()
{
    $kelas_id = $this->session->userdata('kelas_id');

    $nama   = $this->input->post('nama');
    $dari   = $this->input->post('dari');
    $sampai = $this->input->post('sampai');
    $status = $this->input->post('status');

    $this->db->select("
        q.id,
        q.nis,
        q.tanggal,
        q.jam_masuk,
        q.jam_pulang,
        q.status,
        q.kehadiran,
        s.nama as nama_siswa
    ");
    $this->db->from("absensi_qr q");
    $this->db->join("siswa s", "s.nis = q.nis", "left");
    $this->db->where("s.id_kelas", $kelas_id);

    if ($nama != "") {
        $this->db->like("s.nama", $nama);
    }

    if ($status != "") {
        $this->db->where("q.kehadiran", $status);
    }

    if ($dari != "" && $sampai != "") {
        $this->db->where("q.tanggal >=", $dari);
        $this->db->where("q.tanggal <=", $sampai);
    }

    $this->db->order_by("q.tanggal", "ASC");

    echo json_encode($this->db->get()->result());
}


    /**
     * PDF export — kalender per siswa per bulan (sama seperti admin)
     * GET: dari, sampai
     */
    public function absensi_pdf()
{
    $kelas_id = $this->session->userdata('kelas_id');

    $dari   = $this->input->get('dari');
    $sampai = $this->input->get('sampai');

    if (!$dari || !$sampai) {
        echo "Tanggal belum diisi";
        return;
    }

    $this->load->library('pdf');
    $this->pdf->setPrintHeader(false);
    $this->pdf->setPrintFooter(false);
    $this->pdf->SetMargins(5, 5, 5);
    $this->pdf->SetFont('helvetica', '', 9);

    // Tanggal range
    $tanggal = [];
    $start = strtotime($dari);
    $end   = strtotime($sampai);
    while ($start <= $end) {
        $tanggal[] = date("Y-m-d", $start);
        $start = strtotime("+1 day", $start);
    }

    // Ambil siswa kelas ini
    $siswa = $this->db->select("nis, nama")
        ->from("siswa")
        ->where("id_kelas", $kelas_id)
        ->order_by("nama", "ASC")
        ->get()->result();

    // Ambil absensi QR
    $q = $this->db->select("nis, tanggal, kehadiran")
        ->from("absensi_qr")
        ->where("tanggal >=", $dari)
        ->where("tanggal <=", $sampai)
        ->get()->result();

    $rekap = [];
    foreach ($q as $r) {
        $rekap[$r->nis][$r->tanggal] = $r->kehadiran;
    }

    // Ambil libur
    $hari_libur = $this->db->get("hari_libur")->result();
    $tanggalMerah = array_map(function($x){ return $x->start; }, $hari_libur);

    // Nama kelas
    $kelas_nama = $this->session->userdata('kelas_nama');
    // Ambil wali kelas dari tabel kelas
$kelas = $this->db->get_where('kelas', ['id' => $kelas_id])->row();

if ($kelas && $kelas->wali_kelas_id) {
    $walikelas = $this->db->get_where('guru', ['id' => $kelas->wali_kelas_id])->row();
} else {
    $walikelas = (object)[
        'nama' => '-',
        'nip'  => '-'
    ];
}


    // Kirim ke view PDF
    $data = [
    'tanggal'      => $tanggal,
    'rekap'        => $rekap,
    'siswa'        => $siswa,
    'kelas_nama'   => $kelas_nama,
    'tanggalMerah' => $tanggalMerah,
    'bulan_label'  => date("F Y", strtotime($dari)),
    'tahun'        => date("Y", strtotime($dari)),
    'logo'         => base_url('assets/img/logo.png'),
    'walikelas'    => $walikelas,
    'tanggal_ttd'  => "Kuningan, " . date('d F Y'),
];


    // Render
    $this->pdf->AddPage('L', [330, 210]);
    $html = $this->load->view("walikelas/absensi/pdf_rekap_bulan", $data, true);
    $this->pdf->writeHTML($html, true, false, true, false, '');
    $this->pdf->Output("Rekap_Absensi_QR.pdf", "I");
}


    /**
     * Excel export — kalender style multi-sheet (per bulan)
     * GET: dari, sampai
     */
    public function absensi_excel()
{
    $kelas_id = $this->session->userdata('kelas_id');

    $dari   = $this->input->get('dari');
    $sampai = $this->input->get('sampai');

    if (!$dari || !$sampai) {
        show_error("Tanggal wajib diisi.");
    }

    $this->load->library('PHPExcel_Lib');
    $excel = new PHPExcel();

    // Range tanggal
    $tanggal_all = [];
    $start = new DateTime($dari);
    $end   = new DateTime($sampai);

    for ($d = $start; $d <= $end; $d->modify('+1 day')) {
        $tanggal_all[] = $d->format('Y-m-d');
    }

    // Group by month
    $tanggal_per_bulan = [];
    foreach ($tanggal_all as $tgl) {
        $k = date('Y-m', strtotime($tgl));
        $tanggal_per_bulan[$k][] = $tgl;
    }

    // Ambil siswa
    $siswa = $this->db->select("id, nis, nama")
        ->from("siswa")
        ->where("id_kelas", $kelas_id)
        ->where("status", "aktif")
        ->order_by("nama", "ASC")
        ->get()->result();

    // Ambil absensi
    $abs = $this->db->query("
        SELECT nis, tanggal, kehadiran
        FROM absensi_qr
        WHERE tanggal BETWEEN ? AND ?
    ", [$dari, $sampai])->result();

    $rekap = [];
    foreach ($abs as $a) {
        $rekap[$a->nis][$a->tanggal] = $a->kehadiran;
    }

    // Loop bulan → buat sheet
    $sheetIndex = 0;
    foreach ($tanggal_per_bulan as $bulan_key => $tgl_bulan) {

        if ($sheetIndex > 0) {
            $excel->createSheet();
        }

        $sheet = $excel->setActiveSheetIndex($sheetIndex);
        $sheet->setTitle(substr(date("M Y", strtotime($bulan_key . "-01")), 0, 31));

        // Header
        $sheet->setCellValue("A1", "REKAP ABSENSI QR KELAS " . $this->session->userdata('kelas_nama'));
        $sheet->setCellValue("A3", "Periode: " . $dari . " s/d " . $sampai);

        // Tanggal
        $sheet->setCellValue("A5", "No");
        $sheet->setCellValue("B5", "Nama");

        $col = 'C';
        foreach ($tgl_bulan as $tgl) {
            $sheet->setCellValue($col . "5", date('d', strtotime($tgl)));
            $col++;
        }

        // Isi siswa
        $row = 6;
        $no = 1;
        foreach ($siswa as $s) {

            $sheet->setCellValue("A" . $row, $no++);
            $sheet->setCellValue("B" . $row, $s->nama);

            $col = 'C';

            foreach ($tgl_bulan as $tgl) {
                $val = isset($rekap[$s->nis][$tgl]) ? $rekap[$s->nis][$tgl] : "-";
                $sheet->setCellValue($col . $row, $val);
                $col++;
            }

            $row++;
        }

        $sheetIndex++;
    }

    // Output
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Rekap_Walikelas.xlsx"');
    header('Cache-Control: max-age=0');

    PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save('php://output');
}


    // helper internal jika butuh
    private function _array_months_from_range($start, $end) {
        $months = [];
        $startDt = new DateTime($start);
        $endDt = new DateTime($end);
        for ($d = $startDt; $d <= $endDt; $d->modify('+1 month')) {
            $months[] = $d->format('Y-m');
        }
        return $months;
    }



}
