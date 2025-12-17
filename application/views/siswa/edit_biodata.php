<?php
// view: edit_biodata_full.php
// Pastikan $siswa tersedia dari controller
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();

// nomor tujuan WA (ngadmin)
$owner_phone_raw = "085651414221";
$owner_phone = preg_replace('/[^0-9]/', '', $owner_phone_raw);
if (substr($owner_phone, 0, 1) === '0') $owner_phone = '62' . substr($owner_phone, 1);

// --- daftar pilihan (dipakai berulang) ---
$pendidikan_list = [
    "Tidak Sekolah",
    "SD / sederajat",
    "SMP / sederajat",
    "SMA / sederajat",
    "D1", "D2", "D3", "D4 / S1",
    "S2", "S3"
];

$pekerjaan_list = [
    "Tidak Bekerja",
    "Sudah Meninggal",
    "Buruh", "Petani", "Nelayan",
    "Pedagang Kecil", "Pedagang Besar",
    "Karyawan Swasta", "Wiraswasta", "Wirausaha",
    "Guru/Dosen",
    "ASN/PNS", "Polisi", "TNI",
    "Perangkat Desa",
    "Pensiunan"
];

$penghasilan_list = [
    "Tidak Berpenghasilan",
    "Kurang dari Rp 500,000",
    "Rp. 500,000 - Rp. 999,999",
    "Rp. 1,000,000 - Rp. 1,999,999",
    "Rp. 2,000,000 - Rp. 4,999,999",
    "Rp. 5,000,000 - Rp. 20,000,000",
    "Lebih dari Rp 20,000,000"
];
?>

<div class="container-fluid">
    <!-- Flash -->
    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <?= $this->session->flashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <?= $this->session->flashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Judul -->
    <div class="text-center mb-4">
        <h3 class="font-weight-bold text-primary">Edit Biodata Siswa</h3>
        <p class="text-muted">Perbarui data diri anda dengan benar</p>
    </div>

    <style>
        .section-title {
            font-size: 16px;
            font-weight: bold;
            background: #e8f1ff;
            padding: 10px 12px;
            border-left: 4px solid #4e73df;
            margin-top: 25px;
            border-radius: 4px;
        }
        .bio-table td {
            padding: 8px;
            border: 1px solid #dce3f1;
            vertical-align: top;
        }
        .bio-label {
            font-weight: 600;
            width: 35%;
            background: #f8f9fc;
        }
        .bio-table input, .bio-table textarea, .bio-table select {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #cfd5e2;
            border-radius: 4px;
            font-size: 14px;
        }

        /* WA floating style (modern) */
.wa-floating {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 60px;
    height: 60px;

    /* 🔒 DEFAULT: belum ada perubahan */
    background: transparent;
    border: 2px dashed #9a9a9aff;
    border-radius: 50%;

    display: flex;
    justify-content: center;
    align-items: center;

    box-shadow: none;
    z-index: 99999;
    cursor: default;

    pointer-events: none;
    opacity: 0.5;

    transition: all .25s ease;
}

/* ✅ AKTIF: setelah simpan perubahan */
.wa-floating.enabled {
    background: #25d366;
    border: none;
    box-shadow: 0 6px 16px rgba(37,211,102,.45);

    pointer-events: auto;
    cursor: pointer;
    opacity: 1;

    animation: pulse 1.6s infinite;
}

/* icon */
.wa-icon {
    width: 32px;
    height: 32px;
    filter: grayscale(100%);
    transition: filter .25s ease;
}

/* icon ikut aktif */
.wa-floating.enabled .wa-icon {
    filter: grayscale(0%);
}

@keyframes pulse {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.12); }
    100% { transform: scale(1); }
}

@media (max-width: 576px) {
    .wa-floating {
        width: 52px;
        height: 52px;
        bottom: 18px;
        right: 18px;
    }
    .wa-icon { width: 28px; height: 28px; }
}

input[readonly] {
    background: #f3f6fb;
}

        
    </style>

    <!-- FORM -->
    <form id="biodataForm" method="post" action="<?= site_url('SiswaDashboard/update_biodata') ?>">
        <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">
        <input type="hidden" id="old_data" value='<?= htmlspecialchars(json_encode($siswa), ENT_QUOTES, "UTF-8") ?>'>

        <div class="card shadow mb-4">
            <div class="card-body">

                <!-- A. DATA PRIBADI -->
                <div class="section-title">A. DATA PRIBADI</div>
                <table class="table bio-table">
                    <tr>
                        <td class="bio-label">Nama Lengkap</td>
                        <td><input readonly type="text" name="nama" value="<?= $siswa->nama ?>"></td>
                    </tr>

                    <tr>
                        <td class="bio-label">NIS</td>
                        <td><input readonly type="text" name="nis" value="<?= $siswa->nis ?>"></td>
                    </tr>

                    <tr>
                        <td class="bio-label">Jenis Kelamin</td>
                        <td>
                            <input type="text"
                                   name="jk"
                                   value="<?= $siswa->jk ?>"
                                   maxlength="1"
                                   oninput="this.value = this.value.toUpperCase().replace(/[^LP]/g,'');"
                                   placeholder="L / P">
                        </td>
                    </tr>

                    <tr>
                        <td class="bio-label">NISN</td>
                        <td><input readonly type="text" name="nisn" value="<?= $siswa->nisn ?>"></td>
                    </tr>

                    <tr>
                        <td class="bio-label">Tempat Lahir</td>
                        <td><input type="text" name="tempat_lahir" value="<?= $siswa->tempat_lahir ?>"readonly></td>
                    </tr>

                    <tr>
    <td class="bio-label">Tanggal Lahir</td>
    <td>

        <?php
            // convert 2009-01-02 -> 02-01-2009
            $tgl_display = "";
            if (!empty($siswa->tgl_lahir)) {
                $exp = explode("-", $siswa->tgl_lahir);
                if (count($exp) == 3) {
                    $tgl_display = $exp[2] . "-" . $exp[1] . "-" . $exp[0];
                }
            }
        ?>

        <input 
            type="text" 
            id="tgl_lahir_display" 
            class="form-control"
            value="<?= $tgl_display ?>"
            placeholder="dd-mm-yyyy"
            autocomplete="off">

        <input type="hidden" 
               name="tgl_lahir" 
               id="tgl_lahir"
               value="<?= $siswa->tgl_lahir ?>">
    </td>
</tr>


                    <tr>
                        <td class="bio-label">Nomor KK</td>
                        <td><input type="text" name="nomor_kk" value="<?= $siswa->nomor_kk ?>"></td>
                    </tr>

                    <tr>
                        <td class="bio-label">NIK</td>
                        <td><input readonly type="text" name="nik" value="<?= $siswa->nik ?>"></td>
                    </tr>

                    <tr>
                        <td class="bio-label">Anak Ke</td>
                        <td><input type="text" name="anak_keberapa" value="<?= $siswa->anak_keberapa ?>"></td>
                    </tr>

                    <tr>
                        <td class="bio-label">Agama</td>
                        <td><input readonly type="text" name="agama" value="<?= $siswa->agama ?>"></td>
                    </tr>

                    <tr>
                        <td class="bio-label">Alamat</td>
                        <td><textarea name="alamat" rows="2"><?= $siswa->alamat ?></textarea></td>
                    </tr>

                    <tr><td class="bio-label">RT</td><td><input type="text" name="rt" value="<?= $siswa->rt ?>"></td></tr>
                    <tr><td class="bio-label">RW</td><td><input type="text" name="rw" value="<?= $siswa->rw ?>"></td></tr>
                    <tr><td class="bio-label">Dusun</td><td><input type="text" name="dusun" value="<?= $siswa->dusun ?>"></td></tr>
                    <tr><td class="bio-label">Kecamatan</td><td><input type="text" name="kecamatan" value="<?= $siswa->kecamatan ?>"></td></tr>
                    <tr><td class="bio-label">Kode POS</td><td><input type="text" name="kode_pos" value="<?= $siswa->kode_pos ?>"></td></tr>
                    <tr><td class="bio-label">Telp Rumah</td><td><input type="text" name="telp" value="<?= $siswa->telp ?>"></td></tr>
                    <tr><td class="bio-label">No HP</td><td><input type="text" name="hp" value="<?= $siswa->hp ?>"></td></tr>
                    <tr><td class="bio-label">Email</td><td><input type="email" name="email" value="<?= $siswa->email ?>"></td></tr>
                </table>

                <!-- B. KESEJAHTERAAN -->
                <!-- <div class="section-title">B. KESEJAHTERAAN PESERTA DIDIK</div>
                <table class="table bio-table">
                    <tr><td class="bio-label">Penerima KPS</td><td><input type="text" name="penerima_kps" value="<?= $siswa->penerima_kps ?>"></td></tr>
                    <tr><td class="bio-label">Nomor KPS</td><td><input type="text" name="no_kps" value="<?= $siswa->no_kps ?>"></td></tr>
                </table> -->

                <!-- C. DATA PERIODIK -->
                <div class="section-title">C. DATA PERIODIK</div>
                <table class="table bio-table">
                    <tr><td class="bio-label">Tinggi Badan</td><td><input type="text" name="tinggi_badan" value="<?= $siswa->tinggi_badan ?>"></td></tr>
                    <tr><td class="bio-label">Berat Badan</td><td><input type="text" name="berat_badan" value="<?= $siswa->berat_badan ?>"></td></tr>
                    <tr><td class="bio-label">Hobi</td><td><input type="text" name="hobi" value="<?= $siswa->hobi ?>"></td></tr>
                    <tr><td class="bio-label">Cita-cita</td><td><input type="text" name="cita_cita" value="<?= $siswa->cita_cita ?>"></td></tr>
                </table>

                <!-- D. DATA PENDIDIKAN -->
                <div class="section-title">D. DATA PENDIDIKAN</div>
                <table class="table bio-table">
                    <tr><td class="bio-label">Sekolah Asal</td><td><input type="text" name="sekolah_asal" value="<?= $siswa->sekolah_asal ?>"></td></tr>
                    <tr><td class="bio-label">Nomor Ijazah SMP</td><td><input type="text" name="skhun" value="<?= $siswa->skhun ?>"></td></tr>
                </table>

                <!-- E. DATA AYAH KANDUNG -->
                <div class="section-title">E. DATA AYAH KANDUNG</div>
                <table class="table bio-table">
                    <tr><td class="bio-label">Nama Ayah</td><td><input type="text" name="nama_ayah" value="<?= $siswa->nama_ayah ?>"></td></tr>
                    <tr><td class="bio-label">NIK Ayah</td><td><input type="text" name="nik_ayah" value="<?= $siswa->nik_ayah ?>"></td></tr>
                    <tr><td class="bio-label">Tahun Lahir Ayah</td><td><input type="text" name="tahun_lahir_ayah" value="<?= $siswa->tahun_lahir_ayah ?>"></td></tr>

                    <tr>
                        <td class="bio-label">Pendidikan Ayah</td>
                        <td>
                            <select name="pendidikan_ayah" id="pendidikan_ayah" class="form-control">
                                <?php foreach ($pendidikan_list as $p): ?>
                                    <option value="<?= $p ?>" <?= ($siswa->pendidikan_ayah == $p ? 'selected' : '') ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="bio-label">Pekerjaan Ayah</td>
                        <td>
                            <select name="pekerjaan_ayah" id="pekerjaan_ayah" class="form-control">
                                <?php foreach ($pekerjaan_list as $p): ?>
                                    <option value="<?= $p ?>" <?= ($siswa->pekerjaan_ayah == $p ? 'selected' : '') ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="bio-label">Penghasilan Ayah</td>
                        <td>
                            <select name="penghasilan_ayah" id="penghasilan_ayah" class="form-control">
                                <?php foreach ($penghasilan_list as $p): ?>
                                    <option value="<?= $p ?>" <?= ($siswa->penghasilan_ayah == $p ? 'selected' : '') ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <!-- F. DATA IBU KANDUNG -->
                <div class="section-title">F. DATA IBU KANDUNG</div>
                <table class="table bio-table">
                    <tr><td class="bio-label">Nama Ibu</td><td><input type="text" name="nama_ibu" value="<?= $siswa->nama_ibu ?>"></td></tr>
                    <tr><td class="bio-label">NIK Ibu</td><td><input type="text" name="nik_ibu" value="<?= $siswa->nik_ibu ?>"></td></tr>
                    <tr><td class="bio-label">Tahun Lahir Ibu</td><td><input type="text" name="tahun_lahir_ibu" value="<?= $siswa->tahun_lahir_ibu ?>"></td></tr>

                    <tr>
                        <td class="bio-label">Pendidikan Ibu</td>
                        <td>
                            <select name="pendidikan_ibu" id="pendidikan_ibu" class="form-control">
                                <?php foreach ($pendidikan_list as $p): ?>
                                    <option value="<?= $p ?>" <?= ($siswa->pendidikan_ibu == $p ? 'selected' : '') ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="bio-label">Pekerjaan Ibu</td>
                        <td>
                            <select name="pekerjaan_ibu" id="pekerjaan_ibu" class="form-control">
                                <?php foreach ($pekerjaan_list as $p): ?>
                                    <option value="<?= $p ?>" <?= ($siswa->pekerjaan_ibu == $p ? 'selected' : '') ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="bio-label">Penghasilan Ibu</td>
                        <td>
                            <select name="penghasilan_ibu" id="penghasilan_ibu" class="form-control">
                                <?php foreach ($penghasilan_list as $p): ?>
                                    <option value="<?= $p ?>" <?= ($siswa->penghasilan_ibu == $p ? 'selected' : '') ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <!-- G. DATA WALI (with punya wali radio) -->
                <div class="section-title">G. DATA WALI</div>

                <div class="mb-3">
                    <label class="bio-label d-block">Mempunyai Wali?</label>
                    <label style="margin-right:12px;">
                        <input type="radio" name="punya_wali" value="Ya" <?= ($siswa->nama_wali != "" ? "checked" : "") ?>> Ya
                    </label>
                    <label>
                        <input type="radio" name="punya_wali" value="Tidak" <?= ($siswa->nama_wali == "" ? "checked" : "") ?>> Tidak
                    </label>
                </div>

                <div id="wali_form">
                    <table class="table bio-table">
                        <tr><td class="bio-label">Nama Wali</td><td><input type="text" name="nama_wali" value="<?= $siswa->nama_wali ?>"></td></tr>
                        <tr><td class="bio-label">NIK Wali</td><td><input type="text" name="nik_wali" value="<?= $siswa->nik_wali ?>"></td></tr>
                        <tr><td class="bio-label">Tahun Lahir Wali</td><td><input type="text" name="tahun_lahir_wali" value="<?= $siswa->tahun_lahir_wali ?>"></td></tr>

                        <tr>
                            <td class="bio-label">Pendidikan Wali</td>
                            <td>
                                <select name="pendidikan_wali" id="pendidikan_wali" class="form-control">
                                    <?php foreach ($pendidikan_list as $p): ?>
                                        <option value="<?= $p ?>" <?= ($siswa->pendidikan_wali == $p ? 'selected' : '') ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="bio-label">Pekerjaan Wali</td>
                            <td>
                                <select name="pekerjaan_wali" id="pekerjaan_wali" class="form-control">
                                    <?php foreach ($pekerjaan_list as $p): ?>
                                        <option value="<?= $p ?>" <?= ($siswa->pekerjaan_wali == $p ? 'selected' : '') ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="bio-label">Penghasilan Wali</td>
                            <td>
                                <select name="penghasilan_wali" id="penghasilan_wali" class="form-control">
                                    <?php foreach ($penghasilan_list as $p): ?>
                                        <option value="<?= $p ?>" <?= ($siswa->penghasilan_wali == $p ? 'selected' : '') ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- NOMOR HP ORTU -->
                <div class="section-title">H. NOMOR HP ORANG TUA / WALI</div>
                <table class="table bio-table">
                    <tr>
                        <td class="bio-label">Nomor Whatsapp</td>
                        <td>
                            <input type="text" name="no_hp_ortu" placeholder="08....." value="<?= $siswa->no_hp_ortu ?>">
                        </td>
                    </tr>
                </table>

                <!-- BUTTON -->
                <div class="mt-4">
                    <button type="button" id="saveBtn" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>

                    <a href="<?= site_url('SiswaDashboard/biodata') ?>" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- FLOATING WHATSAPP (disabled until save sukses) -->
<div id="waBtn" class="wa-floating" title="Laporkan perubahan ke admin">
    <img src="https://cdn-icons-png.flaticon.com/512/124/124034.png" class="wa-icon" alt="WA">
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // auto close existing alert
    setTimeout(function() {
        let alert = document.querySelector('.alert');
        if (alert) {
            let bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 3000);

    (function(){
        const saveBtn = document.getElementById('saveBtn');
        const waBtnDiv = document.getElementById('waBtn');
        const form = document.getElementById('biodataForm');

        // old data
        let oldData = {};
        try { oldData = JSON.parse(document.getElementById('old_data').value); } catch(e) { oldData = {}; }

        // last changes after save
        let lastChanges = [];
        let lastFormValues = {}; // cache untuk WA filter

        // helper pretty field name
        function prettyName(field) {
            return field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function getFormValues() {
            const obj = {};
            const fields = form.querySelectorAll("input[name], textarea[name], select[name]");
            fields.forEach(f => {
                if (f.name === '<?= $csrf_name ?>' || f.id === 'old_data') return;
                if (f.type === 'checkbox') {
                    obj[f.name] = f.checked ? (f.value || '1') : '';
                } else if (f.type === 'radio') {
                    if (f.checked) obj[f.name] = f.value;
                    else if (!(f.name in obj)) obj[f.name] = '';
                } else {
                    obj[f.name] = (f.value ?? '').trim();
                }
            });
            return obj;
        }

        function computeChanges(oldObj, newObj) {
            const changes = [];
            for (const key in newObj) {
                const oldVal = (oldObj && oldObj[key] != null) ? String(oldObj[key]).trim() : '';
                const newVal = (newObj[key] != null) ? String(newObj[key]).trim() : '';
                if (oldVal !== newVal) {
                    changes.push({ field: key, oldVal: oldVal, newVal: newVal });
                }
            }
            return changes;
        }

        function changesToTextLines(changes) {
            return changes.map(c => `- ${prettyName(c.field)}: "${c.oldVal}" → "${c.newVal}"`);
        }

        // FILTER FUNCTION — ABAIKAN DATA WALI JIKA pilihannya "Tidak"
        function filterWaliIfNeeded(changes, formValues) {
            if (formValues['punya_wali'] !== "Tidak") return changes;

            return changes.filter(c =>
                !(
                    c.field.startsWith("nama_wali") ||
                    c.field.startsWith("nik_wali") ||
                    c.field.startsWith("tahun_lahir_wali") ||
                    c.field.startsWith("pendidikan_wali") ||
                    c.field.startsWith("pekerjaan_wali") ||
                    c.field.startsWith("penghasilan_wali")
                )
            );
        }

        // Save AJAX
saveBtn.addEventListener('click', function(e) {
    const formValues = getFormValues();
    const rawChanges = computeChanges(oldData, formValues);
    const filteredChanges = filterWaliIfNeeded(rawChanges, formValues);

    const fd = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
    })
    .then(async response => {
        if (!response.ok) throw new Error('Server error ' + response.status);

        let data = null;
        try { data = await response.json(); } catch (_) {}

        // update csrf
        if (data && data.csrf_hash) {
            form.querySelector("input[name='<?= $csrf_name ?>']").value = data.csrf_hash;
        }

        // ❌ TIDAK ADA PERUBAHAN
        if (filteredChanges.length === 0) {
            waBtnDiv.classList.remove('enabled');
            waBtnDiv.classList.add('disabled');

            Swal.fire({
                icon: 'info',
                title: 'Tidak ada perubahan',
                text: 'Data tidak mengalami perubahan.',
                width: '360px',
                background: '#2f3446',
                color: '#eaeaea',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#3498db'
            });
            return;
        }

        // ✅ ADA PERUBAHAN → AKTIFKAN WA
        oldData = Object.assign({}, oldData, formValues);
        lastFormValues = formValues;
        lastChanges = filteredChanges;

        waBtnDiv.classList.remove('disabled');
        waBtnDiv.classList.add('enabled');

        if (!waBtnDiv.dataset.bound) {
            waBtnDiv.dataset.bound = "1";
            waBtnDiv.addEventListener("click", openWhatsAppWithChanges);
        }

        showTempAlert(
            'Perubahan data berhasil disimpan.<br>Silakan klik WhatsApp untuk melaporkan.'
        );
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Terjadi kesalahan saat menyimpan data.',
            width: '360px',
            background: '#2f3446',
            color: '#eaeaea',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#e74c3c'
        });
    });
});

        // WA popup
        function openWhatsAppWithChanges() {

            const tujuan = "<?= $owner_phone ?>";
            const namaSiswa = "<?= addslashes($siswa->nama) ?>";

            // FILTER ULANG ketika kirim WA
            let finalChanges = filterWaliIfNeeded(lastChanges, lastFormValues);

            let msg = [];
            msg.push("Assalamualaikum.");
            msg.push(`${namaSiswa} telah melakukan perubahan data:`);
            msg.push("");

            if (finalChanges.length === 0) {
                msg.push("- Tidak ada perubahan terdeteksi.");
            } else {
                msg = msg.concat(changesToTextLines(finalChanges));
            }

            msg.push("");
            msg.push("Mohon dicek kembali.");

            const url = "https://wa.me/" + tujuan + "?text=" + encodeURIComponent(msg.join("\n"));
            window.open(url, "_blank");
        }

        function showTempAlert(text) {
    Swal.fire({
        title: 'Berhasil!',
        html: `<p style="margin-top:6px">${text}</p>`,
        width: '360px',
        padding: '1.5rem',
        background: '#2f3446',
        color: '#eaeaea',
        confirmButtonText: 'Ok',
        confirmButtonColor: '#2ecc71',

        // ✨ smooth animation
        showClass: {
            popup: 'swal2-show swal2-animate-smooth'
        },
        hideClass: {
            popup: 'swal2-hide swal2-animate-smooth'
        }
    });
}


        // AUTO PENGHASILAN AYAH/IBU/WALI
        function setupAutoPenghasilan(mapping) {
            mapping.forEach(item => {
                const p = document.getElementById(item.pekerjaan);
                const h = document.getElementById(item.penghasilan);
                if (!p || !h) return;

                function update() {
                    if (p.value === "Tidak Bekerja" || p.value === "Sudah Meninggal") {
                        h.value = "Tidak Berpenghasilan";
                        h.setAttribute("readonly","readonly");
                        h.style.background="#f3f6fb";
                    } else {
                        h.removeAttribute("readonly");
                        h.style.background="white";
                    }
                }

                p.addEventListener("change", update);
                update();
            });
        }

        setupAutoPenghasilan([
            { pekerjaan: "pekerjaan_ayah", penghasilan: "penghasilan_ayah" },
            { pekerjaan: "pekerjaan_ibu", penghasilan: "penghasilan_ibu" },
            { pekerjaan: "pekerjaan_wali", penghasilan: "penghasilan_wali" }
        ]);

        // SHOW / HIDE WALI FORM
        (function(){
            const radios = document.querySelectorAll("input[name='punya_wali']");
            const waliForm = document.getElementById('wali_form');

            function toggle() {
                const val = document.querySelector("input[name='punya_wali']:checked").value;

                if (val === "Ya") {
                    waliForm.style.display = "block";
                } else {
                    waliForm.style.display = "none";

                    // kosongkan isian wali
                    ["nama_wali","nik_wali","tahun_lahir_wali","pendidikan_wali","pekerjaan_wali","penghasilan_wali"]
                    .forEach(n => {
                        const el = document.querySelector(`[name="${n}"]`);
                        if (!el) return;
                        if (el.tagName.toLowerCase() === "select") el.value = "";
                        else el.value = "";
                    });
                }
            }

            radios.forEach(r => r.addEventListener("change", toggle));
            toggle();
        })();

    })();
    // === FLATPICKR TANGGAL LAHIR ===
(function(){
    const disp = document.getElementById("tgl_lahir_display");
    const real = document.getElementById("tgl_lahir");

    flatpickr(disp, {
        dateFormat: "d-m-Y",   // tampil ke siswa
        allowInput: true,
        defaultDate: disp.value !== "" ? disp.value : null,

        onChange: function(selectedDates, dateStr, instance) {
            // convert dd-mm-yyyy → yyyy-mm-dd
            if (dateStr && dateStr.includes("-")) {
                let p = dateStr.split("-");
                if (p.length === 3) {
                    real.value = `${p[2]}-${p[1]}-${p[0]}`;
                }
            }
        }
    });
})();

</script>

