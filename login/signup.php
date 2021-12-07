<?php
session_start();

$db['host'] = "localhost";  // DBサーバのURL
$db['dbname'] = "u22";  // データベース名
$db['user'] = "root";  // ユーザー名
$db['pass'] = "";  // パスワード
$errorMessage = "";//エラーメッセージ
$signUpMessage = "";//登録完了メッセージ

// 新規登録が押された場合
if (isset($_POST["signUp"])) {
    // 入力チェック
    if(empty($_POST["user_name"])){
        $errorMessage="ユーザー名が入力されていません";
    }else if(empty($_POST["login_id"])){
        $errorMessage="ログインIDが入力されていません";
    }else if(empty($_POST["password"]) || empty($_POST["password2"])){
        $errorMessage="パスワードが入力されていません";
    }

    if (!empty($_POST["user_name"]) && !empty($_POST["login_id"]) && !empty($_POST["password"]) && !empty($_POST["password2"]) && $_POST["password"] === $_POST["password2"]) {
        //ユーザー名、ログインID、パスワード
        $user_name = $_POST["user_name"];
        $login_id = $_POST["login_id"];
        $password = $_POST["password"];

        //Data Source Name(接続情報)
        $dsn = sprintf('mysql: host=%s; dbname=%s; charset=utf8', $db['host'], $db['dbname']);

        try {
            $pdo = new PDO($dsn, $db['user'], $db['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
                );

            $sql = 'INSERT INTO users(name,login_id,password) VALUES(:n,:l,:p)';

            $ps = $pdo->prepare($sql);
            $ps->bindValue(':n', $user_name);
            $ps->bindValue(':l', $login_id);
            $ps->bindValue(':p', password_hash($password, PASSWORD_DEFAULT));
            $ps->execute();
            $signUpMessage = '登録が完了しました。';
        } catch (PDOException $e) {
            $errorMessage = 'データベースエラー';
            // echo $e->getMessage(); でエラー内容を参照可能(デバッグ用)
        }
    } else if($_POST["password"] != $_POST["password2"]) {
        $errorMessage = 'パスワードに誤りがあります。';
    }
}
?>

<!doctype html>
<html lang="ja">
    <head>
            <meta charset="UTF-8">
            <title>新規登録</title>
    </head>
    <body>
        <form id="loginForm" name="loginForm" action="" method="POST">
            <fieldset>
                <legend>アカウントを作成</legend>
                <div><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></div>
                <div><?php echo htmlspecialchars($signUpMessage, ENT_QUOTES); ?></div>
                <label for="user_name">ユーザー名</label><input type="text" id="user_name" name="user_name" value="" placeholder="ユーザー名を入力">
                <br>
                <label for="login_id">ログインID</label><input type="text" id="login_id" name="login_id" placeholder="ログインIDを入力" value="<?php if (!empty($_POST["login_id"])) {echo htmlspecialchars($_POST["login_id"], ENT_QUOTES);} ?>">
                <br>
                <label for="password">パスワード</label><input type="password" id="password" name="password" value="" placeholder="パスワードを入力">
                <br>
                <label for="password2">パスワード(確認用)</label><input type="password" id="password2" name="password2" value="" placeholder="パスワードを再入力">
                <br>
                <input type="submit" id="signUp" name="signUp" value="登録">
            </fieldset>
        </form>
        <br>
        既にアカウントを持っている場合：<a href="login.php">ログイン</a>
    </body>
</html>