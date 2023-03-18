<?php
require('dbconnect.php');
session_start();

if(!isset($_SESSION['personID'])){
    header('Location: adminlogin.php');
    exit;
}

if(!empty($_POST['name']) || !empty($_POST['startdate']) || !empty($_POST['enddate'])){
    $error['date'] = "";
    $sql = "SELECT * FROM data WHERE username=? ORDER BY startdate";
    if(!empty($_POST['startdate']) && !empty($_POST['enddate']) && !empty($_POST['name'])){
        $sql = "SELECT * FROM data WHERE username=? AND startdate BETWEEN ? AND ?";
        $query = $data->prepare($sql);
        $query->execute(array($_POST['name'], $_POST['startdate'], $_POST['enddate']));
    }else if(empty($_POST['startdate']) && empty($_POST['enddate'])){
        $query = $data->prepare($sql);
        $query->execute(array($_POST['name']));
    }else if(!empty($_POST['startdate']) && !empty($_POST['enddate'])){
        $sql = "SELECT * FROM data WHERE startdate BETWEEN ? AND ?";
        $query = $data->prepare($sql);
        $query->execute(array($_POST['startdate'], $_POST['enddate']));
    }else{
        $error['date'] = "error";
        $query = $data->query("SELECT * FROM data ORDER BY startdate");
    }
}else{
    $query = $data->query("SELECT * FROM data ORDER BY startdate");
}

if(!empty($_POST['download'])){
    $putdata = [];
    $column = ["ID", "氏名", "部屋数", "人数", "チェックイン", "チェックアウト", "宿泊数", "料金", "予約時刻"];

    foreach($query as $index){
        $startdate = strtotime($index['startdate']);
        $enddate = strtotime($index['enddate']);
        $search = ($enddate - $startdate)/(60*60*24);
        if(!empty($_POST['startdate']) && !empty($_POST['enddate'])){

        }else{
            if($search != $index['stayscount']){
                continue;
            }
        }
        
        array_push($putdata, $index);
    }
    

    for($i = 0;$i<count($putdata);$i++){
        for($j = 0;$j<count($putdata[$i]);$j++){
            unset($putdata[$i][$j]);
        }
    }
    $fp = fopen("./datafile.csv", "w");
    //書き込んだCSVファイルをExcelで開くと文字化けしてしまうので
    //文字コードをUTF-8からShift-jis(cp932)に変換する 参照: https://cpoint-lab.co.jp/article/202010/17519/
    stream_filter_prepend($fp,'convert.iconv.utf-8/cp932'); //ストリームフィルタ指定
    fputcsv($fp, $column);
    foreach($putdata as $index){
        fputcsv($fp, $index);
    }

    fclose($fp);

    //CSVファイルのダウンロード 参照: https://webukatu.com/wordpress/blog/35994/
    // ダウンロードするサーバのファイルパス
    $filepath = './datafile.csv';
    
    // HTTPヘッダ設定
    //ダウンロードするファイルのタイプを指定する(octet-streamはファイルタイプを意識する必要がないときに使う)
    header('Content-Type: application/octet-stream');
    //ダウンロードするファイルのサイズを指定する(サイズを指定することでダウンロードの進捗状況を表示できる)
    header('Content-Length: '.filesize($filepath));
    //Content-Dispositionはファイルの処理方法を指定する文字列で
    //attachment; filename=ファイル名.拡張子 でダウンロードするファイル名を指定する
    header('Content-Disposition: attachment; filename=download.csv');
    // ファイル出力
    readfile($filepath);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者画面</title>
    <style>
        .title{
            position: absolute;
        }
        .box{
            background-color: white;
            margin-left: auto;
            margin-right: auto;
            border: solid 1px black;
            width: 1300px;
            text-align: center;
        }
        .column{
            font-size: 23px;
            width: 12.5%;
            display: inline-block;
            border-right: solid 1px black;
        }
        .subbox{
            display: flex;
            border-bottom: solid 1px black;
        }

        #searchform{
            text-align: center;
            margin-bottom: 50px;
        }
        #inputtext, #inputsubmit, #resetbutton, .searchdate{
            font-size: 30px;
            margin-bottom: 10px;
        }
        #resetbutton, .cancelbutton{
            color: blue;
            transition: .5s ease;
        }
        #resetbutton:hover, .cancelbutton:hover{
            color: red;
        }

        .error{
            color: red;
            font-size: 30px;
        }

        .downloadbox{
            position: fixed;
            top: 10px;
            right: 0;
        }
        .downloadbutton{
            font-size: 25px;
        }
        #personID, #logoutbutton{
            font-size: 30px;
        }
    </style>
</head>
<body>
    <h1 class="title">予約データ</h1>
    <?php
    $inputname = "";
    $startval = "";
    $endval = "";
    if(!empty($_POST['name'])){
        $inputname = $_POST['name'];
    }
    if(!empty($_POST['startdate']) && !empty($_POST['enddate'])){
        $startval = $_POST['startdate'];
        $endval = $_POST['enddate'];
    }
    ?>
    <form action="" method="POST" id="searchform">
        <input type="text" name="name" id="inputtext" placeholder="氏名" value="<?php echo $inputname;?>"><br>
        <input type="date" name="startdate" class="searchdate" value="<?php echo $startval;?>">
        <span style="font-size: 30px">～</span>
        <input type="date" name="enddate" class="searchdate" value="<?php echo $endval;?>"><br>
        <input type="submit" value="検索" id="inputsubmit">
        <a href="./adminview.php" id="resetbutton">リセット</a><br>
        <span>※日付検索の場合は宿泊数分のすべての日付が表示されます。<br>※
        キャンセルボタンを押すと宿泊数分のすべての予約が削除されます</span><br>
        <?php if(!empty($_POST) && $error['date'] == "error"):?>
            <span class="error">* 検索開始日と終了日両方を指定してください</span>
        <?php endif;?>
        <span class="downloadbox">
            <button class="downloadbutton" type="submit" name="download" value="on">ファイルをダウンロードする</button><br>
            <span>※宿泊数の日帰りは0に置き換えられます</span><br>
            <span id="personID"><?php echo $_SESSION['personID'];?>でログイン中</span><br>
            <a href="./logout.php" id="logoutbutton">ログアウト</a>
        </span>
    </form>
    <div class="box">
        <div class="subbox">
            <span class="column">氏名</span>
            <span class="column">部屋数</span>
            <span class="column">人数</span>
            <span class="column">チェックイン</span>
            <span class="column">チェックアウト</span>
            <span class="column">宿泊数</span>
            <span class="column">料金</span>
            <span class="column">予約時刻</span>
            <span class="column"></span>
        </div>
        <?php foreach($query as $index):?>
            <?php
            $checkin = "15:00～";
            $checkout = "～10:00";
            if($index['stayscount'] <= 0){
                $checkin = "15:00～";
                $checkout = "～21:00";
            }


            //参考: https://www.flatflag.nir87.com/diff-504
            $startdate = strtotime($index['startdate']);
            $enddate = strtotime($index['enddate']);
            $search = ($enddate - $startdate)/(60*60*24);
            if(!empty($_POST['startdate']) && !empty($_POST['enddate'])){
                
            }else{
                if($search != $index['stayscount']){
                    continue;
                }
            }
            

            $stays = $index['stayscount']. "泊". ($index['stayscount']+1). "日";
            if($index['stayscount'] <= 0){
                $stays = "日帰り";
            }

            $cancelcheck = true;
            $nowdate = new DateTime();
            $checkdate = new DateTime($index['startdate']);
            if($nowdate > $checkdate){
                $cancelcheck = false;
            }
            
            
            if($nowdate->format("Y-m-d") == $checkdate->format("Y-m-d")){
                $cancelcheck = true;
            }
            ?>
            <div class="subbox">
                <span class="yoyakuname column"><?php echo $index['username'];?></span>
                <span class="roomcount column"><?php echo $index['roomcount'];?>部屋</span>
                <span class="peoplecount column"><?php echo $index['peoplecount'];?>名</span>
                <span class="startdate column"><?php echo $index['startdate'];?> <?php echo $checkin;?></span>
                <span class="enddate column"><?php echo $index['enddate'];?> <?php echo $checkout;?></span>
                <span class="stayscount column"><?php echo $stays;?></span>
                <span class="price column"><?php echo $index['price'];?>円</span>
                <span class="yoyakujikoku column"><?php echo $index['yoyakujikoku'];?></span>
                <span class="column">
                    <?php if($cancelcheck):?>
                        <a class="cancelbutton"
                        href='javascript:yoyakudelete("delete.php?username=<?php echo $index['username'];?>&enddate=<?php echo $index['enddate'];?>")'>キャンセル</a>
                    <?php endif;?>
                </span>
            </div>
        <?php endforeach;?>
    </div>
    <script>
        'use strict';

        let yoyakudelete = (url) => {
            if(window.confirm("本当にキャンセルしますか?(キャンセル料1000円がかかります)")){
                location.href = url
            }
        }
    </script>
</body>
</html>