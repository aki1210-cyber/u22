<?php
session_start();
if(!isset($_SESSION["id"])){
	header("Location: ../login/logout.php");
	exit;
}
if (isset($_SESSION["group_id"])) {
    $_SESSION["group_id"]=null;
}
//DB設定
$dsn = 'mysql:dbname=u22;host=localhost';
$user = 'root';
$password = '';
date_default_timezone_set('Asia/Tokyo');

//前月・次月リンクが選択された場合は、GETパラメーターから年月を取得
if(isset($_GET['ym'])){
    $ym = $_GET['ym'];
}else{
    //今月の年月を表示
    $ym = date('Y-m');
    $_GET['ym'] = $ym;
}
//DB処理
try{
    $dbh = new PDO($dsn, $user, $password);
    $sql = "SELECT * FROM calenders INNER JOIN groups ON calenders.group_id = groups.id WHERE ym=:ym AND(calenders.users_id = :users_id OR calenders.group_id IN (select group_id FROM members WHERE users_id = :users_id)) AND groups.delete_at IS null";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':ym',$_GET['ym']);
    $stmt->bindValue(':users_id',$_SESSION["id"]);
    $stmt->execute();
    $row =$stmt->fetchAll(PDO::FETCH_ASSOC);
    //所属グループ一覧
    $sql1 = "SELECT groups.name, groups.id as group_id FROM groups INNER JOIN members ON groups.id = members.group_id WHERE members.users_id=:users_id AND groups.delete_at IS NULL AND members.delete_at IS NULL";
    $stmt1 = $dbh->prepare($sql1);
    $stmt1->bindValue(':users_id',$_SESSION["id"]);
    $stmt1->execute();
    $row1 =$stmt1->fetchAll(PDO::FETCH_ASSOC);
}catch(\Exception $e){
    print("=----");
}
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
        $week .= '<td class="today">' .'<a href="add_task.php?day='.$day.'&ym='.$ym.'">'. $day;//今日の場合はclassにtodayをつける
    } else {
        $week .= '<td>' .'<a href="add_task.php?day='.$day.'&ym='.$ym.'">'. $day;
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
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>PHPカレンダー</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
    <link rel="stylesheet" href="../css/calendar.css">
</head>
<body>
    <!-- ナビゲーション部分 ホーム、グループ、アカウント等？-->
    <div class="nav">
        <ul>
        <!-- グループ(アコーディオンメニュー) -->
            <li>Groups</li>
            <?php
                //members.users_id の所属するグループ一覧をリストに格納
                foreach($row1 as $groups){
                    echo "<li class='job'><a href='../group/group_top.php?group_id=".$groups["group_id"]."'>".$groups["name"]."</a></li>";
                }
            ?>
        <br>
        <li class="job"><a href="../group/create_group.php">+Create group</a></li>
        </ul>
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