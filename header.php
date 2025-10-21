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
            if (isset($_SESSION["userid"])) {
            ?>
                <li><a href="/pages/profile.php"><?php echo $_SESSION["useruid"]; ?></a></li>
                <li><a href="../includes/logout.inc.php" class="header-login-a">LOGOUT</a></li>
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