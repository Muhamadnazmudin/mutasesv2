<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;


class Mutasi extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model(['Mutasi_model','Siswa_model']);
    $this->load->library(['pagination','form_validation']);
    $this->load->helper(['url','form']);
    if (!$this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
    }
  }

 public function index($offset = 0)
{
    $this->load->library('pagination');
    $data['offset'] = $offset;

    // =============================
    // KONFIGURASI PAGINATION
    // =============================
    $config['base_url'] = site_url('mutasi/index');
    $config['total_rows'] = $this->Mutasi_model->count_all();
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;
    $config['reuse_query_string'] = true;

    // ===== Bootstrap 5 Pagination =====
    $config['full_tag_open']   = '<ul class="pagination justify-content-center">';
$config['full_tag_close']  = '</ul>';

$config['first_link']      = '&laquo;';
$config['first_tag_open']  = '<li class="page-item">';
$config['first_tag_close'] = '</li>';

$config['last_link']       = '&raquo;';
$config['last_tag_open']   = '<li class="page-item">';
$config['last_tag_close']  = '</li>';

$config['next_link']       = '&rsaquo;';
$config['next_tag_open']   = '<li class="page-item">';
$config['next_tag_close']  = '</li>';

$config['prev_link']       = '&lsaquo;';
$config['prev_tag_open']   = '<li class="page-item">';
$config['prev_tag_close']  = '</li>';

$config['cur_tag_open']    = '<li class="page-item active"><span class="page-link">';
$config['cur_tag_close']   = '</span></li>';

$config['num_tag_open']    = '<li class="page-item">';
$config['num_tag_close']   = '</li>';

$config['attributes']      = ['class' => 'page-link'];

    // =============================
    $this->pagination->initialize($config);

    // DATA
    $data['title'] = 'Data Mutasi Siswa';
    $data['active'] = 'mutasi';
    $data['mutasi'] = $this->Mutasi_model->get_all($config['per_page'], $offset);
    $data['pagination'] = $this->pagination->create_links();
    $data['siswa'] = $this->Mutasi_model->get_siswa_aktif();
    $data['kelas'] = $this->Mutasi_model->get_kelas_list();
    $data['tahun'] = $this->Mutasi_model->get_tahun_list();

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('mutasi/index', $data);
    $this->load->view('templates/footer');
}


 public function add()
{
    if ($this->input->post()) {

        $jenis     = $this->input->post('jenis');
        $tahun_id  = $this->input->post('tahun_id');

        // ============ VALIDASI SESSION ============
        $created_by = $this->session->userdata('user_id');
        if (!$created_by) {
            $this->session->set_flashdata('error', 'Session login tidak valid.');
            redirect('auth/logout');
        }

        // ========================================================
        // PROSES MUTASI MASUK (SISWA BARU)
        // ========================================================
        if ($jenis == 'masuk') {

            // ---- Validasi minimal ----
            if (empty($this->input->post('nama_baru'))) {
                $this->session->set_flashdata('error', 'Nama siswa baru wajib diisi.');
                redirect('mutasi');
            }

            // ---- Ambil data siswa baru ----
            $data_siswa = [
    'nis'           => $this->input->post('nis_baru'),
    'nisn'          => $this->input->post('nisn_baru'),
    'nama'          => $this->input->post('nama_baru'),
    'jk'            => $this->input->post('jk_baru'),
    'sekolah_asal'  => $this->input->post('asal_sekolah_baru'),
    'id_kelas'      => $this->input->post('tujuan_kelas_id'),
    'tahun_id'      => $tahun_id,
    'status'        => 'aktif',
];



            // ---- Insert siswa baru ----
            $this->db->insert('siswa', $data_siswa);
            $siswa_id = $this->db->insert_id();
            // ================== GENERATE QR OFFLINE OTOMATIS ==================
require_once APPPATH . 'libraries/phpqrcode/qrlib.php';

$qr_folder = FCPATH . 'uploads/qr/';
if (!file_exists($qr_folder)) {
    mkdir($qr_folder, 0777, true);
}

// Token berdasarkan ID siswa
$token = 'qr_' . $siswa_id;
$qr_file = $qr_folder . $token . '.png';

// Generate QR jika belum ada
if (!file_exists($qr_file)) {
    QRcode::png(
        $token,
        $qr_file,
        QR_ECLEVEL_H,   // kualitas tinggi
        10,             // size besar
        2               // margin
    );
}

// Update token_qr siswa
$this->db->where('id', $siswa_id)->update('siswa', [
    'token_qr' => $token
]);


            // ---- Buat siswa_tahun ----
            $this->db->insert('siswa_tahun', [
                'siswa_id' => $siswa_id,
                'kelas_id' => $this->input->post('tujuan_kelas_id'),
                'tahun_id' => $tahun_id,
                'status'   => 'aktif'
            ]);

            // ---- Upload file mutasi (opsional) ----
            $file_name = null;
            if (!empty($_FILES['berkas']['name'])) {

                $upload_path = './uploads/mutasi/';
                if (!is_dir($upload_path)) mkdir($upload_path, 0777, TRUE);

                $config['upload_path']   = $upload_path;
                $config['allowed_types'] = 'pdf';
                $config['encrypt_name']  = TRUE;
                $config['max_size']      = 512;

                $this->load->library('upload');
                $this->upload->initialize($config);

                if (!$this->upload->do_upload('berkas')) {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('mutasi');
                }

                $file_name = $this->upload->data('file_name');
            }

            // ---- Insert mutasi masuk ----
            $data_mutasi = [
                'siswa_id'        => $siswa_id,
                'kelas_asal_id'   => NULL, // tidak punya kelas asal
                'jenis'           => 'masuk',
                'tanggal'         => $this->input->post('tanggal'),
                'tujuan_kelas_id' => $this->input->post('tujuan_kelas_id'),
                'tujuan_sekolah'  => NULL,
                'alasan'          => $this->input->post('alasan'),
                'nohp_ortu'       => $this->input->post('nohp_ortu'),
                'tahun_id'        => $tahun_id,
                'berkas'          => $file_name,
                'created_by'      => $created_by
            ];

            $this->db->insert('mutasi', $data_mutasi);

            $this->session->set_flashdata('success', 'Mutasi siswa masuk berhasil disimpan.');
            redirect('mutasi');
            return;
        }

        // ========================================================
        // PROSES MUTASI KELUAR (SISWA SUDAH ADA)
        // ========================================================

        $siswa_id = $this->input->post('siswa_id');

        if (empty($siswa_id)) {
            $this->session->set_flashdata('error', 'Siswa belum dipilih.');
            redirect('mutasi');
        }

        // Ambil kelas asal dari siswa_tahun
        $siswa_tahun = $this->db->get_where('siswa_tahun', [
            'siswa_id' => $siswa_id,
            'tahun_id' => $tahun_id
        ])->row();

        $kelas_asal_id = $siswa_tahun ? $siswa_tahun->kelas_id : NULL;

        // ---- Upload file mutasi keluar ----
        $file_name = null;
        if (!empty($_FILES['berkas']['name'])) {

            $upload_path = './uploads/mutasi/';
            if (!is_dir($upload_path)) mkdir($upload_path, 0777, TRUE);

            $config['upload_path']   = $upload_path;
            $config['allowed_types'] = 'pdf';
            $config['encrypt_name']  = TRUE;
            $config['max_size']      = 512;

            $this->load->library('upload');
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('berkas')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('mutasi');
            }

            $file_name = $this->upload->data('file_name');
        }

        // ---- Data mutasi keluar ----
        $data = [
            'siswa_id'        => $siswa_id,
            'kelas_asal_id'   => $kelas_asal_id,
            'jenis'           => 'keluar',
            'jenis_keluar'    => $this->input->post('jenis_keluar'),
            'tanggal'         => $this->input->post('tanggal'),
            'alasan'          => $this->input->post('alasan'),
            'nohp_ortu'       => $this->input->post('nohp_ortu'),
            'tujuan_sekolah'  => $this->input->post('tujuan_sekolah'),
            'tujuan_kelas_id' => NULL,
            'tahun_id'        => $tahun_id,
            'berkas'          => $file_name,
            'created_by'      => $created_by
        ];

        $this->Mutasi_model->mutasi_keluar($data);

        $this->session->set_flashdata('success', 'Mutasi siswa keluar berhasil disimpan.');
        redirect('mutasi');
    }
}


public function edit($id)
{
    $this->form_validation->set_rules('jenis', 'Jenis', 'required');
    $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');

    if ($this->form_validation->run() == FALSE) {
        $this->session->set_flashdata('error', validation_errors());
        redirect('mutasi');
    }

    // Ambil data lama untuk cek file lama
    $mutasi = $this->db->get_where('mutasi', ['id' => $id])->row();

    // === Tambahan input baru ===
    $jenis_keluar = $this->input->post('jenis_keluar'); // ✅ Jenis keluar spesifik
    $nohp_ortu    = $this->input->post('nohp_ortu');    // ✅ Nomor HP orang tua

    // Data utama yang akan diupdate
    $data = [
    'jenis'         => $this->input->post('jenis'),
    'jenis_keluar'  => $jenis_keluar,
    'alasan'        => $this->input->post('alasan'),
    'nohp_ortu'     => $nohp_ortu,
    'tanggal'       => $this->input->post('tanggal'),
    'tahun_id'      => $this->input->post('tahun_id'),
];

// Tujuan
if ($this->input->post('jenis') == 'keluar') {
    $data['tujuan_sekolah'] = $this->input->post('tujuan');
    $data['tujuan_kelas_id'] = NULL;
} else {
    $data['tujuan_kelas_id'] = $this->input->post('tujuan_kelas_id');
    $data['tujuan_sekolah'] = NULL;
}


    // ==== Upload File (Opsional) ====
    if (!empty($_FILES['berkas']['name'])) {
        $upload_path = './uploads/mutasi/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, TRUE);
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = 'pdf';
        $config['max_size']      = 512; // KB
        $config['encrypt_name']  = TRUE;
        $config['detect_mime']   = TRUE;
        $config['remove_spaces'] = TRUE;

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('berkas')) {
            $error = strip_tags($this->upload->display_errors());
            $this->session->set_flashdata('error', 'Upload gagal: '.$error);
            redirect('mutasi');
            return;
        }

        $upload_data = $this->upload->data();
        $file_name = $upload_data['file_name'];

        // Validasi tambahan MIME type
        $mime = mime_content_type($upload_data['full_path']);
        if ($mime != 'application/pdf') {
            unlink($upload_data['full_path']);
            $this->session->set_flashdata('error', 'Upload gagal: file bukan PDF valid (deteksi MIME: '.$mime.').');
            redirect('mutasi');
            return;
        }

        // Hapus file lama jika ada
        if (!empty($mutasi->berkas) && file_exists($upload_path.$mutasi->berkas)) {
            unlink($upload_path.$mutasi->berkas);
        }

        $data['berkas'] = $file_name;
    }

    // === Update ke database ===
    $this->db->where('id', $id)->update('mutasi', $data);

    $this->session->set_flashdata('success', 'Data mutasi berhasil diperbarui.');
    redirect('mutasi');
}



  public function delete($id) {
    $this->Mutasi_model->delete($id);
    redirect('mutasi');
  }
  // =======================================================
// EXPORT EXCEL (pakai Spreadsheet_Lib)
// =======================================================
public function export_excel()
{
    // ambil data mutasi
    $data = $this->Mutasi_model->get_all(10000, 0);

    // gunakan PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->setActiveSheetIndex(0);
    $sheet->setTitle('Data Mutasi');

    // HEADER
    $sheet->setCellValue('A1', 'No');
    $sheet->setCellValue('B1', 'Nama Siswa');
    $sheet->setCellValue('C1', 'Jenis Mutasi');
    $sheet->setCellValue('D1', 'Tanggal');
    $sheet->setCellValue('E1', 'Alasan');
    $sheet->setCellValue('F1', 'Tujuan Kelas');
    $sheet->setCellValue('G1', 'Tujuan Sekolah');
    $sheet->setCellValue('H1', 'Tahun Ajaran');

    // STYLE HEADER
    $sheet->getStyle('A1:H1')->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ]
        ]
    ]);

    // DATA
    $no = 1;
    $row = 2;
    foreach ($data as $m) {

        $sheet->setCellValue("A$row", $no++);
        $sheet->setCellValue("B$row", $m->nama_siswa);
        $sheet->setCellValue("C$row", ucfirst($m->jenis));
        $sheet->setCellValue("D$row", $m->tanggal);
        $sheet->setCellValue("E$row", $m->alasan);
        $sheet->setCellValue("F$row", $m->tujuan_kelas_nama);
        $sheet->setCellValue("G$row", $m->tujuan_sekolah);
        $sheet->setCellValue("H$row", $m->tahun_ajaran);

        $row++;
    }

    // AUTOSIZE
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // OUTPUT XLSX
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="data_mutasi_siswa.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}


public function download_template()
{
    $this->load->library('Spreadsheet_Lib');
    $objSpreadsheet = new Spreadsheet();

    $objSpreadsheet->setActiveSheetIndex(0)
        ->setCellValue('A1', 'No')
        ->setCellValue('B1', 'Nama Siswa')
        ->setCellValue('C1', 'Jenis (masuk/keluar)')
        ->setCellValue('D1', 'Tanggal (YYYY-MM-DD)')
        ->setCellValue('E1', 'Alasan')
        ->setCellValue('F1', 'Tujuan Kelas (ID)')
        ->setCellValue('G1', 'Tujuan Sekolah')
        ->setCellValue('H1', 'Tahun ID');

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="template_import_mutasi.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = Spreadsheet_IOFactory::createWriter($objSpreadsheet, 'Excel5');
    $objWriter->save('php://output');
}

public function import_excel()
{
    $this->load->library('Spreadsheet_Lib');

    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        $path = $_FILES['file']['tmp_name'];
        $objSpreadsheet = Spreadsheet_IOFactory::load($path);
        $sheetData = $objSpreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $key => $row) {
            if ($key == 1) continue; // skip header
            if (empty($row['B'])) continue; // nama siswa kosong → skip

            $data = [
                'siswa_id'        => $this->Mutasi_model->get_siswa_id_by_name($row['B']),
                'jenis'           => strtolower($row['C']),
                'tanggal'         => $row['D'],
                'alasan'          => $row['E'],
                'tujuan_kelas_id' => $row['F'] ?: NULL,
                'tujuan_sekolah'  => $row['G'] ?: NULL,
                'tahun_id'        => $row['H'] ?: NULL,
                'created_by'      => $this->session->userdata('user_id')
            ];

            $this->db->insert('mutasi', $data);
        }
    }

    $this->session->set_flashdata('success', 'Data mutasi berhasil diimport.');
    redirect('mutasi');
}
public function search_siswa() {
    $term  = $this->input->get('term', TRUE);
    $jenis = $this->input->get('jenis', TRUE);

    // pastikan query LIKE tergrup agar filter status tidak bocor
    $this->db->group_start()
             ->like('nama', $term)
             ->or_like('nis', $term)
             ->group_end();

    // filter berdasarkan jenis mutasi
    if ($jenis === 'masuk') {
        $this->db->where('status', 'mutasi_masuk');
    } else {
        $this->db->where('status', 'aktif');
    }

    $this->db->limit(10);
    $query = $this->db->get('siswa');

    $result = [];
    foreach ($query->result() as $row) {
        $result[] = [
            'id' => $row->id,
            'label' => $row->nis . ' - ' . $row->nama,
            'value' => $row->nama
        ];
    }

    echo json_encode($result);
}
public function batalkan($id)
{
    $mutasi = $this->db->get_where('mutasi', ['id' => $id])->row();
    if (!$mutasi) {
        $this->session->set_flashdata('error', 'Data mutasi tidak ditemukan.');
        redirect('mutasi');
    }

    // ============================
    // 1. Set mutasi jadi dibatalkan
    // ============================
    $this->db->where('id', $id)->update('mutasi', [
        'status_mutasi' => 'dibatalkan'
    ]);

    // ============================
    // 2. Kembalikan status siswa
    // ============================
    $updateData = ['status' => 'aktif'];

    if (!empty($mutasi->kelas_asal_id)) {
        $updateData['id_kelas'] = $mutasi->kelas_asal_id;
    }

    $this->db->where('id', $mutasi->siswa_id)->update('siswa', $updateData);

    // ============================
    // 3. FIX UTAMA: perbaiki siswa_tahun
    // ============================
    $st = $this->db->get_where('siswa_tahun', [
        'siswa_id' => $mutasi->siswa_id,
        'tahun_id' => $mutasi->tahun_id
    ])->row();

    if ($st) {
        // Update → kembali aktif
        $this->db->where('id', $st->id)->update('siswa_tahun', [
            'status'   => 'aktif',
            'kelas_id' => $mutasi->kelas_asal_id
        ]);
    } else {
        // Insert → jika row tidak ada
        $this->db->insert('siswa_tahun', [
            'siswa_id' => $mutasi->siswa_id,
            'kelas_id' => $mutasi->kelas_asal_id,
            'tahun_id' => $mutasi->tahun_id,
            'status'   => 'aktif'
        ]);
    }

    // ============================
    // 4. Redirect
    // ============================
    $this->session->set_flashdata('success', 'Mutasi siswa berhasil dibatalkan.');
    redirect('mutasi');
}

}
