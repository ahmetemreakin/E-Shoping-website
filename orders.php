<?php
include 'inc/baglan.php';
session_start();

// Kullanıcı oturum kontrolü
if (!isset($_SESSION['customer_id'])) {
    header("Location: loginPage.php");
    exit();
}
$customer_id = (int)$_SESSION['customer_id'];

// MySQL prosedürünü çağırarak iade işlemini başlatma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_order_id'], $_POST['return_reason'])) {
    $return_order_id = (int)$_POST['return_order_id'];
    $return_reason = mysqli_real_escape_string($baglanti, $_POST['return_reason']);
    $return_date = date('Y-m-d'); // İade tarihi için bugünün tarihi

    // Cursor işlemini çağıran prosedür
    $return_call_query = "CALL ProcessReturnWithCursor(?, ?, ?, ?)";
    $stmt_return_call = mysqli_prepare($baglanti, $return_call_query);
    mysqli_stmt_bind_param($stmt_return_call, 'iiss', $return_order_id, $customer_id, $return_reason, $return_date);

    if (mysqli_stmt_execute($stmt_return_call)) {
        $success_message = "İade işlemi başarıyla tamamlandı (Cursor ile).";
    } else {
        $error_message = "İade işlemi sırasında bir hata oluştu.";
    }
    mysqli_stmt_close($stmt_return_call);
}

// Siparişleri müşteri kimliğine göre al
$order_query = "
    SELECT
        o.Order_id,
        o.Supplier_id,
        o.OrderDate,
        o.TotalPrice,
        o.ReturnStatus,
        o.ReturnReason,
        o.ReturnDate,
        o.Payment_id,
        o.Product_id,
        o.Quantity,
        o.Customer_id,
        p.ProductName,
        s.CompanyName AS SupplierName
    FROM
        orders o
    JOIN
        product p ON o.Product_id = p.Product_id
    JOIN
        supplier s ON p.Supplier_id = s.Supplier_id
    WHERE
        o.Customer_id = ?
";
$stmt = mysqli_prepare($baglanti, $order_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Siparişlerim</title>
</head>

<body>
    <div class="baslik ">
        <h1><i>Alışverişin Adresi</i></h1>
    </div>
    <div class="container mt-4">
        <h2><i>Siparişlerim</i></h2>
        <div class="row h-100">
            <div class="kategori col-md-1 mt-2">
                <a href="mainPage.php" class="kat_login">Ana Sayfa</a>
            </div>
            <div class="col-md-11 mt-2">

                <?php if (mysqli_num_rows($order_result) > 0): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sipariş ID</th>
                                <th>Tedarikçi Adı</th>
                                <th>Sipariş Tarihi</th>
                                <th>Toplam Tutar</th>
                                <th>Kargo Durumu</th>
                                <th>İade Durumu</th>
                                <th>İade Sebebi</th>
                                <th style="width: 100px;">İade Tarihi</th>
                                <th>İade Et</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($order_result)): ?>
                                <tr>
                                    <td><?= $order['Order_id'] ?></td>
                                    <td><?= $order['SupplierName'] ?></td>
                                    <td><?= $order['OrderDate'] ?></td>
                                    <td><?= number_format($order['TotalPrice'], 2) ?> TL</td>

                                    <td>
                                        <?php
                                        $shipment_query = "
                                            SELECT TrackingNumber
                                            FROM shipment
                                            WHERE Order_id = ?
                                            ";
                                        $stmt_shipment = mysqli_prepare($baglanti, $shipment_query);
                                        mysqli_stmt_bind_param($stmt_shipment, 'i', $order['Order_id']);
                                        mysqli_stmt_execute($stmt_shipment);
                                        $shipment_result = mysqli_stmt_get_result($stmt_shipment);
                                        $shipment = mysqli_fetch_assoc($shipment_result);

                                        if (!empty($shipment['TrackingNumber'])) {
                                            echo htmlspecialchars($shipment['TrackingNumber']);
                                        } else if($order['ReturnStatus'] == 1){
                                            echo "Sipariş iade edildi.";
                                        } 
                                        else{
                                            echo "Sipariş alındı, kargoya verilecek";
                                        }
                                        ?>
                                    </td>

                                    <td><?= $order['ReturnStatus'] ? $order['ReturnStatus'] : 'İade edilmedi' ?></td>
                                    <td><?= $order['ReturnReason'] ? $order['ReturnReason'] : ' N/A ' ?></td>
                                    <td><?= $order['ReturnDate'] ? $order['ReturnDate'] : ' N/A ' ?></td>
                                    <td>
                                        <?php if ($order['ReturnStatus'] == NULL): ?>
                                            <form method="POST" action="orders.php">
                                                <input type="hidden" name="return_order_id" value="<?= $order['Order_id'] ?>">
                                                <textarea name="return_reason" placeholder="İade sebebini yazın" required></textarea>
                                                <button type="submit" class="btn btn-danger">İade Et</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>İade Edildi</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Henüz siparişiniz bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

</body>

</html>