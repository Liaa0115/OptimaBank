<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptimaBank Login</title>
    <link rel="stylesheet" href="../css/authentication.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/navbar.css">

    <style>
        /* Your modal styles here (unchanged) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 16px;
            padding: 30px;
            width: 400px;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            border: 3px solid #189d82;
        }

        .modal-content h3 {
            color: #189d82;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .modal-content img {
            width: 130px;
            margin-bottom: 20px;
        }

        .modal-content label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            text-align: left;
        }

        .modal-content input[type="email"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }

        .modal-content button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background-color: #189d82;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-content button:hover {
            background-color: #147f6b;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            font-weight: bold;
            color: #999;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }
        
        /* New Mobile Navbar Styles */
        .mobile-navbar {
            display: none; /* Hidden by default */
            background-color: white;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 10px 20px;
            justify-content: space-between;
            align-items: center;
        }
        
        .mobile-navbar .nav-logo {
            height: 30px;
        }
        
        .mobile-navbar .nav-links {
            display: flex;
            gap: 15px;
        }
        
        .mobile-navbar .nav-links a {
            color: #333;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .mobile-navbar {
                display: flex; /* Show the mobile navbar */
            }
        }
    </style>
</head>

<body>
    <nav class="top-navbar">
        <div class="logo">
            <a href="../index.php">
                <img src="../images/logo.png" alt="OptimaBank Logo" style="height: 30px;">
            </a>
        </div>
        
        <div class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
        
        <ul class="nav-links">
            <li><a href="../index.php">Home</a></li>
            <li><a href="../voucher_list.php">Voucher</a></li>
            <li><a href="register.php">Sign Up</a></li>
        </ul>
    </nav>
    
    <script>
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
    </script>

    <div class="container">
        <div class="left">
            <img src="../images/logo.png" alt="OptimaBank Logo" class="logo" style="width: 70%; height:auto; margin-top:30%">
            <h1>Welcome Back!</h1>
            <p>Secure. Smart. Seamless Banking.</p>
        </div>

        <div class="right">
            <form action="login_process.php" method="POST" class="login-form">
                <center><h2>LOGIN</h2></center>

                <?php
                if (isset($_SESSION['error'])) {
                    echo "<div style='color:red; font-weight:bold;'>" . $_SESSION['error'] . "</div>";
                    unset($_SESSION['error']);
                }
                ?>

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

            <div id="forgotModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h3>RESET YOUR PASSWORD</h3>
                    <img src="../images/logo.png" alt="OptimaBank Logo">
                    <form action="../send_reset_email.php" method="POST">
                        <label for="reset_email">Email</label>
                        <input type="email" name="reset_email" id="reset_email" placeholder="Enter your Email" required>
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
        </div>
    </div>
</body>
</html>