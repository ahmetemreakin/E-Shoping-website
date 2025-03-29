<?php
include 'inc/baglan.php';
session_start();

// Kullanıcı oturum kontrolü
if (!isset($_SESSION['customer_id'])) {
    header("Location: loginPage.php");
    exit();
}
$customer_id = (int)$_SESSION['customer_id'];

// Müşteri bilgilerini çek
$customer_query = mysqli_query(
    $baglanti,
    "SELECT FirstName, LastName, Address FROM customer WHERE Customer_id = $customer_id"
);
$customer_data = mysqli_fetch_assoc($customer_query);

// Müşteri bilgilerini kontrol et
if (!$customer_data) {
    die("Müşteri bilgileri bulunamadı.");
}

$first_name = $customer_data['FirstName'];
$last_name = $customer_data['LastName'];
$address = $customer_data['Address'];

// Sepet ürünlerini çek
$urunler = mysqli_query(
    $baglanti,
    "
    SELECT p.*, c.Quantity, c.Cart_id 
    FROM cart c
    JOIN product p ON c.Product_id = p.Product_id
    WHERE c.Customer_id = $customer_id
");

$toplam = 0;
$cart_ids = [];

while ($urun = mysqli_fetch_assoc($urunler)) {
    $urun_toplam = $urun['ProductPrice'] * $urun['Quantity'];
    $toplam += $urun_toplam;
    $cart_ids[] = $urun['Cart_id'];
}

// Ödeme işlemi başlatıldı mı kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form doğrulama ve işlem
    if ($toplam > 0) {
        mysqli_begin_transaction($baglanti);

        try {
            // 1. Payment tablosuna ödeme kaydını ekleyin
            $payment_sql = "INSERT INTO payment (Cart_id, PaymentStatus, Customer_id)  
            VALUES (?, 1, ?)";
            $stmt_payment = mysqli_prepare($baglanti, $payment_sql);
            mysqli_stmt_bind_param($stmt_payment, 'ii', $cart_id, $customer_id);

            foreach ($cart_ids as $cart_id) {
                mysqli_stmt_execute($stmt_payment);
            }

            // Otomatik olarak artan Payment_id'yi alın
            $payment_id = mysqli_insert_id($baglanti);

            // 2. CashOnDelivery tablosuna ödeme bilgilerini ekleyin
            $payment_code = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT); // 6 haneli benzersiz ödeme kodu oluştur
            $cashondelivery_sql = "INSERT INTO cashondelivery (Payment_id, PaymentCode, Amount)
                                   VALUES (?, ?, ?)";
            $stmt_cod = mysqli_prepare($baglanti, $cashondelivery_sql);
            mysqli_stmt_bind_param($stmt_cod, 'isi', $payment_id, $payment_code, $toplam);
            mysqli_stmt_execute($stmt_cod);

            // 3. Orders tablosuna sipariş kaydını ekleyin
            foreach ($cart_ids as $cart_id) {
                $urun_query = mysqli_query($baglanti, "SELECT p.Product_id, p.Supplier_id, p.ProductPrice, c.Quantity FROM cart c
                    JOIN product p ON c.Product_id = p.Product_id WHERE c.Cart_id = $cart_id");
                $urun = mysqli_fetch_assoc($urun_query);
                $product_id = $urun['Product_id'];
                $supplier_id = $urun['Supplier_id'];
                $quantity = $urun['Quantity'];
                $total_price = $urun['ProductPrice'] * $quantity;

                $insert_order_query = "
                    INSERT INTO orders (Supplier_id, OrderDate, TotalPrice, ReturnStatus, ReturnReason, ReturnDate, Payment_id, Product_id, Quantity, Customer_id) 
                    VALUES (?, NOW(), ?, NULL, NULL, NULL, ?, ?, ?, ?)
                ";

                $stmt_order = mysqli_prepare($baglanti, $insert_order_query);
                mysqli_stmt_bind_param($stmt_order, 'idiiii', $supplier_id, $total_price, $payment_id, $product_id, $quantity, $customer_id);
                mysqli_stmt_execute($stmt_order);
                
            // 3. Payment tablosundaki Cart_id'yi NULL yap
            $update_payment_sql = "UPDATE payment SET Cart_id = NULL WHERE Cart_id = $cart_id";
            mysqli_query($baglanti, $update_payment_sql);
            }
            
            /*
            // 4. Cart tablosundaki kayıtları sil
            $delete_cart_sql = "DELETE FROM cart WHERE Customer_id = $customer_id";
            mysqli_query($baglanti, $delete_cart_sql);
            */
            mysqli_commit($baglanti);
            header("Location: orders.php?payment_done=1");//ödeme yapıldığı bilgisi ile sepet silinecek
            exit();
        } catch (Exception $e) {
            mysqli_rollback($baglanti);
            echo "Hata: " . $e->getMessage();
        }
    } else {
        echo "Sepetiniz boş, ödeme yapılamaz!";
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
    <title>Kapıda Ödeme</title>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="odemeSayfasi col-md-12">
                <h2><i>Kapıda Ödeme Sayfası</i></h2>
                <h4>Toplam Tutar: <strong><?= $toplam ?> TL</strong></h4>

                <form method="POST">
                    <div class="form-group">
                        <label>Kargo Alıcı İsmi:</label>
                        <p><?= $first_name . ' ' . $last_name ?></p>
                    </div>

                    <div class="form-group">
                        <label>Kargo Adresi:</label>
                        <p><?= $address ?></p>
                    </div>

                    <button type="submit" class="btn btn-success btn-block mt-2">Siparişi Tamamla</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>