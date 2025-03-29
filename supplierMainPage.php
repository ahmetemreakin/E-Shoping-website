<?php
include("inc/baglan.php");
session_start();
// Kullanıcı oturumu kontrolü
if (!isset($_SESSION['kulanici_id'])) {
    header("Location: supplierLoginPage.php"); // Giriş sayfasına yönlendirin.
    exit;
}

if (isset($_POST["ürün_ekle"])) {
    $productName = $_POST["productName"];
    $productPrice = $_POST["productPrice"];
    $productStock = $_POST["productStock"];
    $productDescription = $_POST["productDescription"];
    $supplierID = $_SESSION["kulanici_id"];
    $category = $_POST["category"];

    // Ürünü ekle
    $secim = "INSERT INTO product (ProductName, ProductPrice, ProductStock, ProductDescription, Category_id, Supplier_id) 
              VALUES ('$productName', '$productPrice', '$productStock', '$productDescription', '$category', '$supplierID')";
    if (mysqli_query($baglanti, $secim)) {
        echo '<div class="alert alert-success" role="alert">Başarıyla ürünü eklediniz!</div>';
    } else {
        echo "Ürün eklenemedi: " . mysqli_error($baglanti);
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
    <title>Satıcı Paneli</title>
</head>

<body>
    <div class="baslik">
        <h1><i>Satıcı Paneli</i></h1>
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
                <h2>Ürün Yönetimi</h2>

                <!-- Ürün ekleme formu -->
                <div class="form-container">
                    <h4>Yeni Ürün Ekle</h4>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="productName">Ürün Adı</label>
                            <input type="text" class="form-control" id="productName" name="productName" required>
                        </div>
                        <div class="form-group">
                            <label for="productDescription">Açıklama</label>
                            <input type="text" class="form-control" id="productDescription" name="productDescription" required>
                        </div>
                        <div class="form-group">
                            <label for="productPrice">Fiyat</label>
                            <input type="number" class="form-control" id="productPrice" name="productPrice" required>
                        </div>
                        <div class="form-group">
                            <label for="productStock">Stok</label>
                            <input type="number" class="form-control" id="productStock" name="productStock" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <select class="form-control" id="category" name="category">
                                <?php
                                $query = "SELECT Category_id, CategoryName FROM category";
                                $result = mysqli_query($baglanti, $query);

                                if ($result && mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<option value="' . htmlspecialchars($row['Category_id']) . '">' . htmlspecialchars($row['CategoryName']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">Kategori Bulunamadı</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="ürün_ekle" class="btn btn-primary">Ürün Ekle</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS ve JQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>