<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: index.php");
    exit();
}

$pageTitle = "Dashboard Admin";
$username = $_SESSION['username'];

$userResult = $conn->query("SELECT * FROM users ORDER BY id ASC");
$barangResult = $conn->query("SELECT * FROM barang ORDER BY nama ASC");
$memberResult = $conn->query("SELECT * FROM members ORDER BY tanggal_daftar DESC");

$salesResult = $conn->query("
    SELECT t.id AS transaksi_id, t.tanggal, b.nama AS barang_nama, dt.jumlah, dt.subtotal, u.username AS kasir
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN barang b ON dt.barang_id = b.id
    JOIN users u ON t.kasir_id = u.id
    ORDER BY t.tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../public/styles/adminStyle.css">

    <style>
        .btn-print,
        .btn-add {
            background-color: #6a11cb;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.3s, transform 0.2s;
            box-shadow: 0 2px 6px rgba(106, 17, 203, 0.3);
            text-decoration: none;
            display: inline-block;
            margin-bottom: 10px;
        }

        .btn-print:hover,
        .btn-add:hover {
            background-color: #9c88ff;
            transform: translateY(-1px);
        }

        .btn-edit,
        .btn-delete {
            background-color: #6a11cb;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s, transform 0.2s;
        }

        .btn-delete {
            background-color: #ff6b6b;
        }

        .btn-edit:hover {
            background-color: #9c88ff;
            transform: translateY(-1px);
        }

        .btn-delete:hover {
            background-color: #e55a5a;
            transform: translateY(-1px);
        }

        @media print {

            .sidebar,
            .topbar,
            .section:not(#sales),
            .btn-print,
            .btn-add {
                display: none !important;
            }

            #laporan {
                display: block !important;
                position: static !important;
                width: 100% !important;
                padding: 20px 30px !important;
                background: #fff !important;
                color: #333 !important;
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                box-shadow: none !important;
            }

            html,
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="logo">
                <h2>Kasir<span>App</span></h2>
            </div>
            <nav class="menu">
                <ul>
                    <li><a href="#overview">Overview</a></li>
                    <li><a href="#users">Kelola User</a></li>
                    <li><a href="#products">Manajemen Barang</a></li>
                    <li><a href="#members">Manajemen Member</a></li>
                    <li><a href="#sales">Laporan Penjualan</a></li>
                    <li><a href="logout.php" class="logout">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="topbar">
                <h1>Dashboard Admin</h1>
                <p>Halo, <b><?= $username ?></b></p>
            </header>

            <section id="overview" class="section">
                <header class="section-header">
                    <h2>Overview</h2>
                </header>
                <div class="section-body cards">
                    <div class="card">
                        <h3>Total User</h3>
                        <p><?= $userResult->num_rows ?></p>
                    </div>
                    <div class="card">
                        <h3>Total Barang</h3>
                        <p><?= $barangResult->num_rows ?></p>
                    </div>
                    <div class="card">
                        <?php
                        $totalPenjualan = 0;
                        $salesTemp = $conn->query("
                            SELECT subtotal 
                            FROM transaksi t
                            JOIN detail_transaksi dt ON t.id = dt.transaksi_id
                            WHERE DATE(t.tanggal) = CURDATE()
                        ");
                        while ($row = $salesTemp->fetch_assoc()) {
                            $totalPenjualan += $row['subtotal'];
                        }
                        ?>
                        <h3>Penjualan Hari Ini</h3>
                        <p>Rp <?= number_format($totalPenjualan, 0, ",", ".") ?></p>
                    </div>
                </div>
            </section>

            <section id="users" class="section">
                <header class="section-header">
                    <h2>Kelola User</h2>
                </header>
                <a href="../public/register_user/addUser.php" class="btn-add">Tambah User Baru</a>
                <div class="section-body table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $userResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['username'] ?></td>
                                    <td><?= ucfirst($row['role']) ?></td>
                                    <td>
                                        <a href="edit_delete/edit_user.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                        <a href="edit_delete/hapus_user.php?id=<?= $row['id'] ?>" class="btn-delete">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="products" class="section">
                <header class="section-header">
                    <h2>Manajemen Barang</h2>
                </header>
                <a href="../public/tambah_barang/tambah_barang.php" class="btn-add">Tambah Produk Baru</a>
                <div class="section-body table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $barangResult = $conn->query("SELECT * FROM barang ORDER BY nama ASC");
                            while ($row = $barangResult->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?= $row['nama'] ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ",", ".") ?></td>
                                    <td><?= $row['stok'] ?></td>
                                    <td>
                                        <a href="edit_delete/edit_barang.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                        <a href="edit_delete/hapus_barang.php?id=<?= $row['id'] ?>" class="btn-delete">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="members" class="section">
                <header class="section-header">
                    <h2>Manajemen Member</h2>
                </header>
                <a href="../public/register_user/addMember.php" class="btn-add">Tambah Member Baru</a>
                <div class="section-body table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>No HP</th>
                                <th>Level</th>
                                <th>Diskon (%)</th>
                                <th>Total Transaksi</th>
                                <th>Total Spent</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $memberResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                    <td><?= htmlspecialchars($row['level']) ?></td>
                                    <td><?= number_format($row['diskon_member'], 2) ?>%</td>
                                    <td><?= $row['total_transaksi'] ?></td>
                                    <td>Rp <?= number_format($row['total_spent'], 0, ",", ".") ?></td>
                                    <td><?= date("d/m/Y", strtotime($row['tanggal_daftar'])) ?></td>
                                    <td>
                                        <a href="edit_delete/edit_member.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                        <a href="edit_delete/hapus_member.php?id=<?= $row['id'] ?>" class="btn-delete">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="sales" class="section">
                <header class="section-header">
                    <h2>Laporan Penjualan</h2>
                </header>
                <div id="laporan" class="section-body">
                    <ul>
                        <?php
                        $salesResult = $conn->query("
                            SELECT t.id AS transaksi_id, t.tanggal, b.nama AS barang_nama, dt.jumlah, dt.subtotal, u.username AS kasir
                            FROM transaksi t
                            JOIN detail_transaksi dt ON t.id = dt.transaksi_id
                            JOIN barang b ON dt.barang_id = b.id
                            JOIN users u ON t.kasir_id = u.id
                            ORDER BY t.tanggal DESC 
                        ");
                        while ($row = $salesResult->fetch_assoc()):
                        ?>
                            <li><?= date("d/m/Y H:i", strtotime($row['tanggal'])) ?> - <?= $row['barang_nama'] ?> (<?= $row['jumlah'] ?>) - Rp <?= number_format($row['subtotal'], 0, ",", ".") ?> (Kasir: <?= $row['kasir'] ?>)</li>
                        <?php endwhile; ?>
                    </ul>
                    <button onclick="printLaporan()" class="btn-print">Cetak Laporan</button>
                </div>
            </section>
        </main>
    </div>

    <script>
        function printLaporan() {
            window.print();
        }
    </script>
</body>

</html>