<?php
    session_start();
    session_destroy();


    echo '  <script>
                alert("Logout Successfullly");
                window.location.assign("index.php");
            </script>';

?>