<?php 
session_start();
require_once 'php_action/db_connect.php';

// If already logged in, redirect based on role
if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    $sql = "SELECT uas FROM users WHERE user_id = $userId";
    $result = $connect->query($sql);
    if ($result && $result->num_rows == 1) {
        $uas = $result->fetch_assoc()['uas'];
        if($uas == '0'){
            header('Location: admin/index.php');
            exit;
        } else if($uas == '1'){
            header('Location: client/index.php'); // Adjust this if your user panel is at /user/
            exit;
        } else if($uas == '2'){
            header('Location: client/index.php');
            exit;
        }
    }
}

$errors = array();
if($_POST) {		
	$username = $_POST['username'];
	$password = $_POST['password'];
	if(empty($username) || empty($password)) {
		if($username == "") $errors[] = "Username is required";
		if($password == "") $errors[] = "Password is required";
	} else {
		$sql = "SELECT * FROM users WHERE username = '$username'";
		$result = $connect->query($sql);
		if($result->num_rows == 1) {
			$password = md5($password);
			$mainSql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
			$mainResult = $connect->query($mainSql);
			if($mainResult->num_rows == 1) {
				$value = $mainResult->fetch_assoc();
				$_SESSION['userId'] = $value['user_id'];
				$uas = $value['uas'];
				if($uas == '0'){
					header('Location: admin/index.php');
				} else if($uas == '1'){
					header('Location: client/index.php');
				} else if($uas == '2'){
					header('Location: client/index.php');
				}
				exit;
			} else {
				$errors[] = "Wrong Username or Password";
			}
		} else {		
			$errors[] = "Username does not exist";		
		}
	}
}
?>

<!------ Include the above in your HEAD tag ---------->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Smart Stockers</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>


<body>
    <style>
    .login-bg {
        background: url('img/background.jpg') no-repeat center center;
        background-size: cover;
    }


    .form-container {
        width: 100%;
        max-width: 400px;
    }

    .login-text {
        position: absolute;
        top: 40%;
        left: 25%;
        transform: translate(-50%, -50%);
        max-width: 70%;
    }
    </style>

    <div class="container-fluid min-vh-100">
        <div class="row min-vh-100">
            <!-- Background Image Column -->
            <div class="col-lg-8 d-none d-lg-block login-bg">
                <div class="login-text text-white text-center">
                    <h1 class="display-3 fw-bold text-white ">SmartStockers</h1>

                    <p class="lead">All-in-One Dashboard for Your Online Store Orders</p>
                </div>

            </div>

            <!-- Login Form Column -->
            <div class="col-lg-4 d-flex justify-content-center align-items-center bg-white">
                <div class="form-container">

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" style="zoom:70%;">
                        <i class="fa fa-exclamation-circle me-2"></i>

                        <?php foreach ($errors as $error): ?>
                        <span><?php echo $error; ?></span>
                        <?php endforeach; ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <div class="text-center">
                        <img src="img/logo.png" alt="Smart Stocker's Logo" height="150px">
                        <br>
                        <h5 class="mt-3 text-primary">Sign in to your account</h5>
                    </div>

                    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" id="loginForm" class="mt-3">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="username" id="username" placeholder="Email">
                            <label for="username">Email</label>
                        </div>

                        <div class="form-floating mb-3 position-relative">
                            <input type="password" class="form-control" name="password" id="password"
                                placeholder="Password">
                            <label for="password">Password</label>

                            <!-- Toggle button (absolute positioned inside input) -->
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-2"
                                onclick="togglePassword(this)" tabindex="-1" style="z-index: 2;">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>


                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="scale:0.9;">
                                <input type="checkbox" id="rememberMe">
                                <label for="rememberMe">Keep me logged in</label>
                            </div>
                            <a href="#" onclick="alert('Contact Admin to Reset Password');"
                                class="small text-decoration-none">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">Log in</button>
                    </form>

                    <!-- <div class="text-center mt-3">
                            <small>Don't have an account? <a href="#" class="text-decoration-none">Sign up</a></small>
                        </div> -->
                    <div class="footer text-center py-3">
                        <small>Developed By <strong>Nexvel Hub</strong>.</small>
                    </div>
                </div>



            </div>

        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script>
    function togglePassword(button) {
        const icon = button.querySelector('i');
        const input = button.closest('.form-floating').querySelector('input');

        if (!input || !icon) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        icon.classList.toggle('bi-eye', isPassword);
        icon.classList.toggle('bi-eye-slash', !isPassword);
    }
    </script>
</body>

</html>