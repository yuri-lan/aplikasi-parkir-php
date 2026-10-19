<?php
// includes/footer.php
?>
    </div> <!-- Tutup col-lg-10 -->
  </div> <!-- Tutup row -->
</div> <!-- Tutup container-fluid -->

<!-- OFFCANVAS SIDEBAR UNTUK HP -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMobile" style="width: 280px;">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title fw-bold" style="color: var(--warna-teks-aksen);">Menu Navigasi</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-3">
      <?php include __DIR__ . '/sidebar.php'; ?>
  </div>
</div>

<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

<!-- Bootstrap Bundle LOKAL (Biar tombol garis 3 jalan) -->
<script src="<?= $base_url ?>assets/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js LOKAL (Biar grafik jalan) -->
<script src="<?= $base_url ?>assets/js/chart.js"></script>

<!-- DataTables Core & Bootstrap 5 -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    // --- SCRIPT DARK MODE ---
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const body = document.body;

    if (body.classList.contains('dark-mode')) {
        if(themeIcon) themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
    }

    if(themeToggle) {
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                if(themeIcon) themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
            } else {
                localStorage.setItem('theme', 'light');
                if(themeIcon) themeIcon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
            }
        });
    }

    // --- SCRIPT DATATABLES GLOBAL ---
    $(document).ready(function() {
        const dtConfig = {
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-success btn-sm me-2' },
                { extend: 'pdfHtml5', text: '<i class="bi bi-file-earmark-pdf"></i> PDF', className: 'btn btn-danger btn-sm' }
            ],
            "language": { 
                "search": "Cari:", 
                "lengthMenu": "Tampilkan _MENU_ data", 
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data", 
                "zeroRecords": "Data tidak ditemukan", 
                "paginate": { "first": "Awal", "last": "Akhir", "next": "Selanjutnya", "previous": "Sebelumnya" } 
            }
        };

        if ($('#tabelUser').length > 0) $('#tabelUser').DataTable(dtConfig);
        if ($('#tabelTarif').length > 0) $('#tabelTarif').DataTable(dtConfig);
        if ($('#tabelArea').length > 0) $('#tabelArea').DataTable(dtConfig);
        if ($('#tabelKendaraan').length > 0) $('#tabelKendaraan').DataTable(dtConfig);
        if ($('#tabelLog').length > 0) $('#tabelLog').DataTable(dtConfig);
        
        if ($('#tabelMasuk').length > 0) $('#tabelMasuk').DataTable({ dom: 'frtip', "language": dtConfig.language });
        if ($('#tabelKeluar').length > 0) $('#tabelKeluar').DataTable({ dom: 'frtip', "language": dtConfig.language });
        
        if ($('#tabelLaporan').length > 0) {
            const urlParams = new URLSearchParams(window.location.search);
            const tglMulai = urlParams.get('tgl_mulai') || 'Awal';
            const tglSampai = urlParams.get('tgl_sampai') || 'Akhir';
            
            $('#tabelLaporan').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-success btn-sm me-2', title: `Laporan_Parkir_${tglMulai}_sd_${tglSampai}` },
                    { extend: 'pdfHtml5', text: '<i class="bi bi-file-earmark-pdf"></i> PDF', className: 'btn btn-danger btn-sm', title: `Laporan_Parkir_${tglMulai}_sd_${tglSampai}` }
                ],
                "language": dtConfig.language
            });
        }
    });
</script>
</body>
</html>