<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AbsensiQRAdmin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();

        // models yang dibutuhkan (pastikan ada)
        $this->load->model('Kelas_model');
        $this->load->model('Siswa_model');
        $this->load->model('Hari_libur_model');

        // coba load pdf library (jika ada wrapper). Jika tidak, controller akan fallback ke TCPDF di third_party.
        // Jangan hapus; wrapper sering ada di project CI
        @$this->load->library('pdf');
        if (!$this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
    }
    }

    // 1) Halaman list absensi QR
    public function index() {

    $data['kelas']  = $this->Kelas_model->get_all();
    $data['judul']  = "Absensi QR Siswa";
    $data['active'] = "absensiqr_siswa";

    // === AMBIL SEMUA DATA ABSENSI QR ===
    $this->db->select("
        q.id,
        q.nis,
        q.tanggal,
        q.jam_masuk,
        q.jam_pulang,
        q.status,
        q.kehadiran,
        q.keterangan_telat,
        s.nama as nama_siswa,
        k.nama as nama_kelas
    ");
    $this->db->from("absensi_qr q");
    $this->db->join("siswa s", "s.nis = q.nis", "left");
    $this->db->join("kelas k", "k.id = s.id_kelas", "left");
    $this->db->order_by("q.id", "DESC");

    $data['absen'] = $this->db->get()->result();

    // === LOAD VIEW ===
    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensiqr/admin_index', $data);
    $this->load->view('templates/footer');
}


    // 2) Halaman laporan (form filter)
    public function laporan() {
        $data['kelas']  = $this->Kelas_model->get_all();
        $data['judul']  = "Laporan Absensi QR";
        $data['active'] = "laporan_absensiqr";

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('absensiqr/admin_laporan', $data);
        $this->load->view('templates/footer');
    }

    // 3) AJAX: ambil data filtered (untuk preview tabel)
    public function data()
    {
        $nama   = $this->input->post('nama');
        $kelas  = $this->input->post('kelas');
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
            q.keterangan_telat,
            s.nama as nama_siswa,
            k.nama as nama_kelas
        ");
        $this->db->from("absensi_qr q");
        $this->db->join("siswa s", "s.nis = q.nis", "left");
        $this->db->join("kelas k", "k.id = s.id_kelas", "left");

        if ($nama != "") {
            $this->db->like("s.nama", $nama);
        }

        if ($kelas != "") {
            $this->db->where("s.id_kelas", $kelas);
        }

        if ($status != "") {
            // status filter expects kehadiran code: H, S, I, A
            $this->db->where("q.kehadiran", $status);
        }

        if ($dari != "" && $sampai != "") {
            $this->db->where("q.tanggal >=", $dari);
            $this->db->where("q.tanggal <=", $sampai);
        }

        $this->db->order_by("q.tanggal", "ASC");

        echo json_encode($this->db->get()->result());
    }

    // 4) PDF export: TCPDF, kalender per kelas per bulan
    public function pdf()
{
    ob_clean();
    $kelas  = $this->input->get('kelas');
    $dari   = $this->input->get('dari');
    $sampai = $this->input->get('sampai');

    if (!$dari || !$sampai) show_error("Tanggal wajib diisi");

    $this->load->library('pdf');
    $this->pdf->setPrintHeader(false);
    $this->pdf->setPrintFooter(false);
    $this->pdf->SetMargins(5, 5, 5);
    $this->pdf->SetFont('helvetica', '', 9);

    // -----------------------------
    // Buat range tanggal
    // -----------------------------
    $tanggal = [];
    $start = strtotime($dari);
    $end   = strtotime($sampai);

    while ($start <= $end) {
        $tanggal[] = date("Y-m-d", $start);
        $start = strtotime("+1 day", $start);
    }

    // -----------------------------
    // Ambil siswa per kelas
    // -----------------------------
    $siswa = $this->db->query("
        SELECT nis, nama FROM siswa
        WHERE id_kelas = ?
        ORDER BY nama ASC
    ", [$kelas])->result();

    // -----------------------------
    // Ambil data absensi QR
    // -----------------------------
    $q = $this->db->query("
        SELECT nis, tanggal, kehadiran
        FROM absensi_qr
        WHERE tanggal BETWEEN ? AND ?
    ", [$dari, $sampai])->result();

    // bentuk index
    $rekap = [];
    foreach ($q as $r) {
        $rekap[$r->nis][$r->tanggal] = strtoupper($r->kehadiran);
    }

    // -----------------------------
    // Ambil tanggal libur (hari_libur)
    // -----------------------------
    $hari_libur = $this->db->get("hari_libur")->result();
    $tanggalMerah = [];
    foreach ($hari_libur as $hl) {
        $tanggalMerah[] = $hl->start;
    }

    // -----------------------------
    // Ambil nama kelas
    // -----------------------------
    $kelas_row = $this->db->get_where("kelas", ["id" => $kelas])->row();
    $kelas_nama = $kelas_row ? $kelas_row->nama : "-";

    // -----------------------------
    // Data ke view
    // -----------------------------
    $data = [
        'tanggal'      => $tanggal,
        'rekap'        => $rekap,
        'siswa'        => $siswa,
        'bulan_label'  => date("F Y", strtotime($dari)),
        'tahun'        => date("Y"),
        'kelas_nama'   => $kelas_nama,
        'tanggalMerah' => $tanggalMerah
    ];

    // Render ke PDF
    $this->pdf->AddPage('L', array(330, 210));
    $html = "<div style='font-size:8px'>" .
        $this->load->view("absensiqr/pdf_rekap_bulan", $data, true) .
        "</div>";

    $this->pdf->writeHTML($html, true, false, true, false, '');
    $this->pdf->Output("Rekap_Absensi_QR.pdf", "I");
}

    // 5) Excel export (kalender style)
    public function excel()
{
    if (ob_get_length()) { ob_end_clean(); }

    $kelas_param  = $this->input->get('kelas');
    $dari_raw     = $this->input->get('dari');
    $sampai_raw   = $this->input->get('sampai');
    $status_param = $this->input->get('status');

    if (!$dari_raw || !$sampai_raw) {
        show_error("Filter tanggal wajib diisi.");
    }

    $dari   = date('Y-m-d', strtotime($dari_raw));
    $sampai = date('Y-m-d', strtotime($sampai_raw));

    // ================================
    // **GANTI PHPExcel → SPREADSHEET**
    // ================================
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    // ambil semua kelas
    if ($kelas_param === "" || $kelas_param === "all" || $kelas_param === null) {
        $kelas_list = $this->db->query("SELECT id, nama FROM kelas ORDER BY nama ASC")->result();
        $single_class = false;
    } else {
        $kelas_list = $this->db->query("SELECT id, nama FROM kelas WHERE id = ?", [$kelas_param])->result();
        $single_class = true;
    }

    if (empty($kelas_list)) {
        show_error("Tidak ada data kelas.");
    }

    // rangkai tanggal dari..sampai
    $tanggal_all = [];
    $start = new DateTime($dari);
    $end   = new DateTime($sampai);

    for ($d = $start; $d <= $end; $d->modify('+1 day')) {
        $tanggal_all[] = $d->format('Y-m-d');
    }

    // kelompok tanggal per bulan
    $tanggal_per_bulan = [];
    foreach ($tanggal_all as $tgl) {
        $month = date('Y-m', strtotime($tgl));
        if (!isset($tanggal_per_bulan[$month])) $tanggal_per_bulan[$month] = [];
        $tanggal_per_bulan[$month][] = $tgl;
    }

    // libur
    $q_libur = $this->db->query("SELECT start FROM hari_libur")->result();
    $hariMerah = array_map(fn($r) => $r->start, $q_libur);

    // ambil semua absensi dulu (satu query besar)
    $params = [$dari, $sampai];
    $sqlStatus = "";

    if ($status_param !== null && $status_param !== "") {
        $sqlStatus = " AND kehadiran = ?";
        $params[] = $status_param;
    }

    $q_all = $this->db->query("
        SELECT nis, tanggal, kehadiran 
        FROM absensi_qr 
        WHERE tanggal BETWEEN ? AND ? {$sqlStatus}
    ", $params)->result();

    $arrAbsenGlobal = [];
    foreach ($q_all as $r) {
        $arrAbsenGlobal[$r->nis][$r->tanggal] = strtoupper($r->kehadiran);
    }

    $sheetIndex = 0;

    // ==============================================================
    //                LOOP PER KELAS DAN PER BULAN
    // ==============================================================
    foreach ($kelas_list as $k) {

        $siswa = $this->db->query("
            SELECT id, nis, nama
            FROM siswa
            WHERE id_kelas = ? AND status='aktif'
            ORDER BY nama ASC
        ", [$k->id])->result();

        if (empty($siswa)) continue;

        foreach ($tanggal_per_bulan as $bulan_key => $tgl_bulan) {

            if ($sheetIndex == 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            $sheet->setTitle(
                substr($single_class ?
                    date('F Y', strtotime($bulan_key . '-01')) :
                    $k->nama . " - " . date('M Y', strtotime($bulan_key . '-01')),
                0, 31)
            );

            // HEADER
            $sheet->setCellValue('A1', 'REKAP ABSENSI SISWA');
            $sheet->setCellValue('A2', 'KELAS: ' . $k->nama);
            $sheet->setCellValue('A3', 'PERIODE: ' . date('d-m-Y', strtotime($dari)) . ' s/d ' . date('d-m-Y', strtotime($sampai)));
            $sheet->setCellValue('A4', 'BULAN: ' . date('F Y', strtotime($bulan_key . '-01')));

            // TABEL HEADER
            $sheet->setCellValue('A6', 'No');
            $sheet->setCellValue('B6', 'Nama');

            $colIndex = 3;
            foreach ($tgl_bulan as $tgl) {
                $sheet->setCellValueByColumnAndRow($colIndex, 6, date('d', strtotime($tgl)));
                $sheet->getColumnDimension(
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex)
                )->setWidth(4.5);
                $colIndex++;
            }

            // Kolom total
            $totH = $colIndex++;
            $totS = $colIndex++;
            $totI = $colIndex++;
            $totA = $colIndex++;
            $totL = $colIndex;

            $sheet->setCellValueByColumnAndRow($totH, 6, 'H');
            $sheet->setCellValueByColumnAndRow($totS, 6, 'S');
            $sheet->setCellValueByColumnAndRow($totI, 6, 'I');
            $sheet->setCellValueByColumnAndRow($totA, 6, 'A');
            $sheet->setCellValueByColumnAndRow($totL, 6, 'L');

            // ROW DATA
            $row = 7;
            $no  = 1;

            foreach ($siswa as $s) {

                $sheet->setCellValue("A{$row}", $no++);
                $sheet->setCellValue("B{$row}", $s->nama);

                $col = 3;
                $countH = $countS = $countI = $countA = $countL = 0;

                foreach ($tgl_bulan as $tgl) {

                    $val = '-';

                    if (in_array($tgl, $hariMerah) || in_array(date('N', strtotime($tgl)), [6,7])) {
                        $val = 'L';
                    }

                    if (isset($arrAbsenGlobal[$s->nis][$tgl])) {
                        $val = $arrAbsenGlobal[$s->nis][$tgl];
                    }

                    // hitung total
                    if ($val === 'H') $countH++;
                    if ($val === 'S') $countS++;
                    if ($val === 'I') $countI++;
                    if ($val === 'A') $countA++;
                    if ($val === 'L') $countL++;

                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                    $sheet->setCellValue($cell, $val);

                    // warna merah untuk L
                    if ($val === 'L') {
                        $sheet->getStyle($cell)->getFill()->setFillType(
                            \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID
                        )->getStartColor()->setARGB('FFFF9999');
                    }

                    $col++;
                }

                // TOTAL
                $sheet->setCellValueByColumnAndRow($totH, $row, $countH);
                $sheet->setCellValueByColumnAndRow($totS, $row, $countS);
                $sheet->setCellValueByColumnAndRow($totI, $row, $countI);
                $sheet->setCellValueByColumnAndRow($totA, $row, $countA);
                $sheet->setCellValueByColumnAndRow($totL, $row, $countL);

                $row++;
            }

            $sheetIndex++;
        }
    }

    // OUTPUT
    $spreadsheet->setActiveSheetIndex(0);

    $filename = 'Rekap_Absensi_QR_' . date('Ymd_His') . '.xlsx';

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=\"$filename\"");
    header("Cache-Control: max-age=0");

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}


    // internal helper kalau perlu (tidak wajib)
    private function _array_months_from_range($start, $end)
    {
        $months = [];
        $startDt = new DateTime($start);
        $endDt = new DateTime($end);
        for ($d = $startDt; $d <= $endDt; $d->modify('+1 month')) {
            $months[] = $d->format('Y-m');
        }
        return $months;
    }
}
