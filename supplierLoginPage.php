<?php
if (isset($_POST["submit"])) {
    session_start();
    include("inc/baglan.php");
    $email = $_POST["email"];
    $password = ($_POST["password"]);
    $secim="SELECT * FROM user u join supplier s on u.User_id=s.User_id WHERE   Email='$email'";
    $calistir=mysqli_query($baglanti,$secim);
    if ($calistir && mysqli_num_rows($calistir) > 0) {
        // Kullanıcı bulundu, bilgileri al
        $ilgili_kayit = mysqli_fetch_assoc($calistir);

        // Şifre ve yetki kontrolü
        if ($ilgili_kayit["Password"] == $password ) {
            // Kullanıcı adı ve e-posta oturuma ekleniyor
            $_SESSION["email"] = $ilgili_kayit["Email"];
            $_SESSION["kulanici_id"] = $ilgili_kayit["Supplier_id"];
            $_SESSION["kulAdı"] = $ilgili_kayit["CompanyName"]; // İsim bilgisini oturuma ekliyoruz

            // Ana sayfaya yönlendirme
            header("Location:product.php");
            exit();
        } else {
            $hata = "Şifre yanlış veya yetkisiz giriş!";
        }
    } else {
        $hata = "Bu e-posta ile kayıtlı kullanıcı bulunamadı!";
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
    <title>Supplier Login Page </title>
</head>

<body>
    <div class="loginBackground">
        <div class="login">
            <h2> <i>Satıcı Girişi</i></h2>
            <form method="POST" action="">
                <input type="text" id="email" name="email" placeholder="Satıcı e-maili" required>
                <input type="password" id="password" name="password" placeholder="Şifre" required>
                <input type="submit" class="loginButton form-control mt-2" value="Giriş" name="submit">
            </form>
            
            <div class="buttonContainer mt-3">
                <!-- Satıcı Kayıt Ol Butonu -->
                <div class="giris_kayitButton">
                    <a href="supplierSignupPage.php"> Satıcı Kayıt</a>
                </div>
                
                <!-- Üye Girişi -->
                <div class="KullanıcıGiris">
                    <a href="loginPage.php">Üye Girişi</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>