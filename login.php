<?php

ini_set('display_errors',1); 
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="en-US">
  <head>
    <title>Facems Admin | Sign In</title>
    <?php require_once 'includes/head.inc'; ?>
  </head>
  <body id="login">
    <div id="login-wrapper" class="png_bg">
      <div id="login-top">
        <h1>Facems Admin</h1>
        <!-- Logo (221px width) -->
        <img id="logo" src="resources/images/logo.png" alt="Simpla Admin logo" />
      </div>
      <!-- End #logn-top -->
      <div id="login-content">
        <form action="index.php" enctype="application/x-www-form-urlencoded" method="post">
          <div class="notification information png_bg">
            <div>Enter your username and password.</div>
          </div>
          <p>
            <label>Username</label>
            <input class="text-input" type="text" name="username" />
          </p>
          <div class="clear"></div>
          <p>
            <label>Password</label>
            <input class="text-input" type="password" name="password" />
          </p>
          <div class="clear"></div>
          <!-- <p id="remember-password">
          <input type="checkbox" />Remember me
          </p>
          <div class="clear"></div> -->
          <p>
            <label>Comfirm</label>
            <input name="action" id="action" value="login" type="hidden" />
            <input class="button" type="submit" value="Connect with Facebook"
            style="margin-left:5px; margin-top:0px;" />
            <input class="button" type="submit" value="Sign In" style="margin-top:0px;" />
          </p>
        </form>
      </div>
      <!-- End #login-content -->
    </div>
    <!-- End #login-wrapper -->
  </body>
</html>