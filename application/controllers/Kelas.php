<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;


class Kelas extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Kelas_model');
    $this->load->library(['form_validation', 'pagination', 'Spreadsheet_Lib']);
    $this->load->helper(['url', 'form']);
    if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
    }
  }

  public function index($offset = 0)
  {
    // === Konfigurasi Pagination ===
    $config['base_url'] = site_url('kelas/index');
    $config['total_rows'] = $this->Kelas_model->count_all();
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;

    // 💅 Pagination Styling (Bootstrap 5)
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

    // Gunakan method fallback: jika model punya get_guru_list_edit gunakan itu, else get_guru_list
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

        $wali_kelas_id = $this->input->post('wali_kelas_id', TRUE);

        // Validasi: jika wali_kelas_id dipilih, pastikan guru belum jadi wali kelas lain
        if ($wali_kelas_id) {
            $cek = $this->db->get_where('kelas', ['wali_kelas_id' => $wali_kelas_id])->row();
            if ($cek) {
                $this->session->set_flashdata('error', 'Guru "' . $this->db->get_where('guru', ['id' => $wali_kelas_id])->row()->nama . '" sudah menjadi wali kelas di: ' . $cek->nama);
                redirect('kelas');
                return;
            }
        }

        // === Simpan kelas ===
        $data = [
            'nama' => $this->input->post('nama', TRUE),
            'wali_kelas_id' => $wali_kelas_id ? $wali_kelas_id : NULL,
            'kapasitas' => $this->input->post('kapasitas', TRUE)
        ];
        $this->Kelas_model->insert($data);

        // === AUTO GENERATE AKUN USER WALIKELAS ===
        if ($wali_kelas_id) {
            $guru = $this->db->get_where('guru', ['id' => $wali_kelas_id])->row();

            // hanya lanjutkan jika guru ada dan memiliki email
            if ($guru && !empty($guru->email)) {

                // cek apakah guru ini sudah punya user (by guru_id OR by email)
                $cekUser = $this->db->get_where('users', ['guru_id' => $wali_kelas_id])->row();
                $cekByEmail = $this->db->get_where('users', ['email' => $guru->email])->row();

                if (!$cekUser && !$cekByEmail) {
                    $password_default = password_hash('guruwali123', PASSWORD_BCRYPT);

                    // insert user (cek roles sudah ada -> asumsinya role_id 3 sudah dibuat)
                    $insert_user = [
                        'username'   => $guru->email,
                        'password'   => $password_default,
                        'nama'       => $guru->nama,
                        'email'      => $guru->email,
                        'role_id'    => 3,  // role walikelas
                        'guru_id'    => $wali_kelas_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    // lakukan insert dengan handling DB error sederhana
                    $this->db->insert('users', $insert_user);
                }
            }
        }

        $this->session->set_flashdata('success', 'Data kelas berhasil ditambahkan.');
        redirect('kelas');
    } else {
        // jika tidak post, redirect ke index
        redirect('kelas');
    }
  }

  public function edit($id)
  {
    $data['kelas']  = $this->Kelas_model->get_by_id($id);
    $data['title']  = 'Edit Kelas';
    $data['active'] = 'kelas';

    // Untuk dropdown guru saat edit: tampilkan guru yg belum menjadi wali + wali saat ini
    if (method_exists($this->Kelas_model, 'get_guru_list_edit')) {
        $data['guru'] = $this->Kelas_model->get_guru_list_edit($id);
    } else {
        // fallback: ambil semua guru yang belum jadi wali OR guru yg saat ini menjadi wali kelas ini
        $wali_now = $data['kelas'] ? $data['kelas']->wali_kelas_id : 0;
        $sql = "
            SELECT g.*
            FROM guru g
            LEFT JOIN kelas k ON k.wali_kelas_id = g.id
            WHERE k.wali_kelas_id IS NULL
               OR g.id = ?
            GROUP BY g.id
            ORDER BY g.nama ASC
        ";
        $data['guru'] = $this->db->query($sql, [$wali_now])->result();
    }

    if ($this->input->post()) {

        $wali_kelas_id = $this->input->post('wali_kelas_id', TRUE);

        // Validasi: jika memilih wali baru, cek apakah guru sudah menjadi wali di kelas lain
        if ($wali_kelas_id) {
            $cek = $this->db->get_where('kelas', ['wali_kelas_id' => $wali_kelas_id])->row();
            if ($cek && $cek->id != $id) {
                $this->session->set_flashdata('error', 'Guru "' . $this->db->get_where('guru', ['id' => $wali_kelas_id])->row()->nama . '" sudah menjadi wali kelas di: ' . $cek->nama);
                redirect('kelas/edit/' . $id);
                return;
            }
        }

        // === UPDATE KELAS ===
        $update_data = [
            'nama'           => $this->input->post('nama', TRUE),
            'wali_kelas_id'  => $wali_kelas_id ? $wali_kelas_id : NULL,
            'kapasitas'      => $this->input->post('kapasitas', TRUE)
        ];
        $this->Kelas_model->update($id, $update_data);

        // === AUTO GENERATE USER WALIKELAS (jika belum ada) ===
        if ($wali_kelas_id) {
            $guru = $this->db->get_where('guru', ['id' => $wali_kelas_id])->row();

            if ($guru && !empty($guru->email)) {
                $cekUser = $this->db->get_where('users', ['guru_id' => $wali_kelas_id])->row();
                $cekByEmail = $this->db->get_where('users', ['email' => $guru->email])->row();

                if (!$cekUser && !$cekByEmail) {
                    $password_default = password_hash('guruwali123', PASSWORD_BCRYPT);
                    $insert_user = [
                        'username'   => $guru->email,
                        'password'   => $password_default,
                        'nama'       => $guru->nama,
                        'email'      => $guru->email,
                        'role_id'    => 3,
                        'guru_id'    => $wali_kelas_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('users', $insert_user);
                }
            }
        }

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
    $this->session->set_flashdata('success', 'Data kelas berhasil dihapus.');
    redirect('kelas');
  }

  // EXPORT
  public function export_excel()
{
    @ob_end_clean();
    ob_start();
    error_reporting(0);

    $data = $this->Kelas_model->get_all(10000, 0);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $sheet->setCellValue('A1', 'No')
          ->setCellValue('B1', 'Nama Kelas')
          ->setCellValue('C1', 'Wali Kelas')
          ->setCellValue('D1', 'Kapasitas');

    // Style header
    $sheet->getStyle('A1:D1')->applyFromArray([
        'font' => ['bold' => true],
        'borders' => ['allBorders' => ['style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
    ]);

    $no = 1;
    $row = 2;

    foreach ($data as $k) {
        $sheet->setCellValue("A$row", $no++);
        $sheet->setCellValue("B$row", $k->nama);
        $sheet->setCellValue("C$row", $k->wali_nama);

        // kapasitas supaya tidak dianggap angka scientific
        $sheet->setCellValueExplicit(
            "D$row",
            (string)$k->kapasitas,
            DataType::TYPE_STRING
        );

        $row++;
    }

    // Auto width
    foreach (range('A', 'D') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Output
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="data_kelas.xls"');
    header('Cache-Control: max-age=0');

    $writer = new Xls($spreadsheet);
    $writer->save('php://output');

    ob_end_flush();
    exit;
}


  // IMPORT
  public function import_excel()
{
    if (!empty($_FILES['file']['name'])) {

        $path = $_FILES['file']['tmp_name'];
        $spreadsheet = IOFactory::load($path);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $key => $row) {
            if ($key == 1) continue; // skip header

            // B = nama kelas
            // C = wali kelas (nama guru)
            // D = kapasitas

            $guru = $this->db->get_where('guru', ['nama' => trim($row['C'])])->row();
            $wali_id = $guru ? $guru->id : NULL;

            // Cek guru sudah dipakai wali kelas lain
            if ($wali_id) {
                $cek = $this->db->get_where('kelas', ['wali_kelas_id' => $wali_id])->row();
                if ($cek) {
                    $wali_id = NULL; // kosongkan
                }
            }

            $data_insert = [
                'nama'          => $row['B'],
                'wali_kelas_id' => $wali_id,
                'kapasitas'     => $row['D']
            ];

            $this->Kelas_model->insert($data_insert);

            // Auto-create user jika wali valid & belum punya akun
            if ($wali_id) {
                $guru = $this->db->get_where('guru', ['id' => $wali_id])->row();
                if ($guru && !empty($guru->email)) {
                    $cekUser = $this->db->get_where('users', ['guru_id' => $wali_id])->row();
                    $cekByEmail = $this->db->get_where('users', ['email' => $guru->email])->row();

                    if (!$cekUser && !$cekByEmail) {
                        $password_default = password_hash('guruwali123', PASSWORD_BCRYPT);
                        $this->db->insert('users', [
                            'username'   => $guru->email,
                            'password'   => $password_default,
                            'nama'       => $guru->nama,
                            'email'      => $guru->email,
                            'role_id'    => 3,
                            'guru_id'    => $wali_id,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }
    }

    $this->session->set_flashdata('success', 'Import selesai.');
    redirect('kelas');
}

}
