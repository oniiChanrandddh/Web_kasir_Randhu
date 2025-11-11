    <?php
    session_start();
    include("../../config/db.php");

    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'kasir'])) {
        header("Location: ../../public/index.php");
        exit();
    }


    $level_diskon = [
        'Bronze' => 2,
        'Silver' => 5,
        'Gold' => 10,
        'Platinum' => 15
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama   = trim($_POST['nama']);
        $no_hp  = trim($_POST['no_hp']);
        $level  = $_POST['level'];

        $errors = [];

        if (empty($nama)) $errors[] = "Nama wajib diisi.";
        if (empty($no_hp)) $errors[] = "Nomor HP wajib diisi.";
        if (!in_array($level, array_keys($level_diskon))) $errors[] = "Level keanggotaan tidak valid.";

        $stmt = $conn->prepare("SELECT id FROM members WHERE no_hp = ?");
        $stmt->bind_param("s", $no_hp);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Nomor HP sudah terdaftar.";
        $stmt->close();

        if (count($errors) === 0) {
            $diskon = $level_diskon[$level];
            $stmt = $conn->prepare("INSERT INTO members (nama, no_hp, diskon_member, level, total_transaksi, total_spent, tanggal_daftar) VALUES (?, ?, ?, ?, 0, 0.00, NOW())");
            $stmt->bind_param("ssds", $nama, $no_hp, $diskon, $level);

            if ($stmt->execute()) {
                if ($_SESSION['role'] === 'admin') {
                    header("Location: ../dashboardAdmin.php#members");
                } elseif ($_SESSION['role'] === 'kasir') {
                    header("Location: ../dashboardKasir.php#members");
                } else {

                    header("Location: ../../public/index.php");
                }
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
        <title>Tambah Member Baru</title>
        <link rel="stylesheet" href="../public/styles/adminStyle.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            body {
                background-color: #f5f5f7;
                color: #333;
            }

            .layout {
                display: flex;
                justify-content: center;
                padding: 50px 20px;
            }

            .content {
                background: #fff;
                padding: 30px;
                border-radius: 12px;
                width: 100%;
                max-width: 500px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .topbar h1 {
                font-size: 1.8rem;
                margin-bottom: 15px;
                color: #4B0082;
            }

            .section-edit {
                margin-top: 20px;
            }

            .section-edit p.error {
                color: #b00020;
                margin-bottom: 15px;
                background: #fdd;
                padding: 10px;
                border-radius: 6px;
            }

            .section-edit p.success {
                color: #006400;
                margin-bottom: 15px;
                background: #dff0d8;
                padding: 10px;
                border-radius: 6px;
            }

            .form-edit {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .form-edit label {
                font-weight: 600;
                color: #4B0082;
            }

            .form-edit input,
            .form-edit select {
                padding: 10px 12px;
                border: 1px solid #ccc;
                border-radius: 6px;
                font-size: 1rem;
                transition: border 0.3s;
            }

            .form-edit input:focus,
            .form-edit select:focus {
                border-color: #4B0082;
                outline: none;
            }

            .diskon-info {
                font-size: 0.95rem;
                color: #006400;
                margin-top: -5px;
            }

            .btn-primary {
                background-color: #006400;
                color: #fff;
                border: none;
                padding: 12px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 600;
                transition: background 0.3s;
            }

            .btn-primary:hover {
                background-color: #35006e;
            }

            .btn-secondary {
                background-color: #b00020;
                color: #fff;
                text-decoration: none;
                text-align: center;
                padding: 12px;
                border-radius: 6px;
                display: inline-block;
                transition: background 0.3s;
            }

            .btn-secondary:hover {
                background-color: #999;
                color: #fff;
            }

            @media(max-width:600px) {
                .content {
                    padding: 20px;
                }

                .topbar h1 {
                    font-size: 1.5rem;
                }
            }
        </style>
    </head>

    <body>
        <div class="layout">
            <div class="content">
                <div class="topbar">
                    <h1>Tambah Member Baru</h1>
                </div>
                <div class="section-edit">
                    <?php if (!empty($errors)): ?>
                        <p class="error">
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        </p>
                    <?php endif; ?>
                    <form class="form-edit" action="" method="POST">
                        <label>Nama Member</label>
                        <input type="text" name="nama" value="<?= isset($nama) ? htmlspecialchars($nama) : '' ?>" required>

                        <label>No. HP</label>
                        <input type="text" name="no_hp" value="<?= isset($no_hp) ? htmlspecialchars($no_hp) : '' ?>" required>

                        <label>Level Keanggotaan</label>
                        <select name="level" id="level" required>
                            <option value="">Pilih Level</option>
                            <option value="Bronze" <?= isset($level) && $level === "Bronze" ? 'selected' : '' ?>>Bronze</option>
                            <option value="Silver" <?= isset($level) && $level === "Silver" ? 'selected' : '' ?>>Silver</option>
                            <option value="Gold" <?= isset($level) && $level === "Gold" ? 'selected' : '' ?>>Gold</option>
                            <option value="Platinum" <?= isset($level) && $level === "Platinum" ? 'selected' : '' ?>>Platinum</option>
                        </select>

                        <p class="diskon-info" id="diskonInfo"></p>

                        <button type="submit" class="btn-primary">Tambah Member</button>
                        <a href="<?=
                                    ($_SESSION['role'] === 'admin')
                                        ? '../dashboardAdmin.php#members'
                                        : '../dashboardKasir.php#members'
                                    ?>" class="btn-secondary">Batal</a>

                    </form>
                </div>
            </div>
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

            levelSelect.addEventListener('change', () => {
                const selected = levelSelect.value;
                if (selected && levelDiskon[selected]) {
                    diskonInfo.textContent = `Diskon untuk level ${selected} adalah ${levelDiskon[selected]}%`;
                } else {
                    diskonInfo.textContent = '';
                }
            });

            <?php if (isset($level) && array_key_exists($level, $level_diskon)): ?>
                diskonInfo.textContent = "Diskon untuk level <?= htmlspecialchars($level) ?> adalah <?= $level_diskon[$level] ?>%";
            <?php endif; ?>
        </script>
    </body>

    </html>