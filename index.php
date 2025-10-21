<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link ref="stylesheet" href= "src/style.css">
</head>
<body>

    <?php include_once "header.php"?>
    <!-- script which scans for the URL if there is a error tag inside --> 
    <script src = "scripts/alerts.js" > </script>

<section class = "index-login">
    <div class="index-signup">
        <h4>SIGN UP</h4>
        <p>Don't have an account yet? Sign up here!</p>
        <form action="includes/signup.inc.php" method="post">
            <input type="text" name="uid" placeholder="Username">
            <input type="password" name="pwd" placeholder="Password">
            <input type="password" name="pwdRepeat" placeholder="Repeat Password">
            <input type="text" name="email" placeholder="E-mail">
            <br>
            <button type="submit" name="submit">SIGN UP</button>
        </form>
    </div>

    <div class="index-login">
        <h4>LOGIN</h4>
        <p>Don't have an account yet? Sign up here!</p>
        <form action="includes/login.inc.php" method="post">
            <input type="text" name="uid" placeholder="Username">
            <input type="password" name="pwd" placeholder="Password">
            <br>
            <button type="submit" name="submit">LOGIN</button>
        </form>
    </div>
</section>
<?php

?>
</body>
</html>