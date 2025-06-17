<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OptimaBank Login</title>
  <link rel="stylesheet" href="../css/authentication.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> 
  
  
  <style>
      /* Modal Styles */
      .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
      .modal-content { background-color: white; padding: 20px; border-radius: 8px; width: 300px; position: relative; }
      .close { position: absolute; right: 10px; top: 5px; cursor: pointer; font-size: 18px; }
  </style>

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
          <span class="icon" style="font-size: medium;"><i class="fa-solid fa-envelope"></i></span>
        </div>

        <label>Password</label>
        <div class="input-group">
          <input type="password" name="password" placeholder="Enter your Password" required>
          <span class="icon" style="font-size: medium;"><i class="fa-solid fa-lock"></i></span>
        </div>

        <div class="form-options">
          <label><input type="checkbox" name="remember"> Remember Me</label>
          <p class="fpassword"><a href="#" onclick="openModal()">Forgot Password?</a></p>
        </div>

        <button type="submit" class="btn">Log In</button>

        <div class="divider">Or Sign Up Using</div>

        <button class="google-btn" onclick="window.location.href='google-login.php'" type="button">
          <img src="https://cdn.iconscout.com/icon/free/png-256/free-google-logo-icon-download-in-svg-png-gif-file-formats--brands-pack-logos-icons-189824.png?f=webp&w=256" alt="Google"> Sign in with Google
        </button>

        <p class="register">Don’t Have an Account? <a href="register.php">Register</a></p>
      </form>

      <!-- Forgot Password Modal -->
      <div id="forgotModal" class="modal">
        <div class="modal-content">
          <span class="close" onclick="closeModal()">&times;</span>
          <h3>Forgot Password</h3>
          <form action="../send_reset_email.php" method="POST">
            <label for="reset_email">Enter your email:</label>
            <input type="email" name="reset_email" required>
            <button type="submit">Send Reset Link</button>
          </form>
        </div>
      </div>

      <script>
      function openModal() {
        document.getElementById('forgotModal').style.display = 'flex';
      }
      function closeModal() {
        document.getElementById('forgotModal').style.display = 'none';
      }
      </script>

      <?php session_start(); ?>
        <?php if (isset($_SESSION['error'])): ?>
          <div class="error-message" style="color:red; text-align:center;">
            <?php
              echo $_SESSION['error'];
              unset($_SESSION['error']); // Clear message after showing
            ?>
          </div>
        <?php endif; ?>

    </div>
  </div>
</body>
</html>
