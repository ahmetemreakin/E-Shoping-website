<?php
session_start();
include("inc/baglan.php");

// Favorilerden ürün silme
if (isset($_GET['delete'])) {
    $favorite_id = (int)$_GET['delete'];
    mysqli_query($baglanti, "DELETE FROM favorites WHERE Favorite_id='$favorite_id'");
    header("Location: favorites.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Kategoriler</title>
</head>

<body>
<div class="kategori"> <!-- DB'den kategoriler alınacak -->
    <div class="m-1">
        
            <h3>Kategoriler</h3>
            <ul>
            <a href="mainservices.php">Servisler</a>
            <li><a href="mainpage.php?kat_id=0">Tüm Ürünler</a></li>
            
                <?php
                // Kategorileri al
                $kategoriler = $baglanti->query("SELECT * FROM category");
        
                if ($kategoriler->num_rows > 0) {
                    while ($kategori = $kategoriler->fetch_assoc()) {
                        echo '<li><a href="?kat_id=' . $kategori["Category_id"] . '">' . htmlspecialchars($kategori["CategoryName"]) . '</a></li>';
                    }
                } else {
                    echo "<li>Henüz kategori eklenmemiş.</li>";
                }

                ?>
            </ul>
        </div>

        <div class="kat_user">
            <!-- Kullanıcı durumu -->
            <?php
            if (isset($_SESSION["email"])) { // Kullanıcı giriş yaptıysa
                echo '<p>Hoşgeldiniz, <b>' . htmlspecialchars($_SESSION["kulAdı"]) . '</b></p>';
                echo '<a href="favorites.php" class="kat_login"">Favorilerim</a>'; // Favorilerim bağlantısı
                echo '<a href="cart.php" class="kat_login"">Sepetim</a>'; // SEPET bağlantısı
                echo '<a href="orders.php" class="kat_login"">Siparişlerim</a>'; // Siparişlerim bağlantısı
                echo '<a href="logout.php" class="kat_logout"">Çıkış Yap</a>'; // Çıkış bağlantısı

            } else { // Giriş yapılmamışsa
                echo '<a href="loginPage.php" class="kat_login;">Giriş Yap</a>'; // Giriş bağlantısı
            }
            ?>
        </div>
    </div>
</body>

</html>