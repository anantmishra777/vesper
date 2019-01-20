<?php
    session_start();

    //redirect to login page if admin isn't logged in
    // if( !isset($_SESSION['admin_email']) )
    //     header('Location:../logout');
    
    //set language
    // if( !isset($_SESSION['language']) )
    //     $_SESSION['language']='en';
    
    //creating CRSF token if not already set
    if( !isset($_SESSION['csrf_token']) )
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    //connect database
    include '../class/dbconnector1.php';
    include '../class/dbconnector2.php';

    //include config file
    include '../class/config.php';

    //including mysql functions file
    include '../class/class.base.php';
    $base = new base();

    //log errors
    ini_set("log_errors", 1);
    ini_set("error_log", "error.log");

    function dec($string)
    {
        return openssl_decrypt($string, 'AES-256-CBC', '48f5d1ba295d17e6ecc0cd508b6a242c501f1aff', true, '48f5d1ba295d17e6');
    }
?>



<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <title id="title_admin_panel">Admin Panel - Vesper Group</title>
        <link rel="stylesheet" href="../theme/css/bootstrap.css">
        <link rel="stylesheet" href="../theme/css/bootstrap-responsive.css">
        <link rel="stylesheet" href="../theme/css/jquery.fancybox.css">
        <link rel="stylesheet" href="../theme/css/style.css">
    </head>
    <body class="position1">
        <!-- CSRF token -->
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <!--Topbar-->
        <?php include 'topbar.php'; ?>
        <!--End Topbar-->

         <!-- Second Topbar -->
         <div class="breadcrumbs">
            <div class="container-fluid">
                <ul class="bread pull-left">
                    <li>
                        <a href="../adminPanel"><i class="icon-home icon-white"></i></a>
                    </li>
                    <li >
                        <a href="security.php">Säkerhet</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- End Second Topbar -->

        <div class="main">
            <div class="container-fluid">

                <!--Sidebar-->
                <?php include 'sidebar.php'; ?>
                <!--End Sidebar-->               

                <div class="content">
                    <div class="row-fluid">
                        <div class="span6">
                            <div class="box">
                                <div class="box-head">
                                    <h3>Log Table</h3>
                                </div>
                                <div class="box-content box-nomargin">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th><strong id="log_id">Log ID</strong></th>
                                            <th><strong id='log_description'>Description</strong></th>
                                            <th><strong id="log_date">Date</strong></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                //fetch all logs now
                                                try
                                                {
                                                    $query =$conn2->query("SELECT * FROM logs ORDER BY l_logID DESC");
                                                }
                                                catch(PDOException $e)
                                                {
                                                    $e->getMessage();
                                                }

                                                while($row = $query->fetch(PDO::FETCH_OBJ))
                                                {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row->l_logID); ?></td>
                                                        <td><?php echo htmlspecialchars(dec($row->l_logDescription)); ?></td>
                                                        <td><?php echo htmlspecialchars(dec($row->l_dateTime)); ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            
        <!--Footer-->
        <?php include 'footer.php'; ?>

        <script src="../theme/js/jquery.js"></script>
        <script src="../theme/js/less.js"></script>
        <script src="../theme/js/bootstrap.min.js"></script>
        <script src="../theme/js/jquery.peity.js"></script>
        <script src="../theme/js/jquery.fancybox.js"></script>
        <script src="../theme/js/jquery.flot.js"></script>
        <script src="../theme/js/jquery.color.js"></script>
        <script src="../theme/js/jquery.flot.resize.js"></script>
        <script src="../theme/js/jquery.cookie.js"></script>
        <script src="../theme/js/jquery.cookie.js"></script>
        <script src="../theme/js/custom.js"></script>
        <script src="../theme/js/demo.js"></script>
        <script src="../theme/js/main.js"></script>
    </body>
    <script>
        $('#li_security').addClass('active');
    </script>
</html>