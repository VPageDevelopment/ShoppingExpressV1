<?php 
    require('php-includes/connection.php');
    include('php-includes/check-login.php');

    $userid = $_SESSION['userid'];
    $capping = 1000;

?>


<?php 
    // User clicked on join 

    if(isset($_GET['join_user'])){

        $side = '';

        $pin = mysqli_real_escape_string($con , $_GET['pin']);
        $email = mysqli_real_escape_string($con , $_GET['email']);
        $mobile = mysqli_real_escape_string($con , $_GET['mobile']);
        $address = mysqli_real_escape_string($con , $_GET['address']);
        $account = mysqli_real_escape_string($con , $_GET['account']);
        $under_userid = mysqli_real_escape_string($con , $_GET['under_userid']);
        $side = mysqli_real_escape_string($con , $_GET['side']);

        $password = "123456";

        $flag = 0;




        if($pin !='' && $email != '' && $mobile!='' && $account !='' && $under_userid !='' && $side != ''){
            // if user filled all fields correctly ... 

            // pin is ok ..
            if(check_pin($pin)){

                // email is ok ...
                if(check_email($email)){
                    // under userid is ok ...
                    if(!check_email($under_userid)){
                        // under userid side is ok ...
                        if(check_under_userid_side($under_userid , $side)){
                            $flag = 1;
                        }else{
                            // check under userid
                            echo '<script>alert("Invalid Under User id Side..")</script>';
                        }

                    }else{
                        // check under userid
                        echo '<script>alert("Invalid Under User id..")</script>';
                    }

                }else{
                    // check email
                     echo '<script>alert("User already exist..")</script>';
                }

            }else{
                // check pin
                echo '<script>alert("Invalid pin")</script>';
            }

        }else{

            // check all the fields ..
            echo '<script>alert("Please fill all the fields.")</script>';
        }

        // now we are here 
        // it mean all the infomation is correct ...
        // now we will save the information ... 

        if($flag == 1){

            // insert into the user table...
            $query = mysqli_query($con , "insert into 
                                            user(`email`, `password`, `mobile`, `address`, `account`, `under_userid`)
                                            values('$email', '$password' , '$mobile', '$address' , '$account' , '$under_userid')
            " );

            // insert into tree .. 
            // as a user ...
            $query = mysqli_query($con , "insert into tree (`userid`) values('$email')");



            // update the side values ...
            $query = mysqli_query($con , "update tree set `$side`='$email' where userid = '$under_userid' ");

            // update the pin to close

            $query = mysqli_query($con , "update pin_list set `status`='close' where pin='$pin'");

            // insert into income ..
            $query = mysqli_query($con , "insert into income (`userid`) values('$userid') ");

            // update income table ... 
            $temp_under_userid = $under_userid;
            $temp_side = $side ;
            $temp_side_count = $side.'count';


            $total_count = 1;

            while($total_count >0){
                $query = mysqli_query($con , "select * from tree where userid='$temp_under_userid'");
                $result = mysqli_fetch_array($query);
                $current_temp_side_count = $result[$temp_side_count] + 1;

                mysqli_query($con, "update tree set `$temp_side_count`= '$current_temp_side_count' where userid='$temp_under_userid' ");
            
                // income update ... 


                if($userid){
                     $income_data = income($temp_under_userid); 
                     if($income_data['day_bal'] < $capping){
                            // all the tree infomation here ... 

                            $tree_data = tree($temp_under_userid);

                            $temp_left_count = $tree_data['leftcount'];
                            $temp_right_count = $tree_data['rightcount'];

                            if($temp_left_count > 0 && $temp_right_count > 0 ){
                                // check the site the user selected 
                                if($temp_side == "left"){

                                    if($temp_left_count <= $temp_right_count){
                                        $new_day_bal = $income_data['day_bal'] + 250;
                                        $new_current_bal = $income_data['current_bal'] + 250;
                                        $new_total_bal = $income_data['total_bal'] + 250;


                                      // update income .... 

                                      mysqli_query($con , "update income set day_bal = '$new_day_bal' ,  current_bal = '$new_current_bal', total_bal = '$new_total_bal' where userid='$temp_under_userid' limit 1 ");

                                    }
                                       
                                }else{

                                     if($temp_left_count >= $temp_right_count){
                                        $new_day_bal = $income_data['day_bal'] + 250;
                                        $new_current_bal = $income_data['current_bal'] + 250;
                                        $new_total_bal = $income_data['total_bal'] + 250;


                                      // update income .... 

                                      mysqli_query($con , "update income set day_bal = '$new_day_bal' ,  current_bal = '$new_current_bal', total_bal = '$new_total_bal' where userid='$temp_under_userid' limit 1 ");

                                    }

                                }
                            }



                     }


                    //   change user under id ... 


                    $next_under_userid = get_under_userid($temp_user_userid);
                    $temp_side = get_under_userid_side($temp_user_userid);
                    $temp_side_count = $temp_side.'count';
                    $temp_under_userid = $next_under_userid;
                }

                if($userid){
                    $total_count = 0;
                }
            }



             // check all the fields ..
            echo '<script>alert("User created successfully.")</script>';

        }


      
    }



    // check the pin 
    function check_pin($pin){
        global $con , $userid;

        $query = mysqli_query($con , "select * from pin_list where pin = '$pin' and userid = '$userid' and status='open'");

        if(mysqli_num_rows($query) >0){
            return true;
        }else{
            return false;
        }
    }



    // check the pin 
    function check_email($email){
        global $con;

        $query = mysqli_query($con , "select * from user where email = '$email' ");

        if(mysqli_num_rows($query) >0){
            return false;
        }else{
            return true;
        }
    }

    // check side 

    function check_under_userid_side($email , $side){
        global $con ;

        $query = mysqli_query($con , "select * from tree where userid ='$email'");
        $result = mysqli_fetch_array($query);
        $side_value = $result[$side];

        if($side_value == ''){
            return true;
        }else{
            return false;
        }

    }

    // income 


    function income ($userid){
        global $con;
        $data = array();
        $query = mysqli_query($con , "select * from income where userid = '$userid'");
        $result = mysqli_fetch_array($query);
        $data['day_bal'] = $result ['day_bal'];
        $data['current_bal'] = $result ['current_bal'];
        $data['total_bal'] = $result ['total_bal'];


    }


    // tree

      function tree ($userid){
        global $con;
        $data = array();
        $query = mysqli_query($con , "select * from income where userid = '$userid'");
        $result = mysqli_fetch_array($query);
        $data['left'] = $result ['left'];
        $data['right'] = $result ['right'];
        $data['leftcount'] = $result ['leftcount'];
        $data['rightcount'] = $result ['rightcount'];


    }

// under user id
    function get_under_userid($userid){
        global $con;

        $query = mysqli_query($con , "select * from user where email='$userid' ");

        $result = mysqli_fetch_array($query);
        return result['under_userid'];
    }

// under user id side ..

function get_under_userid_side($userid){
        global $con;

        $query = mysqli_query($con , "select * from user where email='$userid' ");

        $result = mysqli_fetch_array($query);
        return result['side'];
    }
    
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>MLM Website - Join</title>

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

    <div id="wrapper">

        <!-- Navigation -->
        <?php include('php-includes/menu.php') ?>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">Join  <?php // echo $userid ;?></h1>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-lg-4">
                        <form method="get">
                            <div class="form-group">
                                <label>Pin</label>
                                <input type="text" name="pin" class="form-control" required />
                            </div> 
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email" class="form-control" required />
                            </div> 
                        
                            <div class="form-group">
                                <label>Mobile</label>
                                <input type="text" name="mobile" class="form-control" required />
                            </div> 
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control" required />
                            </div> 
                            <div class="form-group">
                                <label>Account No</label>
                                <input type="text" name="account" class="form-control" required />
                            </div> 

                             <div class="form-group">
                                <label>Under User Id</label>
                                <input type="text" name="under_userid" class="form-control" required />
                            </div> 

                            <div class="form-group">
                                <label>Side</label> <br>
                                <input type="radio" name="side" value="left" /> Left
                                <input type="radio" name="side" value="right" /> Right
                            </div> 

                            <div class="form-group">
                                <input type="submit" name="join_user" class="btn btn-primary" value="join" />
                            </div> 
                        </form>
                    </div>
                </div>
                <!--./ pin row-->
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="dist/js/sb-admin-2.js"></script>

</body>

</html>
