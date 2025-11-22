<style>
.badge {
    font-size: 13px;
    padding: 6px 10px;
}
</style>

<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="fa fa-search"></i> Rekap Absensi QR – <?= $kelas_nama ?>
    </div>

    <div class="card-body">

        <form id="formLaporanQR">

            <input type="hidden" 
                name="<?= $this->security->get_csrf_token_name(); ?>" 
                value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="row">

                <div class="col-md-4">
                    <label>Cari Nama Siswa</label>
                    <input id="nama" type="text" name="nama" class="form-control" placeholder="Cari Siswa...">
                </div>

                <div class="col-md-4">
                    <label>Dari Tanggal</label>
                    <input id="dari" type="date" name="dari" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="col-md-4">
                    <label>Sampai Tanggal</label>
                    <input id="sampai" type="date" name="sampai" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

            </div>

            <div class="row mt-3">
                <div class="col-md-4">
                    <label>Status Kehadiran</label>
                    <select id="keterangan" name="keterangan" class="form-control">
                        <option value="">[ SEMUA STATUS ]</option>
                        <option value="H">Hadir</option>
                        <option value="Terlambat">Terlambat</option>
                        <option value="I">Izin</option>
                        <option value="S">Sakit</option>
                        <option value="A">Alpa</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">

                <button id="btnTampil" type="button" class="btn btn-info">
                    <i class="fa fa-search"></i> Tampilkan Data
                </button>

                <button id="btnPdf" type="button" class="btn btn-danger">
                    <i class="fa fa-file-pdf"></i> PDF
                </button>

                <button id="btnExcel" type="button" class="btn btn-success">
                    <i class="fa fa-file-excel"></i> Excel
                </button>

            </div>

        </form>

    </div>
</div>


<!-- HASIL -->
<div id="hasilBox" style="display:none; margin-top:30px;">
    <h4>Hasil Pencarian</h4>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Siswa</th>
                <th>Kehadiran</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="hasilBody"></tbody>
    </table>
</div>


<script>
function badgeKehadiranJS(k) {
    k = (k ?? "").toUpperCase();
    switch(k) {
        case 'H': return "<span class='badge bg-success'>Hadir</span>";
        case 'Terlambat': return "<span class='badge bg-warning text-dark'>Terlambat</span>";
        case 'I': return "<span class='badge bg-primary'>Izin</span>";
        case 'S': return "<span class='badge bg-info text-dark'>Sakit</span>";
        case 'A': return "<span class='badge bg-danger'>Alpa</span>";
        default: return "<span class='badge bg-secondary'>-</span>";
    }
}

$(document).ready(function() {

    $("#btnTampil").click(function() {

        $.ajax({
            url: "<?= site_url('walikelas/absensi_data') ?>",
            type: "POST",
            data: {
                nama: $("#nama").val(),
                dari: $("#dari").val(),
                sampai: $("#sampai").val(),
                status: $("#keterangan").val(),
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
                            <td>${ badgeKehadiranJS(r.kehadiran) }</td>
                            <td>${r.jam_masuk ?? '-'}</td>
                            <td>${r.jam_pulang ?? '-'}</td>
                            <td>${r.status}</td>
                        </tr>
                    `;
                });

                $("#hasilBody").html(html);
                $("#hasilBox").show();
            }
        });

    });

    $("#btnPdf").click(function(){

        window.open(
            "<?= site_url('walikelas/absensi_pdf') ?>?dari="+$("#dari").val()+
            "&sampai="+$("#sampai").val()+
            "&nama="+$("#nama").val()+
            "&status="+$("#keterangan").val(),
            "_blank"
        );
    });

    $("#btnExcel").click(function(){

        window.location.href =
            "<?= site_url('walikelas/absensi_excel') ?>?dari="+$("#dari").val()+
            "&sampai="+$("#sampai").val()+
            "&nama="+$("#nama").val()+
            "&status="+$("#keterangan").val();
    });

});
</script>
