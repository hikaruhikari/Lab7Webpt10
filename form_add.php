<?= $this->include('template/header'); ?> <h2><?= $title; ?></h2> 
 
<form action="" method="post" enctype="multipart/form-data">  
    <p> 
        <label for="judul">Judul</label><br>
        <input type="text" name="judul" id="judul" class="form-control" required> 
    </p> 
    <p> 
        <label for="isi">Isi</label><br>
        <textarea name="isi" id="isi" cols="50" rows="10" class="form-control"></textarea> 
    </p> 
    <p> 
        <label for="id_kategori">Kategori</label><br>
        <select name="id_kategori" id="id_kategori" class="form-control" required> 
            <option value="">-- Pilih Kategori --</option>
            <?php if (!empty($kategori) && is_array($kategori)): ?>
                <?php foreach($kategori as $k): ?> 
                    <option value="<?= $k['id_kategori']; ?>"><?= $k['nama_kategori']; ?></option> 
                <?php endforeach; ?> 
            <?php endif; ?>
        </select> 
    </p>
    <p> 
        <label for="gambar">Upload Gambar</label><br>
        <input type="file" name="gambar" id="gambar" class="form-control-file"> 
    </p> 
    <p>
        <input type="submit" value="Kirim" class="btn btn-primary btn-large">
    </p>
</form> 
 
<?= $this->include('template/footer'); ?>