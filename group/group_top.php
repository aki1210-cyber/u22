<?php
session_start();
if(!isset($_SESSION["id"])){
	header("Location: ../login/logout.php");
	exit;
}
$dsn = 'mysql:dbname=u22;host=localhost';
$user = 'root';
$password = '';
//タイムゾーンを設定
date_default_timezone_set('Asia/Tokyo');
if (!isset($_SESSION["group_id"])) {
    $_SESSION["group_id"] = $_GET["group_id"];
}else {
    if (isset($_GET["group_id"])) {
        $_SESSION["group_id"] = $_GET["group_id"];
    }else{
        $_GET["group_id"] = $_SESSION["group_id"];
    }
}

//前月・次月リンクが選択された場合は、GETパラメーターから年月を取得
if(isset($_GET['ym'])){
    $ym = $_GET['ym'];
}else{
    //今月の年月を表示
    $ym = date('Y-m');
    $_GET['ym'] = $ym;
}

try{
    $dbh = new PDO($dsn, $user, $password);
    $sql = "SELECT * FROM calenders WHERE ym=:ym AND group_id=:group_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':ym',$_GET['ym']);
    $stmt->bindValue(':group_id',$_SESSION["group_id"]);
    $stmt->execute();
    $row =$stmt->fetchAll(PDO::FETCH_ASSOC);
    //print_r($row);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql2 = "SELECT users.id, users.name, members.role FROM members INNER JOIN users ON users.id = members.users_id WHERE members.group_id=:group_id AND members.delete_at IS NULL";
    $stmt2 = $dbh->prepare($sql2);
    $stmt2->bindValue(':group_id', $_SESSION["group_id"]);
    $stmt2->execute();
    $row2 =$stmt2->fetchAll(PDO::FETCH_ASSOC);
    //print_r($row2);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql1 = "SELECT name FROM groups WHERE id=:id";
    $stmt1 = $dbh->prepare($sql1);
    $stmt1->bindValue(':id', $_SESSION["group_id"]);
    $stmt1->execute();
    $row1 =$stmt1->fetch(PDO::FETCH_ASSOC);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql3 = "SELECT role FROM members WHERE users_id=:users_id AND group_id=:group_id";
    $stmt3 = $dbh->prepare($sql3);
    $stmt3->bindValue(':users_id', $_SESSION["id"]);
    $stmt3->bindValue(':group_id', $_SESSION["group_id"]);
    $stmt3->execute();
    $row3 =$stmt3->fetch(PDO::FETCH_ASSOC);

    $user_role = $row3["role"];
    if (isset($_GET["button1"])) {
        $dbh = new PDO($dsn, $user, $password);
        $sql4 = "UPDATE groups SET delete_at = 1 WHERE id = :id";
        $stmt4 = $dbh->prepare($sql4);
        $stmt4->bindValue(':id',$_SESSION["group_id"]);
        $flg = $stmt4->execute();
        // if ($flg) {
        //     print("OK");
        // }else{
        //     print("NO");
        // }
        //print_r($row);   
        header("Location: ../calendar/calendar.php"); 
    }
    if (isset($_GET["button2"])) {
        $dbh = new PDO($dsn, $user, $password);
        $sql5 = "UPDATE members SET delete_at = 1 WHERE users_id = :users_id AND group_id = :group_id";
        $stmt5 = $dbh->prepare($sql5);
        $stmt5->bindValue(':users_id',$_SESSION["id"]);
        $stmt5->bindValue(':group_id',$_SESSION["group_id"]);
        $flg = $stmt5->execute();
        // if ($flg) {
        //     print("OK");
        // }else{
        //     print("NO");
        // }
        //print_r($row);   
        header("Location: ../calendar/calendar.php"); 
    }

}catch (PDOException $e){
    print('Error:'.$e->getMessage());
}
//print($user_role);
//タイムスタンプ（どの時刻を基準にするか）を作成し、フォーマットをチェックする
//strtotime('Y-m-01')
$timestamp = strtotime($ym . '-01');
if($timestamp === false){//エラー対策として形式チェックを追加
    //falseが返ってきた時は、現在の年月・タイムスタンプを取得
    $ym = date('Y-m');
    $timestamp = strtotime($ym . '-01');
}

//今月の日付　フォーマット　例）2020-10-2
$today = date('Y-m-j');

//カレンダーのタイトルを作成　例）2020年10月
$html_title = date('Y年n月', $timestamp);//date(表示する内容,基準)

//前月・次月の年月を取得
//strtotime(,基準)
$prev = date('Y-m', strtotime('-1 month', $timestamp));
$next = date('Y-m', strtotime('+1 month', $timestamp));

//該当月の日数を取得
$day_count = date('t', $timestamp);

//１日が何曜日か
$youbi = date('w', $timestamp);

//カレンダー作成の準備
$weeks = [];
$week = '';

//第１週目：空のセルを追加
//str_repeat(文字列, 反復回数)
$week .= str_repeat('<td></td>', $youbi);

for($day = 1; $day <= $day_count; $day++, $youbi++){

    $date = $ym . '-' . $day; //2020-00-00
    if($today == $date){

        $week .= '<td class="today">';
        if ($user_role!=3) {
            $week .= '<a href="../calendar/add_task.php?day='.$day.'&ym='.$ym.'">';
        }
        $week .= $day;
        //今日の場合はclassにtodayをつける
    } else {
        $week .= '<td>';
        if ($user_role!=3) {
            $week .= '<a href="../calendar/add_task.php?day='.$day.'&ym='.$ym.'">';
        }
        $week .= $day;
    }
    if(!empty($row)){
    for($i=0;$i<count($row);$i++){
    if($day==$row[$i]["dates"]){
      $week .= "<br>".$row[$i]["title"];
    }
    }
    }
    $week .= '</a></td>';

    if($youbi % 7 == 6 || $day == $day_count){//週終わり、月終わりの場合
        //%は余りを求める、||はまたは
        //土曜日を取得

        if($day == $day_count){//月の最終日、空セルを追加
            $week .= str_repeat('<td></td>', 6 - ($youbi % 7));
        }

        $weeks[] = '<tr>' . $week . '</tr>'; //weeks配列にtrと$weekを追加
        //print_r($week);
        $week = '';//weekをリセット
    }
}
//print_r($weeks);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
	<link rel="stylesheet" href="../css/calendar.css">
</head>
<body>
    <div class="nav">
        <ul id="group-info">
        <!-- トップページへ -->
        <a href="../calendar/calendar.php" class="top">トップへ</a>
		<li><?php print($row1["name"]); ?></li><br>
			<?php
            $leader="";
            $sub_leader="";
            $normal="";
				//グループ所属メンバー一覧
        		foreach($row2 as $member){
                    $flagment="";
                    if ($member["id"]==$_SESSION["id"]) {
                        $flagment="id='own'";
                    }
                    if ($member["role"]==1) {
                        $leader.="<li class='members' ".$flagment.">".$member["name"];
                        //マウスオーバーで表示されるフォーム要素
                        if($user_role==1 && $_SESSION["id"] != $member["id"]){
                            $leader.='<div class="update-content">
                                    <p>役職を変更</p>
                                    <form action="role_update.php?user_id=';
                                    $leader.= $member["id"];
                                    $leader.='" method="post">
                                        リーダー<input type="radio" name="role" value=1><br>
                                        サブリーダー<input type="radio" name="role" value=2><br>
                                        メンバー<input type="radio" name="role" value=3><br>
                                        <input type="submit" name="submit" class="button" value="確定">
                                    </form>
                                </div>';
                        }
                        $leader.="</li>";
                    }elseif($member["role"]==2){
                        $sub_leader.="<li class='members' ".$flagment.">".$member["name"];
                        //マウスオーバーで表示されるフォーム要素
                        if($user_role==1){
                            $sub_leader.='<div class="update-content">
                                    <p>役職を変更</p>
                                    <form action="role_update.php?user_id=';
                                    $sub_leader.= $member["id"];
                                    $sub_leader.='" method="post">
                                        リーダー<input type="radio" name="role" value=1><br>
                                        サブリーダー<input type="radio" name="role" value=2><br>
                                        メンバー<input type="radio" name="role" value=3><br>
                                        <input type="submit" name="submit" class="button" value="確定">
                                    </form>
                                </div>';
                        }
                        $sub_leader.="</li>";
                    }else {
                        $normal.="<li class='members' ".$flagment.">".$member["name"];
                        //マウスオーバーで表示されるフォーム要素
                        if ($user_role==1) {
                            $normal.='<div class="update-content">
                                    <p>役職を変更</p>
                                    <form action="role_update.php?user_id=';
                                    $normal.= $member["id"];
                                    $normal.='" method="post">
                                        リーダー<input type="radio" name="role" value=1><br>
                                        サブリーダー<input type="radio" name="role" value=2><br>
                                        メンバー<input type="radio" name="role" value=3><br>
                                        <input type="submit" name="submit" class="button" value="確定">
                                    </form>
                                </div>';
                        }
                        $normal.="</li>";
                    }
				}
                echo "<li class='job'>リーダー</li>";
                echo $leader;
                echo "<br>";
                echo "<li class='job'>サブリーダー</li>";
                echo $sub_leader;
                echo "<br>";
                echo "<li class='job'>メンバー</li>";
                echo $normal;
    		?>
		<br>
        <?php 
            if ($user_role!=3) {
                print("<li><a href='search_member.php'>+招待</a></li>");
            }
        ?>
        </ul>
        <!-- グループ削除ボタン -->
        <?php if ($user_role==1) {
            echo "<form action='group_top.php'>";
            echo "<input type='submit' value='グループ削除' name='button1' class='button' >";
            echo "</form>";
        }?>
        <?php if ($user_role!=1) {
            echo "<form action='group_top.php'>";
            echo "<input type='submit' value='グループ脱退' name='button2' class='button'> ";
            echo "</form>";
        }?>
    </div>


    <div class="container">
        <h3><a href="?ym=<?php echo $prev; ?>">&lt;</a><?php echo $html_title; ?><a href="?ym=<?php echo $next; ?>">&gt;</a></h3>
        <table class="table table-bordered">
            <tr>
                <th>日</th>
                <th>月</th>
                <th>火</th>
                <th>水</th>
                <th>木</th>
                <th>金</th>
                <th>土</th>
            </tr>
             <?php
                foreach ($weeks as $week) {
                    echo $week;
                }
            ?>
        </table>
    </div>
</body>
</html>