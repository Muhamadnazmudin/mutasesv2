<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class Siswa extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Siswa_model');
    $this->load->library(['form_validation', 'pagination', 'Spreadsheet_Lib']);
    $this->load->helper(['url', 'form']);
    $this->load->library('idcard_lib');
    if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
    }
  }

  public function index($offset = 0)
{
    $this->load->library('pagination');

    // 🔹 Ambil parameter filter dan pencarian dari GET
    $kelas_id = $this->input->get('kelas');
    $search   = $this->input->get('search');
    $limit    = $this->input->get('limit') ?: 10;

    // 🔹 Hitung total baris untuk pagination
    $this->db->from('siswa');
    $this->db->where('status', 'aktif');
    if (!empty($kelas_id)) {
        $this->db->where('id_kelas', $kelas_id);
    }
    if (!empty($search)) {
        $this->db->group_start()
                 ->like('nama', $search)
                 ->or_like('nis', $search)
                 ->or_like('nisn', $search)
                 ->group_end();
    }
    $config['total_rows'] = $this->db->count_all_results();

    // 🔹 Konfigurasi pagination
    $config['base_url'] = site_url('siswa/index');
    $config['per_page'] = $limit;
    $config['uri_segment'] = 3;
    $config['reuse_query_string'] = true;

    // 💅 Bootstrap 5 pagination style
$config['full_tag_open']   = '<nav><ul class="pagination pagination-sm justify-content-center my-3">';
$config['full_tag_close']  = '</ul></nav>';
$config['attributes']      = ['class' => 'page-link'];

$config['first_link']      = '<i class="fas fa-angle-double-left"></i>';
$config['first_tag_open']  = '<li class="page-item">';
$config['first_tag_close'] = '</li>';

$config['last_link']       = '<i class="fas fa-angle-double-right"></i>';
$config['last_tag_open']   = '<li class="page-item">';
$config['last_tag_close']  = '</li>';

$config['next_link']       = '<i class="fas fa-angle-right"></i>';
$config['next_tag_open']   = '<li class="page-item">';
$config['next_tag_close']  = '</li>';

$config['prev_link']       = '<i class="fas fa-angle-left"></i>';
$config['prev_tag_open']   = '<li class="page-item">';
$config['prev_tag_close']  = '</li>';

$config['cur_tag_open']    = '<li class="page-item active"><a class="page-link bg-primary border-primary text-white" href="#">';
$config['cur_tag_close']   = '</a></li>';

$config['num_tag_open']    = '<li class="page-item">';
$config['num_tag_close']   = '</li>';

$config['reuse_query_string'] = true;

    $this->pagination->initialize($config);

    // 🔹 Query utama siswa (dengan join dan filter)
    $this->db->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left');
    $this->db->where('siswa.status', 'aktif');
    if (!empty($kelas_id)) {
        $this->db->where('siswa.id_kelas', $kelas_id);
    }
    if (!empty($search)) {
        $this->db->group_start()
                 ->like('siswa.nama', $search)
                 ->or_like('siswa.nis', $search)
                 ->or_like('siswa.nisn', $search)
                 ->group_end();
    }
    $this->db->order_by('siswa.id', 'ASC');
    $data['siswa'] = $this->db->get('siswa', $limit, $offset)->result();

    // 🔹 Data tambahan untuk tampilan
    $data['title'] = 'Data Siswa Aktif';
    $data['active'] = 'siswa';
    $data['pagination'] = $this->pagination->create_links();
    $data['kelas'] = $this->Siswa_model->get_kelas_list();
    $data['tahun'] = $this->Siswa_model->get_tahun_list();
    $data['start'] = $offset;
    $data['kelas_id'] = $kelas_id;
    $data['search'] = $search;
    $data['limit'] = $limit;

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('siswa/index', $data);
    $this->load->view('templates/footer');
}


  // ========================================
  // TAMBAH SISWA
  // ========================================
  public function add() {
  if ($this->input->post()) {
    $nisn = $this->input->post('nisn', TRUE);

    // 🔹 Cek apakah NISN sudah ada
    $cek = $this->db->get_where('siswa', ['nisn' => $nisn])->row();

    if ($cek) {
      $this->session->set_flashdata('error', 
        'NISN <strong>' . $nisn . '</strong> sudah terdaftar atas nama <strong>' . $cek->nama . '</strong>.');
      redirect('siswa');
      return;
    }

    // 🔹 Data baru
    $data = [
      'nis'          => $this->input->post('nis', TRUE),
      'nisn'         => $nisn,
      'nama'         => $this->input->post('nama', TRUE),
      'jk'           => $this->input->post('jk', TRUE),
      'agama'        => $this->input->post('agama', TRUE),
      'tempat_lahir' => $this->input->post('tempat_lahir', TRUE),
      'tgl_lahir'    => $this->input->post('tgl_lahir', TRUE),
      'alamat'       => $this->input->post('alamat', TRUE),
      'id_kelas'     => $this->input->post('id_kelas', TRUE),
      'tahun_id'     => $this->input->post('tahun_id', TRUE),
      'status'       => 'aktif'
    ];

    $this->Siswa_model->insert($data);
    // ================== GENERATE QR OFFLINE OTOMATIS ==================
require_once APPPATH . 'libraries/phpqrcode/qrlib.php';


$qr_folder = FCPATH . 'uploads/qr/';
if (!file_exists($qr_folder)) {
    mkdir($qr_folder, 0777, true);
}

// $token = uniqid('qr_');
// $qr_file = $qr_folder . $token . '.png';
// QRcode::png($token, $qr_file, QR_ECLEVEL_M, 6);

$token = 'qr_' . $id_siswa;   // QR tetap seumur hidup
$qr_file = $qr_folder . $token . '.png';

if (!file_exists($qr_file)) {
    QRcode::png(
        $token,
        $qr_file,
        QR_ECLEVEL_H,   // 🔥 Tingkat akurasi tinggi → scan super cepat
        10,             // 🔥 Size besar → gampang dibaca kamera
        2               // 🔥 Margin minimal aman
    );
}
// update token siswa
$id_siswa = $this->db->insert_id();
$this->db->where('id', $id_siswa)->update('siswa', ['token_qr' => $token]);

    $this->session->set_flashdata('success', 'Data siswa berhasil ditambahkan.');
    redirect('siswa');
  }
}

  // ========================================
  // EDIT SISWA
  // ========================================
  public function edit($id)
{
    $siswa = $this->Siswa_model->get_by_id($id);

    if ($this->input->post()) {

        $data = [
            'nis' => $this->input->post('nis', TRUE),
            'nisn' => $this->input->post('nisn', TRUE),
            'nama' => $this->input->post('nama', TRUE),
            'jk' => $this->input->post('jk', TRUE),
            'agama' => $this->input->post('agama', TRUE),
            'tempat_lahir' => $this->input->post('tempat_lahir', TRUE),
            'tgl_lahir' => $this->input->post('tgl_lahir', TRUE),
            'alamat' => $this->input->post('alamat', TRUE),
            'id_kelas' => $this->input->post('id_kelas', TRUE),
            'tahun_id' => $this->input->post('tahun_id', TRUE),
            'status' => $this->input->post('status', TRUE),
            'no_hp_ortu' => $this->input->post('no_hp_ortu'),
        ];

        // ============================================
        // 🔥 HANDLE UPLOAD FOTO
        // ============================================
        if (!empty($_FILES['foto']['name'])) {

            // Konfigurasi upload
            $config['upload_path']   = './uploads/foto/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size']      = 2048; // 2MB
            $config['file_name']     = 'foto_' . time();

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('foto')) {

                $upload = $this->upload->data();
                $data['foto'] = $upload['file_name'];

                // Hapus foto lama jika ada
                if (!empty($siswa->foto) && file_exists('./uploads/foto/' . $siswa->foto)) {
                    unlink('./uploads/foto/' . $siswa->foto);
                }

            } else {
                // Jika upload gagal
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('siswa/edit/' . $id);
            }
        }
        // ============================================


        // UPDATE KE DATABASE
        $this->Siswa_model->update($id, $data);

        $this->session->set_flashdata('success', 'Data siswa berhasil diperbarui.');
        redirect('siswa');

    } else {

        // Menampilkan form edit
        $data['siswa'] = $siswa;
        $data['kelas'] = $this->Siswa_model->get_kelas_list();
        $data['tahun'] = $this->Siswa_model->get_tahun_list();
        $data['title'] = 'Edit Siswa';
        $data['active'] = 'siswa';

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('siswa/edit', $data);
        $this->load->view('templates/footer');
    }
}


  // ========================================
  // HAPUS SISWA (cek mutasi dulu)
  // ========================================
  public function delete($id) {
    $ada_mutasi = $this->db->where('siswa_id', $id)->count_all_results('mutasi');
    if ($ada_mutasi > 0) {
      $this->session->set_flashdata('error', 'Siswa ini sudah memiliki data mutasi dan tidak dapat dihapus.');
    } else {
      $this->Siswa_model->delete($id);
      $this->session->set_flashdata('success', 'Data siswa berhasil dihapus.');
    }
    redirect('siswa');
  }

 // ========================================================
// EXPORT EXCEL ANTI SCIENTIFIC NOTATION
// ========================================================
public function export_excel()
{
    @ob_end_clean();
    ob_start();

    $this->load->database();
    $this->load->model("Siswa_model");

    error_reporting(0);

    $data   = $this->db->get('siswa')->result();
    $fields = $this->db->list_fields('siswa');

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->setActiveSheetIndex(0);
    $sheet->setTitle('Data Siswa');

    // ======================== HEADER ========================
    $colIndex = 1;
    foreach ($fields as $f) {
        $sheet->setCellValueByColumnAndRow($colIndex, 1, strtoupper($f));
        $colIndex++;
    }

    // Style header
    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($fields));
    $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
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

    // ======================== DEFINISI FIELD TEXT ========================
    // semua nomor identitas HARUS dipaksa jadi TEXT
    $textFields = [
        'nis', 'nisn', 'nomor_kk', 'nik',
        'nik_ayah', 'nik_ibu', 'nik_wali',
        'no_hp_ortu', 'hp', 'telp'
    ];

    // ======================== DATA ROWS ========================
    $row = 2;
    foreach ($data as $d) {
        $colIndex = 1;
        foreach ($fields as $f) {

            $value = $d->$f;
            $cell  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row;

            // detect otomatis jika angka panjang
            $isLongNumber = is_numeric($value) && strlen((string)$value) >= 12;

            if (in_array($f, $textFields) || $isLongNumber) {

                // Set sebagai string explicit
                $sheet->setCellValueExplicit(
                    $cell,
                    (string)$value,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                // Paksa Excel tidak mengubah format
                $sheet->getStyle($cell)->getNumberFormat()
                        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

            } else {
                // Normal data
                $sheet->setCellValueByColumnAndRow($colIndex, $row, $value);
            }

            $colIndex++;
        }
        $row++;
    }

    // ======================== AUTOSIZE ========================
    for ($i = 1; $i <= count($fields); $i++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    }

    // ======================== OUTPUT ========================
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="data_siswa_full.xls"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
    $writer->save('php://output');

    ob_end_flush();
    exit;
}


public function download_template()
{
    // ambil referensi data
    $kelas       = $this->db->select('nama')->get('kelas')->result_array();
    $tahun       = $this->db->select('tahun')->get('tahun_ajaran')->result_array();
    $status      = ['aktif','mutasi_keluar','mutasi_masuk','lulus','keluar'];
    $agama_list  = ['Islam','Kristen','Katolik','Hindu','Budha','Konghucu'];

    // kolom template (jangan diubah)
    $fields = [
        'nis','nisn','nik','nama','jk','agama','tempat_lahir','tgl_lahir',
        'alamat','jalan','rt','rw','dusun','kecamatan','kode_pos','jenis_tinggal',
        'alat_transportasi','telp','hp','email','skhun','penerima_kps','no_kps',
        'nama_ayah','tahun_lahir_ayah','pendidikan_ayah','pekerjaan_ayah','penghasilan_ayah','nik_ayah',
        'nama_ibu','tahun_lahir_ibu','pendidikan_ibu','pekerjaan_ibu','penghasilan_ibu','nik_ibu',
        'no_hp_ortu',
        'nama_wali','tahun_lahir_wali','pendidikan_wali','pekerjaan_wali','penghasilan_wali','nik_wali',
        'sekolah_asal','hobi','cita_cita','anak_keberapa','nomor_kk','berat_badan',
        'tinggi_badan','jumlah_saudara_kandung',
        'kelas','tahun_ajaran','status'
    ];

    // start excel
    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->setActiveSheetIndex(0);
    $sheet->setTitle('Template Siswa');

    // header
    $colIndex = 1;
    foreach ($fields as $f) {
        $sheet->setCellValueByColumnAndRow($colIndex, 1, strtoupper($f));
        $colIndex++;
    }

    // sheet referensi
    $ref = $spreadsheet->createSheet();
    $ref->setTitle('Referensi');

    // isi referensi kelas
    $i=1;
    foreach ($kelas as $k) {
        $ref->setCellValue("A$i", $k['nama']);
        $i++;
    }

    // isi referensi tahun
    $i=1;
    foreach ($tahun as $t) {
        $ref->setCellValue("B$i", $t['tahun']);
        $i++;
    }

    // isi referensi status
    $i=1;
    foreach ($status as $s) {
        $ref->setCellValue("C$i", $s);
        $i++;
    }

    // isi referensi agama
    $i=1;
    foreach ($agama_list as $a) {
        $ref->setCellValue("D$i", $a);
        $i++;
    }

    // kembali ke sheet utama
    $sheet = $spreadsheet->setActiveSheetIndex(0);

    // range dropdown
    $kelasRange  = "'Referensi'!\$A\$1:\$A\$" . count($kelas);
$tahunRange  = "'Referensi'!\$B\$1:\$B\$" . count($tahun);
$statusRange = "'Referensi'!\$C\$1:\$C\$" . count($status);
$agamaRange  = "'Referensi'!\$D\$1:\$D\$" . count($agama_list);

    // mapping kolom
    $jkCol         = array_search('jk', $fields) + 1;
    $agamaCol      = array_search('agama', $fields) + 1;
    $kelasCol      = array_search('kelas', $fields) + 1;
    $tahunCol      = array_search('tahun_ajaran', $fields) + 1;
    $statusCol     = array_search('status', $fields) + 1;

    // apply validation
    for ($row = 2; $row <= 300; $row++) {

        // JK
        $sheet->getCellByColumnAndRow($jkCol, $row)
              ->getDataValidation()
              ->setType(DataValidation::TYPE_LIST)
              ->setAllowBlank(true)
              ->setShowDropDown(true)
              ->setFormula1('"L,P"');

        // Agama
        $sheet->getCellByColumnAndRow($agamaCol, $row)
              ->getDataValidation()
              ->setType(DataValidation::TYPE_LIST)
              ->setAllowBlank(true)
              ->setShowDropDown(true)
              ->setFormula1($agamaRange);

        // Kelas
        $sheet->getCellByColumnAndRow($kelasCol, $row)
              ->getDataValidation()
              ->setType(DataValidation::TYPE_LIST)
              ->setAllowBlank(true)
              ->setShowDropDown(true)
              ->setFormula1($kelasRange);

        // Tahun
        $sheet->getCellByColumnAndRow($tahunCol, $row)
              ->getDataValidation()
              ->setType(DataValidation::TYPE_LIST)
              ->setAllowBlank(true)
              ->setShowDropDown(true)
              ->setFormula1($tahunRange);

        // Status
        $sheet->getCellByColumnAndRow($statusCol, $row)
              ->getDataValidation()
              ->setType(DataValidation::TYPE_LIST)
              ->setAllowBlank(true)
              ->setShowDropDown(true)
              ->setFormula1($statusRange);
    }

    // autosize columns
    $lastCol = count($fields);
    for ($i=1; $i <= $lastCol; $i++) {
        $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
    }

    // output
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="template_siswa_full.xls"');
    header('Cache-Control: max-age=0');

    $writer = new Xls($spreadsheet);
    $writer->save('php://output');
}



 // IMPORT EXCEL (versi tolerant header + auto siswa_tahun)
public function import_excel()
{
    if (!isset($_FILES['file']['name'])) {
        redirect('siswa');
        return;
    }

    // ============================================================
    // LOAD FILE EXCEL (SUPPORT PHPSPREADSHEET)
    // ============================================================
    $path = $_FILES['file']['tmp_name'];
    $objSpreadsheet = IOFactory::load($path);
    $sheet = $objSpreadsheet->getActiveSheet();

    // FIX: toArray PhpSpreadsheet → key numeric menjadi A,B,C,D...
    $rowsRaw = $sheet->toArray(null, true, true, false);

// Convert numeric index → A,B,C...
$rows = [];
foreach ($rowsRaw as $rIndex => $cols) {
    $newRow = [];
    $colIndex = 1;
    foreach ($cols as $v) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $newRow[$colLetter] = $v;
        $colIndex++;
    }
    $rows[$rIndex] = $newRow;
}

    // ============================================================


    if (count($rows) < 2) {
        $this->session->set_flashdata('error', 'File Excel kosong atau hanya header.');
        redirect('siswa');
        return;
    }

    // ======= Daftar field yang kita dukung =======
    $expected_fields = [
        'nis','nisn','nik','nama','jk','agama','tempat_lahir','tgl_lahir','alamat',
        'jalan','rt','rw','dusun','kecamatan','kode_pos','jenis_tinggal','alat_transportasi',
        'telp','hp','email','skhun','penerima_kps','no_kps',
        'nama_ayah','tahun_lahir_ayah','pendidikan_ayah','pekerjaan_ayah','penghasilan_ayah','nik_ayah',
        'nama_ibu','tahun_lahir_ibu','pendidikan_ibu','pekerjaan_ibu','penghasilan_ibu','nik_ibu',
        'no_hp_ortu',
        'nama_wali','tahun_lahir_wali','pendidikan_wali','pekerjaan_wali','penghasilan_wali','nik_wali',
        'sekolah_asal','hobi','cita_cita','anak_keberapa','nomor_kk','berat_badan',
        'tinggi_badan','jumlah_saudara_kandung',
        'id_kelas','tahun_id','status'
    ];

    // Normalize helper
    $normalize = function($s) {
        $s = strtolower(trim((string)$s));
        $s = str_replace(["\r","\n","\t"], ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        $s = str_replace([' ', '_', '.', '-', '–', '—'], '', $s);
        return $s;
    };

    // Build header mapping: kolom Excel → field DB
    $header = $rows[0];
    $colMap = [];

    foreach ($header as $col => $text) {
        // Normalisasi header agar pasti cocok
$norm = strtolower(trim((string)$text));
$norm = str_replace(["\xc2\xa0", " ", "_", ".", "-", "–", "—"], "", $norm); // hapus NBSP & simbol
$norm = preg_replace('/[^a-z0-9]/', '', $norm); // keep alnum only

        if ($norm === '') continue;

        foreach ($expected_fields as $f) {
            if ($norm === $normalize($f)) {
                $colMap[$col] = $f;
                break;
            }
        }
        if (isset($colMap[$col])) continue;

        // Sinonim
        $synonyms = [
            'nis' => ['nis','no.nis','no nis','nomor nis'],
            'nisn' => [
    'nisn','no.nisn','nonisn','no nisn','nomor nisn','nis/n','nis n','nis n '
],

            'nik' => ['nik','no.nik','no nik'],
            'nama' => ['nama','nama siswa','nama_lengkap'],
            'jk' => ['jk','jenis kelamin','jenis_kelamin'],
            'hp' => ['hp','nohp','handphone','telepon seluler'],
            'telp' => ['telp','telepon'],
            'id_kelas' => ['kelas','id_kelas','nama_kelas','kelas_nama','kelas id','id kelas'],
            'tahun_id' => ['tahun','tahunajaran','tahun_ajaran','tahun id'],
            'no_kps' => ['no_kps','nokps','no kps'],
            'anak_keberapa' => ['anakkeberapa','anak_ke','anak ke'],
            'nomor_kk' => ['nomorkk','no_kk','nomor kk','no kk'],
        ];

        foreach ($synonyms as $field => $variants) {
            foreach ($variants as $v) {
                if ($norm === $normalize($v)) {
                    $colMap[$col] = $field;
                    break 2;
                }
            }
        }

        if (isset($colMap[$col])) continue;

        // Containing words
        foreach ($expected_fields as $f) {
            if (strpos($norm, $normalize($f)) !== false) {
                $colMap[$col] = $f;
                break;
            }
        }
    }

    // Invers mapping
    $fieldToCol = [];
    foreach ($colMap as $col => $f) {
        $fieldToCol[$f] = $col;
    }

    // alias kelas → id_kelas
    if (!isset($fieldToCol['id_kelas'])) {
        foreach ($colMap as $c => $fname) {
            if (in_array($fname, ['kelas','nama_kelas'])) {
                $fieldToCol['id_kelas'] = $c;
                break;
            }
        }
    }

    // alias tahun_id
    if (!isset($fieldToCol['tahun_id']) && isset($fieldToCol['tahun_ajaran'])) {
        $fieldToCol['tahun_id'] = $fieldToCol['tahun_ajaran'];
    }

    // Ambil data kelas & tahun
    $kelasDB = $this->db->get('kelas')->result();
    $tahunDB = $this->db->get('tahun_ajaran')->result();

    $normalize = $normalize; // alias

    $kelasLookupByNorm = [];
    foreach ($kelasDB as $k) {
        $kelasLookupByNorm[$normalize($k->nama)] = $k->id;
    }

    $tahunById = [];
    $tahunByValue = [];
    foreach ($tahunDB as $t) {
        $tahunById[(string)$t->id] = $t->id;
        $tahunByValue[$normalize($t->tahun)] = $t->id;
    }

    $insert = 0;
    $update = 0;
    $gagal = [];

    // ======================================================================
    // LOOP DATA ROW
    // ======================================================================
    foreach ($rows as $rowIdx => $row) {
        if ($rowIdx == 0) continue;

        $data = [];
        foreach ($expected_fields as $field) {
            if ($field === 'id' || $field === 'created_at') continue;
            if (isset($fieldToCol[$field])) {
                $col = $fieldToCol[$field];
                $val = isset($row[$col]) ? trim($row[$col]) : '';
                $data[$field] = $val;
            } else {
                $data[$field] = '';
            }
        }

        // Map kelas
        $kelasRaw = trim((string)($data['id_kelas'] ?? ''));

        if ($kelasRaw !== '') {
            if (ctype_digit($kelasRaw)) {
                $kelasRow = $this->db->get_where('kelas', ['id' => $kelasRaw])->row();
                if ($kelasRow) {
                    $data['id_kelas'] = $kelasRow->id;
                } else {
                    $gagal[] = "Baris $rowIdx: Kelas tidak valid ('$kelasRaw')";
                    continue;
                }
            } else {
                $kNorm = $normalize($kelasRaw);
                if (isset($kelasLookupByNorm[$kNorm])) {
                    $data['id_kelas'] = $kelasLookupByNorm[$kNorm];
                } else {
                    $gagal[] = "Baris $rowIdx: Kelas tidak valid ('$kelasRaw')";
                    continue;
                }
            }
        } else {
            $data['id_kelas'] = null;
        }

        // Map tahun ajaran
        $tahunRaw = trim((string)($data['tahun_id'] ?? ''));
        if ($tahunRaw !== '') {
            if (ctype_digit($tahunRaw)) {
                if (isset($tahunById[$tahunRaw])) {
                    $data['tahun_id'] = $tahunById[$tahunRaw];
                } else {
                    $gagal[] = "Baris $rowIdx: Tahun ajaran id tidak ditemukan ('$tahunRaw')";
                    continue;
                }
            } else {
                $tNorm = $normalize($tahunRaw);
                if (isset($tahunByValue[$tNorm])) {
                    $data['tahun_id'] = $tahunByValue[$tNorm];
                } else {
                    $gagal[] = "Baris $rowIdx: Tahun ajaran tidak valid ('$tahunRaw')";
                    continue;
                }
            }
        } else {
            $data['tahun_id'] = null;
        }

        // NISN wajib
        $nisnVal = trim($data['nisn']);
        if ($nisnVal === '') {
            $gagal[] = "Baris $rowIdx: NISN wajib diisi.";
            continue;
        }

        // JK normalize
        if (!empty($data['jk'])) {
            $data['jk'] = strtoupper(substr($data['jk'], 0, 1)) === 'P' ? 'P' : 'L';
        }

        // Prepare save data
        $save = [];
        foreach ($expected_fields as $f) {
            if ($f === 'id' || $f == 'created_at') continue;
            $save[$f] = ($data[$f] === '') ? null : $data[$f];
        }

        // Insert / Update siswa
        $exist = $this->db->get_where('siswa', ['nisn' => $nisnVal])->row();
        if ($exist) {
            $this->db->where('nisn', $nisnVal)->update('siswa', $save);
            $siswa_id = $exist->id;
            $update++;
        } else {
            $this->db->insert('siswa', $save);
            $siswa_id = $this->db->insert_id();
            $insert++;
        }

        // QR Generator
        if (!$exist || empty($exist->token_qr)) {
            require_once APPPATH . 'libraries/phpqrcode/qrlib.php';

            $qr_folder = FCPATH . 'uploads/qr/';
            if (!file_exists($qr_folder)) mkdir($qr_folder, 0777, true);

            $token = 'qr_' . $siswa_id;
            $qr_file = $qr_folder . $token . '.png';

            if (!file_exists($qr_file)) {
                QRcode::png($token, $qr_file, QR_ECLEVEL_H, 10, 2);
            }

            $this->db->where('id', $siswa_id)->update('siswa', ['token_qr' => $token]);
        }

        // siswa_tahun update
        $kelas_id = $save['id_kelas'];
        $tahun_id = $save['tahun_id'];
        if ($kelas_id && $tahun_id) {
            $st = $this->db->get_where('siswa_tahun', [
                'siswa_id' => $siswa_id,
                'tahun_id' => $tahun_id
            ])->row();

            if ($st) {
                $this->db->where('id', $st->id)->update('siswa_tahun', [
                    'kelas_id' => $kelas_id,
                    'status' => 'aktif'
                ]);
            } else {
                $this->db->insert('siswa_tahun', [
                    'siswa_id' => $siswa_id,
                    'kelas_id' => $kelas_id,
                    'tahun_id' => $tahun_id,
                    'status' => 'aktif'
                ]);
            }
        }
    }

    // Finish message
    if (!empty($gagal)) {
        $msg = "<b>Import selesai dengan catatan:</b><br>Insert: $insert<br>Update: $update<br><ul>";
        foreach ($gagal as $e) $msg .= "<li>$e</li>";
        $msg .= "</ul>";
        $this->session->set_flashdata('error', $msg);
    } else {
        $this->session->set_flashdata('success', "Import selesai. Insert: <b>$insert</b>, Update: <b>$update</b>");
    }

    redirect('siswa');
}


public function cetak($id)
{
    // Ambil data siswa lengkap
    $data['siswa'] = $this->db
        ->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran')
        ->join('kelas', 'kelas.id = siswa.id_kelas', 'left')
        ->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left')
        ->where('siswa.id', $id)
        ->get('siswa')
        ->row();

    if (!$data['siswa']) {
        echo "Data siswa tidak ditemukan.";
        return;
    }

    // Load view HTML
    $html = $this->load->view('siswa/cetak', $data, TRUE);

    // Load TCPDF
    $this->load->library('pdf');

    // Buat objek PDF baru
    $pdf = new Tcpdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

    // Info PDF
    $pdf->SetCreator('Sistem Mutasi');
    $pdf->SetAuthor('Sekolah');
    $pdf->SetTitle('Data Siswa');

    // Margin
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);

    // Auto break
    $pdf->SetAutoPageBreak(TRUE, 10);

    // Tambah halaman
    $pdf->AddPage();

    // Tulis HTML
    $pdf->writeHTML($html, true, false, true, false, '');

    // Nama file
    $fileName = 'Data_Siswa_' . str_replace(' ', '_', $data['siswa']->nama) . '.pdf';

    // Output PDF ke browser
    $mode = $this->input->get('download') ? 'D' : 'I';
$pdf->Output($fileName, $mode);

}
public function cetak_semua()
{
    // Ambil filter dari URL
    $kelas_id = $this->input->get('kelas');
    $search   = $this->input->get('search');

    // Load library ZIP & Idcard_lib
    $this->load->library('zip');
    $this->load->library('Idcard_lib');

    // Ambil data siswa sesuai filter
    $this->db->select('siswa.*, kelas.nama AS nama_kelas');
    $this->db->from('siswa');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->where('siswa.status', 'aktif');

    if (!empty($kelas_id)) {
        $this->db->where('siswa.id_kelas', $kelas_id);
    }

    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('siswa.nama', $search);
        $this->db->or_like('siswa.nis', $search);
        $this->db->or_like('siswa.nisn', $search);
        $this->db->group_end();
    }

    $query = $this->db->get();
    $siswa_list = $query->result();

    if (!$siswa_list) {
        $this->session->set_flashdata('error', 'Tidak ada data siswa sesuai filter.');
        redirect('siswa');
    }

    // =====================================================================
    // ZIP generator per folder kelas
    // =====================================================================
    $folder_zip = [];

    foreach ($siswa_list as $s) {

        // Generate JPG dari library
        $jpgData = $this->idcard_lib->generate($s->id);
        if (!$jpgData) continue;

        // Tentukan nama folder kelas
        $kelas_folder = !empty($s->nama_kelas) ? $s->nama_kelas : "Tanpa_Kelas";

        // Nama file idcard
        $safeName = strtolower(str_replace(" ", "_", $s->nama));
        $fileName = "idcard_" . $safeName . ".jpg";

        // Masukkan ke folder kelas
        $this->zip->add_data($kelas_folder . "/" . $fileName, $jpgData);
    }

    // Nama file ZIP hasil download
    $zipName = "idcard_filtered_" . date("Ymd_His") . ".zip";

    // Download ZIP
    $this->zip->download($zipName);
}

}
