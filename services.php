<?php
include("inc/baglan.php");
session_start();

// Giriş yapmış satıcının kimliği
if (!isset($_SESSION['kulanici_id'])) {
    echo "Lütfen giriş yapınız.";
    exit;
}

// Satıcının hizmetlerini al
$satici_id = $_SESSION['kulanici_id']; // Giriş yapan satıcının ID'si
$sorgu = "SELECT * FROM services WHERE Supplier_id = $satici_id"; // Satıcıya ait hizmetler
$hizmetler = mysqli_query($baglanti, $sorgu);

// Hizmet güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ServiceId'])) {
    $serviceId = $_POST['ServiceId'];
    $serviceName = $_POST['ServiceName'];
    $servicePrice = $_POST['ServicePrice'];
    $serviceDescription = $_POST['ServiceDescription'];

    // Hizmeti güncelle
    $update_query = "UPDATE services SET ServiceName = ?, ServicePrice = ?, ServiceDescription = ? WHERE Service_id = ?";
    $stmt = $baglanti->prepare($update_query);
    $stmt->bind_param("sdsi", $serviceName, $servicePrice, $serviceDescription, $serviceId);
    $stmt->execute();
    $stmt->close();

    echo '<script>alert("\u00c7e\u015fim ba\u015far\u0131yla g\u00fcncellendi!"); window.location.href="services.php";</script>';
}

// Hizmet silme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['DeleteServiceId'])) {
    $serviceIdToDelete = $_POST['DeleteServiceId'];

    // Hizmeti silme sorgusu
    $delete_query = "DELETE FROM services WHERE Service_id = ?";
    $stmt = $baglanti->prepare($delete_query);
    $stmt->bind_param("i", $serviceIdToDelete);
    $stmt->execute();
    $stmt->close();

    echo '<script>alert("\u00c7e\u015fim ba\u015far\u0131yla silindi!"); window.location.href="services.php";</script>';
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Satıcı Hizmetleri</title>
</head>

<body>
    <div class="baslik">
        <h1><i>Hizmetleriniz</i></h1>
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
                    <h2>Satıcı Hizmetleri</h2>

                    <!-- Hizmet listesi -->
                    <?php
                    if ($hizmetler && mysqli_num_rows($hizmetler) > 0) {
                        echo '<div class="row">';
                        while ($hizmet = mysqli_fetch_assoc($hizmetler)) {
                            echo '<div class="col-md-3">';
                            echo '<div class="card mb-4">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title service-name">' . htmlspecialchars($hizmet["ServiceName"]) . '</h5>';
                            echo '<p class="card-text service-description">Açıklama: ' . htmlspecialchars($hizmet["ServiceDescription"]) . '</p>';
                            echo '<p class="card-text service-price">Fiyat: ' . htmlspecialchars($hizmet["ServicePrice"]) . ' TL</p>';
                            // Düzenle butonu
                            echo '<button class="btn btn-warning" data-toggle="modal" data-target="#editModal" onclick="fillEditForm(' . $hizmet["Service_id"] . ')">Hizmeti Düzenle</button>';
                            // Silme formu
                            echo '<form method="POST" class="mt-2" action="" style="display:inline;">
                                    <input type="hidden" name="DeleteServiceId" value="' . $hizmet["Service_id"] . '">
                                    <button type="submit" class="btn btn-danger">Sil</button>
                                  </form>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>Hizmetiniz bulunmamaktadır.</p>';
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
                        <h5 class="modal-title" id="editModalLabel">Hizmet Düzenle</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editServiceId" name="ServiceId">
                        <div class="form-group">
                            <label for="editServiceName">Hizmet Adı:</label>
                            <input type="text" class="form-control" id="editServiceName" name="ServiceName" required>
                        </div>
                        <div class="form-group">
                            <label for="editServicePrice">Fiyat:</label>
                            <input type="number" class="form-control" id="editServicePrice" name="ServicePrice" required>
                        </div>
                        <div class="form-group">
                            <label for="editServiceDescription">Açıklama:</label>
                            <textarea class="form-control" id="editServiceDescription" name="ServiceDescription" rows="3" required></textarea>
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
        function fillEditForm(serviceId) {
            var row = document.querySelector('button[data-target="#editModal"][onclick*="' + serviceId + '"]').closest('.card-body');
            var serviceName = row.querySelector('.service-name').innerText;
            var servicePrice = row.querySelector('.service-price').innerText.replace("Fiyat: ", "").replace(" TL", "");
            var serviceDescription = row.querySelector('.service-description').innerText.replace("Açıklama: ", "");

            document.getElementById('editServiceId').value = serviceId;
            document.getElementById('editServiceName').value = serviceName;
            document.getElementById('editServicePrice').value = servicePrice;
            document.getElementById('editServiceDescription').value = serviceDescription;
        }
    </script>
</body>

</html>