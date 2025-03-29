<?php
include 'inc/baglan.php';
session_start();
$customer_id = (int)$_SESSION['customer_id'];

// Kullanıcı oturum kontrolü
if (!isset($_SESSION['kulanici_id']) || !isset($_SESSION['customer_id'])) {
    header("Location: loginPage.php");
    exit();
}
// Ürün sepete ekleme
if (isset($_GET['id']) || isset($_GET['add_to_cart'])) {
    if (isset($_GET['id'])) {
        $urun_id = (int)$_GET['id'];
    } elseif (isset($_GET['add_to_cart'])) {
        $urun_id = (int)$_GET['add_to_cart'];
    }
    // Sepette ürün var mı kontrolü
    $urun_kontrol = mysqli_query($baglanti, "SELECT * FROM cart WHERE Product_id='$urun_id' AND Customer_id='$customer_id'");
    if (mysqli_num_rows($urun_kontrol) > 0) {
        // Sepette varsa, Quantity artır
        mysqli_query($baglanti, "UPDATE cart SET Quantity=Quantity+1 WHERE Product_id='$urun_id' AND Customer_id='$customer_id'");
    } else {
        // Sepette yoksa yeni ürün ekle
        mysqli_query($baglanti, "INSERT INTO cart (Product_id, Quantity, Customer_id) VALUES ('$urun_id', 1, '$customer_id')");
    }

    header("Location: cart.php");
    exit();
}

// Ürün adet azaltma
if (isset($_GET['azalt'])) {
    $urun_id = (int)$_GET['azalt'];

    // Sepetteki ürünü çek
    $urun = mysqli_fetch_assoc(mysqli_query($baglanti, "SELECT * FROM cart WHERE Product_id='$urun_id'"));

    if ($urun['Quantity'] > 1) {
        // Adeti 1 azalt
        mysqli_query($baglanti, "UPDATE cart SET Quantity=Quantity-1 WHERE Product_id='$urun_id'");
    } else {
        // Sepetten ürünü sil
        mysqli_query($baglanti, "DELETE FROM cart WHERE Product_id='$urun_id'");
    }

    header("Location: cart.php");
    exit();
}

if (isset($_GET['sil'])) {
    $urun_id = (int)$_GET['sil'];

    // Sepetteki ürüne ait ödeme (payment) kaydı olup olmadığını kontrol et
    $payment_query = mysqli_query($baglanti, "SELECT Payment_id FROM payment WHERE Cart_id IN (SELECT Cart_id FROM cart WHERE Product_id='$urun_id')");
    $payment_row = mysqli_fetch_assoc($payment_query);
    $payment_id = $payment_row['Payment_id'];

    // Eğer ödeme kaydı varsa ve ödeme yapılmışsa, payment tablosu ve diğer ilişkili kayıtları sil
    if ($payment_id) {
        // Önce onlinepayment tablosundaki ilişkili kayıtları sil
        mysqli_query($baglanti, "DELETE FROM onlinepayment WHERE Payment_id='$payment_id'");

        // Ardından payment tablosundaki ilişkili kayıtları sil
        mysqli_query($baglanti, "DELETE FROM payment WHERE Payment_id='$payment_id'");

        // İlgili siparişleri sil
        mysqli_query($baglanti, "DELETE FROM orders WHERE Payment_id='$payment_id'");
    }

    // Sepetten ürünü sil
    mysqli_query($baglanti, "DELETE FROM cart WHERE Product_id='$urun_id'");

    header("Location: cart.php");
    exit();
}



// Sepet ürünlerini çek
$urunler = mysqli_query($baglanti, "
    SELECT p.*, c.Quantity 
    FROM cart c
    JOIN product p ON c.Product_id = p.Product_id
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['payment_method'])) {
        $payment_method = $_POST['payment_method'];
        if ($payment_method === 'cashonDelivery') {
            header("Location: cashonDelivery.php");
            exit();
        } elseif ($payment_method === 'onlinePayment') {
            header("Location: onlinePayment.php");
            exit();
        }
    } else {
        echo '<p style="color:red;">Lütfen ödeme yöntemini seçin.</p>';
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
    <title>Sepetim</title>
</head>

<body>
    <div class="baslik ">
        <h1><i>Alışverişin Adresi</i></h1>
    </div>
    <div class="container mt-5">
        <h2>Sepetim</h2>
        <div class="row h-100">
            <div class="kategori col-md-2 mt-2">
                <a href="mainPage.php" class="kat_login">Ana Sayfa</a>
            </div>
            <div class="col-md-10 mt-2">
                <table class="table">
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Adet</th>
                        <th>Fiyat</th>
                        <th>Toplam</th>
                        <th>İşlem</th>
                    </tr>
                    <?php
                    $toplam = 0;

                    while ($urun = mysqli_fetch_assoc($urunler)) {
                        $urun_toplam = $urun['ProductPrice'] * $urun['Quantity'];
                        $toplam += $urun_toplam;

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($urun['ProductName']) . "</td>";
                        echo "<td>" . $urun['Quantity'] . "</td>";
                        echo "<td>" . $urun['ProductPrice'] . " TL</td>";
                        echo "<td>" . $urun_toplam . " TL</td>";
                        echo '<td>';
                        echo '<a href="cart.php?id=' . $urun['Product_id'] . '" class="btn btn-success">+</a> ';
                        echo '<a href="cart.php?azalt=' . $urun['Product_id'] . '" class="btn btn-warning">-</a> ';
                        echo '<a href="cart.php?sil=' . $urun['Product_id'] . '" class="btn btn-danger">Sil</a>';
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
                <?php
                if ($toplam > 0): ?>
                    <h4>Toplam: <?= $toplam ?> TL</h4>
                    <form method="POST">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash_on_delivery" value="cashonDelivery">
                            <label class="form-check-label" for="cash_on_delivery">Kapıda Ödeme</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="online_payment" value="onlinePayment">
                            <label class="form-check-label" for="online_payment">Online Ödeme</label>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Satın Al</button>
                    </form>

                <?php else: ?>
                    <p>Sepette ürün bulunmamaktadır.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>

</body>

</html>