<?php
session_start();
if(!isset($_SESSION["id"])){
	header("Location: ../login/logout.php");
	exit;
}
$dsn = 'mysql:dbname=u22;host=localhost';
$user = 'root';
$password = '';

if(isset($_POST["submit"]) && strlen($_POST["name"])>0){
try{
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO groups(name) VALUES (:name)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':name', $_POST['name']);
    $flag = $stmt->execute();

    $sql1 = "SELECT * FROM groups WHERE name=:name";
    $stmt1 = $dbh->prepare($sql1);
    $stmt1->bindValue(':name',$_POST["name"]);
    $stmt1->execute();
    $row =$stmt1->fetch(PDO::FETCH_ASSOC);

//役職テーブル挿入
    $sql2 = "INSERT INTO members(users_id, group_id, role) VALUES (:users_id, :group_id, :role)";
    $stmt2 = $dbh->prepare($sql2);
    $stmt2->bindValue(':users_id', $_SESSION["id"]);
    $stmt2->bindValue(':group_id', $row["id"]);
    $stmt2->bindValue(':role', 1);
    $flag = $stmt2->execute();

    header("Location: ../calendar/calendar.php");
	exit;

}catch (PDOException $e){
    print('Error:'.$e->getMessage());
}
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Document</title>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/search_member.css">
</head>
<body>
<div id="form">
<p class="form-title">Create group</p>
    <form action="create_group.php" method="post" class="search_container">
        <input type="text" size="25" placeholder="グループ名を入力" name="name">
        <input type="submit" value="作成" name="submit">
    </form>
    <a href="../calendar/calendar.php">戻る</a>
</div>
</body>
</html>