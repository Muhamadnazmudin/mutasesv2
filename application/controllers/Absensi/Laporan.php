<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Absensi/Absensi_model');
        $this->load->model('Kelas_model');
        $this->load->model('Siswa_model');
    }

    public function index()
    {
        $data['judul']   = 'Laporan Absensi Siswa';
        $data['active']  = 'laporan_absensi';
        $data['kelas']   = $this->Kelas_model->get_all();
        $data['hasil']   = []; // awal kosong

        // jika tombol "tampilkan" ditekan
        if ($this->input->post('submit') == 'tampil') {

            $nama    = $this->input->post('nama');
            $kelas   = $this->input->post('kelas');
            $dari    = $this->input->post('dari');
            $sampai  = $this->input->post('sampai');
            $tahun   = $this->input->post('tahun');
            $ket     = $this->input->post('keterangan');

            $data['hasil'] = $this->Absensi_model->laporan_filter(
                $nama, $kelas, $dari, $sampai, $tahun, $ket
            );

            // untuk cetak header periode
            $data['periode'] = $dari . " s/d " . $sampai;
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('absensi/laporan', $data);
        $this->load->view('templates/footer');
    }
    public function pdf()
{
    // HINDARI OUTPUT SEBELUM PDF
    ob_clean();

    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 300);

    $this->load->library('pdf');
    $this->load->model('Hari_libur_model');

    // Ambil parameter
    $kelas  = $this->input->get('kelas');
    $dari   = $this->input->get('dari');
    $sampai = $this->input->get('sampai');
    $tahun  = $this->input->get('tahun');

    if (!$dari || !$sampai) show_error("Filter tanggal wajib diisi.");

    $dari   = date('Y-m-d', strtotime($dari));
    $sampai = date('Y-m-d', strtotime($sampai));

    // ================================
    // 1. Ambil daftar kelas
    // ================================
    if ($kelas == '' || $kelas == 'all') {

        $kelas_list = $this->db->query("
            SELECT id, nama
            FROM kelas
            ORDER BY nama ASC
        ")->result();

    } else {

        $kelas_list = $this->db->query("
            SELECT id, nama
            FROM kelas WHERE id = ?
        ", [$kelas])->result();
    }

    if (empty($kelas_list)) show_error("Tidak ada data kelas.");

    // ================================
    // 2. Ambil tanggal sesuai filter
    // ================================
    $tanggal_all = [];
    $start = new DateTime($dari);
    $end   = new DateTime($sampai);

    for ($i = $start; $i <= $end; $i->modify('+1 day')) {
        $tanggal_all[] = $i->format('Y-m-d');
    }

    // Kelompokkan tanggal per bulan
    $tanggal_per_bulan = [];
    foreach ($tanggal_all as $tgl) {
        $bulan = date('Y-m', strtotime($tgl));
        if (!isset($tanggal_per_bulan[$bulan])) {
            $tanggal_per_bulan[$bulan] = [];
        }
        $tanggal_per_bulan[$bulan][] = $tgl;
    }

    // ================================
    // 3. Ambil hari libur
    // ================================
    // 3. Ambil daftar libur dari database
$tanggalMerah = $this->Hari_libur_model->get_all_dates();


    // ================================
    // 4. Ambil seluruh absensi detail
    // ================================
    $where = " WHERE a.tanggal BETWEEN ? AND ? ";
    $params = [$dari, $sampai];

    if ($kelas != '' && $kelas != 'all') {
        $where .= " AND a.id_kelas = ? ";
        $params[] = $kelas;
    }

    $qabsen = $this->db->query("
        SELECT d.id_siswa, a.tanggal, d.status
        FROM absensi_detail d
        JOIN absensi a ON a.id_absensi = d.id_absensi
        $where
        ORDER BY a.tanggal ASC
    ", $params)->result();


    // Index absensi
    $absen = [];
    foreach ($qabsen as $r) {

        $tgl = $r->tanggal;
        $hari = date('N', strtotime($tgl));
        $is_libur = in_array($tgl, $tanggalMerah);
        $is_weekend = ($hari == 6 || $hari == 7);

        if ($is_libur || $is_weekend) {
            $absen[$r->id_siswa][$tgl] = 'L';
        } else {

            $st = strtoupper($r->status);

            if ($st == 'SAKIT' || $st == 'S') $kode = 'S';
            elseif ($st == 'IZIN' || $st == 'I') $kode = 'I';
            elseif ($st == 'ALPA' || $st == 'A') $kode = 'A';
            else $kode = 'H';

            $absen[$r->id_siswa][$tgl] = $kode;
        }
    }

    // ================================
    // 5. SETUP PDF
    // ================================
    $this->pdf->setPrintHeader(false);
    $this->pdf->setPrintFooter(false);
    $this->pdf->SetMargins(5, 5, 5);
    $this->pdf->SetAutoPageBreak(true, 5);
    $this->pdf->SetFont('helvetica', '', 8);
    $this->pdf->SetTitle('Rekap Absensi Siswa');
    // ================================
    // 6. LOOP PER KELAS → PER BULAN
    // ================================
    foreach ($kelas_list as $k) {

        // Ambil siswa per kelas
        $siswa = $this->db->query("
            SELECT id, nama 
            FROM siswa
            WHERE id_kelas = ? AND status='aktif'
            ORDER BY nama ASC
        ", [$k->id])->result();

        if (empty($siswa)) continue;

        // Print per bulan juga
        foreach ($tanggal_per_bulan as $bulan => $tgl_bulan) {

            if (empty($tgl_bulan)) continue;

            $data = [
                'siswa'        => $siswa,
                'tanggal'      => $tgl_bulan,
                'absen'        => $absen,
                'tanggalMerah' => $tanggalMerah,
                'nama_kelas'   => $k->nama,
                'bulan_label'  => date('F Y', strtotime($bulan . '-01')),
                'tahun'        => $tahun
            ];

            $html = $this->load->view(
                'absensi/laporan_pdf_bulan',
                $data,
                true
            );

            $this->pdf->AddPage('L', 'A4');
            $this->pdf->writeHTML($html, true, false, true, false, '');
        }
    }

    // ================================
    // 7. OUTPUT PDF
    // ================================
    $filename = "Rekap_Absensi_" . date('Ymd_His') . ".pdf";
    $this->pdf->Output($filename, 'I');
}
public function excel()
{
    // bersihkan buffer supaya header file tidak rusak
    if (ob_get_length()) { ob_end_clean(); }

    $kelas_param  = $this->input->get('kelas');
    $dari_raw     = $this->input->get('dari');
    $sampai_raw   = $this->input->get('sampai');

    if (!$dari_raw || !$sampai_raw) {
        show_error("Filter tanggal wajib diisi.");
    }

    $dari   = date('Y-m-d', strtotime($dari_raw));
    $sampai = date('Y-m-d', strtotime($sampai_raw));

    // load PHPExcel wrapper & buat object
    $this->load->library('PHPExcel_Lib');
    $excel = new PHPExcel();

    // 1) daftar kelas
    if ($kelas_param == "" || $kelas_param == "all") {
        $kelas_list = $this->db->query("SELECT id, nama FROM kelas ORDER BY nama ASC")->result();
        $single_class = false;
    } else {
        $kelas_list = $this->db->query("SELECT id, nama FROM kelas WHERE id = ?", array($kelas_param))->result();
        $single_class = true;
    }

    if (empty($kelas_list)) {
        show_error("Tidak ada data kelas.");
    }

    // 2) buat array tanggal dari dari..sampai
    $tanggal_all = array();
    $start = new DateTime($dari);
    $end   = new DateTime($sampai);
    for ($d = $start; $d <= $end; $d->modify('+1 day')) {
        $tanggal_all[] = $d->format('Y-m-d');
    }

    // 3) kelompokkan tanggal per bulan (format key: YYYY-mm)
    $tanggal_per_bulan = array();
    foreach ($tanggal_all as $tgl) {
        $bulan_key = date('Y-m', strtotime($tgl));
        if (!isset($tanggal_per_bulan[$bulan_key])) {
            $tanggal_per_bulan[$bulan_key] = array();
        }
        $tanggal_per_bulan[$bulan_key][] = $tgl;
    }

    // 4) ambil hari libur (kolom start)
    $q_libur = $this->db->query("SELECT start FROM hari_libur")->result();
    $hariMerah = array();
    foreach ($q_libur as $r) {
        $hariMerah[] = $r->start;
    }

    $sheetIndex = 0;

    // 5) loop per kelas
    foreach ($kelas_list as $k) {

        // Jika single class: buat sheet per bulan saja.
        // Jika multi class: buat sheet per bulan per kelas.
        foreach ($tanggal_per_bulan as $bulan_key => $tgl_bulan) {

            if ($sheetIndex > 0) {
                $excel->createSheet();
            }
            $sheet = $excel->setActiveSheetIndex($sheetIndex);

            // penamaan sheet:
            if ($single_class) {
                // nama sheet = "Nov 2025" style
                $sheetTitle = date('F Y', strtotime($bulan_key . '-01'));
            } else {
                // "KELAS - Month Year" (potong agar <=31 chars)
                $sheetTitle = $k->nama . ' - ' . date('M Y', strtotime($bulan_key . '-01'));
            }

            // pastikan tidak lebih dari 31 karakter untuk sheet title
            $sheet->setTitle(substr($sheetTitle, 0, 31));

            // Header utama
            $sheet->setCellValue('A1', 'REKAP ABSENSI SISWA');
            $sheet->setCellValue('A2', 'KELAS: ' . $k->nama);
            $sheet->setCellValue('A3', 'PERIODE: ' . date('d-m-Y', strtotime($dari)) . ' s/d ' . date('d-m-Y', strtotime($sampai)));
            $sheet->setCellValue('A4', 'BULAN: ' . date('F Y', strtotime($bulan_key . '-01')));

            // Header kolom (baris 6)
            $sheet->setCellValue('A6', 'No');
            $sheet->setCellValue('B6', 'Nama Siswa');

            // tulis tanggal khusus untuk bulan ini
            $col = 'C';
            foreach ($tgl_bulan as $tgl) {
                $sheet->setCellValue($col . '6', date('d', strtotime($tgl)));
                $sheet->getColumnDimension($col)->setWidth(4.5);
                $col++;
            }

            // setelah tanggal, tulis header jumlah
            $sheet->setCellValue($col . '6', 'H'); $col++;
            $sheet->setCellValue($col . '6', 'S'); $col++;
            $sheet->setCellValue($col . '6', 'I'); $col++;
            $sheet->setCellValue($col . '6', 'A'); $col++;
            $sheet->setCellValue($col . '6', 'L');

            // ambil siswa per kelas
            $siswa = $this->db->query("SELECT id, nama FROM siswa WHERE id_kelas = ? AND status='aktif' ORDER BY nama ASC", array($k->id))->result();

            // ambil absensi untuk kelas ini dalam rentang global (dari..sampai)
            $qabsen = $this->db->query("
                SELECT d.id_siswa, a.tanggal, d.status
                FROM absensi_detail d
                JOIN absensi a ON a.id_absensi = d.id_absensi
                WHERE a.tanggal BETWEEN ? AND ?
                AND a.id_kelas = ?
            ", array($dari, $sampai, $k->id))->result();

            // index absensi [id_siswa][tanggal] => kode (S/I/A/H/L)
            $arrAbsen = array();
            foreach ($qabsen as $aa) {
                $tgl = $aa->tanggal;

                // jika tgl termasuk hari merah atau weekend, tetap catat L
                $hariNum = date('N', strtotime($tgl));
                $isWeekend = ($hariNum == 6 || $hariNum == 7);
                $isMerah = in_array($tgl, $hariMerah);

                if ($isWeekend || $isMerah) {
                    $arrAbsen[$aa->id_siswa][$tgl] = 'L';
                } else {
                    $st = strtoupper(trim($aa->status));
                    if ($st == 'SAKIT' || $st == 'S') $kode = 'S';
                    elseif ($st == 'IZIN' || $st == 'I') $kode = 'I';
                    elseif ($st == 'ALPA' || $st == 'A') $kode = 'A';
                    else $kode = 'H';
                    $arrAbsen[$aa->id_siswa][$tgl] = $kode;
                }
            }

            // tulis data siswa mulai baris 7
            $row = 7;
            $no = 1;
            foreach ($siswa as $s) {

                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $s->nama);

                // reset hitungan
                $jumlahH = $jumlahS = $jumlahI = $jumlahA = $jumlahL = 0;

                $col = 'C';
                foreach ($tgl_bulan as $tgl) {

                    $hariNum = date('N', strtotime($tgl));
                    $isWeekend = ($hariNum == 6 || $hariNum == 7);
                    $isMerah   = in_array($tgl, $hariMerah);

                    // default: libur jika weekend/libur nasional, else hadir
                    if ($isWeekend || $isMerah) {
                        $val = 'L';
                    } else {
                        $val = 'H';
                    }

                    // override jika ada detail absensi
                    if (isset($arrAbsen[$s->id][$tgl])) {
                        $val = $arrAbsen[$s->id][$tgl];
                    }

                    // hitung totals
                    if ($val == 'H') $jumlahH++;
                    if ($val == 'S') $jumlahS++;
                    if ($val == 'I') $jumlahI++;
                    if ($val == 'A') $jumlahA++;
                    if ($val == 'L') $jumlahL++;

                    $sheet->setCellValue($col . $row, $val);

                    // jika L beri warna ringan merah
                    if ($val == 'L') {
                        $sheet->getStyle($col . $row)->getFill()->applyFromArray(array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array('rgb' => 'FF9999')
                        ));
                    }

                    $col++;
                }

                // tulis totals di kolom setelah tanggal
                $sheet->setCellValue($col . $row, $jumlahH); $col++;
                $sheet->setCellValue($col . $row, $jumlahS); $col++;
                $sheet->setCellValue($col . $row, $jumlahI); $col++;
                $sheet->setCellValue($col . $row, $jumlahA); $col++;
                $sheet->setCellValue($col . $row, $jumlahL);

                $row++;
                $no++;
            }

            $sheetIndex++;
        } // end foreach bulan

    } // end foreach kelas

    // aktifkan sheet pertama
    $excel->setActiveSheetIndex(0);

    // output ke browser
    $filename = 'Rekap_Absensi_' . date('Ymd_His') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $writer->save('php://output');
    exit;
}

public function data()
{
    $kelas  = $this->input->post('kelas');
    $dari   = $this->input->post('dari');
    $sampai = $this->input->post('sampai');
    $ket    = $this->input->post('keterangan');

    $this->db->select("
        ad.id_detail,
        a.tanggal,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        ad.status
    ");
    $this->db->from("absensi_detail ad");
    $this->db->join("absensi a", "a.id_absensi = ad.id_absensi");
    $this->db->join("siswa s", "s.id = ad.id_siswa");
    $this->db->join("kelas k", "k.id = s.id_kelas");

    if ($kelas != "all" && $kelas != "") {
        $this->db->where("s.id_kelas", $kelas);
    }

    if ($ket != "all" && $ket != "") {
        $this->db->where("ad.status", $ket);
    }

    $this->db->where("a.tanggal >=", $dari);
    $this->db->where("a.tanggal <=", $sampai);
    $this->db->order_by("a.tanggal", "ASC");

    $result = $this->db->get()->result();

    echo json_encode($result);
}

}
