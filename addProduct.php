<?php
include("inc/baglan.php");
session_start();

// Kullanıcı oturumu kontrolü
if (!isset($_SESSION['kulanici_id'])) {
    header("Location: supplierLoginPage.php"); // Giriş sayfasına yönlendir
    exit;
}

// Form gönderildiğinde ürün ekleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productName = mysqli_real_escape_string($baglanti, $_POST['productName']);
    $productPrice = mysqli_real_escape_string($baglanti, $_POST['productPrice']);
    $productStock = mysqli_real_escape_string($baglanti, $_POST['productStock']);
    $productDescription = mysqli_real_escape_string($baglanti, $_POST['productDescription']);
    $categoryID = mysqli_real_escape_string($baglanti, $_POST['categoryID']);
    $discount = mysqli_real_escape_string($baglanti, $_POST['discount']);

    $sellerID = $_SESSION['kulanici_id']; // Giriş yapan satıcının ID'si
    $query_cam="INSERT INTO campaign(Discount) value ('$discount')";
    if (mysqli_query($baglanti, $query_cam)) {
        $campaign_id = mysqli_insert_id($baglanti);

    } 


    // Ürün ekleme sorgusu
    $query = "INSERT INTO product (ProductName, ProductPrice, ProductStock, ProductDescription, Category_id, Supplier_id,Campaign_id) 
              VALUES ('$productName', '$productPrice', '$productStock', '$productDescription', '$categoryID', '$sellerID','$campaign_id')";

    if (mysqli_query($baglanti, $query)) {
        $message = "Ürün başarıyla eklendi.";
    } else {
        $message = "Ürün eklenirken bir hata oluştu: " . mysqli_error($baglanti);
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
    <title>Yeni Ürün Ekle</title>
</head>

<body>
    <div class="baslik">
        <h1><i>Yeni Ürün Ekle</i></h1>
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
                <h2>Ürün Bilgilerini Girin</h2>
                <?php if (isset($message)) { ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php } ?>
                <form method="POST" action="addProduct.php">
                    <div class="form-group">
                        <label for="productName">Ürün Adı</label>
                        <input type="text" class="form-control" id="productName" name="productName" required>
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Ürün Fiyatı (TL)</label>
                        <input type="number" class="form-control" id="productPrice" name="productPrice" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="productStock">Stok Miktarı</label>
                        <input type="number" class="form-control" id="productStock" name="productStock" required>
                    </div>
                    <div class="form-group">
                        <label for="discount">İndirim </label>
                        <input type="number" class="form-control" id="discount" name="discount" required>
                    </div>
                    <div class="form-group">
                        <label for="productDescription">Ürün Açıklaması</label>
                        <textarea class="form-control" id="productDescription" name="productDescription" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="categoryID">Kategori</label>
                        <select class="form-control" id="categoryID" name="categoryID" required>
                            <?php
                            // Kategorileri çek ve seçime ekle
                            $categoryQuery = "SELECT Category_id, CategoryName FROM category";
                            $categories = mysqli_query($baglanti, $categoryQuery);
                            while ($category = mysqli_fetch_assoc($categories)) {
                                echo '<option value="' . htmlspecialchars($category['Category_id']) . '">' . htmlspecialchars($category['CategoryName']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Ürün Ekle</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>