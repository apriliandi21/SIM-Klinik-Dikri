
</div> </div> </div> </div> </div> <script src="/klinik-dikri/assets/js/bootstrap.bundle.min.js"></script> 

</div> </div> </div> </div> </div> <script src="/klinik-dikri/assets/js/bootstrap.bundle.min.js"></script> 

<script>
    // 1. Ambil "cetakan" Modal-nya
    var detailModal = document.getElementById('modalObatDetail');
    
    // 2. Tambahkan "pendengar" saat Modal-nya mau kebuka
    detailModal.addEventListener('show.bs.modal', function (event) {
        
        // 3. Ambil data dari tombol mana yang diklik
        var button = event.relatedTarget; // Tombol "Lihat" yg diklik
        
        // 4. Ekstrak SEMUA data dari atribut `data-*` di tombol
        var nama = button.getAttribute('data-nama-obat');
        var satuan = button.getAttribute('data-satuan');
        var dosis = button.getAttribute('data-dosis');
        var stok = button.getAttribute('data-stok');
        var indikasi = button.getAttribute('data-indikasi');
        var efek = button.getAttribute('data-efek');
        
        // 5. Temukan elemen di dalam Modal berdasarkan ID-nya
        var modalTitle = detailModal.querySelector('#modalNamaObat');
        var modalSatuan = detailModal.querySelector('#modalSatuan');
        var modalDosis = detailModal.querySelector('#modalDosis');
        var modalStok = detailModal.querySelector('#modalStok');
        var modalIndikasi = detailModal.querySelector('#modalIndikasi');
        var modalEfekSamping = detailModal.querySelector('#modalEfekSamping');
        
        // 6. "Suntik" (Inject) data itu ke dalam Modal
        modalTitle.textContent = nama;
        modalSatuan.textContent = satuan || '-';
        modalDosis.textContent = dosis || '-';
        modalStok.textContent = (stok || '0') + ' (Stok Saat Ini)';
        modalIndikasi.textContent = indikasi || 'Tidak ada data.';
        modalEfekSamping.textContent = efek || 'Tidak ada data.';
    });
</script>
</body>
</html>