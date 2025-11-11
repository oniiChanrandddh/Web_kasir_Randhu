<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../dashboardAdmin.php");
    exit();
}

$memberId = intval($_GET['id']);
$res = $conn->query("SELECT * FROM members WHERE id = $memberId");
if ($res->num_rows === 0) {
    echo "Member tidak ditemukan.";
    exit();
}
$member = $res->fetch_assoc();

$level_diskon = [
    'Bronze' => 2,
    'Silver' => 5,
    'Gold' => 10,
    'Platinum' => 15
];

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = $conn->real_escape_string($_POST['nama']);
    $no_hp = $conn->real_escape_string($_POST['no_hp']);
    $level = $_POST['level'];

    if ($nama === "" || $no_hp === "" || !in_array($level, array_keys($level_diskon))) {
        $error = "Semua field harus diisi dengan benar.";
    } else {
        $diskon = $level_diskon[$level];
        $update = $conn->prepare("UPDATE members SET nama=?, no_hp=?, level=?, diskon_member=? WHERE id=?");
        $update->bind_param("sssdi", $nama, $no_hp, $level, $diskon, $memberId);
        if ($update->execute()) {
            $success = "Data member berhasil diperbarui!";
            $member = ['nama' => $nama, 'no_hp' => $no_hp, 'level' => $level, 'diskon_member' => $diskon];
        } else {
            $error = "Terjadi kesalahan: " . $conn->error;
        }
        $update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Member</title>
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
        .diskon-info { font-size: 0.95rem; color: #006400; margin-top: -5px; }
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
        <main class="content">
            <header class="topbar">
                <h1>Edit Member</h1>
            </header>
            <div class="section section-edit">
                <?php if($error): ?>
                    <p class="error"><?= $error ?></p>
                <?php endif; ?>
                <?php if($success): ?>
                    <p class="success"><?= $success ?></p>
                <?php endif; ?>
                <form method="POST" class="form-edit">
                    <label for="nama">Nama Member</label>
                    <input id="nama" type="text" name="nama" value="<?= htmlspecialchars($member['nama']) ?>" required>
                    
                    <label for="no_hp">Nomor HP</label>
                    <input id="no_hp" type="text" name="no_hp" value="<?= htmlspecialchars($member['no_hp']) ?>" required>

                    <label for="level">Level Keanggotaan</label>
                    <select id="level" name="level" required>
                        <option value="">Pilih Level</option>
                        <?php foreach ($level_diskon as $lvl => $disc): ?>
                            <option value="<?= $lvl ?>" <?= $member['level'] === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="diskon-info" id="diskonInfo"></p>

                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    <a href="../dashboardAdmin.php#members" class="btn-secondary">Batal</a>
                </form>
            </div>
        </main>
    </div>

    <script>
        const levelSelect = document.getElementById('level');
        const diskonInfo = document.getElementById('diskonInfo');
        const levelDiskon = {
            'Bronze': 2,
            'Silver': 5,
            'Gold': 10,
            'Platinum': 15
        };

        function updateDiskonInfo() {
            const selected = levelSelect.value;
            diskonInfo.textContent = selected && levelDiskon[selected]
                ? `Diskon untuk level ${selected} adalah ${levelDiskon[selected]}%`
                : '';
        }

        levelSelect.addEventListener('change', updateDiskonInfo);
        updateDiskonInfo();
    </script>
</body>
</html>
