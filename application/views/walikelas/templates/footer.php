<!-- ================= FOOTER WALIKELAS ================= -->

        </div> <!-- container-fluid -->
    </div> <!-- End of Main Content -->

    <!-- Footer -->
    <footer class="sticky-footer bg-white text-dark py-3 border-top">
        <div class="container my-auto">
            <div class="text-center my-auto small">
                <span>
                    © <?= date('Y') ?> Created by 
                    <a href="https://www.profilsaya.my.id" target="_blank" class="text-decoration-none text-primary fw-bold">
                        M. Nazmudin
                    </a> 
                    — Sistem Mutasi Siswa (Wali Kelas)
                </span>
            </div>
        </div>
    </footer>

</div> <!-- End of Content Wrapper -->
</div> <!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- ========================= -->
<!-- LOAD JAVASCRIPT CORET -->
<!-- ========================= -->
<script src="<?= base_url('assets/sbadmin2/vendor/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/sbadmin2/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/sbadmin2/vendor/jquery-easing/jquery.easing.min.js') ?>"></script>
<script src="<?= base_url('assets/sbadmin2/js/sb-admin-2.min.js') ?>"></script>

<!-- DATATABLES, CHART, SWEETALERT -->
<script src="<?= base_url('assets/sbadmin2/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/sbadmin2/vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('assets/sbadmin2/vendor/chart.js/Chart.min.js') ?>"></script>
<script src="<?= base_url('assets/sbadmin2/vendor/sweetalert/sweetalert.min.js') ?>"></script>


<!-- ========================= -->
<!-- DARK MODE SCRIPT -->
<!-- ========================= -->
<script>
const toggleBtn = document.getElementById('toggleMode');
const body = document.body;
let mode = localStorage.getItem('mode') || 'dark';

function setMode(mode) {
    if (mode === 'dark') {
        body.classList.add('dark-mode');
        body.classList.remove('light-mode');
        toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        localStorage.setItem('mode', 'dark');
    } else {
        body.classList.remove('dark-mode');
        body.classList.add('light-mode');
        toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        localStorage.setItem('mode', 'light');
    }
}

setMode(mode);

toggleBtn.addEventListener('click', () => {
    mode = body.classList.contains('dark-mode') ? 'light' : 'dark';
    setMode(mode);
});
</script>


<!-- ========================================================= -->
<!-- FIX SCRIPT ABSENSI WALIKELAS (DIPINDAH KE FOOTER AGAR BERFUNGSI) -->
<!-- ========================================================= -->
<script>
function badgeKehadiranJS(k) {
    k = (k ?? "").toUpperCase();
    switch(k) {
        case 'H': return "<span class='badge bg-success'>Hadir</span>";
        case 'TERLAMBAT': return "<span class='badge bg-warning text-dark'>Terlambat</span>";
        case 'I': return "<span class='badge bg-primary'>Izin</span>";
        case 'S': return "<span class='badge bg-info text-dark'>Sakit</span>";
        case 'A': return "<span class='badge bg-danger'>Alpa</span>";
        default: return "<span class='badge bg-secondary'>-</span>";
    }
}

$(document).ready(function() {

    // ============================
    // TAMPILKAN DATA
    // ============================
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

    // ============================
    // EXPORT PDF
    // ============================
    $("#btnPdf").click(function(){
        window.open(
            "<?= site_url('walikelas/absensi_pdf') ?>?dari="+$("#dari").val()+
            "&sampai="+$("#sampai").val()+
            "&nama="+$("#nama").val()+
            "&status="+$("#keterangan").val(),
            "_blank"
        );
    });

    // ============================
    // EXPORT EXCEL
    // ============================
    $("#btnExcel").click(function(){
        window.location.href =
            "<?= site_url('walikelas/absensi_excel') ?>?dari="+$("#dari").val()+
            "&sampai="+$("#sampai").val()+
            "&nama="+$("#nama").val()+
            "&status="+$("#keterangan").val();
    });

});
</script>

</body>
</html>
