<?php
include("inc/baglan.php");
session_start();

// Kullanıcı oturumu kontrolü
if (!isset($_SESSION['kulanici_id'])) {
    header("Location: supplierLoginPage.php"); // Giriş sayfasına yönlendir
    exit;
}

// Form gönderildiğinde hizmet ekleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceName = mysqli_real_escape_string($baglanti, $_POST['serviceName']);
    $servicePrice = mysqli_real_escape_string($baglanti, $_POST['servicePrice']);
    $serviceDescription = mysqli_real_escape_string($baglanti, $_POST['serviceDescription']);
    $sellerID = $_SESSION['kulanici_id']; // Giriş yapan satıcının ID'si

    // Hizmet ekleme sorgusu
    $query = "INSERT INTO services (ServiceName, ServicePrice, ServiceDescription, Supplier_id) 
              VALUES ('$serviceName', '$servicePrice', '$serviceDescription', '$sellerID')";

    if (mysqli_query($baglanti, $query)) {
        $message = "Hizmet başarıyla eklendi.";
    } else {
        $message = "Hizmet eklenirken bir hata oluştu: " . mysqli_error($baglanti);
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Yeni Hizmet Ekle</title>
</head>

<body>
    <div class="baslik">
        <h1><i>Yeni Hizmet Ekle</i></h1>
    </div>
    <div class="anaEkran">
        <div class="row h-100">
            <div class="col-md-2 mt-2">
                <div class="menu">
                    <h4>Menü</h4>
                    <ul>
                        <li><a href="product.php">Ürünlerim</a></li>
                        <li><a href="services.php">Hizmetlerim</a></li>
                        <li><a href="suppplierorder.php">Siparişlerim</a></li>
                        <li><a href="addProduct.php">Yeni Ürün Ekle</a></li>
                        <li><a href="addServices.php">Yeni Hizmet Ekle</a></li>
                        <li><a href="logout.php">Çıkış Yap</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-10 mt-2">
                <h2>Hizmet Bilgilerini Girin</h2>
                <?php if (isset($message)) { ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php } ?>
                <form method="POST" action="addServices.php">
                    <div class="form-group">
                        <label for="serviceName">Hizmet Adı</label>
                        <input type="text" class="form-control" id="serviceName" name="serviceName" required>
                    </div>
                    <div class="form-group">
                        <label for="servicePrice">Hizmet Fiyatı (TL)</label>
                        <input type="number" class="form-control" id="servicePrice" name="servicePrice" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="serviceDescription">Hizmet Açıklaması</label>
                        <textarea class="form-control" id="serviceDescription" name="serviceDescription" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Hizmet Ekle</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>