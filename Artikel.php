<?php
namespace App\Controllers;
use App\Models\ArtikelModel;
use App\Models\KategoriModel;
class Artikel extends BaseController
{
    public function index()
    {
        $title = 'Daftar Artikel';
        $model = new ArtikelModel();
        $artikel = $model->getArtikelDenganKategori(); // Use the new method
        return view('artikel/index', compact('artikel', 'title'));
    }

    public function admin_index()
    {
        $title = 'Daftar Artikel (Admin)'; // [cite: 197]
        $model = new \App\Models\ArtikelModel(); // [cite: 198]
        
        // Mengambil parameter input untuk pencarian dan pagination [cite: 199, 200, 201]
        $q = $this->request->getVar('q') ?? ''; 
        $kategori_id = $this->request->getVar('kategori_id') ?? '';
        $page = $this->request->getVar('page') ?? 1;
        
        // Query Builder untuk mengambil data artikel dikombinasikan dengan kategori [cite: 202, 203, 204, 205]
        $sort = $this->request->getVar('sort') ?? 'id_desc'; // default urutan ID terbesar/terbaru

        $builder = $model->table('artikel')
                        ->select('artikel.*, kategori.nama_kategori')
                        ->join('kategori', 'kategori.id_kategori = artikel.id_kategori', 'left');
        
        // Jika ada input kata kunci pencarian [cite: 206]
        if ($q != '') {
            $builder->like('artikel.judul', $q); // [cite: 207]
        }
        
        // Jika ada filter kategori yang dipilih [cite: 209]
        if ($kategori_id != '') {
            $builder->where('artikel.id_kategori', $kategori_id); // [cite: 210]
        }
        
        if ($sort == 'judul_asc') {
        $builder->orderBy('artikel.judul', 'ASC');
    } elseif ($sort == 'judul_desc') {
        $builder->orderBy('artikel.judul', 'DESC');
    } else {
        $builder->orderBy('artikel.id', 'DESC'); // Default ID terbaru
    }
    
    $artikel = $builder->paginate(5, 'bootstrap_full', $page);
    $pager = $model->pager;

    if ($this->request->isAJAX()) {
        return $this->response->setJSON([
            'artikel' => $artikel,
            'pager' => $pager->getDetails('bootstrap_full'),
            'q' => $q,
            'kategori_id' => $kategori_id,
            'sort' => $sort // Ikut kirim status sort aktif ke AJAX
        ]);
    } else {
        $kategoriModel = new \App\Models\KategoriModel();
        $data['kategori'] = $kategoriModel->findAll();
        $data['title'] = $title;
        $data['q'] = $q;
        $data['kategori_id'] = $kategori_id;
        $data['sort'] = $sort; // Kirim ke halaman pertama kali dibuka
        return view('artikel/admin_index', $data);
    }

    }
    // ... (methods add, edit, delete remain largely the same, but update to handle id_kategori)
    public function add()
    {
        // validasi data.
        $validation = \Config\Services::validation();
        $validation->setRules(['judul' => 'required']);
        $isDataValid = $validation->withRequest($this->request)->run();

        if ($isDataValid) {
            $file = $this->request->getFile('gambar');
            $fileName = '';

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $file->move(ROOTPATH . 'public/gambar');
                $fileName = $file->getName();
            }

            $artikel = new ArtikelModel();
            $artikel->insert([
                'judul'       => $this->request->getPost('judul'),
                'isi'         => $this->request->getPost('isi'),
                'id_kategori' => $this->request->getPost('id_kategori'), // Memastikan id_kategori ikut tersimpan
                'slug'        => url_title($this->request->getPost('judul'), '-', true),
                'gambar'      => $fileName,
            ]);

            return redirect('admin/artikel');
        }

        // --- BAGIAN INI YANG DIPERBAIKI ---
        // Panggil KategoriModel untuk mengambil data kategori dari database
        $kategoriModel = new \App\Models\KategoriModel();
        
        $data = [
            'title'    => "Tambah Artikel",
            'kategori' => $kategoriModel->findAll() // Mengambil semua data kategori untuk dropdown
        ];

        return view('artikel/form_add', $data); // Kirim array $data ke view
    }
    public function edit($id)  
    { 
        $artikelModel = new ArtikelModel(); 
        $db = \Config\Database::connect();

        // 1. Ambil data kategori untuk pilihan select
        $kategori = $db->table('kategori')->get()->getResultArray();

        // Validasi data
        $validation = \Config\Services::validation(); 
        $validation->setRules(['judul' => 'required']); 
        $isDataValid = $validation->withRequest($this->request)->run(); 

        if ($isDataValid) 
        { 
            $artikelModel->update($id, [ 
                'judul' => $this->request->getPost('judul'), 
                'isi' => $this->request->getPost('isi'), 
                'id_kategori' => $this->request->getPost('id_kategori'), // Update kategorinya juga
            ]); 
            return redirect('admin/artikel'); 
        } 

        // Ambil data artikel yang sedang diedit
        $data = $artikelModel->where('id', $id)->first(); 
        $title = "Edit Artikel"; 

        // 2. Kirim $data (artikel) dan $kategori ke view
        return view('artikel/form_edit', [
            'title' => $title,
            'artikel' => $data,
            'kategori' => $kategori
        ]); 
    }
    public function delete($id)
    {
        $model = new ArtikelModel();
        $model->delete($id);
        return redirect()->to('/admin/artikel');
    }
    public function view($slug)
    {
        $model = new ArtikelModel();
        // Melakukan join ke tabel kategori untuk mendapatkan nama_kategori
        $artikel = $model->select('artikel.*, kategori.nama_kategori')
                        ->join('kategori', 'kategori.id_kategori = artikel.id_kategori', 'left')
                        ->where('artikel.slug', $slug)
                        ->first();

        if (!$artikel) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $title = $artikel['judul'];
        return view('artikel/detail', compact('artikel', 'title'));
    }
    public function kategori($id_kategori)
    {
        $model = new ArtikelModel();
        $db = \Config\Database::connect();

        // Ambil nama kategori untuk judul halaman
        $kategori = $db->table('kategori')->where('id_kategori', $id_kategori)->get()->getRowArray();
        
        if (!$kategori) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Kategori tidak ditemukan");
        }

        // Ambil artikel yang sesuai dengan id_kategori saja
        $artikel = $model->select('artikel.*, kategori.nama_kategori')
                        ->join('kategori', 'kategori.id_kategori = artikel.id_kategori', 'left')
                        ->where('artikel.id_kategori', $id_kategori)
                        ->findAll();

        $data = [
            'title' => 'Kategori: ' . $kategori['nama_kategori'],
            'artikel' => $artikel
        ];

        // Kita gunakan view index artikel yang sudah ada untuk menampilkan hasilnya
        return view('artikel/index', $data);
}
}