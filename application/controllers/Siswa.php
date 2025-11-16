<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Siswa extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Siswa_model');
    $this->load->library(['form_validation', 'pagination', 'PHPExcel_lib']);
    $this->load->helper(['url', 'form']);
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
    $this->db->order_by('siswa.id', 'DESC');
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

$token = uniqid('qr_');
$qr_file = $qr_folder . $token . '.png';
QRcode::png($token, $qr_file, QR_ECLEVEL_M, 6);


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
  public function edit($id) {
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
        'status' => $this->input->post('status', TRUE)
      ];

      $this->Siswa_model->update($id, $data);
      $this->session->set_flashdata('success', 'Data siswa berhasil diperbarui.');
      redirect('siswa');
    } else {
      $data['siswa'] = $this->Siswa_model->get_by_id($id);
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

  // EXPORT EXCEL
  public function export_excel()
{
    $data = $this->db->get('siswa')->result();
    $fields = $this->db->list_fields('siswa');

    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->setActiveSheetIndex(0);
    $sheet->setTitle('Data Siswa');

    // Header
    $col = 'A';
    foreach ($fields as $f) {
        $sheet->setCellValue($col . '1', strtoupper($f));
        $col++;
    }

    // Rows
    $row = 2;
    foreach ($data as $d) {
        $col = 'A';
        foreach ($fields as $f) {
            $sheet->setCellValue($col . $row, $d->$f);
            $col++;
        }
        $row++;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="data_siswa_full.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
}

public function download_template() {

    // Ambil referensi dropdown
    $kelas = $this->db->select('nama')->get('kelas')->result_array();
    $tahun = $this->db->select('tahun')->get('tahun_ajaran')->result_array();
    $status = ['aktif', 'mutasi_keluar', 'mutasi_masuk', 'lulus', 'keluar'];
    $agama_list = ['Islam','Kristen','Katolik','Hindu','Budha','Konghucu'];

    // === Semua kolom tabel siswa ===
    $fields = [
        'nis','nisn','nik','nama','jk','agama','tempat_lahir','tgl_lahir',
        'alamat','jalan','rt','rw','dusun','kecamatan','kode_pos','jenis_tinggal',
        'alat_transportasi','telp','hp','email','skhun','penerima_kps','no_kps',
        'nama_ayah','tahun_lahir_ayah','pendidikan_ayah','pekerjaan_ayah','penghasilan_ayah','nik_ayah',
        'nama_ibu','tahun_lahir_ibu','pendidikan_ibu','pekerjaan_ibu','penghasilan_ibu','nik_ibu',
        'nama_wali','tahun_lahir_wali','pendidikan_wali','pekerjaan_wali','penghasilan_wali','nik_wali',
        'sekolah_asal','hobi','cita_cita','anak_keberapa','nomor_kk','berat_badan',
        'tinggi_badan','jumlah_saudara_kandung',

        // Bagian relasi
        'kelas',          // id_kelas → pakai nama kelas dropdown
        'tahun_ajaran',   // tahun_id → pakai dropdown tahun
        'status'
    ];

    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->setActiveSheetIndex(0);
    $sheet->setTitle('Template Siswa');

    // === HEADER DINAMIS ===
    $col = 'A';
    foreach ($fields as $f) {
        $sheet->setCellValue($col . '1', strtoupper($f));
        $col++;
    }

    // === SHEET REFERENSI (untuk dropdown) ===
    $objPHPExcel->createSheet();
    $refSheet = $objPHPExcel->setActiveSheetIndex(1);
    $refSheet->setTitle('Referensi');

    // Kelas
    $rowKelas = 1;
    foreach ($kelas as $k) {
        $refSheet->setCellValue("A$rowKelas", $k['nama']);
        $rowKelas++;
    }

    // Tahun
    $rowTahun = 1;
    foreach ($tahun as $t) {
        $refSheet->setCellValue("B$rowTahun", $t['tahun']);
        $rowTahun++;
    }

    // Status
    $rowStatus = 1;
    foreach ($status as $s) {
        $refSheet->setCellValue("C$rowStatus", $s);
        $rowStatus++;
    }

    // Agama
    $rowAgama = 1;
    foreach ($agama_list as $a) {
        $refSheet->setCellValue("D$rowAgama", $a);
        $rowAgama++;
    }

    // Kembali ke sheet utama
    $sheet = $objPHPExcel->setActiveSheetIndex(0);

    // Range dropdown
    $kelasRange  = 'Referensi!$A$1:$A$' . count($kelas);
    $tahunRange  = 'Referensi!$B$1:$B$' . count($tahun);
    $statusRange = 'Referensi!$C$1:$C$' . count($status);
    $agamaRange  = 'Referensi!$D$1:$D$' . count($agama_list);

    // =======================
//   DROPDOWN YANG BENAR
// =======================

for ($i = 2; $i <= 300; $i++) {

    // ==== Cari posisi kolom berdasarkan nama field ====
    $colJK        = PHPExcel_Cell::stringFromColumnIndex(array_search('jk', $fields));
    $colAgama     = PHPExcel_Cell::stringFromColumnIndex(array_search('agama', $fields));
    $colKelas     = PHPExcel_Cell::stringFromColumnIndex(array_search('kelas', $fields));
    $colTahun     = PHPExcel_Cell::stringFromColumnIndex(array_search('tahun_ajaran', $fields));
    $colStatus    = PHPExcel_Cell::stringFromColumnIndex(array_search('status', $fields));

    // ==== DROPDOWN JK ====
    $validJK = $sheet->getCell($colJK . $i)->getDataValidation();
    $validJK->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
    $validJK->setShowDropDown(true);
    $validJK->setAllowBlank(true);
    $validJK->setFormula1('"L,P"');

    // ==== DROPDOWN AGAMA ====
    $validAgama = $sheet->getCell($colAgama . $i)->getDataValidation();
    $validAgama->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
    $validAgama->setShowDropDown(true);
    $validAgama->setAllowBlank(true);
    $validAgama->setFormula1($agamaRange);

    // ==== DROPDOWN KELAS ====
    $validKelas = $sheet->getCell($colKelas . $i)->getDataValidation();
    $validKelas->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
    $validKelas->setShowDropDown(true);
    $validKelas->setAllowBlank(true);
    $validKelas->setFormula1($kelasRange);

    // ==== DROPDOWN TAHUN AJARAN ====
    $validTahun = $sheet->getCell($colTahun . $i)->getDataValidation();
    $validTahun->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
    $validTahun->setShowDropDown(true);
    $validTahun->setAllowBlank(true);
    $validTahun->setFormula1($tahunRange);

    // ==== DROPDOWN STATUS ====
    $validStatus = $sheet->getCell($colStatus . $i)->getDataValidation();
    $validStatus->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
    $validStatus->setShowDropDown(true);
    $validStatus->setAllowBlank(true);
    $validStatus->setFormula1($statusRange);
}


    // Auto width
    $lastColumn = PHPExcel_Cell::stringFromColumnIndex(count($fields) - 1);
    foreach (range('A', $lastColumn) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $objPHPExcel->setActiveSheetIndex(0);

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="template_siswa_full.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
}


 // IMPORT EXCEL (versi tolerant header + auto siswa_tahun)
public function import_excel()
{
    if (!isset($_FILES['file']['name'])) {
        redirect('siswa');
        return;
    }

    // Load file Excel
    $path = $_FILES['file']['tmp_name'];
    $objPHPExcel = PHPExcel_IOFactory::load($path);
    $sheet = $objPHPExcel->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true); // keys A,B,C...

    if (count($rows) < 2) {
        $this->session->set_flashdata('error', 'File Excel kosong atau hanya header.');
        redirect('siswa');
        return;
    }

    // ======= Daftar field yang kita dukung (sesuaikan bila perlu) =======
    $expected_fields = [
        'nis','nisn','nik','nama','jk','agama','tempat_lahir','tgl_lahir','alamat',
        'jalan','rt','rw','dusun','kecamatan','kode_pos','jenis_tinggal','alat_transportasi',
        'telp','hp','email','skhun','penerima_kps','no_kps',
        'nama_ayah','tahun_lahir_ayah','pendidikan_ayah','pekerjaan_ayah','penghasilan_ayah','nik_ayah',
        'nama_ibu','tahun_lahir_ibu','pendidikan_ibu','pekerjaan_ibu','penghasilan_ibu','nik_ibu',
        'nama_wali','tahun_lahir_wali','pendidikan_wali','pekerjaan_wali','penghasilan_wali','nik_wali',
        'sekolah_asal','hobi','cita_cita','anak_keberapa','nomor_kk','berat_badan',
        'tinggi_badan','jumlah_saudara_kandung',
        // relasi / akhir
        'id_kelas','tahun_id','status'
    ];

    // Helper normalize string (header/kelas)
    $normalize = function($s) {
        $s = strtolower(trim((string)$s));
        $s = str_replace(["\r","\n","\t"], ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        $s = str_replace([' ', '_', '.', '-', '–', '—'], '', $s);
        return $s;
    };

    // Build header mapping: Excel column letter -> field name
    $header = $rows[1]; // first row
    $colMap = []; // 'A' => 'nis', ...
    foreach ($header as $col => $text) {
        $norm = $normalize($text);
        if ($norm === '') continue;

        // Direct match against expected field names
        foreach ($expected_fields as $f) {
            if ($norm === $normalize($f)) {
                $colMap[$col] = $f;
                break;
            }
        }
        if (isset($colMap[$col])) continue;

        // Some common synonyms
        $synonyms = [
            'nis' => ['nis','no.nis','no nis','nomor nis'],
            'nisn' => ['nisn','no.nisn','no nisn','nomor nisn','no nis/nisn','nis / nisn'],
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

        // If still not matched, try matching by containing words (e.g. header "Nama Siswa" -> 'nama')
        foreach ($expected_fields as $f) {
            if (strpos($norm, $normalize($f)) !== false) {
                $colMap[$col] = $f;
                break;
            }
        }
    }

    // Inverse mapping: field -> col letter (if present)
    $fieldToCol = [];
    foreach ($colMap as $col => $f) {
        $fieldToCol[$f] = $col;
    }

    // If id_kelas not found but there is a 'kelas' header mapped to something else, normalize it to id_kelas
    if (!isset($fieldToCol['id_kelas'])) {
        foreach ($colMap as $c => $fname) {
            if (in_array($fname, ['kelas','nama_kelas'])) {
                $fieldToCol['id_kelas'] = $c;
                break;
            }
        }
    }

    // Jika tahun_id tidak ada tapi ada 'tahun_ajaran'
    if (!isset($fieldToCol['tahun_id']) && isset($fieldToCol['tahun_ajaran'])) {
        $fieldToCol['tahun_id'] = $fieldToCol['tahun_ajaran'];
    }

    // Ambil daftar kelas dan tahun dari DB untuk pencocokan cepat
    $kelasDB = $this->db->get('kelas')->result();
    $tahunDB = $this->db->get('tahun_ajaran')->result();

    // Pre-normalize kelas DB
    $kelasLookupByNorm = [];
    foreach ($kelasDB as $k) {
        $kelasLookupByNorm[$normalize($k->nama)] = $k->id;
    }

    // Pre-index tahun: by id and by tahun string
    $tahunById = [];
    $tahunByValue = [];
    foreach ($tahunDB as $t) {
        $tahunById[(string)$t->id] = $t->id;
        $tahunByValue[$normalize($t->tahun)] = $t->id;
    }

    $insert = 0;
    $update = 0;
    $gagal = [];

    // Process rows mulai dari baris 2
    foreach ($rows as $rowIdx => $row) {
        if ($rowIdx == 1) continue; // header

        // Build data array
        $data = [];
        foreach ($expected_fields as $field) {
            if ($field === 'id' || $field === 'created_at') continue;
            if (isset($fieldToCol[$field])) {
                $col = $fieldToCol[$field];
                $val = isset($row[$col]) ? trim($row[$col]) : '';
                $data[$field] = $val;
            } else {
                // field tidak ada di file -> kosongkan
                $data[$field] = '';
            }
        }

        // Jika pengguna memakai header 'kelas' (alias) map ke id_kelas
        $kelasRaw = isset($data['id_kelas']) ? $data['id_kelas'] : '';
        $kelasRaw = trim((string)$kelasRaw);

        if ($kelasRaw !== '') {
            // 1) numeric -> coba langsung id
            if (ctype_digit($kelasRaw)) {
                $kelasRow = $this->db->get_where('kelas', ['id' => $kelasRaw])->row();
                if ($kelasRow) {
                    $data['id_kelas'] = $kelasRow->id;
                } else {
                    $gagal[] = "Baris $rowIdx: Kelas tidak valid ('$kelasRaw'). (angka tapi id tidak ditemukan)";
                    continue;
                }
            } else {
                // 2) non-numeric => normalisasi dan cari di lookup
                $kNorm = $normalize($kelasRaw);
                if (isset($kelasLookupByNorm[$kNorm])) {
                    $data['id_kelas'] = $kelasLookupByNorm[$kNorm];
                } else {
                    // Coba pencocokan lebih longgar: hilangkan semua non-alnum lalu compare
                    $kNormLoose = preg_replace('/[^a-z0-9]/', '', strtolower($kelasRaw));
                    $found = false;
                    foreach ($kelasLookupByNorm as $dbNorm => $dbId) {
                        $dbNormLoose = preg_replace('/[^a-z0-9]/', '', $dbNorm);
                        if ($kNormLoose === $dbNormLoose) {
                            $data['id_kelas'] = $dbId;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $gagal[] = "Baris $rowIdx: Kelas tidak valid ('$kelasRaw').";
                        continue;
                    }
                }
            }
        } else {
            // kosong -> biarkan kosong (tidak wajib)
            $data['id_kelas'] = null;
        }

        // Tahun ajaran mapping
        $tahunRaw = isset($data['tahun_id']) ? trim((string)$data['tahun_id']) : '';
        if ($tahunRaw !== '') {
            if (ctype_digit($tahunRaw)) {
                // numeric: bisa id
                if (isset($tahunById[$tahunRaw])) {
                    $data['tahun_id'] = $tahunById[$tahunRaw];
                } else {
                    $gagal[] = "Baris $rowIdx: Tahun ajaran id tidak ditemukan ('$tahunRaw').";
                    continue;
                }
            } else {
                // cari by value
                $tNorm = $normalize($tahunRaw);
                if (isset($tahunByValue[$tNorm])) {
                    $data['tahun_id'] = $tahunByValue[$tNorm];
                } else {
                    $gagal[] = "Baris $rowIdx: Tahun ajaran tidak valid ('$tahunRaw').";
                    continue;
                }
            }
        } else {
            $data['tahun_id'] = null;
        }

        // Pastikan nisn ada (kamu bisa ubah jika mau pakai nis sebagai kunci)
        $nisnVal = isset($data['nisn']) ? trim($data['nisn']) : '';
        if ($nisnVal === '') {
            $gagal[] = "Baris $rowIdx: NISN wajib diisi.";
            continue;
        }

        // Normalize JK: jika ada L/P variasi, ambil huruf pertama uppercase
        if (isset($data['jk']) && $data['jk'] !== '') {
            $jk = strtoupper(substr(trim($data['jk']), 0, 1));
            $data['jk'] = ($jk === 'P') ? 'P' : 'L';
        }

        // Siapkan array yang akan di-insert/update (cocokkan nama kolom DB)
        $save = [];
        foreach ($expected_fields as $f) {
            if ($f == 'id' || $f == 'created_at') continue;
            // Jika field ada dalam data -> masukkan; kosongkan string jadi NULL bila perlu
            $save[$f] = ($data[$f] === '') ? null : $data[$f];
        }

        // Cek ada di DB berdasarkan nisn
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
// ================== GENERATE QR OFFLINE OTOMATIS ==================
if (!$exist || empty($exist->token_qr)) { // hanya jika belum punya token

   require_once APPPATH . 'libraries/phpqrcode/qrlib.php';


    $qr_folder = FCPATH . 'uploads/qr/';
    if (!file_exists($qr_folder)) {
        mkdir($qr_folder, 0777, true);
    }

    $token = uniqid('qr_');
    $qr_file = $qr_folder . $token . '.png';
    QRcode::png($token, $qr_file, QR_ECLEVEL_M, 6);


    // simpan token
    $this->db->where('id', $siswa_id)
             ->update('siswa', ['token_qr' => $token]);
}

        // =============================================================
        // TAMBAHKAN / UPDATE siswa_tahun
        // =============================================================

        // Pastikan kelas dan tahun tidak null
        $kelas_id = $save['id_kelas'];
        $tahun_id = $save['tahun_id'];
        if ($kelas_id && $tahun_id) {

            // Cek apakah data siswa_tahun sudah ada
            $st = $this->db->get_where('siswa_tahun', [
                'siswa_id' => $siswa_id,
                'tahun_id' => $tahun_id
            ])->row();

            if ($st) {
                // Update siswa_tahun saja
                $this->db->where('id', $st->id)->update('siswa_tahun', [
                    'kelas_id' => $kelas_id,
                    'status'   => 'aktif'
                ]);
            } else {
                // Insert baru ke siswa_tahun
                $this->db->insert('siswa_tahun', [
                    'siswa_id' => $siswa_id,
                    'kelas_id' => $kelas_id,
                    'tahun_id' => $tahun_id,
                    'status'   => 'aktif'
                ]);
            }
        }

    } // END foreach rows

    // Build message
    if (!empty($gagal)) {
        $msg = "<b>Import selesai dengan catatan:</b><br>";
        $msg .= "Insert: $insert<br>Update: $update<br><ul>";
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
    $pdf->Output($fileName, 'I'); // I = inline, D = download
}

}
