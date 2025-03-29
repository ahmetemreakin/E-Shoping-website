<?php
session_start();
include 'inc/baglan.php';

// Kullanıcı giriş kontrolü
$kul_id = $_SESSION['kulanici_id'] ?? null; // Kullanıcı ID'sini session'dan al

$customer_id = (int)$_SESSION['customer_id']; // customer_id'yi session'dan al

// Favorilerden ürün silme
if (isset($_GET['delete'])) {
    $favorite_id = (int)$_GET['delete'];

    // Favoriler tablosunda ürün silme
    $sil_sorgu = "DELETE FROM favorites WHERE Favorite_id = '$favorite_id' AND Customer_id = '$customer_id'";
    if (mysqli_query($baglanti, $sil_sorgu)) {
        header("Location: favorites.php"); // Başarılı silme sonrası favoriler sayfasına yönlendir
        exit();
    } else {
        echo "Bir hata oluştu, favori silinemedi.";
    }
}

// Ürün favorilere ekleme
if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];

    // Kullanıcı ID'sini kontrol et
    if ($customer_id) {
        // Favorilerde aynı ürün var mı kontrolü
        $kontrol = mysqli_query($baglanti, "SELECT * FROM favorites WHERE Product_id='$product_id' AND Customer_id='$customer_id'");

        if (mysqli_num_rows($kontrol) === 0) {
            // Yoksa favorilere ekle
            $tarih = date('Y-m-d H:i:s');
            $insert_sorgu = "INSERT INTO favorites (Product_id, Customer_id, AddedDate) VALUES ('$product_id', '$customer_id', '$tarih')";
            if (mysqli_query($baglanti, $insert_sorgu)) {
                header("Location: favorites.php"); // Başarılı ekleme sonrası favoriler sayfasına yönlendir
                exit();
            } else {
                echo "Favorilere eklerken bir hata oluştu.";
            }
        }
    } else {
        echo "Giriş yapmadınız, favori ekleme işlemi yapılamaz.";
    }
}

// Favori ürünleri listeleme
$favoriler = mysqli_query($baglanti, "
    SELECT f.Favorite_id, f.AddedDate, p.ProductName, p.ProductPrice, p.Product_id
    FROM favorites f
    JOIN product p ON f.Product_id = p.Product_id
    WHERE f.Customer_id = '$customer_id'"); // Kullanıcıya özel favorileri listele
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Favorilerim</title>
</head>

<body>
    <div class="baslik ">
        <h1><i>Alışverişin Adresi</i></h1>
    </div>
    <div class="container mt-5">
        <h2>Favorilerim</h2>
        <div class="row h-100">
            <div class="kategori col-md-2 mt-2">
                <a href="mainPage.php" class="kat_login">Ana Sayfa</a>
            </div>
            <div class="col-md-10 mt-2">
                <table class="table">
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Fiyat</th>
                        <th>Eklenme Tarihi</th>
                        <th>İşlem</th>
                        <th>Sepete Ekle</th> <!-- Sepete Ekle butonu -->
                    </tr>
                    <?php
                    if ($favoriler && mysqli_num_rows($favoriler) > 0) {
                        while ($favori = mysqli_fetch_assoc($favoriler)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($favori["ProductName"]) . '</td>';
                            echo '<td>' . htmlspecialchars($favori["ProductPrice"]) . ' TL</td>';
                            echo '<td>' . htmlspecialchars($favori["AddedDate"]) . '</td>';
                            echo '<td><a href="favorites.php?delete=' . $favori["Favorite_id"] . '" class="btn btn-danger">Sil</a></td>';
                            // Sepete Ekle butonu
                            echo '<td><a href="cart.php?add_to_cart=' . $favori["Product_id"] . '" class="btn btn-success">Sepete Ekle</a></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">Favoriler listenizde ürün bulunmamaktadır.</td></tr>';
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>

</html>