<?php
include("inc/baglan.php");

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Hizmetler</title>
</head>

<body>
    <div class="baslik">
        <h1><i>Hizmetler</i></h1>
    </div>
    <div class="anaEkran">
        <div class="row h-100">
            <div class="col-md-2 mt-2">
                <?php include 'kategori.php'; ?>
            </div>
            <div class="col-md-10 mt-2">
                <div class="content">
                    <h2>Hizmetler</h2>
                    
                    <?php
                    // SQL sorgusu
                    $sorgu = "SELECT * FROM services"; // Hizmetler tablosundan tüm hizmetleri al

                    $hizmetler = mysqli_query($baglanti, $sorgu);

                    if ($hizmetler && mysqli_num_rows($hizmetler) > 0) {
                        echo '<div class="row">';
                        while ($hizmet = mysqli_fetch_assoc($hizmetler)) {
                            echo '<div class="col-md-3">';
                            echo '<div class="card mb-4">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title">' . htmlspecialchars($hizmet["ServiceName"]) . '</h5>';
                            echo '<p class="card-text">Fiyat: ' . htmlspecialchars($hizmet["ServicePrice"]) . ' TL</p>';
                            echo '<p class="card-text">Açıklama: ' . htmlspecialchars($hizmet["ServiceDescription"]) . '</p>';
                                                       
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>Hizmet bulunmamaktadır.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>