<?php
session_start();
if(!isset($_SESSION["id"])){
	header("Location: ../login/logout.php");
	exit;
}
$dsn = 'mysql:dbname=u22;host=localhost';
$user = 'root';
$password = '';
if(isset($_POST["submit"])){
    if ($_SESSION["flagment"]) {
        try{
            $dbh = new PDO($dsn, $user, $password);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql1 = "UPDATE members SET delete_at = :delete_at WHERE users_id = :users_id AND group_id = :group_id";
            $stmt1 = $dbh->prepare($sql1);
            $stmt1->bindValue(':delete_at', null);
            $stmt1->bindValue(':users_id', $_SESSION["search_id"]);
            $stmt1->bindValue(':group_id', $_SESSION["group_id"]);
            $flag = $stmt1->execute(); 
            header("Location: group_top.php");  // メイン画面へ遷移
        }catch (PDOException $e){
            print('Error:'.$e->getMessage());
        }
    }else{
        try{
            $dbh = new PDO($dsn, $user, $password);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "INSERT INTO members(users_id, group_id, role) VALUES (:users_id, :group_id, :role)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':users_id', $_SESSION["search_id"]);
            $stmt->bindValue(':group_id', $_SESSION["group_id"]);
            $stmt->bindValue(':role', 3);
            $flag = $stmt->execute(); 
            header("Location: group_top.php");
        }catch (PDOException $e){
            print('Error:'.$e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link rel="stylesheet" href="../css/invite_group.css">
</head>
<body>
    <div id="form">
        <p class="form-title">Invite Member</p>
        <form action="invite_group.php" method="post">
            <?php echo $_SESSION["name"]?><br>
            グループに招待する<br>
            <input type="submit" class="cp_btn" value="招待" name="submit">
        </form>
        <a href="search_member.php" class="back">戻る</a>
    </div>
</body>
</html>