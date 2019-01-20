<?php
    session_start();

    //redirect to login page if admin isn't logged in
    // if( !isset($_SESSION['admin_email']) )
    //     header('Location: ../login');
    
    //set language
    if( !isset($_SESSION['language']) )
        $_SESSION['language']='en';
    
    //creating CRSF token if not already set
    if( !isset($_SESSION['csrf_token']) )
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    //connect database
    include '../class/dbconnector1.php';
    include '../class/dbconnector2.php';

    //including mysql functions file
    include '../class/class.base.php';
    //include '../class/class.baseAKS.php';
    $base = new base();
    //$baseAKS = new baseAKS();

    //log errors
    ini_set("log_errors", 1);
    ini_set("error_log", "error.log");
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
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
        <style>
            #toast-container
            {
                margin-top: 8em;
            }
            #templates_form table td
            {
                padding-right: 3em;
            }
        </style>
    </head>
    <body class="position1">
        <!-- CSRF token -->
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

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
                        <a href="manageClients.php">Manage Clients</a>
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
                                    <h3 id="title_settings">CV Templates</h3>
                                </div>
                                <div class="box-content">
                                    <form id="templates_form">
                                        <!-- CSRF token -->
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"> 
                                        
                                        <!-- Flag -->
                                        <input type="hidden" name="flag" value="12">
                                        
                                        <table>                                         
                                            <tr>
                                                <td><label for="client_email">Client</label></td>
                                                <td>
                                                    <select name="client_email" id="client_email">
                                                        <option value disabled selected>Select client</option>
                                                        <option value disabled >----------------------------------------------</option>
                                                        <?php 
                                                            $clients = $base->select('clients', '*', null, 0, 1);
                                                            for($i=0; $i<$base->getRowCount($conn2, 'clients'); $i++)
                                                            {
                                                                ?>
                                                                <option value="<?php echo $base->dec($clients[$i]->c_email); ?>" title="<?php echo $base->dec($clients[$i]->c_contactName); ?>"><?php echo $base->dec($clients[$i]->c_contactName).' ('. $base->dec($clients[$i]->c_email).')'; ?></option>
                                                                <?php
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td><label for="medgivande_sample">CV Template (Swedish) [.doc]</label></td>
                                                <td><input type="file" name="newCVT1" id="newCVT1" /></td>
                                            </tr>

                                            <tr>
                                                <td><label for="medgivande_sample">CV Template (Swedish) [.pdf]</label></td>
                                                <td><input type="file" name="newCVT2" id="newCVT2"/></td>
                                            </tr>

                                            <tr>
                                                <td><label for="medgivande_sample">CV Template (English) [.doc]</label></td>
                                                <td><input type="file" name="newCVT3" id="newCVT3"/></td>
                                            </tr>

                                            <tr>
                                                <td><label for="medgivande_sample">CV Template (English) [.pdf]</label></td>
                                                <td><input type="file" name="newCVT4" id="newCVT4" /></td>
                                            </tr>

                                            <tr>
                                                <td><button class="btn btn-red5" disabled>Update Templates</button></td>
                                            </tr>                                                   
                                        </table>
                                    </form>                                 
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row-fluid">
                        <div class="span6">
                            <div class="box">
                                <div class="box-head">
                                    <h3>Client List</h3>
                                </div>
                                <div class="box-content box-nomargin">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th><strong id="l_company_name">Company Name</strong></th>
                                                <th><strong id="l_contact_name">Contact Name</strong></th>
                                                <th><strong id="l_email">Email</strong></th>
                                                <th><strong id="l_contact_number">Contact Number</strong></th>
                                                <th><strong id="th_orders">Orders</strong></th>
                                                <th><strong id="th_actions">Actions</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                //fetch all the clients now
                                                try
                                                {
                                                    $s = $conn2->query("SELECT * FROM clients");
                                                }
                                                catch(PDOException $e)
                                                {
                                                    $e->getMessage();
                                                }

                                                while($clients = $s->fetch(PDO::FETCH_OBJ))
                                                {
                                                    ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($base->dec($clients->c_companyName)); ?></td>
                                                            <td><?php echo ($base->dec($clients->c_contactName)); ?></td>
                                                            <td><?php echo htmlspecialchars($base->dec($clients->c_email)); ?></td>
                                                            <td><?php echo htmlspecialchars($base->dec($clients->c_contactNumber)); ?></td>
                                                            <td><?php echo $base->getRowCount($conn2, 'orders', array('c_clientID'=>$base->enc($clients->c_clientID))); ?></td>       
                                                            <td>                                                            
                                                                <a class="removeClient" id="<?php echo htmlspecialchars($base->dec($clients->c_clientID)); ?>">Remove Client</a>
                                                            </td>
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
        <script src="../theme/js/toastr.min.js"></script>
        <script src="../theme/js/main.js"></script>
    </body>
    <script>
        $('#li_clients').addClass('active');
        $('#collapsed_nav_clients').css('display', 'block');
    </script>
</html>