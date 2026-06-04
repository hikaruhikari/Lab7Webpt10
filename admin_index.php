<?= $this->include('template/header'); ?>
<h2><?= $title; ?></h2>
<div class="row mb-3">
    <div class="col-md-8">
        <form id="search-form" class="form-inline">
            <input type="text" name="q" id="search-box" value="<?= $q; ?>" placeholder="Cari judul artikel" class="form-control mr-2">
            
            <select name="kategori_id" id="category-filter" class="form-control mr-2">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategori as $k): ?>
                    <option value="<?= $k['id_kategori']; ?>" <?= ($kategori_id == $k['id_kategori']) ? 'selected' : ''; ?>>
                        <?= $k['nama_kategori']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="sort" id="sort-filter" class="form-control mr-2">
                <option value="id_desc">Terbaru</option>
                <option value="judul_asc">Judul (A-Z)</option>
                <option value="judul_desc">Judul (Z-A)</option>
            </select>
            
            <input type="submit" value="Cari" class="btn btn-primary">
        </form>
    </div>
</div>

<div id="article-container"></div>
<div id="pagination-container" class="mt-3"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const articleContainer = $('#article-container');
    const paginationContainer = $('#pagination-container');
    const searchForm = $('#search-form');
    const searchBox = $('#search-box');
    const categoryFilter = $('#category-filter');
    const sortFilter = $('#sort-filter'); // Selector Sort Baru

    const fetchData = (url) => {
        // --- INDIKATOR LOADING (TUGAS NO 3) ---
        articleContainer.html('<div class="text-center my-3"><strong>⏳ Memuat data dari server...</strong></div>');
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                renderArticles(data.artikel);
                renderPagination(data.pager, data.q, data.kategori_id, data.sort); // Tambah variabel sort
            },
            error: function(xhr, status, error) {
                articleContainer.html('<div class="alert alert-danger">Gagal mengambil data.</div>');
            }
        });
    };

    const renderArticles = (articles) => {
        let html = '<table class="table table-bordered table-striped">';
        html += '<thead><tr><th>ID</th><th>Judul</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';

        if (articles && articles.length > 0) {
            articles.forEach(article => {
                let isiPotong = article.isi ? article.isi.substring(0, 50) : '';
                html += `
                <tr>
                    <td>${article.id}</td>
                    <td><b>${article.judul}</b><p><small>${isiPotong}...</small></p></td>
                    <td>${article.nama_kategori ? article.nama_kategori : '-'}</td>
                    <td>${article.status}</td>
                    <td>
                        <a class="btn btn-sm btn-info" href="/admin/artikel/edit/${article.id}">Ubah</a>
                        <a class="btn btn-sm btn-danger" onclick="return confirm('Hapus?');" href="/admin/artikel/delete/${article.id}">Hapus</a>
                    </td>
                </tr>`;
            });
        } else {
            html += '<tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>';
        }
        html += '</tbody></table>';
        articleContainer.html(html);
    };

    const renderPagination = (pager, q, kategori_id, sort) => {
        if (!pager || !pager.links || pager.links.length === 0) {
            paginationContainer.html('');
            return;
        }

        let html = '<nav><ul class="pagination">';
        pager.links.forEach(link => {
            // --- URL DISERTAI VARIABEL SORT AGAR SAAT PINDAH HALAMAN URUTANNYA TIDAK RESET ---
            let url = link.url ? `${link.url}&q=${q}&kategori_id=${kategori_id}&sort=${sort}` : '#';
            let activeClass = link.active ? 'active' : '';
            html += `<li class="page-item ${activeClass}"><a class="page-link page-ajax" href="${url}">${link.title}</a></li>`;
        });
        html += '</ul></nav>';
        paginationContainer.html(html);
    };

    // Handler Submit Form (Cari & Urutkan)
    searchForm.on('submit', function(e) {
        e.preventDefault();
        const q = searchBox.val();
        const kategori_id = categoryFilter.val();
        const sort = sortFilter.val(); // Ambil nilai urutan aktif
        fetchData(`/admin/artikel?q=${q}&kategori_id=${kategori_id}&sort=${sort}`);
    });

    // Otomatis submit AJAX jika Filter Kategori atau Sort diganti oleh user
    categoryFilter.on('change', function() { searchForm.trigger('submit'); });
    sortFilter.on('change', function() { searchForm.trigger('submit'); }); // Trigger Sort

    $(document).on('click', '.page-ajax', function(e) {
        e.preventDefault();
        let targetUrl = $(this).attr('href');
        if (targetUrl !== '#') fetchData(targetUrl);
    });

    fetchData('/admin/artikel');
});
</script>
<?= $this->include('template/footer'); ?>