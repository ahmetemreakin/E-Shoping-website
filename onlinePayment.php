<?php
include 'inc/baglan.php';
session_start();

// Kullanıcı oturum kontrolü
if (!isset($_SESSION['customer_id'])) {
    header("Location: loginPage.php");
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];

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

// POST işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = $_POST['card_number'] ?? '';
    $card_holder = $_POST['card_holder'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';

    if (empty($card_number) || empty($card_holder) || empty($expiry_date) || empty($cvv)) {
        $error_message = "Lütfen tüm kart bilgilerini doldurun!";
    } elseif ($toplam > 0) {
        mysqli_begin_transaction($baglanti);

        try {
            // Ödeme işlemi ve sipariş ekleme
            foreach ($cart_ids as $cart_id) {
                // 1. Payment tablosuna kayıt
                $payment_sql = "INSERT INTO payment (Cart_id, PaymentStatus, Customer_id)
                                VALUES (?, 1, ?)";
                $stmt_payment = mysqli_prepare($baglanti, $payment_sql);
                mysqli_stmt_bind_param($stmt_payment, 'ii', $cart_id, $customer_id);
                mysqli_stmt_execute($stmt_payment);

                $payment_id = mysqli_insert_id($baglanti);

                // 2. OnlinePayment tablosuna kayıt
                $onlinepayment_sql = "INSERT INTO onlinepayment (Payment_id, CardNumber, ExpiryDate, CVV)
                                      VALUES (?, ?, ?, ?)";
                $stmt_onlinepayment = mysqli_prepare($baglanti, $onlinepayment_sql);
                mysqli_stmt_bind_param($stmt_onlinepayment, 'isss', $payment_id, $card_number, $expiry_date, $cvv);
                mysqli_stmt_execute($stmt_onlinepayment);

                // 3. Orders tablosuna kayıt
                $urun_query = mysqli_query($baglanti, "SELECT p.Product_id, p.Supplier_id, p.ProductPrice, c.Quantity 
                                                       FROM cart c
                                                       JOIN product p ON c.Product_id = p.Product_id 
                                                       WHERE c.Cart_id = $cart_id");
                $urun = mysqli_fetch_assoc($urun_query);
                $product_id = $urun['Product_id'];
                $supplier_id = $urun['Supplier_id'];
                $quantity = $urun['Quantity'];
                $total_price = $urun['ProductPrice'] * $quantity;

                $order_sql = "
                    INSERT INTO orders (Supplier_id, OrderDate, TotalPrice, ReturnStatus, ReturnReason, ReturnDate, Payment_id, Product_id, Quantity, Customer_id)
                    VALUES (?, NOW(), ?, NULL, NULL, NULL, ?, ?, ?, ?)
                ";
                $stmt_order = mysqli_prepare($baglanti, $order_sql);
                mysqli_stmt_bind_param($stmt_order, 'idiiii', $supplier_id, $total_price, $payment_id, $product_id, $quantity, $customer_id);
                mysqli_stmt_execute($stmt_order);

                // 4. Payment'teki Cart_id alanını NULL yap
                $nullify_payment_sql = "UPDATE payment SET Cart_id = NULL WHERE Cart_id = ?";
                $stmt_nullify = mysqli_prepare($baglanti, $nullify_payment_sql);
                mysqli_stmt_bind_param($stmt_nullify, 'i', $cart_id);
                mysqli_stmt_execute($stmt_nullify);
            }

            // 5. Cart tablosundaki ürünleri sil
            $delete_cart_sql = "DELETE FROM cart WHERE Customer_id = ?";
            $stmt_delete_cart = mysqli_prepare($baglanti, $delete_cart_sql);
            mysqli_stmt_bind_param($stmt_delete_cart, 'i', $customer_id);
            mysqli_stmt_execute($stmt_delete_cart);

            mysqli_commit($baglanti);
            header("Location: orders.php?payment_done=1");
            exit();
        } catch (Exception $e) {
            mysqli_rollback($baglanti);
            $error_message = "Bir hata oluştu: " . $e->getMessage();
        }
    } else {
        $error_message = "Sepetiniz boş, ödeme yapılamaz!";
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
    <title>Online Ödeme</title>
</head>

<body>
    <div class="container mt-5">

        <div class="row">
            <div class="odemeSayfasi col-md-12">
                <h2><i>Online Ödeme Sayfası</i></h2>
                <h4>Toplam Tutar: <strong><?= $toplam ?> TL</strong></h4>

                <?php if (!empty($error_message)) {
                    echo '<div class="alert alert-danger">' . $error_message . '</div>';
                } ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="card_number">Kart Numarası</label>
                        <input type="text" name="card_number" id="card_number" class="form-control" placeholder="Kart numaranızı giriniz" maxlength="16" required>
                    </div>

                    <div class="form-group">
                        <label for="card_holder">Kart Sahibi Adı</label>
                        <input type="text" name="card_holder" id="card_holder" class="form-control" placeholder="Kart sahibinin adını giriniz" required>
                    </div>

                    <div class="form-group">
                        <label for="expiry_date">Son Kullanma Tarihi</label>
                        <input type="text" name="expiry_date" id="expiry_date" class="form-control" placeholder="MM/YY" required>
                    </div>

                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" name="cvv" id="cvv" class="form-control" placeholder="(3 haneli güvenlik kodu)" required>
                    </div>

                    <button type="submit" class="btn btn-success btn-block mt-2">Ödemeyi Tamamla</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>