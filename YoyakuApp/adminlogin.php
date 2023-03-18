<?php
require('dbconnect.php');
session_start();

if(isset($_COOKIE['count'])){
    if($_COOKIE['count'] >= 5){
        header('Location: loack.php');
        exit;
    }
}else{
    setcookie('count', 0, time()+60*60*24);
}
if(!empty($_POST)){
    $name = $_POST['name'];
    $pw = $_POST['pw'];

    $check = $data->prepare('SELECT COUNT(*) AS cnt FROM adminaccount WHERE personID=? AND password=?');
    $check->execute(array($name, sha1($pw)));
    $check = $check->fetch();
    if($check['cnt'] == 1){
        $_SESSION['personID'] = $name;
        header('Location: adminview.php');
        exit;
    }else{
        $_COOKIE['count']++;
        setcookie('count', $_COOKIE['count'], time()+60*60*24);
        if($_COOKIE['count'] >= 5){
            header('Location: loack.php');
            exit;
        }
        $error['name'] = "type";
    }
}

function escape($val){
    return htmlspecialchars($val, ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン</title>
    <link rel="stylesheet" href="./cssfile/loginstyle.css">
</head>
<body>
        <?php
        $inputname = "";
        if(!empty($_POST)){
            $inputname = $_POST['name'];
        }
        ?>
        <div class="box">
            <form action="" method="POST" style="padding: 100px;">
                <span class="title">管理者ログイン</span><br>
                <input id="inputtext" class="inputfield" type="text" name="name" placeholder="社員ID" required onkeyup="keycheck()" value="<?php echo escape($inputname);?>"><br>
                <input id="inputpw" class="inputfield" type="password" name="pw" placeholder="パスワード※4文字以上" onkeyup="keycheck()" required><br>
                <?php if(!empty($_POST) && $error['name'] == "type"):?>
                    <span class="error">* 社員IDまたはパスワードが違います</span><br>
                <?php endif;?>
                <input id="submitbutton" type="submit" value="ログイン" disabled><br>
            </form>
        </div>
        <script src="../jquery-3.5.1.min.js"></script>
        <script>
            'use strict';

            const text = document.getElementById('inputtext');
            const pw = document.getElementById('inputpw');
            const submitbutton = document.getElementById('submitbutton');

            let keycheck = () => {
                let textval = text.value;
                let pwval = pw.value;
                if(pwval.length >= 4 && textval.length > 0){
                    submitbutton.disabled = false;
                }else{
                    submitbutton.disabled = true;
                }
            }
        </script>
</body>
</html>