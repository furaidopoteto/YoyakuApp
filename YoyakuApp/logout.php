<?php
session_start();
$path = "";
if(!empty($_SESSION['personID'])){
    $path = "adminview.php";
}else{
    $path = "home.php";
}
setcookie('name', null, time()-3600);//期限をマイナスにすればすぐに削除される
setcookie('pw', null, time()-3600);
$_SESSION = array();
session_destroy();
header('Location: '. $path);
exit;
?>