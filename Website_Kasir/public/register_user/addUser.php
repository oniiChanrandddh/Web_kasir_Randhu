<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: ../public/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = $_POST['role'];

    $errors = [];

    if (empty($username)) $errors[] = "Username wajib diisi.";
    if (empty($password)) $errors[] = "Password wajib diisi.";
    if ($role !== "admin" && $role !== "kasir") $errors[] = "Role tidak valid.";

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Username sudah terdaftar.";
    $stmt->close();

    if (count($errors) === 0) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashedPassword, $role);
        if ($stmt->execute()) {
            header("Location: ../dashboardAdmin.php");
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
    <title>Tambah User Baru</title>
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
        .form-edit input, .form-edit select { padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; transition: border 0.3s; }
        .form-edit input:focus, .form-edit select:focus { border-color: #4B0082; outline: none; }

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
                <h1>Tambah User Baru</h1>
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
                    <label>Username</label>
                    <input type="text" name="username" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" required>

                    <label>Password</label>
                    <input type="password" name="password" required>

                    <label>Role</label>
                    <select name="role" required>
                        <option value="">Pilih Role</option>
                        <option value="admin" <?= isset($role) && $role === "admin" ? 'selected' : '' ?>>Admin</option>
                        <option value="kasir" <?= isset($role) && $role === "kasir" ? 'selected' : '' ?>>Kasir</option>
                    </select>

                    <button type="submit" class="btn-primary">Tambah User</button>
                    <a href="../dashboardAdmin.php" class="btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
