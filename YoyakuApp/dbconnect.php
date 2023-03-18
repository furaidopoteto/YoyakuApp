<?php
try{
    $data = new PDO("mysql:dbname=yoyakudb3;host=db;charset=utf8", 'root', 'password');
}catch(PDOException $e){
    $e->getMessage();
}
?>