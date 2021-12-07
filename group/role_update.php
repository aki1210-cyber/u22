<?php
session_start();
if(!isset($_SESSION["id"])){
	header("Location: ../login/logout.php");
	exit;
}
$dsn = 'mysql:dbname=u22;host=localhost';
$user = 'root';
$password = '';
try{
    $dbh = new PDO($dsn, $user, $password);
    if (isset($_POST["submit"])) {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "UPDATE members SET role = :role WHERE users_id = :users_id AND group_id = :group_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':role', $_POST["role"]);
        $stmt->bindValue(':users_id',$_GET["user_id"]);
        $stmt->bindValue(':group_id', $_SESSION["group_id"]);
        $stmt->execute();
    }
}catch (PDOException $e){
    print('Error:'.$e->getMessage());
}
$location="Location: group_top.php?group_id=".$_SESSION["group_id"];
header($location);
?>