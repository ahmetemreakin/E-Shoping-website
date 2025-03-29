<?php
session_start();
if (isset($_POST["submit"])) {
    include("inc/baglan.php");

    $name = ($_POST["name"]);
    $address = ($_POST["address"]);
    $company_phone_number = ($_POST["phone_number"]);
    $email = ($_POST["eposta"]);
    $password = ($_POST["password"]); // Şifreleme eklenebilir
    $check_email_query = "SELECT * FROM user WHERE email = '$email'";
    $result = mysqli_query($baglanti, $check_email_query);
    if (mysqli_num_rows($result) > 0) {
        // Eğer kullanıcı varsa, hata mesajı göster
        echo '<div class="alert alert-danger" role="alert">Bu e-posta adresi zaten kullanılıyor.</div>';
    } else {
        // Yeni kullanıcıyı user tablosuna ekle
        $insert_user_query = "INSERT INTO user (Email, Password) VALUES ('$email', '$password')";
        if (mysqli_query($baglanti, $insert_user_query)) {
            // Son eklenen user_id'yi al
            $user_id = mysqli_insert_id($baglanti);
            
            // Şimdi satıcı bilgilerini supplier tablosuna ekle
            $insert_supplier_query = "INSERT INTO supplier (User_id,CompanyName, CompanyAddress, CompanyPhone) 
                                       VALUES ('$user_id', '$name', '$address', '$company_phone_number')";
            
            if (mysqli_query($baglanti, $insert_supplier_query)) {
                echo '<div class="alert alert-success" role="alert">Başarıyla kaydoldunuz! <a href="supplierLoginPage.php">Giriş yapın</a></div>';
            } else {
                echo '<div class="alert alert-danger" role="alert">Satıcı bilgileri eklenirken bir hata oluştu.</div>';
            }
        } else {
            echo '<div class="alert alert-danger" role="alert">Kullanıcı kaydı oluşturulurken bir hata oluştu.</div>';
        }
    }
    mysqli_close($baglanti);
}
?>


<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Kayıt Sayfası</title>
</head>

<body>
    <div class="kayitBackground">
        <div class="kayit">
            <h2 class="mb-1"><i>Satıcı Kayıt</i> </h2>
            <form method="POST" action="">
                <!--Sirket Adı -->
                <input type="text" name="name" placeholder=" Şirket Adı" required>
                <!-- Eposta -->
                <input type="text" name="eposta" placeholder=" Sirket E-mail" required>
                <!-- Şifre -->
                <input type="password" name="password" placeholder="Password" required>
                <!--Sirket Adres -->
                <input type="text" name="address" placeholder="Sirket Adres" required>
                <!--Sirket Telefon Numarası -->
                <input type="text" name="phone_number" placeholder="Sirket Telefon Numarası" maxlength="11" required>
                <!-- Gönder Butonu -->
                <input type="submit" name="submit" class="kayitButton" value="Kayıt Ol">
            </form>
            <div class="buttonContainer mt-3">
                <!-- Satıcı Kayıt Ol Butonu -->
                <div class="giris_kayitButton">
                    <a href="supplierLoginPage.php"> Satıcı Giriş</a>
                </div>
            </div>
        </div>
    </div>

    </div>
</body>

</html>