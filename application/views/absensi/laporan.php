<div class="card">
    <div class="card-header bg-info text-white">
        <i class="fa fa-search"></i> Laporan Absen Siswa
    </div>

    <div class="card-body">

        <form id="formLaporan">

            <!-- CSRF -->
            <input type="hidden" 
                name="<?= $this->security->get_csrf_token_name(); ?>" 
                value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="row">

                <div class="col-md-3">
                    <label>Cari Siswa</label>
                    <input id="nama" type="text" name="nama" class="form-control" placeholder="Cari Siswa...">
                </div>

                <div class="col-md-3">
                    <label>Dari Tanggal</label>
                    <input id="dari" type="date" name="dari" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="col-md-3">
                    <label>Sampai Tanggal</label>
                    <input id="sampai" type="date" name="sampai" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="col-md-3">
                    <label>Kelas</label>
                    <select id="kelas" name="kelas" class="form-control">
                        <option value="">[ SEMUA KELAS ]</option>
                        <?php foreach($kelas as $k): ?>
                        <option value="<?= $k->id ?>"><?= $k->nama ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="row mt-3">

                <div class="col-md-3">
                    <label>Tahun Ajaran</label>
                    <input id="tahun" type="text" name="tahun" class="form-control" value="2025/2026">
                </div>

                <div class="col-md-3">
                    <label>Keterangan</label>
                    <select id="keterangan" name="keterangan" class="form-control">
                        <option value="">[ SEMUA STATUS ]</option>
                        <option value="SAKIT">SAKIT</option>
                        <option value="IZIN">IZIN</option>
                        <option value="ALPA">ALPA</option>
                    </select>
                </div>

            </div>

            <div class="mt-4">

                <button id="btnTampil" type="button" class="btn btn-info">
                    <i class="fa fa-search"></i> Tampilkan Data
                </button>

                <button id="btnPrint" type="button" class="btn btn-danger">
                    <i class="fa fa-print"></i> Print Data
                </button>

                <button id="btnPdf" type="button" class="btn btn-success">
                    <i class="fa fa-file-pdf"></i> Rekap PDF
                </button>

                <button id="btnExcel" type="button" class="btn btn-primary">
                    <i class="fa fa-file-excel"></i> Rekap Excel
                </button>

            </div>

        </form>

    </div>
</div>


<!-- HASIL TAMPILKAN DATA -->
<div id="hasilBox" style="display:none; margin-top:30px;">
    <h4>Hasil Pencarian</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody id="hasilBody"></tbody>
    </table>
</div>


<!-- pastikan jquery ada -->
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>

<script>
$(document).ready(function() {

    console.log("JS Loaded");

    // ========== 1. Tampil ==============
    $("#btnTampil").click(function() {

        console.log("BTN tampil diklik");

        $.ajax({
            url: "<?= site_url('Absensi/Laporan/data') ?>",
            type: "POST",
            data: {
                nama: $("#nama").val(),
                kelas: $("#kelas").val(),
                dari: $("#dari").val(),
                sampai: $("#sampai").val(),
                keterangan: $("#keterangan").val(),
                "<?= $this->security->get_csrf_token_name(); ?>": 
                "<?= $this->security->get_csrf_hash(); ?>"
            },
            dataType: "json",
            success: function(res){

                let html = "";
                let no = 1;

                res.forEach(r => {
                    html += `
                        <tr>
                            <td>${no++}</td>
                            <td>${r.tanggal}</td>
                            <td>${r.nama_siswa}</td>
                            <td>${r.nama_kelas}</td>
                            <td>${r.status}</td>
                        </tr>
                    `;
                });

                $("#hasilBody").html(html);
                $("#hasilBox").show();
            }
        });

    });

    // ========== 2. Print ==============
    $("#btnPrint").click(function(){
        console.log("BTN print diklik");

        let kelas  = $("#kelas").val();
        let dari   = $("#dari").val();
        let sampai = $("#sampai").val();
        let ket    = $("#keterangan").val();

        window.open(
            "<?= site_url('Absensi/Laporan/print') ?>?kelas="+kelas+
            "&dari="+dari+"&sampai="+sampai+"&ket="+ket,
            "_blank"
        );
    });

    // ========== 3. PDF ==============
    $("#btnPdf").click(function(){
        console.log("BTN pdf diklik");

        let kelas  = $("#kelas").val();
        let dari   = $("#dari").val();
        let sampai = $("#sampai").val();
        let tahun  = $("#tahun").val();

        window.open(
            "<?= site_url('Absensi/Laporan/pdf') ?>?kelas="+kelas+
            "&dari="+dari+"&sampai="+sampai+"&tahun="+tahun,
            "_blank"
        );
    });

    // ========== 4. Excel ==============
    $("#btnExcel").click(function(){
        console.log("BTN excel diklik");

        let kelas  = $("#kelas").val();
        let dari   = $("#dari").val();
        let sampai = $("#sampai").val();
        let tahun  = $("#tahun").val();

        window.location.href =
            "<?= site_url('Absensi/Laporan/excel') ?>?kelas="+kelas+
            "&dari="+dari+"&sampai="+sampai+"&tahun="+tahun;
    });

});
</script>

