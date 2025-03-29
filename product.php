<?php
include("inc/baglan.php");
session_start();

// Giriş yapmış satıcının kimliği
if (!isset($_SESSION['kulanici_id'])) {
    echo "Lütfen giriş yapınız.";
    exit;
}

// Satıcının ürünlerini al
$satici_id = $_SESSION['kulanici_id']; // Giriş yapan satıcının ID'si
$sorgu = "SELECT * FROM product WHERE Supplier_id = $satici_id"; // Satıcıya ait ürünler
$urunler = mysqli_query($baglanti, $sorgu);

// Bildirimleri al
$bildirim_sorgu = "SELECT Notifications.Message, product.ProductName FROM Notifications INNER JOIN product   ON Notifications.Product_id = product.Product_id and product.Supplier_id='$satici_id'";
$bildirimler = mysqli_query($baglanti, $bildirim_sorgu);

// Ürün güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ProductId'])) {
    $productId = $_POST['ProductId'];
    $productName = $_POST['ProductName'];
    $productPrice = $_POST['ProductPrice'];
    $productStock = $_POST['ProductStock'];
    $productDescription = $_POST['ProductDescription'];

    // Ürünü güncelle
    $update_query = "UPDATE product SET ProductName = ?, ProductPrice = ?, ProductStock = ?, ProductDescription = ? WHERE Product_id = ?";
    $stmt = $baglanti->prepare($update_query);
    $stmt->bind_param("sdisi", $productName, $productPrice, $productStock, $productDescription, $productId);
    $stmt->execute();
    $stmt->close();

    echo '<script>alert("\u00dcr\u00fcn ba\u015far\u0131yla g\u00fcncellendi!"); window.location.href="product.php";</script>';
}

// Ürün silme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['DeleteProductId'])) {
    $productIdToDelete = $_POST['DeleteProductId'];

    // Ürünü silme sorgusu
    $delete_query = "DELETE FROM product WHERE Product_id = ?";
    $stmt = $baglanti->prepare($delete_query);
    $stmt->bind_param("i", $productIdToDelete);
    $stmt->execute();
    $stmt->close();

    echo '<script>alert("\u00dcr\u00fcn ba\u015far\u0131yla silindi!"); window.location.href="product.php";</script>';
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Satıcı Ürünleri</title>
</head>

<body>
    <div class="baslik">
        <h1><i>Ürünleriniz</i></h1>
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
                <div class="content">
                    <h2>Satıcı Ürünleri</h2>

                    <!-- Bildirimler -->
                    <!-- Bildirimler -->
                    <div class="alerts">
                        <h4>Bildirimler</h4>
                        <?php
                        if ($bildirimler && mysqli_num_rows($bildirimler) > 0) {
                            echo '<ul>';
                            while ($bildirim = mysqli_fetch_assoc($bildirimler)) {
                                echo '<li> Ürün: ' . htmlspecialchars($bildirim['ProductName']) . ' - ' . htmlspecialchars($bildirim['Message']) . '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>Hiçbir bildirim bulunmamaktadır.</p>';
                        }
                        ?>
                    </div>


                    <!-- Ürün listesi -->
                    <?php
                    if ($urunler && mysqli_num_rows($urunler) > 0) {
                        echo '<div class="row">';
                        while ($urun = mysqli_fetch_assoc($urunler)) {
                            $stok_uyarisi = ($urun['ProductStock'] < 10) ? '<span class="text-danger">(Stok Kritik!)</span>' : '';
                            echo '<div class="col-md-3">';
                            echo '<div class="card mb-4">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title product-name">' . htmlspecialchars($urun["ProductName"]) . '</h5>';
                            echo '<p class="card-text product-description">Açıklama: ' . htmlspecialchars($urun["ProductDescription"]) . '</p>';
                            echo '<p class="card-text product-price">Fiyat: ' . htmlspecialchars($urun["ProductPrice"]) . ' TL</p>';
                            echo '<p class="card-text product-stock">Stok: ' . htmlspecialchars($urun["ProductStock"]) . ' ' . $stok_uyarisi . '</p>';
                            // Düzenle butonu
                            echo '<button class="btn btn-warning" data-toggle="modal" data-target="#editModal" onclick="fillEditForm(' . $urun["Product_id"] . ')">Ürünü Düzenle</button>';
                            // Silme formu
                            echo '<form method="POST" class="mt-2" action="" style="display:inline;">
                                    <input type="hidden" name="DeleteProductId" value="' . $urun["Product_id"] . '">
                                    <button type="submit" class="btn btn-danger">Sil</button>
                                  </form>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>Ürününüz bulunmamaktadır.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Düzenle -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Ürün Düzenle</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editProductId" name="ProductId">
                        <div class="form-group">
                            <label for="editProductName">Ürün Adı:</label>
                            <input type="text" class="form-control" id="editProductName" name="ProductName" required>
                        </div>
                        <div class="form-group">
                            <label for="editProductPrice">Fiyat:</label>
                            <input type="number" class="form-control" id="editProductPrice" name="ProductPrice" required>
                        </div>
                        <div class="form-group">
                            <label for="editProductStock">Stok Miktarı:</label>
                            <input type="number" class="form-control" id="editProductStock" name="ProductStock" required>
                        </div>
                        <div class="form-group">
                            <label for="editProductDescription">Açıklama:</label>
                            <textarea class="form-control" id="editProductDescription" name="ProductDescription" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS ve JQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Modal formu doldurmak için JavaScript
        function fillEditForm(productId) {
            var row = document.querySelector('button[data-target="#editModal"][onclick*="' + productId + '"]').closest('.card-body');
            var productName = row.querySelector('.product-name').innerText;
            var productPrice = row.querySelector('.product-price').innerText.replace("Fiyat: ", "").replace(" TL", "");
            var productStock = row.querySelector('.product-stock').innerText.replace("Stok: ", "");
            var productDescription = row.querySelector('.product-description').innerText.replace("Açıklama: ", "");

            document.getElementById('editProductId').value = productId;
            document.getElementById('editProductName').value = productName;
            document.getElementById('editProductPrice').value = productPrice;
            document.getElementById('editProductStock').value = productStock;
            document.getElementById('editProductDescription').value = productDescription;
        }
    </script>
</body>

</html>