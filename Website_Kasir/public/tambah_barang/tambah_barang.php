<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: ../public/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $harga = trim($_POST['harga']);
    $stok  = trim($_POST['stok']);

    $errors = [];

    if (empty($nama)) $errors[] = "Nama produk wajib diisi.";
    if (empty($harga) || !is_numeric($harga)) $errors[] = "Harga harus berupa angka.";
    if (empty($stok) || !is_numeric($stok)) $errors[] = "Stok harus berupa angka.";

    if (count($errors) === 0) {
        $stmt = $conn->prepare("INSERT INTO barang (nama, harga, stok) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $nama, $harga, $stok);
        if ($stmt->execute()) {
            header("Location: ../dashboardAdmin.php#products");
            exit();
        } else {
            $errors[] = "Terjadi kesalahan saat menyimpan data: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk Baru</title>
    <link rel="stylesheet" href="../public/styles/adminStyle.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f5f7; color: #333; }

        .layout { display: flex; justify-content: center; padding: 50px 20px; }
        .content { background: #fff; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }

        .topbar h1 { font-size: 1.8rem; margin-bottom: 15px; color: #4B0082; }

        .section-edit { margin-top: 20px; }
        .section-edit p.error { color: #b00020; margin-bottom: 15px; background: #fdd; padding: 10px; border-radius: 6px; }
        .section-edit p.success { color: #006400; margin-bottom: 15px; background: #dff0d8; padding: 10px; border-radius: 6px; }

        .form-edit { display: flex; flex-direction: column; gap: 15px; }
        .form-edit label { font-weight: 600; color: #4B0082; }
        .form-edit input { padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; transition: border 0.3s; }
        .form-edit input:focus { border-color: #4B0082; outline: none; }

        .btn-primary { background-color: #006400; color: #fff; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.3s; }
        .btn-primary:hover { background-color: #35006e; }

        .btn-secondary { background-color: #b00020; color: #fff; text-decoration: none; text-align: center; padding: 12px; border-radius: 6px; display: inline-block; transition: background 0.3s; }
        .btn-secondary:hover { background-color: #999; color: #fff; }

        @media(max-width:600px){ 
            .content { padding: 20px; }
            .topbar h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <div class="content">
            <div class="topbar">
                <h1>Tambah Produk Baru</h1>
            </div>

            <div class="section-edit">
                <?php if (!empty($errors)): ?>
                    <p class="error">
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?= $err ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </p>
                <?php endif; ?>

                <form class="form-edit" action="" method="POST">
                    <label>Nama Produk</label>
                    <input type="text" name="nama" value="<?= isset($nama) ? htmlspecialchars($nama) : '' ?>" required>

                    <label>Harga</label>
                    <input type="number" name="harga" value="<?= isset($harga) ? htmlspecialchars($harga) : '' ?>" required>

                    <label>Stok</label>
                    <input type="number" name="stok" value="<?= isset($stok) ? htmlspecialchars($stok) : '' ?>" required>

                    <button type="submit" class="btn-primary">Tambah Produk</button>
                    <a href="../dashboardAdmin.php#products" class="btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
