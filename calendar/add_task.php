<?php
session_start();
$dsn = 'mysql:dbname=u22;host=localhost';
$user = 'root';
$password = '';
if(!isset($_SESSION["group_id"])){
    $group_id=0;
}else{
    $group_id=$_SESSION["group_id"];
}
if(isset($_POST['submit'])){
try{
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO calenders(title, ym, dates, users_id, group_id) VALUES (:title, :ym, :dates, :users_id, :group_id)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':title', $_POST['title']);
    $stmt->bindValue(':ym', $_GET['ym']);
    $stmt->bindValue(':dates', $_GET['day']);
    $stmt->bindValue(':users_id', $_SESSION["id"]);
    $stmt->bindValue(':group_id', $group_id);
    $flag = $stmt->execute();
    if ($group_id==0) {
        header("Location: calendar.php");
    }else {
        header("Location: ../group/group_top.php");
    }

}catch (PDOException $e){
    print('Error:'.$e->getMessage());
}
}
try{
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql1 = "SELECT name FROM groups WHERE id=:id";
    $stmt1 = $dbh->prepare($sql1);
    $stmt1->bindValue(':id', $group_id);
    $stmt1->execute();
    $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
}catch (PDOException $e){
    print('Error:'.$e->getMessage());
}
?>
<html lang="ja">
<form action="add_task.php?day=<?php echo $_GET['day']?>&ym=<?php echo $_GET['ym']?>" method="post">
    <script>
    const button = document.querySelector('.btn')
    const form   = document.querySelector('.form')

    button.addEventListener('click', function() {
    form.classList.add('form--no') 
    });
    </script>
  <head>
    <meta charset="utf-8">
    <title>Web Entertainment Design</title>
    <meta name="description" content="テキストテキストテキストテキストテキストテキストテキストテキスト">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="img/favicon.ico">
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="../css/task.css">
    <link rel="stylesheet" href="../css/common.css">
  </head>

  <body>

    <main>
        <!--入力フォーム-->
        <div class="form-wrapper">
        <?php echo "<h2>".$row1["name"]."</h2>"; ?>
            <h1>register a schedule</h1>
            <form>
              <div class="form-item">
                <input type="text" placeholder="例：課題締め切り" name="title" class="form__input" />
            
              </div>
            </form>
            <div class="form-footer">
                <input type="submit"  name="submit" class="btn" value="登録">
                

    <p>
    <?php 
        if ($group_id==0) {
            echo "<a href='calendar.php' class='back_btn'>戻る</a>";
        }else {
            echo "<a href='../group/group_top.php' class='back_btn'>戻る</a>";
        }    
    ?>
    </p>
            </div>
          </div>
    </main>
  </body>
</html>