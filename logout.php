<?php
session_start();
session_unset(); 
session_destroy(); // Oturumu sonlandırır
header("Location: mainPage.php"); // ana sayfaya yönlendirir
exit();
