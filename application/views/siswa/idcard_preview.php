<div class="card shadow p-3 mb-4" style="border-radius: 15px;">

    <h4 class="mb-3 text-center">
        <i class="fas fa-id-card"></i> ID Card Siswa
    </h4>
    <hr>

    <div class="idcard-container text-center">

        <!-- Preview ID Card -->
        <img src="data:image/png;base64,<?= $idcard_base64 ?>"
             class="idcard-image shadow"
             alt="ID Card">

        <!-- Tombol Download -->
        <a href="<?= site_url('idcard/cetak/'.$siswa->id) ?>" 
           class="btn btn-primary btn-lg download-btn"
           target="_blank">
            <i class="fas fa-download"></i> Download ID Card
        </a>

    </div>
</div>

<style>
    /* Container untuk memaksa konten center di HP */
    .idcard-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Image ID Card responsive */
    .idcard-image {
        width: 100%;
        max-width: 330px;
        border-radius: 25px;
        margin-bottom: 20px;
    }

    /* Tombol download responsif */
    .download-btn {
        width: 100%;
        max-width: 330px;
        font-size: 1.1rem;
        padding: 12px;
        border-radius: 10px;
    }

    /* Mobile adjustments */
    @media (max-width: 576px) {

        h4 {
            font-size: 1.2rem;
        }

        .idcard-image {
            max-width: 300px;
        }

        .download-btn {
            max-width: 300px;
        }
    }
</style>
