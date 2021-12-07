<?php
session_start();

$db['host']="localhost";//DBサーバのURL
$db['dbname']="u22";//データベース名
$db['user']="root";//ユーザー名
$db['pass']="";//パスワード
$error_exist=false;
$er_lid="";
$er_pass="";

if(isset($_POST["login"])){
	//入力チェック
	if(preg_match('/^[0-9|A-Z|a-z]+$/',$_POST["login_id"])){
		if((strlen($_POST["login_id"])<4) || (strlen($_POST["login_id"])>16)){
			$error_exist=true;
			$er_lid="ログインIDを4~16文字で入力して下さい";
		}
	}else{
		$error_exist=true;
		$er_lid="ログインIDを半角英数字で入力して下さい";
	}
	if(preg_match('/^[0-9|A-Z|a-z]+$/',$_POST["password"])){
		if((strlen($_POST["password"])<4) || (strlen($_POST["password"])>16)){
			$error_exist=true;
			$er_pass="パスワードを4~16文字で入力して下さい";
		}
	}else{
		$error_exist=true;
		$er_pass="パスワードを半角英数字で入力して下さい";
	}

	if(!$error_exist){
		$dsn = sprintf('mysql: host=%s;dbname=%s;charset=utf8',$db['host'],$db['dbname']);
		try{
			$pdo=new PDO($dsn,$db['user'],$db['pass'],
				[
					PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_EMULATE_PREPARES=>false
				]
			);
			$sql='SELECT * FROM users WHERE login_id=:l';
			$ps=$pdo->prepare($sql);
			$ps->bindValue(':l',$_POST["login_id"]);
			$ps->execute();
			if($row=$ps->fetch(PDO::FETCH_ASSOC)){
				//パスワード認証
				if(password_verify($_POST["password"],$row["password"])){
					session_regenerate_id(true);
					$_SESSION["id"]=$row["id"];
					header("Location: ../calendar/calendar.php");
					exit();
				}else{
					//pass不一致
					$er_pass="ユーザーIDあるいはパスワードに誤りがあります";
				}
			}else{
				//入力されたログインIDのレコードが存在しない
				$er_pass="ユーザーIDあるいはパスワードに誤りがあります";
			}
		}catch(PDOException $e){
			echo "データベースエラー";
			//echo $e->getMessage(); デバッグ用
		}
	}
}
?>
<!doctype html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="../css/login.css">
	<title>Sign in</title>
</head>

<body>
	<div class="main">
		<h1 class="login">merge calendars</h1>
		<form id="loginForm" name="loginForm" action="" method="POST">

			<div class="form-item">
				<input class="login_id" type="text" name="login_id" placeholder="Login ID" value="<?php if(!empty($_POST["login_id"])){echo htmlspecialchars($_POST["login_id"],ENT_QUOTES);} ?>">
			</div>

			<div class="form-item">
				<small class="er_message"><?php echo $er_lid; ?></small>
			</div>
			<br>

			<div class="form-item">
				<input class="password" type="password" name="password" placeholder="Password">
			</div>

			<div class="form-item">
				<small class="er_message"><?php echo $er_pass; ?></small>
			</div>

			<div class="button-panel"><input type="submit" class="button" name="login" value="Sign in"></div>
			<div class="form-footer"><p class="create"><a href="signup.php">Create an account</p><div>

		</form>
	</div>
</body>
</html>