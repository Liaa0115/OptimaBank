<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OptimaBank Login</title>
  <link rel="stylesheet" href="../css/authentication.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> 

</head>
<body>
  <div class="container">
    <div class="left">
      <img src="../images/logo.png" alt="OptimaBank Logo" class="logo" style="width: 70%; height:auto; margin-top:30%">
      <h1>Welcome Back!</h1>
      <p>Secure. Smart. Seamless Banking.</p>
    </div>

    <div class="right">
      <form action="login_process.php" method="POST" class="login-form">
        <center><h2>LOGIN</h2></center>

        <label>Email</label>
        <div class="input-group">
          <input type="email" name="email" placeholder="Enter your Email" required>
          <span class="icon"><i class="fa-solid fa-envelope"></i></span>
        </div>

        <label>Password</label>
        <div class="input-group">
          <input type="password" name="password" placeholder="Enter your Password" required>
          <span class="icon"><i class="fa-solid fa-lock"></i></span>
        </div>

        <div class="form-options">
          <label><input type="checkbox" name="remember"> Remember Me</label>
          <p class="fpassword" ><a href="#">Forgot Password?</a></p>
        </div>

        <button type="submit" class="btn">Log In</button>

        <div class="divider">Or Sign Up Using</div>

        <button class="google-btn">
          <img src="https://cdn.iconscout.com/icon/free/png-256/free-google-logo-icon-download-in-svg-png-gif-file-formats--brands-pack-logos-icons-189824.png?f=webp&w=256" alt="Google"> Sign in with Google
        </button>

        <p class="register">Don’t Have an Account? <a href="register.php">Register</a></p>
      </form>
    </div>
  </div>
</body>
</html>
