<?php
session_start();


if (isset($_POST["submit"])) {
    
    include("inc/baglan.php");
    $email = $_POST["email"];
    $password = ($_POST["password"]);
    $secim="SELECT * FROM user u join customer c on u.user_id=c.user_id WHERE   Email='$email'";
    $calistir=mysqli_query($baglanti,$secim);
    if ($calistir && mysqli_num_rows($calistir) > 0) {
        // Kullanıcı bulundu, bilgileri al
        $ilgili_kayit = mysqli_fetch_assoc($calistir);

        // Şifre ve yetki kontrolü
        if ($ilgili_kayit["Password"] === $password ) {
            // Kullanıcı adı ve e-posta oturuma ekleniyor
            $_SESSION["email"] = $ilgili_kayit["Email"];
            $_SESSION["kulanici_id"] = $ilgili_kayit["User_id"];
            $_SESSION["kulAdı"] = $ilgili_kayit["FirstName"]; // İsim bilgisini oturuma ekle
            $_SESSION["customer_id"] = $ilgili_kayit["Customer_id"]; // Customer_id 

            // Ana sayfaya yönlendirme
            header("Location: mainpage.php");
            exit();
        } else {
            $hata = "Şifre yanlış veya yetkisiz giriş!";
        }
    } else {
        $hata = "Bu e-posta ile kayıtlı kullanıcı bulunamadı!";
    }
    $kayit_sayisi=mysqli_num_rows($calistir);
   
    mysqli_close($baglanti);   
}
?>


<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Giriş Sayfası</title>
</head>

<body>
    <div class="loginBackground">
        <div class="login">
            <h2 style="color:black">Üye Girişi</h2>

            <!-- Giriş Formu -->
            <form method="POST" action="">
                <input type="text" id="email" name="email" placeholder="E-posta" required>
                <input type="password" id="password" name="password" placeholder="Şifre" required>
                <input type="submit" class="loginButton form-control mt-2" value="Giriş" name="submit">
            </form>

            <!-- Kayıt Ol ve Satıcı Girişi -->
            <div class="buttonContainer mt-3">
                <div class="giris_kayitButton">
                    <a href="signupPage.php">Kayıt Ol</a>
                </div>
                <div class="saticiGiris">
                    <a href="supplierLoginPage.php">Satıcı Girişi</a>
                </div>
            </div>
            <?php
            // Hata mesajı varsa göster
            if (isset($hata)) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($hata) . '</div>';
            }
            ?>
        </div>

    </div>
</body>

</html>