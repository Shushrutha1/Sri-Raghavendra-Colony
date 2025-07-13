<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//session_start(); 
include 'config.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture the form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare query to check if the user exists
    $query = "SELECT * FROM admin WHERE username = '$username'";

    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        // Verify the password
        if (password_verify($password, $row['password'])) {
            // Start session and store user details
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
			if($row['role']==1)
			{
			 header('Location: reports.php'); // Redirect to dashboard or home page
			}
			else if($row['role']==2)
			{
				header('Location: receipts.php'); // Redirect to dashboard or home page
			}
            exit();
			
			} else {
            $error_message = "Invalid username or password!";
        }
    } else {
        $error_message = "Invalid username or password!";
    }
			
           /* echo '<script>alert("Login successfully!"); window.location.href = "receipts.php";</script>'; 
        } else {
            echo '<script>alert("Invalid User Name or Password!"); window.location.href = "receipts.php";</script>'; 
        }
    } else {
        echo '<script>alert("Invalid User Name or Password!"); window.location.href = "receipts.php";</script>'; 
    }*/
}
?>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
        <meta name="author" content="Coderthemes">

        <link rel="shortcut icon" href="images/favicon_1.ico">

        <title>SRC LOGIN</title>

        <!-- Base Css Files -->
        <link href="css/bootstrap.min.css" rel="stylesheet" />

        <!-- Font Icons -->
        <link href="assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
        <link href="assets/ionicon/css/ionicons.min.css" rel="stylesheet" />
        <link href="css/material-design-iconic-font.min.css" rel="stylesheet">

        <!-- animate css -->
        <link href="css/animate.css" rel="stylesheet" />

        <!-- Waves-effect -->
        <link href="css/waves-effect.css" rel="stylesheet">

        <!-- Custom Files -->
        <link href="css/helper.css" rel="stylesheet" type="text/css" />
        <link href="css/style.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <script src="js/modernizr.min.js"></script>
        
    </head>
    <body>


        <div class="wrapper-page">
            <div class="panel panel-color panel-primary panel-pages">
                <div class="panel-heading bg-img"> 
                    <div class="bg-overlay"></div>
                    <h3 class="text-center m-t-10 text-white"> Sri Raghavendra Colony <strong> - Login</strong> </h3>
                </div> 


                <div class="panel-body">
                <form method="post" class="form-horizontal m-t-20">
                    
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input class="form-control input-lg " name="username" type="text" required="" placeholder="Username">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <input class="form-control input-lg" type="password" name="password" required="" placeholder="Password">
                        </div>
                    </div>
                                        
                    <div class="form-group text-center m-t-40">
                        <div class="col-xs-12">
                            <button class="btn btn-primary btn-lg w-lg waves-effect waves-light" type="submit">Log In</button>
                        </div>
                    </div>

                   
                </form> 
                </div>                                 
                
            </div>
        </div>

        
    	<script>
            var resizefunc = [];
        </script>
    	<script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/waves.js"></script>
        <script src="js/wow.min.js"></script>
        <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
        <script src="js/jquery.scrollTo.min.js"></script>
        <script src="assets/jquery-detectmobile/detect.js"></script>
        <script src="assets/fastclick/fastclick.js"></script>
        <script src="assets/jquery-slimscroll/jquery.slimscroll.js"></script>
        <script src="assets/jquery-blockui/jquery.blockUI.js"></script>


        <!-- CUSTOM JS -->
        <script src="js/jquery.app.js"></script>
	


