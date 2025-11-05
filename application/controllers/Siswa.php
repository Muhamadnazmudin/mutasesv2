<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Siswa extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Siswa_model');
    $this->load->library(['form_validation', 'pagination', 'PHPExcel_lib']);
    $this->load->helper(['url', 'form']);
  }

  public function index($offset = 0) {
    $this->load->library('pagination');

    // Hitung total siswa aktif saja
    $this->db->where('status', 'aktif');
    $config['base_url'] = site_url('siswa/index');
    $config['total_rows'] = $this->db->count_all_results('siswa');
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;

    $config['full_tag_open'] = '<ul class="pagination justify-content-center">';
    $config['full_tag_close'] = '</ul>';
    $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
    $config['num_tag_close'] = '</span></li>';
    $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
    $config['cur_tag_close'] = '</span></li>';
    $config['next_link'] = '&raquo;';
    $config['prev_link'] = '&laquo;';

    $this->pagination->initialize($config);

    // Ambil data siswa aktif dengan limit & offset
    $this->db->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left');
    $this->db->where('siswa.status', 'aktif');
    $this->db->order_by('siswa.id', 'DESC');
    $data['siswa'] = $this->db->get('siswa', $config['per_page'], $offset)->result();

    $data['title'] = 'Data Siswa Aktif';
    $data['active'] = 'siswa';
    $data['pagination'] = $this->pagination->create_links();
    $data['kelas'] = $this->Siswa_model->get_kelas_list();
    $data['tahun'] = $this->Siswa_model->get_tahun_list();
    $data['start'] = $offset;

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('siswa/index', $data);
    $this->load->view('templates/footer');
}


  public function add() {
    if ($this->input->post()) {
      $data = [
  'nis' => $this->input->post('nis', TRUE),
  'nama' => $this->input->post('nama', TRUE),
  'jk' => $this->input->post('jk', TRUE),
  'agama' => $this->input->post('agama', TRUE),
  'tempat_lahir' => $this->input->post('tempat_lahir', TRUE),
  'tgl_lahir' => $this->input->post('tgl_lahir', TRUE),
  'alamat' => $this->input->post('alamat', TRUE),
  'id_kelas' => $this->input->post('id_kelas', TRUE),
  'tahun_id' => $this->input->post('tahun_id', TRUE),
  'status' => 'aktif'
];
      $this->Siswa_model->insert($data);
      redirect('siswa');
    }
  }

  public function edit($id) {
    if ($this->input->post()) {
     // di function edit()
$data = [
  'nis' => $this->input->post('nis', TRUE),
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

  public function delete($id) {
    $this->Siswa_model->delete($id);
    redirect('siswa');
  }

  // EXPORT EXCEL
  public function export_excel() {
    $data = $this->Siswa_model->get_all(10000, 0);
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('A1', 'No')
      ->setCellValue('B1', 'NIS')
      ->setCellValue('C1', 'Nama')
      ->setCellValue('D1', 'Tempat Lahir')
      ->setCellValue('E1', 'Tanggal Lahir')
      ->setCellValue('F1', 'Alamat')
      ->setCellValue('G1', 'Kelas')
      ->setCellValue('H1', 'Tahun Ajaran')
      ->setCellValue('I1', 'Status');

    $no = 1; $row = 2;
    foreach ($data as $s) {
      $objPHPExcel->getActiveSheet()
        ->setCellValue("A$row", $no++)
        ->setCellValue("B$row", $s->nis)
        ->setCellValue("C$row", $s->nama)
        ->setCellValue("D$row", $s->tempat_lahir)
        ->setCellValue("E$row", $s->tgl_lahir)
        ->setCellValue("F$row", $s->alamat)
        ->setCellValue("G$row", $s->nama_kelas)
        ->setCellValue("H$row", $s->tahun_ajaran)
        ->setCellValue("I$row", ucfirst($s->status));
      $row++;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="data_siswa.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
  }
public function download_template() {
    $kelas = $this->db->select('nama')->get('kelas')->result_array();
    $tahun = $this->db->select('tahun')->get('tahun_ajaran')->result_array();
    $status = ['aktif', 'mutasi_keluar', 'mutasi_masuk', 'lulus', 'keluar'];
    $agama_list = ['Islam','Kristen','Katolik','Hindu','Budha','Konghucu'];

    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->setActiveSheetIndex(0);
    $sheet->setTitle('Template Siswa');

    // HEADER
    $sheet->setCellValue('A1', 'NIS')
          ->setCellValue('B1', 'Nama')
          ->setCellValue('C1', 'JK (L/P)')
          ->setCellValue('D1', 'Agama')
          ->setCellValue('E1', 'Tempat Lahir')
          ->setCellValue('F1', 'Tanggal Lahir (YYYY-MM-DD)')
          ->setCellValue('G1', 'Alamat')
          ->setCellValue('H1', 'Kelas')
          ->setCellValue('I1', 'Tahun Ajaran')
          ->setCellValue('J1', 'Status');

    // SHEET REFERENSI
    $objPHPExcel->createSheet();
    $refSheet = $objPHPExcel->setActiveSheetIndex(1);
    $refSheet->setTitle('Referensi');

    // Data referensi
    $rowKelas = 1;
    foreach ($kelas as $k) {
        $refSheet->setCellValue("A$rowKelas", $k['nama']);
        $rowKelas++;
    }

    $rowTahun = 1;
    foreach ($tahun as $t) {
        $refSheet->setCellValue("B$rowTahun", $t['tahun']);
        $rowTahun++;
    }

    $rowStatus = 1;
    foreach ($status as $s) {
        $refSheet->setCellValue("C$rowStatus", $s);
        $rowStatus++;
    }

    $rowAgama = 1;
    foreach ($agama_list as $a) {
        $refSheet->setCellValue("D$rowAgama", $a);
        $rowAgama++;
    }

    // Kembali ke sheet utama
    $sheet = $objPHPExcel->setActiveSheetIndex(0);

    // Ranges referensi
    $kelasRange  = 'Referensi!$A$1:$A$' . count($kelas);
    $tahunRange  = 'Referensi!$B$1:$B$' . count($tahun);
    $statusRange = 'Referensi!$C$1:$C$' . count($status);
    $agamaRange  = 'Referensi!$D$1:$D$' . count($agama_list);

    // Dropdown: JK, Agama, Kelas, Tahun, Status
    for ($i = 2; $i <= 100; $i++) {
        // JK
        $validJK = $sheet->getCell("C$i")->getDataValidation();
        $validJK->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validJK->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validJK->setAllowBlank(true);
        $validJK->setShowDropDown(true);
        $validJK->setFormula1('"L,P"');
        $sheet->getCell("C$i")->setDataValidation($validJK);

        // Agama
        $validAgama = $sheet->getCell("D$i")->getDataValidation();
        $validAgama->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validAgama->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validAgama->setAllowBlank(true);
        $validAgama->setShowDropDown(true);
        $validAgama->setFormula1($agamaRange);
        $sheet->getCell("D$i")->setDataValidation($validAgama);

        // Kelas
        $validKelas = $sheet->getCell("H$i")->getDataValidation();
        $validKelas->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validKelas->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validKelas->setAllowBlank(true);
        $validKelas->setShowDropDown(true);
        $validKelas->setFormula1($kelasRange);
        $sheet->getCell("H$i")->setDataValidation($validKelas);

        // Tahun
        $validTahun = $sheet->getCell("I$i")->getDataValidation();
        $validTahun->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validTahun->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validTahun->setAllowBlank(true);
        $validTahun->setShowDropDown(true);
        $validTahun->setFormula1($tahunRange);
        $sheet->getCell("I$i")->setDataValidation($validTahun);

        // Status
        $validStatus = $sheet->getCell("J$i")->getDataValidation();
        $validStatus->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validStatus->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validStatus->setAllowBlank(true);
        $validStatus->setShowDropDown(true);
        $validStatus->setFormula1($statusRange);
        $sheet->getCell("J$i")->setDataValidation($validStatus);
    }

    // Auto width
    foreach (range('A','J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Set aktif ke sheet pertama
    $objPHPExcel->setActiveSheetIndex(0);

    // Output file
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="template_siswa.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
}

  // IMPORT EXCEL
  public function import_excel() {
    if (isset($_FILES['file']['name'])) {
        $path = $_FILES['file']['tmp_name'];
        $objPHPExcel = PHPExcel_IOFactory::load($path);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

        $gagal = [];
        foreach ($sheetData as $key => $row) {
            if ($key == 1) continue; // skip header

            // Normalisasi dan cari kelas & tahun
            $kelas_nama = trim(strtolower($row['F']));
            $tahun_nama = trim($row['G']);

            $kelas = $this->db->where('LOWER(nama)', $kelas_nama)->get('kelas')->row();
            $tahun = $this->db->where('tahun', $tahun_nama)->get('tahun_ajaran')->row();

            if (!$kelas || !$tahun) {
                $gagal[] = $row['B'] . ' (' . $row['F'] . ', ' . $row['G'] . ')';
                continue;
            }

            $data = [
                'nis' => trim($row['A']),
                'nama' => trim($row['B']),
                'tempat_lahir' => trim($row['C']),
                'tgl_lahir' => trim($row['D']),
                'alamat' => trim($row['E']),
                'id_kelas' => $kelas->id,
                'tahun_id' => $tahun->id,
                'status' => strtolower(trim($row['H'])) ?: 'aktif'
            ];

            $this->Siswa_model->insert($data);
        }

        if (!empty($gagal)) {
            $msg = 'Import selesai, tetapi beberapa data gagal: <br><ul>';
            foreach ($gagal as $g) $msg .= "<li>$g</li>";
            $msg .= '</ul>';
            $this->session->set_flashdata('error', $msg);
        } else {
            $this->session->set_flashdata('success', 'Semua data siswa berhasil diimport.');
        }
    }

    redirect('siswa');
}

}
