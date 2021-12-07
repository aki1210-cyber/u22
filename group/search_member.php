<?php
session_start();
if(!isset($_SESSION["id"])){
	header("Location: ../login/logout.php");
	exit;
}
//DB設定
$dsn = 'mysql:dbname=u22;host=localhost';
$user = 'root';
$password = '';
if (isset($_POST["submit"]) && strlen($_POST["user_id"])>0) {
    $err_msg='';
    $flagment=false;
    try{
        $dbh = new PDO($dsn, $user, $password);
        $sql = "SELECT * FROM users WHERE login_id=:login_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':login_id',$_POST["user_id"]);
        $stmt->execute();
        $row =$stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $err_msg="存在しないユーザーです";   
        }
        //print_r($row);
        $sql1 = "SELECT * FROM members WHERE users_id=:users_id AND group_id=:group_id";
        $stmt1 = $dbh->prepare($sql1);
        $stmt1->bindValue(':users_id',$row["id"]);
        $stmt1->bindValue(':group_id',$_SESSION["group_id"]);
        $stmt1->execute();
        $row1 =$stmt1->fetch(PDO::FETCH_ASSOC);
        //var_dump($row1);
        if ($row1&&$row1["delete_at"]==null) {
            $err_msg="すでにメンバーのユーザーです。";
        }
        if ($row1["delete_at"]!=null) {
            $flagment=true;
        }
    }catch(\Exception $e){
        print("=----");
    }
    if (empty($err_msg)) {
        $_SESSION["search_id"]=$row["id"];
        $_SESSION["name"]=$row["name"];
        $_SESSION["flagment"]=$flagment;
        header("Location: invite_group.php");  // メイン画面へ遷移
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/search_member.css">
</head>
<body>
<div id="form">
    <p class="form-title">Search member</p>
    <form action="search_member.php" method="post" class="search_container">
        <input type="text" size="25" placeholder="ユーザーID検索" name="user_id">
        <input type="submit" name="submit" value="検索">
    </form>
    <?php 
        if (!empty($err_msg)) {
            echo $err_msg;
        }
    ?>
    <a href="group_top.php">戻る</a>
</div>

</body>