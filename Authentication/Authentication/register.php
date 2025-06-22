<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - OptimaBank</title>
  <link rel="stylesheet" href="../css/authentication.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   <link rel="stylesheet" href="../css/navbar.css">
</head>
<body> 

 <nav class="top-navbar">
  <div class="logo">
    <a href="index.html">
      <img src="../images/logo.png" alt="OptimaBank Logo" style="height: 30px;">
    </a>
  </div>
  <ul>
    <li><a href="index.html">Home</a></li>
    <li><a href="voucher.html">Voucher</a></li>
    <li><a href="login.php">Sign In</a></li>
  </ul>
</nav>


  <div class="container">
    <!-- Left Side (Image + Welcome Text) -->
    <div class="left">
      <img src="../images/logo.png" alt="OptimaBank Logo" class="logo" />
      <h1>Banking<br>Anywhere,<br>Anytime.</h1>
      <p>Welcome to Online Banking from Optima Bank.<br>
      We provide you the convenience of banking at your fingertips.</p>
    </div>

    <!-- Right Side (Form) -->
    <div class="right">
      <form action="register_process.php" method="POST" class="login-form">
        <center><h2>REGISTER</h2></center>

        <div class="input-group">
          <input type="text" name="username" placeholder="Enter username here" required />
          <span class="icon" style="font-size: medium;"><i class="fa-solid fa-user"></i></span>
        </div>

        <div class="input-group">
          <input type="email" name="email" placeholder="Enter your Email" required />
          <span class="icon" style="font-size: medium;"><i class="fa-solid fa-envelope"></i></span>
        </div>

        <div class="input-group">
          <input type="text" name="phone" placeholder="Enter phone number here" required />
          <span class="icon" style="font-size: medium;"><i class="fa-solid fa-phone"></i></span>
        </div>

        <div class="input-group">
          <input type="password" name="password" placeholder="Enter password here" required />
          <span class="icon" style="font-size: medium;"><i class="fa-solid fa-lock"></i></span>
        </div>

        <div class="input-group">
          <input type="password" name="confirm_password" placeholder="Re-enter your password here" required />
          <span class="icon" style="font-size: medium;"><i class="fa-solid fa-lock"></i></span>
        </div>

        <button type="submit" class="btn">Sign Up</button>

        <div class="register">
          Already have an Account? <a href="login.php">Log in.</a>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
