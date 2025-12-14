<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kartu_osis_template extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        if (!$this->session->userdata('logged_in')) {
            redirect('dashboard');
            exit;
    }
    }

    // ===========================================================
    // LOAD CONFIG.JSON
    // ===========================================================
    private function loadConfig($template)
    {
        $file = FCPATH . "assets/kartu_templates/$template/config.json";

        if (!file_exists($file)) {
            return false;
        }

        $json = file_get_contents($file);
        return json_decode($json, true); // PHP 5.6 compatible
    }


    // ===========================================================
    // DRAW TEXT WITH TTF
    // ===========================================================
    private function drawText(&$img, $text, $size, $x, $y, $hex, $font, $bold=false)
    {
        $rgb = $this->hexToRgb($hex);
        $color = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);

        $fontFile = $bold
            ? FCPATH . "assets/fonts/Roboto-Bold.ttf"
            : FCPATH . "assets/fonts/Roboto-Regular.ttf";

        imagettftext($img, $size, 0, $x, $y, $color, $fontFile, $text);
    }

    private function hexToRgb($hex)
    {
        $hex = str_replace("#", "", $hex);
        return array(
            hexdec(substr($hex,0,2)),
            hexdec(substr($hex,2,2)),
            hexdec(substr($hex,4,2))
        );
    }


    // ===========================================================
    // GENERATE FRONT IMAGE
    // ===========================================================
    private function generateFront($template, $s)
    {
        $config = $this->loadConfig($template);
        if (!$config || !isset($config['front'])) {
            return false;
        }

        $c = $config['front'];

        // Load background template
        $bgFile = FCPATH . "assets/kartu_templates/$template/front.png";
        if (!file_exists($bgFile)) return false;

        $bg = imagecreatefrompng($bgFile);
        $W = imagesx($bg);
        $H = imagesy($bg);

        $img = imagecreatetruecolor($W, $H);
        imagecopy($img, $bg, 0, 0, 0, 0, $W, $H);
        imagedestroy($bg);

        // ================= FOTO ================
        if ($s->foto != "") {
            $fp = FCPATH . "uploads/foto/" . $s->foto;
            if (file_exists($fp)) {
                $src = imagecreatefromjpeg($fp);
                imagecopyresampled(
                    $img,
                    $src,
                    $c['foto']['x'],
                    $c['foto']['y'],
                    0, 0,
                    $c['foto']['w'],
                    $c['foto']['h'],
                    imagesx($src),
                    imagesy($src)
                );
                imagedestroy($src);
            }
        }

        // ================= NAMA BESAR ================
        if (isset($c['name'])) {
            $this->drawText($img, strtoupper($s->nama),
                $c['name']['size'],
                $c['text']['label_x'],
                $c['name']['y'],
                $c['name']['color'],
                "Roboto",
                $c['name']['bold']
            );
        }

        // ================= FIELD BIODATA (AUTO GAP) ================
        $startY = $c['text']['start_y'];
        $gap    = $c['text']['gap'];

        foreach ($c['fields'] as $f) {

            $label = $f['label'];

            if ($f['key'] == "ttl") {
                $value = strtoupper($s->tempat_lahir) . ", " . date("d-m-Y", strtotime($s->tgl_lahir));
            } else {
                $value = isset($s->{$f['key']}) ? strtoupper($s->{$f['key']}) : "";
            }

            $this->drawText($img, $label,
                $c['text']['size_label'],
                $c['text']['label_x'],
                $startY,
                $c['text']['color_label'],
                "Roboto",
                true
            );

            $this->drawText($img, ":",
                $c['text']['size_label'],
                $c['text']['colon_x'],
                $startY,
                $c['text']['color_label'],
                "Roboto",
                true
            );

            $this->drawText($img, $value,
                $c['text']['size_value'],
                $c['text']['value_x'],
                $startY,
                $c['text']['color_value'],
                "Roboto",
                false
            );

            $startY += $gap;
        }

        // ================= QR CODE DI FRONT =================
        if (isset($c['qr'])) {

            if (!isset($s->token_qr) || trim($s->token_qr) == "") {
                $token = "qr_" . uniqid();
                $this->db->where("id", $s->id)->update("siswa", array("token_qr" => $token));
            } else {
                $token = $s->token_qr;
            }

            $qrFile = FCPATH . "uploads/qr/" . $token . ".png";

            if (!file_exists($qrFile)) {
                require APPPATH . "libraries/phpqrcode/qrlib.php";
                QRcode::png($token, $qrFile, QR_ECLEVEL_H, 8, 2);
            }

            $qr = imagecreatefrompng($qrFile);

            imagecopyresampled(
                $img,
                $qr,
                $c['qr']['x'],
                $c['qr']['y'],
                0, 0,
                $c['qr']['w'],
                $c['qr']['h'],
                imagesx($qr),
                imagesy($qr)
            );

            imagedestroy($qr);
        }

        // ================= TTD / CAP =================
        if (isset($c['kepsek_cap'])) {
            $capFile = FCPATH . "assets/img/cap.png";
            if (file_exists($capFile)) {
                $cap = imagecreatefrompng($capFile);
                imagecopyresampled(
                    $img, $cap,
                    $c['kepsek_cap']['x'], $c['kepsek_cap']['y'],
                    0, 0,
                    $c['kepsek_cap']['w'], $c['kepsek_cap']['h'],
                    imagesx($cap), imagesy($cap)
                );
                imagedestroy($cap);
            }
        }

        if (isset($c['kepsek_ttd'])) {
            $ttdFile = FCPATH . "assets/img/ttdks.png";
            if (file_exists($ttdFile)) {
                $ttd = imagecreatefrompng($ttdFile);
                imagecopyresampled(
                    $img, $ttd,
                    $c['kepsek_ttd']['x'], $c['kepsek_ttd']['y'],
                    0, 0,
                    $c['kepsek_ttd']['w'], $c['kepsek_ttd']['h'],
                    imagesx($ttd), imagesy($ttd)
                );
                imagedestroy($ttd);
            }
        }

        if (isset($c['kepsek_label'])) {
            $this->drawText($img,
                $c['kepsek_label']['text'],
                $c['kepsek_label']['size'],
                $c['kepsek_label']['x'],
                $c['kepsek_label']['y'],
                $c['kepsek_label']['color'],
                "Roboto",
                false
            );
        }

        $this->drawText($img,
            "Drs. Rosidin",
            $c['kepsek_nama']['size'],
            $c['kepsek_nama']['x'],
            $c['kepsek_nama']['y'],
            $c['kepsek_nama']['color'],
            "Roboto",
            true
        );

        $this->drawText($img,
            "NIP. 196707061994031014",
            $c['kepsek_nip']['size'],
            $c['kepsek_nip']['x'],
            $c['kepsek_nip']['y'],
            $c['kepsek_nip']['color'],
            "Roboto",
            false
        );

        return $img;
    }


    // ===========================================================
    // GENERATE BACK
    // ===========================================================
    private function generateBack($template, $s)
    {
        $config = $this->loadConfig($template);
        if (!$config || !isset($config['back'])) return false;

        $bgFile = FCPATH . "assets/kartu_templates/$template/back.png";
        if (!file_exists($bgFile)) return false;

        $bg = imagecreatefrompng($bgFile);
        $W = imagesx($bg);
        $H = imagesy($bg);

        $img = imagecreatetruecolor($W, $H);
        imagecopy($img, $bg, 0, 0, 0, 0, $W, $H);
        imagedestroy($bg);

        $qr = $config['back']['qr'];

        if (!isset($s->token_qr) || trim($s->token_qr) == "") {
            $token = "qr_" . uniqid();
            $this->db->where("id", $s->id)->update("siswa", array("token_qr" => $token));
        } else {
            $token = $s->token_qr;
        }

        $qrFile = FCPATH . "uploads/qr/" . $token . ".png";
        if (!file_exists($qrFile)) {
            require APPPATH . "libraries/phpqrcode/qrlib.php";
            QRcode::png($token, $qrFile, QR_ECLEVEL_H, 8, 2);
        }

        $qimg = imagecreatefrompng($qrFile);
        imagecopyresampled(
            $img, $qimg,
            $qr['x'], $qr['y'],
            0, 0,
            $qr['w'], $qr['h'],
            imagesx($qimg), imagesy($qimg)
        );
        imagedestroy($qimg);

        return $img;
    }


    // ===========================================================
    // OUTPUT FRONT JPG
    // ===========================================================
    public function front($template, $id)
    {
        $s = $this->db->get_where("siswa", array("id" => $id))->row();
        if (!$s) { echo "Data tidak ditemukan"; return; }

        $img = $this->generateFront($template, $s);
        if (!$img) { echo "Template tidak valid"; return; }

        header("Content-Type: image/png");
        imagepng($img);
        imagedestroy($img);
    }

    // ===========================================================
    // OUTPUT BACK JPG
    // ===========================================================
    public function back($template, $id)
    {
        $s = $this->db->get_where("siswa", array("id" => $id))->row();
        if (!$s) { echo "Data tidak ditemukan"; return; }

        $img = $this->generateBack($template, $s);
        if (!$img) { echo "Template tidak valid"; return; }

        header("Content-Type: image/png");
        imagepng($img);
        imagedestroy($img);
    }

    // ===========================================================
    // PDF OUTPUT
    // ===========================================================
    public function pdf($template, $id)
    {
        $s = $this->db->get_where("siswa", array("id" => $id))->row();
        if (!$s) { echo "Data siswa tidak ditemukan"; return; }

        $front = $this->generateFront($template, $s);
        $back  = $this->generateBack($template, $s);

        if (!$front || !$back) {
            echo "Template tidak valid atau file PNG tidak ditemukan";
            return;
        }

        $tmpDir = FCPATH . "uploads/tmp/";
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);

        $fileFront = $tmpDir . "osis_front_" . $id . ".png";
        $fileBack  = $tmpDir . "osis_back_" . $id . ".png";

        imagepng($front, $fileFront);
        imagepng($back,  $fileBack);

        imagedestroy($front);
        imagedestroy($back);

        $this->load->library('pdf');
        $pdf = new Tcpdf('L', 'mm', array(86, 54), true, 'UTF-8');
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        $pdf->AddPage();
        $pdf->Image($fileFront, 0, 0, 86, 54, 'PNG');

        $pdf->AddPage();
        $pdf->Image($fileBack, 0, 0, 86, 54, 'PNG');

        $pdfName = "kartu_osis_" . strtolower(str_replace(" ", "_", $s->nama)) . ".pdf";
        $pdf->Output($pdfName, "I");

        @unlink($fileFront);
        @unlink($fileBack);
    }

    // ===========================================================
    // INDEX + PAGINATION + FILTER
    // ===========================================================
    public function index()
    {
        // -----------------------------
        // FILTER
        // -----------------------------
        $kelas_id = isset($_GET['kelas']) ? $_GET['kelas'] : "";
        $limit    = isset($_GET['limit']) ? $_GET['limit'] : 25;
        if (!is_numeric($limit)) $limit = 25;

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        if (!is_numeric($page) || $page < 1) $page = 1;

        $offset = ($page - 1) * $limit;

        // -----------------------------
        // QUERY KELAS
        // -----------------------------
        $kelas = $this->db->get("kelas")->result();

        // -----------------------------
        // TOTAL DATA
        // -----------------------------
        if ($kelas_id != "") {
            $this->db->where("id_kelas", $kelas_id);
        }
        $total = $this->db->count_all_results("siswa");

        // -----------------------------
        // DATA PAGE
        // -----------------------------
        $this->db->select("siswa.*, kelas.nama AS nama_kelas");
        $this->db->join("kelas", "kelas.id = siswa.id_kelas", "left");

        if ($kelas_id != "") {
            $this->db->where("siswa.id_kelas", $kelas_id);
        }

        $this->db->limit($limit, $offset);
        $this->db->order_by("siswa.nama", "ASC");

        $siswa = $this->db->get("siswa")->result();

        // -----------------------------
        // PAGINATION MANUAL (PHP 5.6)
        // -----------------------------
        $totalPages = ceil($total / $limit);

        $pagination = "<nav><ul class='pagination'>";

        for ($p=1; $p <= $totalPages; $p++) {

            $active = ($p == $page) ? "active" : "";

            $pagination .= "<li class='page-item $active'>
                <a class='page-link' href='?page=$p&limit=$limit&kelas=$kelas_id'>$p</a>
            </li>";
        }

        $pagination .= "</ul></nav>";

        // -----------------------------
        // SEND TO VIEW
        // -----------------------------
        $data['kelas'] = $kelas;
        $data['siswa'] = $siswa;
        $data['pagination'] = $pagination;

        $data['kelas_id'] = $kelas_id;
        $data['limit']    = $limit;
        $data['start']    = $offset;

        $data['active'] = 'kartu_osis';

        $this->load->view("templates/header", $data);
        $this->load->view("templates/sidebar", $data);
        $this->load->view("kartu_osis/index", $data);
        $this->load->view("templates/footer", $data);
    }

}
