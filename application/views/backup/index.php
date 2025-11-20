<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Backup Database</h3>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <i class="fas fa-database fa-4x text-primary mb-3"></i>
                <h5 class="fw-bold">Simpan Salinan Database</h5>
                <p class="text-muted">
                    Klik tombol di bawah untuk membuat file backup database dalam format ZIP.
                </p>
            </div>

            <div class="text-center">
                <a href="<?= base_url('backup/do_backup') ?>" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-download me-1"></i> Download Backup
                </a>
            </div>

        </div>
    </div>

</div>
