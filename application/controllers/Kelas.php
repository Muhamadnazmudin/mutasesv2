<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kelas extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Kelas_model');
    $this->load->library(['form_validation', 'pagination', 'PHPExcel_lib']);
    $this->load->helper(['url', 'form']);
  }

  public function index($offset = 0)
{
    // === Konfigurasi Pagination ===
    $config['base_url'] = site_url('kelas/index');
    $config['total_rows'] = $this->Kelas_model->count_all();
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

    // === Ambil data kelas + guru wali ===
    $data['title'] = 'Data Kelas';
    $data['active'] = 'kelas';
    $kelas = $this->Kelas_model->get_all($config['per_page'], $offset);

    // === Tambahkan jumlah siswa aktif tiap kelas ===
    foreach ($kelas as &$k) {
        $k->jumlah_siswa = $this->db->where('id_kelas', $k->id)
                                   ->where('status', 'aktif')
                                   ->count_all_results('siswa');
    }

    // === Data tambahan untuk view ===
    $data['kelas'] = $kelas;
    $data['pagination'] = $this->pagination->create_links();
    $data['guru'] = $this->Kelas_model->get_guru_list();
    $data['start'] = $offset;

    // === Tampilkan ke view ===
    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('kelas/index', $data);
    $this->load->view('templates/footer');
}


  public function add() {
    if ($this->input->post()) {
      $data = [
        'nama' => $this->input->post('nama', TRUE),
        'wali_kelas_id' => $this->input->post('wali_kelas_id', TRUE),
        'kapasitas' => $this->input->post('kapasitas', TRUE)
      ];
      $this->Kelas_model->insert($data);
      redirect('kelas');
    }
  }

  public function edit($id)
{
    $data['kelas'] = $this->Kelas_model->get_by_id($id);
    $data['guru'] = $this->Kelas_model->get_guru_list();
    $data['title'] = 'Edit Kelas';
    $data['active'] = 'kelas';

    if ($this->input->post()) {
        $update_data = [
            'nama' => $this->input->post('nama', TRUE),
            'wali_kelas_id' => $this->input->post('wali_kelas_id', TRUE),
            'kapasitas' => $this->input->post('kapasitas', TRUE)
        ];

        $this->Kelas_model->update($id, $update_data);
        $this->session->set_flashdata('success', 'Data kelas berhasil diperbarui.');
        redirect('kelas');
    }

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('kelas/edit', $data);
    $this->load->view('templates/footer');
}


  public function delete($id) {
    $this->Kelas_model->delete($id);
    redirect('kelas');
  }

  // EXPORT
  public function export_excel() {
    $data = $this->Kelas_model->get_all(10000, 0);
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('A1', 'No')
      ->setCellValue('B1', 'Nama Kelas')
      ->setCellValue('C1', 'Wali Kelas')
      ->setCellValue('D1', 'Kapasitas');

    $no = 1; $row = 2;
    foreach ($data as $k) {
      $objPHPExcel->getActiveSheet()
        ->setCellValue("A$row", $no++)
        ->setCellValue("B$row", $k->nama)
        ->setCellValue("C$row", $k->wali_nama)
        ->setCellValue("D$row", $k->kapasitas);
      $row++;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="data_kelas.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
  }

  // IMPORT
  public function import_excel() {
    if (isset($_FILES['file']['name'])) {
      $path = $_FILES['file']['tmp_name'];
      $objPHPExcel = PHPExcel_IOFactory::load($path);
      $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

      foreach ($sheetData as $key => $row) {
        if ($key == 1) continue;
        $guru = $this->db->get_where('guru', ['nama' => $row['C']])->row();
        $wali_id = $guru ? $guru->id : NULL;
        $data = [
          'nama' => $row['B'],
          'wali_kelas_id' => $wali_id,
          'kapasitas' => $row['D']
        ];
        $this->Kelas_model->insert($data);
      }
    }
    redirect('kelas');
  }
}
