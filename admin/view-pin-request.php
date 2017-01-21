<?php 
    require('php-includes/connection.php');
    include('php-includes/check-login.php');

    $product_amount = 2000;
    
?>


<?php
    if(isset($_POST['send'])){
        $id = mysqli_real_escape_string($con, $_POST['id']);
       echo  $userid = mysqli_real_escape_string($con, $_POST['userid']);
        $amount = mysqli_real_escape_string($con , $_POST['amount']);

        $no_of_pin = $amount/$product_amount;

        // insert pin...
        $i=1;

        while($i < $no_of_pin ){
            $new_pin  = pin_generate();

           mysqli_query($con , "insert into pin_list (`userid` , `pin`) values('$userid', '$new_pin')" );
            $i++;
        }


        // update pin ...
        mysqli_query($con , "update pin_request set status = 'close' where id='$id' limit 1");

        echo '<script>
             alert("pin send successfully ..."); window.location.assign("view-pin-request.php");
             </script>';
    }




    // pin generate func ..

    function pin_generate(){
        global $con ;
        $generate_pin = rand(100000 , 999999);

        $query = mysqli_query($con , "select * from pin_list where pin = '$generate_pin'");

        if(mysqli_num_rows($query)>0){
            pin_generate();
        }else{
            return $generate_pin;
        }
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

    <title>MLM Website - View Pin Request </title>

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
                        <h1 class="page-header">Admin - View Pin Request</h1>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <!-- /.row -->


                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>S.N</th>
                                    <th>User Id</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Send</th>
                                    <th>Cancel</th>
                                </tr>


                                <?php 
                                   
                                    $query = mysqli_query($con , " select * from pin_request where status='open' ");
                                    if(mysqli_num_rows($query)>0){
                                        $i=1;

                                        while($row=mysqli_fetch_array($query)){
                                                 $id = $row['id'];
                                                 $email = $row['email'];
                                                 $amount = $row['amount'];
                                                 $date = $row['date'];
                                            
                                ?>

                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $email; ?></td>
                                            <td><?php echo $amount; ?></td>
                                            <td><?php echo $date; ?></td>
                                            <form method="post">
                                                <input type="hidden" name="userid" value="<?php echo $email ?>">
                                                <input type="hidden" name="amount" value="<?php echo $amount ?>">
                                                <input type="hidden" name="id" value="<?php echo $id ?>">
                                                <td>
                                                    <input type="submit" name="send" value="Send" class="btn btn-primary">
                                                </td>
                                            </form>
                                            <td>Cancel</td>
                                        </tr>
                                <?php
                                      $i++;
                                        }
                                    } else{
                                ?>
                                        <tr>
                                            <td colspan="6" assign="center">You have no pin request.</td>
                                        </tr>
                                <?php
                                    }
                                ?>
                            </table>
                        </div>
                    </div>         
                </div>
                <!-- /. pin request row -->
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
