<?php
include("inc/baglan.php");


$kat_id = isset($_GET['kat_id']) ? (int)$_GET['kat_id'] : 0; // Eğer parametre yoksa 0 olarak ayarla


?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Ana Sayfa</title>
</head>

<body>
    <div class="baslik ">
        <h1><i>Alışverişin Adresi</i></h1>
    </div>
    <div class="anaEkran">
        <div class="row h-100">
            <div class="col-md-2 mt-2">
                <?php include 'kategori.php'; ?>
            </div>
            <div class="col-md-10 mt-2">
                <div class="content">
                    <h2>Ana Sayfa</h2>
                    
                    <?php
                    // SQL sorgusu
                    $sorgu = "SELECT * FROM product";
                    if ($kat_id > 0) {
                        $sorgu .= " WHERE Category_id = $kat_id"; // Kategori filtresi
                    }

                    $urunler = mysqli_query($baglanti, $sorgu);

                    if ($urunler && mysqli_num_rows($urunler) > 0) {
                        echo '<div class="row">';
                        while ($urun = mysqli_fetch_assoc($urunler)) {
                            echo '<div class="col-md-3">';
                            echo '<div class="card mb-4">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title">' . htmlspecialchars($urun["ProductName"]) . '</h5>';
                            echo '<p class="card-text">Fiyat: ' . htmlspecialchars($urun["ProductPrice"]) . ' TL</p>';
                            echo '<p class="card-text">Stok: ' . htmlspecialchars($urun["ProductStock"]) . '</p>';
                            echo '<p class="card-text">Ürün Açıklaması: ' . htmlspecialchars($urun["ProductDescription"]) . '</p>';
                            // Favorilere ekle butonu
                            if (isset($_SESSION['kulanici_id'])) {
                                echo '<a href="favorites.php?product_id=' . htmlspecialchars($urun["Product_id"]) . '" class="btn btn-primary mr-3">Favorilere Ekle</a>';
                            } else {
                                echo '<a href="loginPage.php" class="btn btn-secondary p-2">Favorilere Ekle</a>';
                            }
                            if (isset($_SESSION['email'])) {
                                echo '<a href="cart.php?id=' . htmlspecialchars($urun["Product_id"]) . '" class="btn btn-success ml-3">Sepete Ekle</a>';
                            }
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>Bu kategoride ürün bulunmamaktadır.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>