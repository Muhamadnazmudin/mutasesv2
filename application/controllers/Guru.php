<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Guru extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Guru_model');
    $this->load->library(['form_validation', 'pagination', 'Spreadsheet_Lib']);
    $this->load->helper(['url', 'form']);
    if (!$this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
    }
  }

  public function index($offset = 0) {
    $config['base_url'] = site_url('guru/index');
    $config['total_rows'] = $this->Guru_model->count_all();
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;

    // pagination style
    $config['full_tag_open'] = '<ul class="pagination justify-content-center">';
    $config['full_tag_close'] = '</ul>';
    $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
    $config['num_tag_close'] = '</span></li>';
    $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
    $config['cur_tag_close'] = '</span></li>';
    $config['next_link'] = '&raquo;';
    $config['prev_link'] = '&laquo;';

    $this->pagination->initialize($config);

    $data['title'] = 'Data Guru';
    $data['active'] = 'guru';
    $data['guru'] = $this->Guru_model->get_all($config['per_page'], $offset);
    $data['pagination'] = $this->pagination->create_links();
    $data['start'] = $offset;

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('guru/index', $data);
    $this->load->view('templates/footer');
  }

  public function add() {
    if ($this->input->post()) {
      $data = [
        'nip' => $this->input->post('nip', TRUE),
        'nama' => $this->input->post('nama', TRUE),
        'email' => $this->input->post('email', TRUE),
        'telp' => $this->input->post('telp', TRUE)
      ];
      $this->Guru_model->insert($data);
      redirect('guru');
    }
  }

  public function edit($id)
{
    $data['guru'] = $this->Guru_model->get_by_id($id);
    $data['title'] = 'Edit Guru';
    $data['active'] = 'guru';

    if ($this->input->post()) {
        // validasi sederhana
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');

        if ($this->form_validation->run() == TRUE) {
            $update_data = [
                'nip'   => $this->input->post('nip', TRUE),
                'nama'  => $this->input->post('nama', TRUE),
                'email' => $this->input->post('email', TRUE),
                'telp'  => $this->input->post('telp', TRUE)
            ];

            $this->Guru_model->update($id, $update_data);
            $this->session->set_flashdata('success', 'Data guru berhasil diperbarui.');
            redirect('guru');
        }
    }

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('guru/edit', $data);
    $this->load->view('templates/footer');
}


  public function delete($id) {
    $this->Guru_model->delete($id);
    redirect('guru');
  }

  // export Excel
  public function export_excel()
{
    @ob_end_clean();
    ob_start();
    error_reporting(0);

    $data = $this->Guru_model->get_all(10000, 0);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'No')
          ->setCellValue('B1', 'NIP')
          ->setCellValue('C1', 'Nama')
          ->setCellValue('D1', 'Email')
          ->setCellValue('E1', 'Telp');

    $no = 1;
    $row = 2;
    foreach ($data as $g) {

        $sheet->setCellValue("A$row", $no++);

        // << FIX NIP BIAR TIDAK 1.9E+17 >>
        $sheet->setCellValueExplicit(
            "B$row",
            $g->nip,
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );

        $sheet->setCellValue("C$row", $g->nama);
        $sheet->setCellValue("D$row", $g->email);
        $sheet->setCellValue("E$row", $g->telp);

        $row++;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="data_guru.xls"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
    $writer->save('php://output');

    ob_end_flush();
    exit;
}



  // import Excel
  public function import_excel()
{
    if (!empty($_FILES['file']['name'])) {
        $path = $_FILES['file']['tmp_name'];

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $key => $row) {
            if ($key == 1) continue; // skip header

            $data = [
                'nip'  => $row['B'],
                'nama' => $row['C'],
                'email'=> $row['D'],
                'telp' => $row['E'],
            ];

            $this->Guru_model->insert($data);
        }
    }

    redirect('guru');
}

}
