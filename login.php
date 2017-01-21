<?php

    session_start();

    require('php-includes/connection.php');

    $email = mysqli_real_escape_string($con , $_POST['email']);
    $password = mysqli_real_escape_string($con , $_POST['password']);

    $query = mysqli_query($con ,"select * from user where email = '$email' and password = '$password'");

            if(mysqli_num_rows($query)>0){


                $_SESSION['userid'] = $email;
                $_SESSION['id'] = session_id();
                $_SESSION['login_type'] = 'user';
                

                 echo '<script>
                             alert("Login Successfully ..."); 
                             window.location.assign("home.php");
                      </script>';
            } else {
                echo '<script>
                             alert("Email or Password are wrong ..."); 
                             window.location.assign("index.php")
                      </script>';
            }

?>