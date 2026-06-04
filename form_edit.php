<?= $this->include('template/header'); ?> 
 
<h2><?= $title; ?></h2> 
 
<form action="" method="post"> 
    <p> 
        <label for="judul">Judul</label> 
        <input type="text" name="judul" value="<?= $artikel['judul']; ?>" id="judul" required> 
    </p> 
    <p> 
        <label for="isi">Isi</label> 
        <textarea name="isi" id="isi" cols="50" rows="10"><?= $artikel['isi']; ?></textarea> 
    </p> 
    <p> 
        <label for="id_kategori">Kategori</label> 
        <select name="id_kategori" id="id_kategori" required> 
            <?php foreach($kategori as $k): ?> 
                <option value="<?= $k['id_kategori']; ?>" <?= ($artikel['id_kategori'] == $k['id_kategori']) ? 'selected' : ''; ?>><?= $k['nama_kategori']; ?></option> 
            <?php endforeach; ?> 
        </select> 
    </p> 
    <p><input type="submit" value="Kirim" class="btn btn-large"></p> 
</form> 
<?= $this->include('template/footer'); ?>