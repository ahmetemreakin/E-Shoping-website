<?php
session_start();
if (isset($_POST["submit"])) {
    include("inc/baglan.php");

    $name = ($_POST["name"]);
    $surname = ($_POST["surname"]);
    $birth_date = ($_POST["birth_date"]);
    $address = ($_POST["address"]);
    $phone_number = ($_POST["phone_number"]);
    $email = ($_POST["eposta"]);
    $password = ($_POST["password"]); // Şifreleme eklenebilir
    
    // user tablosuna kayıt ekle
    $sql_user = "INSERT INTO user (Email, Password) VALUES ('$email', '$password')";
    if (mysqli_query($baglanti, $sql_user)) {
        // Son eklenen user_ID değerini al
        $user_id = mysqli_insert_id($baglanti);

        // customer tablosuna kayıt ekle
        $sql_customer = "INSERT INTO customer (user_ID, FirstName, LastName, BirthDate, Address, Phone) 
                         VALUES ('$user_id', '$name', '$surname', '$birth_date', '$address', '$phone_number')";

        if (mysqli_query($baglanti, $sql_customer)) {
            header('Location: loginPage.php');
            exit();
        } else {
            echo "Müşteri kaydı sırasında bir hata oluştu: " . mysqli_error($baglanti);
        }
    } else {
        echo "Kayıt olurken hata oluştu: " . mysqli_error($baglanti);
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
            <h2 class="mb-1"><i>Müşteri Kayıt</i> </h2>
            <form method="POST" action="">
                <input type="text" name="name" placeholder="Ad" required>
                <input type="text" name="surname" placeholder="Soyad" required>
                <input type="text" name="eposta" placeholder="E-mail" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="date" name="birth_date" placeholder="Doğum Tarihi" required>
                <input type="text" name="address" placeholder="Adres" required>
                <input type="tel" name="phone_number" placeholder="Telefon Numarası" maxlength="11" required>
                <input type="submit" name="submit" class="kayitButton" value="Kayıt Ol">
            </form>
            <div class="buttonContainer mt-3">
                <div class="KullanıcıGiris">
                    <a href="loginPage.php">Üye Girişi</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>