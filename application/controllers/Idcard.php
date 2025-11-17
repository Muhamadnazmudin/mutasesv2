<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Idcard extends CI_Controller {

    /* ==========================================================
       CREATE FULL TRANSPARENT CANVAS WITH ROUNDED CORNERS
       ========================================================== */
    private function roundedCanvas($w, $h, $radius)
    {
        // Canvas transparan
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);

        // Mask (putih 100% opaque)
        $mask = imagecreatetruecolor($w, $h);
        imagesavealpha($mask, true);
        $trans = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        $white = imagecolorallocatealpha($mask, 255, 255, 255, 0);
        imagefill($mask, 0, 0, $trans);

        // Bentuk rounded rectangle di mask
        imagefilledrectangle($mask, $radius, 0, $w - $radius, $h, $white);
        imagefilledrectangle($mask, 0, $radius, $w, $h - $radius, $white);

        imagefilledellipse($mask, $radius, $radius, $radius*2, $radius*2, $white);
        imagefilledellipse($mask, $w - $radius, $radius, $radius*2, $radius*2, $white);
        imagefilledellipse($mask, $radius, $h - $radius, $radius*2, $radius*2, $white);
        imagefilledellipse($mask, $w - $radius, $h - $radius, $radius*2, $radius*2, $white);

        // Apply mask ke canvas
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($mask, $x, $y) >> 24) & 0x7F;
                $color = imagecolorallocatealpha($img, 255, 255, 255, $alpha);
                imagesetpixel($img, $x, $y, $color);
            }
        }

        imagedestroy($mask);
        return $img;
    }

    /* ==========================================================
       DRAW ROUNDED RECT (NO BLACK EDGES)
       ========================================================== */
    private function drawRoundedRect($img, $x1, $y1, $x2, $y2, $radius, $color)
    {
        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);

        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius*2, $radius*2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius*2, $radius*2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius*2, $radius*2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius*2, $radius*2, $color);
    }

    /* ==========================================================
       TEXT CENTER
       ========================================================== */
    private function centerX($width, $fontSize, $font, $text)
    {
        $box = imagettfbbox($fontSize, 0, $font, $text);
        $textW = abs($box[2] - $box[0]);
        return ($width - $textW) / 2;
    }

    /* ==========================================================
       AUTO FIT TEXT
       ========================================================== */
    private function autoFitText($maxWidth, $fontFile, $text, $maxSize, $minSize)
    {
        for ($s = $maxSize; $s >= $minSize; $s--) {
            $box = imagettfbbox($s, 0, $fontFile, $text);
            if (abs($box[2] - $box[0]) <= $maxWidth) return $s;
        }
        return $minSize;
    }

    /* ==========================================================
       SPLIT NAME (BETTER LOGIC)
       ========================================================== */
    private function splitNameTwoLines($name, $maxChars = 22)
    {
        $parts = explode(" ", $name);
        if (count($parts) <= 1) return [$name, ""];

        $line1 = "";
        $line2 = "";

        foreach ($parts as $p) {
            if (strlen($line1 . " " . $p) <= $maxChars) {
                $line1 .= ($line1 ? " " : "") . $p;
            } else {
                $line2 .= ($line2 ? " " : "") . $p;
            }
        }
        return [$line1, $line2];
    }

    /* ==========================================================
       CETAK
       ========================================================== */
    public function cetak($id)
    {
        while (ob_get_level()) ob_end_clean();
        header_remove();

        $siswa = $this->db->get_where("siswa", ["id"=>$id])->row();
        if (!$siswa) die("Data siswa tidak ditemukan.");

        /* ---- QR ---- */
        require_once APPPATH.'libraries/phpqrcode/qrlib.php';
        $qrFolder = FCPATH."assets/qrcodes/";
        if (!is_dir($qrFolder)) mkdir($qrFolder);

        if (!$siswa->token_qr) {
            $token = uniqid("qr_");
            $this->db->where("id",$id)->update("siswa",["token_qr"=>$token]);
            $siswa->token_qr = $token;
        }

        $qrFile = $qrFolder.$siswa->token_qr.".png";
        if (!file_exists($qrFile)) QRcode::png($siswa->token_qr, $qrFile, QR_ECLEVEL_M, 6);

        /* ---- Canvas ---- */
        $W = 700;
        $H = 1100;
        $img = $this->roundedCanvas($W, $H, 60);

        /* ---- Colors ---- */
        $white = imagecolorallocate($img, 255,255,255);
        $blue  = imagecolorallocate($img, 0,82,164);
        $blue2 = imagecolorallocate($img, 0,92,204);
        $dark  = imagecolorallocate($img, 30,30,30);
        $yellow= imagecolorallocate($img, 253,195,0);

        /* ---- Fonts ---- */
        $fontBold = FCPATH."assets/fonts/Roboto-Bold.ttf";

        /* ==========================================================
           HEADER
           ========================================================== */
        imagefilledrectangle($img, 60,0, $W-60,240, $blue);
        imagefilledrectangle($img, 0,60, $W,240, $blue);
        imagefilledellipse($img, 60,60,120,120,$blue);
        imagefilledellipse($img, $W-60,60,120,120,$blue);

        /* --- Logo --- */
        $logo = FCPATH."assets/img/logobonti.png";
        if (file_exists($logo)) {
            $lg = imagecreatefrompng($logo);
            imagecopyresampled($img, $lg, 270,20,0,0,160,160,imagesx($lg),imagesy($lg));
        }

        /* --- School Name --- */
        $school = "SMK NEGERI 1 CILIMUS";
        $xsch = $this->centerX($W, 42, $fontBold, $school);
        imagettftext($img, 42, 0, $xsch, 230, $white, $fontBold, $school);

        /* ==========================================================
           AUTO LAYOUT START
           ========================================================== */
        $y = 260;

        /* ORNAMENTS */
        imagefilledellipse($img, 160,290,20,20,$yellow);
        imagefilledellipse($img, 540,290,20,20,$yellow);

        /* ---- PHOTO CIRCLE ---- */
        $circleD = 330;
        $cx = $W/2;
        $cy = 430;
        imagefilledellipse($img, $cx, $cy, $circleD, $circleD, $blue2);

        $photo = (!empty($siswa->foto) ? FCPATH."uploads/foto/".$siswa->foto : null);

        if ($photo && file_exists($photo)) {
            $pf = imagecreatefromjpeg($photo);
            $fw = imagesx($pf);
            $fh = imagesy($pf);
            $min = min($fw,$fh);

            $crop = imagecreatetruecolor($circleD,$circleD);
            imagesavealpha($crop,true);
            imagefill($crop,0,0,imagecolorallocatealpha($crop,0,0,0,127));

            imagecopyresampled($crop,$pf,0,0,($fw-$min)/2,($fh-$min)/2,$circleD,$circleD,$min,$min);

            for($x=0;$x<$circleD;$x++){
                for($y2=0;$y2<$circleD;$y2++){
                    $dx=$x-$circleD/2; $dy=$y2-$circleD/2;
                    if($dx*$dx+$dy*$dy <= ($circleD/2)*($circleD/2)){
                        $rgb=imagecolorat($crop,$x,$y2);
                        imagesetpixel($img,$cx-$circleD/2+$x,$cy-$circleD/2+$y2,$rgb);
                    }
                }
            }

        } else {
            $parts = explode(" ", strtoupper($siswa->nama));
            $init = substr($parts[0],0,1).substr(end($parts),0,1);
            $fsIni = 85;
            $xIni = $this->centerX($W,$fsIni,$fontBold,$init);
            imagettftext($img,$fsIni,0,$xIni,$cy+30,$white,$fontBold,$init);
        }

        /* ==========================================================
           NAME AUTO LAYOUT
           ========================================================== */
        $y = $cy + ($circleD/2) + 45;

        $full = strtoupper($siswa->nama);
        list($n1, $n2) = $this->splitNameTwoLines($full, 22);

        // line 1
        $fs1 = $this->autoFitText(600,$fontBold,$n1,40,24);
        $x1 = $this->centerX($W,$fs1,$fontBold,$n1);
        imagettftext($img,$fs1,0,$x1,$y,$dark,$fontBold,$n1);
        $y += $fs1 + 10;

        // line 2
        if (trim($n2)!="") {
            $fs2 = $this->autoFitText(600,$fontBold,$n2,36,22);
            $x2 = $this->centerX($W,$fs2,$fontBold,$n2);
            imagettftext($img,$fs2,0,$x2,$y,$dark,$fontBold,$n2);
            $y += $fs2 + 25;
        } else {
            $y += 15;
        }

        /* ==========================================================
           BADGE NIS
           ========================================================== */
        $badgeW = 420;
        $badgeH = 80;
        $bx = ($W-$badgeW)/2;

        $this->drawRoundedRect($img,$bx,$y,$bx+$badgeW,$y+$badgeH,35,$blue2);

        $xNis = $this->centerX($W,38,$fontBold,$siswa->nis);
        imagettftext($img,38,0,$xNis,$y+55,$white,$fontBold,$siswa->nis);

        $y += $badgeH + 40;

        /* ==========================================================
           QR CODE AUTO CENTER
           ========================================================== */
        $qr = imagecreatefrompng($qrFile);
        $qrSize = 260;
        $qrX = ($W - $qrSize) / 2;
        $qrY = $y - 25;   // naik 25px

        imagecopyresampled($img,$qr,$qrX,$qrY,0,0,$qrSize,$qrSize,imagesx($qr),imagesy($qr));
        $y += $qrSize + 40;

        /* ==========================================================
           OUTPUT
           ========================================================== */
           $filename = "idcard_" . strtolower(str_replace(" ", "_", $siswa->nama)) . ".jpg";
        header("Content-Type: image/png");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        imagepng($img,null,9);
        imagedestroy($img);
        exit;
    }
}
