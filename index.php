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
    <!-- script which scans for the URL if there is a error tag inside --> 
    <script src = "scripts/alerts.js" > </script>
    <header> 
        <h1> My header area <h1> 
        <nav>
    <div>
        <ul class="menu-main">
            <li><a href="index.php">HOME</a></li>
            <li><a href="#">PRODUCTS</a></li>
            <li><a href="#">CURRENT SALES</a></li>
            <li><a href="#">MEMBER+</a></li>
        </ul>
    </div>

    <ul class="menu-member">
        <?php
        echo '<script> alert("Login success")</script>';
        if (isset($_SESSION["userid"])) {
        ?>
            <li><a href="#"><?php echo $_SESSION["useruid"]; ?></a></li>
            <li><a href="includes/logout.inc.php" class="header-login-a">LOGOUT</a></li>
        <?php
        } else {
        ?>
            <li><a href="#">SIGN UP</a></li>
            <li><a href="#" class="header-login-a">LOGIN</a></li>
        <?php
        }
        ?>
    </ul>
</nav>
    </header>

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