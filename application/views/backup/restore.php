<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Restore Database</h3>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success shadow-sm"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger shadow-sm"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <i class="fas fa-upload fa-4x text-danger mb-3"></i>
                <h5 class="fw-bold">Pulihkan Database</h5>
                <p class="text-muted">
                    Unggah file <strong>.sql</strong> untuk merestore database. Proses ini akan menggantikan seluruh data!
                </p>
            </div>

            <form method="post" action="<?= base_url('backup/do_restore') ?>" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File SQL</label>
                    <input type="file" name="file_sql" class="form-control" required>
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-danger btn-lg px-4">
                        <i class="fas fa-exclamation-triangle me-1"></i> Restore Sekarang
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
