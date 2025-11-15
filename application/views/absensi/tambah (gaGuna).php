<?php error_reporting(E_ALL); ini_set('display_errors', 1); ?>

<!-- BOOTSTRAP BUNDLE (WAJIB UNTUK MODAL) -->
<script src="<?= base_url('assets/vendor/bootstrap.bundle.min.js') ?>"></script>

<style>
/* ————— Dropdown Pencarian ————— */
#hasilCari {
    position: absolute;
    background: #1e1f2b;
    border: 1px solid #444;
    border-radius: 6px;
    width: 100%;
    max-height: 240px;
    overflow-y: auto;
    display: none;
    z-index: 99999;
}
.item-siswa {
    padding: 10px;
    display: flex;
    gap: 12px;
    cursor: pointer;
    color: white;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.item-siswa:hover { background:#303241; }

.item-id { width: 110px; font-weight:bold; color:#5ab2ff; }
.item-kelas { width: 90px; color:#ffb366; }
.item-nama { flex: 1; }

/* ————— RESPONSIVE (HP & TABLET) ————— */
@media (max-width: 768px){
    .modal-content { border-radius: 0 !important; }

    #namaSiswa, .form-control {
        font-size: 16px !important;
        height: 50px !important;
    }

    #hasilCari {
        font-size: 16px !important;
        max-height: 260px !important;
    }

    .item-id { width: 90px; }
    .item-kelas { width: 70px; }

    .btn { width: 100%; padding: 14px; font-size: 18px; }
}
/* ============================================
   FIX UTAMA: PAKSAKAN TEKS DROPDOWN JADI SOLID
   ============================================ */

body.dark-mode #hasilCari,
body.dark-mode #hasilCari * {
    color: #ffffff !important;
    opacity: 1 !important;
}

/* background item */
body.dark-mode #hasilCari .item-siswa {
    background: #2b2d3a !important;
}

/* teks warna khusus */
body.dark-mode #hasilCari .item-id {
    color: #6bb7ff !important;
}

body.dark-mode #hasilCari .item-kelas {
    color: #ffcc8a !important;
}

body.dark-mode #hasilCari .item-nama {
    color: #ffffff !important;
}

/* hover */
body.dark-mode #hasilCari .item-siswa:hover {
    background: #3c3f50 !important;
}

</style>

<!-- ————————————————————————————————
     MODAL FULLSCREEN + FORM TAMBAH ABSENSI
——————————————————————————————— -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable modal-lg modal-fullscreen-sm-down">
    <div class="modal-content" style="background:#1e1f2b; color:white;">

      <div class="modal-header">
        <h4 class="modal-title">Tambah Absensi</h4>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">

        <form id="formTambah" method="post" action="<?= site_url('Absensi/Absensi/simpan') ?>">

            <!-- CSRF -->
            <input type="hidden"
                   name="<?= $this->security->get_csrf_token_name(); ?>"
                   value="<?= $this->security->get_csrf_hash(); ?>">

            <!-- Hidden -->
            <input type="hidden" id="id_siswa" name="id_siswa">
            <input type="hidden" id="id_kelas" name="id_kelas">

            <!-- NAMA SISWA -->
            <div class="form-group position-relative">
                <label>Nama Siswa</label>
                <input type="text" id="namaSiswa" class="form-control" placeholder="Ketik nama…" autocomplete="off">
                <div id="hasilCari"></div>
            </div>

            <!-- Keterangan -->
            <div class="form-group">
                <label>Keterangan</label>
                <select class="form-control" id="ketSelect" name="status" required>
                    <option value="">Pilih</option>
                    <option value="SAKIT">SAKIT</option>
                    <option value="IZIN">IZIN</option>
                    <option value="ALPA">ALPA</option>
                </select>
            </div>

            <!-- Alasan -->
            <div class="form-group">
                <label>Alasan</label>
                <input type="text" id="alasan" name="keterangan" class="form-control" required>
            </div>

            <!-- Tanggal -->
            <div class="form-group">
                <label>Tanggal Absen</label>
                <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- Tahun Pelajaran -->
            <div class="form-group">
                <label>Tahun Pelajaran</label>
                <input type="text" class="form-control" name="tahun_pelajaran" value="2025/2026" required>
            </div>

        </form>

      </div>

      <div class="modal-footer">
        <button class="btn btn-success" onclick="$('#formTambah').submit();">
            <i class="fa fa-save"></i> Simpan
        </button>
        <button class="btn btn-danger" data-dismiss="modal">
            <i class="fa fa-times"></i> Tutup
        </button>
      </div>

    </div>
  </div>
</div>

<!-- ————————————————————————————————
     JAVASCRIPT PENCARIAN SISWA (NO ERROR)
——————————————————————————————— -->
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>

<script>
// isi alasan otomatis
$("#ketSelect").on("change", function(){
    const v = $(this).val();
    $("#alasan").val(
        v === "SAKIT" ? "Sakit" :
        v === "IZIN" ? "Izin" :
        v === "ALPA" ? "Tanpa Keterangan" : ""
    );
});

// Pencarian siswa
$(document).ready(function(){

    $("#namaSiswa").on("keyup", function(){

        let keyword = $(this).val().trim();
        if(keyword.length < 2){
            $("#hasilCari").hide();
            return;
        }

        let csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
        let csrfHash = "<?= $this->security->get_csrf_hash(); ?>";

        $.ajax({
            url : "<?= base_url('index.php/Absensi/Absensi/ajax_siswa'); ?>",
            method : "POST",
            data : { keyword: keyword, [csrfName]: csrfHash },
            success : function(res){

                let data = JSON.parse(res);
                let html = "";

                data.forEach(s => {
                    html += `
                        <div class="item-siswa pilih-siswa"
                             data-id="${s.id}"
                             data-kelas="${s.nama_kelas}"
                             data-nama="${s.nama}">
                             
                            <div class="item-id">${s.nisn}</div>
                            <div class="item-kelas">${s.nama_kelas}</div>
                            <div class="item-nama">${s.nama}</div>
                        </div>
                    `;
                });

                $("#hasilCari").html(html).show();
            }
        });

    });

    // pilih siswa
    $(document).on("click", ".pilih-siswa", function(){
        $("#id_siswa").val($(this).data("id"));
        $("#id_kelas").val($(this).data("kelas"));
        $("#namaSiswa").val($(this).data("nama"));
        $("#hasilCari").hide();
    });

    // klik luar
    $(document).click(function(e){
        if (!$(e.target).closest("#namaSiswa, #hasilCari").length){
            $("#hasilCari").hide();
        }
    });

});

<?php if ($this->session->flashdata('success') || $this->session->flashdata('error')): ?>
    <script>
        $(document).ready(function() {
            $("#modalTambah").modal("show");
        });
    </script>
<?php endif; ?>

</script>
