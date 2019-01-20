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
    $base = new base();

    //log errors
    ini_set("log_errors", 1);
    ini_set("error_log", "error.log");

    function getRowCountSingle($tableName)
    {
        global $conn2;
        try
        {
            $s = $conn2->query("SELECT * FROM $tableName");
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
        return $s->rowCount();
    }//function
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
        <link href="cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
        <style>
            #recent_orders_table_paginate, #orders_in_progress_table_paginate
            {
                cursor: pointer;
            }

            #recent_orders_table_paginate span a, #recent_orders_table_first, #recent_orders_table_previous, #recent_orders_table_next, #recent_orders_table_last, #orders_in_progress_table_paginate span a, #orders_in_progress_table_first, #orders_in_progress_table_previous, #orders_in_progress_table_next, #orders_in_progress_table_last
            {
                padding-right: 5px;
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
                        <a href="index.php">Översiktsvy</a>
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
                    <div class="row-fluid no-margin">
                        <div class="span12">
                            <ul class="quickstats">
                                <li>                                    
                                    <div class="chart-detail">
                                        <span class="amount"><?php echo htmlspecialchars($base->getRowCount($conn2, "orders", array("o_orderStatus"=>1))); ?></span>
                                        <span class="description" id="new_orders">New Orders</span>
                                    </div>
                                </li>

                                <li>                                    
                                    <div class="chart-detail">
                                         <span class="amount"><?php echo htmlspecialchars($base->getRowCount($conn2, "orders", array("o_orderStatus"=>2))); ?></span>
                                        <span class="description" id="orders_progress">Orders in Progress</span>
                                    </div>
                                </li>

                                <li>                                    
                                    <div class="chart-detail">
                                        <span class="amount"><?php echo htmlspecialchars(getRowCountSingle("clients")); ?></span>
                                        <span class="description" id="total_clients">Total Clients</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row-fluid no-margin">
                        <div class="span12">
                            <ul class="quicktasks">
                                <li>
                                    <a href="addClient.php">
                                        <img src="../theme/img/icons/essen/32/hire-me.png" alt="">
                                        <span id="add_new_client">Add New Client</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="manageClients.php">
                                        <img src="../theme/img/icons/essen/32/statistics.png" alt="">
                                        <span id="manage_clients">Manage Clients</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="orders.php">
                                        <img src="../theme/img/icons/essen/32/shipping.png" alt="">
                                        <span id="all_orders">All Orders</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </div>

                    <div class="row-fluid">
                        <div class="span6">
                            <div class="box">
                                <div class="box-head">
                                    <h3 id="h_recent_orders">Recent Orders</h3>
                                </div>
                                <div class="box-content box-nomargin">
                                    <table class="table table-striped table-bordered" id="recent_orders_table">
                                        <thead>
                                            <tr>
                                                <th><strong id="th_orderID">Order ID</strong></th>
                                                <th><strong>Level</strong></th>
                                                <th><strong id="th_client_company">Client Company Name</strong></th>
                                                <th><strong>Name</strong></th>
                                                <th><strong id="th_client_email">Client Email</strong></th>
                                                <th><strong id="th_client_status">Current Status</strong></th>
                                                <th><strong id="th_order_date">Order Date</strong></th>
                                                <th><strong>Kostnadsställe</strong></th>
                                                <th><strong id="th_followUp_date">Follow-up Date</strong></th>
                                                <th><strong id="th_actions">Actions</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                //fetch all the clients now
                                                $o_orderStatus = $base->enc('1');
                                                try
                                                {
                                                    $s = $conn2->prepare("SELECT o.*,c.* FROM orders o, clients c
                                                                        WHERE o.c_clientID = c.c_clientID AND o.o_orderStatus=:o_orderStatus ORDER BY o_indexid DESC");
                                                    $s->bindParam(':o_orderStatus', $o_orderStatus);
                                                    $s->execute();
                                                }
                                                catch(PDOException $e)
                                                {
                                                    $e->getMessage();
                                                }

                                                while($orders = $s->fetch(PDO::FETCH_OBJ))
                                                {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($base->dec($orders->o_orderID)); ?></td>
                                                        <td><?php echo htmlspecialchars($base->dec($orders->o_formLevel)); ?></td>
                                                        <td><?php echo htmlspecialchars($base->dec($orders->c_companyName)); ?></td>
                                                        <td><?php echo htmlspecialchars($base->dec($orders->o_name)); ?></td>
                                                        <td><?php echo htmlspecialchars($base->dec($orders->c_email)); ?></td>
                                                        <td>
                                                            <?php
                                                                switch($base->dec($orders->o_orderStatus))
                                                                {
                                                                    case 1:
                                                                        echo '<span class="label label-info">Pending</span>';
                                                                        break;
                                                                    case 2:
                                                                        echo '<span class="label label-warning">In Progress</span>';
                                                                        break;
                                                                    case 3:
                                                                        echo '<span class="label label-success">Completed</span>';
                                                                        break;
                                                                }
                                                            ?>                                                                
                                                        </td>
                                                        <td><?php $exp =  explode(" ", $base->dec($orders->o_orderDateTime)); echo htmlspecialchars($exp[0]); ?></td>
                                                        <td><?php echo htmlspecialchars($base->dec($orders->o_invoice)); ?></td>
                                                        <td><?php echo htmlspecialchars($base->dec($orders->o_followUp_date)); ?></td>                                                       
                                                        <td>
                                                            <a href="view.php?orderID=<?php echo htmlspecialchars($base->dec($orders->o_orderID)); ?>" class="btn btn-success" id='browse_order'
                                                                >Browse Order</a>
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

                    <div class="row-fluid">
                        <div class="span6">
                            <div class="box">
                                <div class="box-head">
                                    <h3 id="orders_progress">Orders In Progress</h3>
                                </div>
                                <div class="box-content box-nomargin">
                                    <table class="table table-striped table-bordered" id="orders_in_progress_table">
                                        <thead>
                                        <tr>
                                            <th><strong id="th_orderID">Order ID</strong></th>
                                            <th><strong>Level</strong></th>
                                            <th><strong id="th_client_company">Client Company Name</strong></th>
                                            <th><strong>Name</strong></th>
                                            <th><strong id="th_client_email">Client Email</strong></th>
                                            <th><strong id="th_client_status">Current Status</strong></th>
                                            <th><strong id="th_order_date">Order Date</strong></th>
                                            <th><strong>Kostnadsställe</strong></th>
                                            <th><strong id="th_followUp_date">Follow-up Date</strong></th>
                                            <th><strong id="th_actions">Actions</strong></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        //fetch all the clients now
                                        $o_orderStatus = $base->enc('2');
                                        try
                                        {
                                            $s = $conn2->prepare("SELECT o.*,c.* FROM orders o, clients c 
                                                                  WHERE o.c_clientID = c.c_clientID AND o.o_orderStatus=:o_orderStatus ORDER BY o_indexid DESC");
                                            $s->bindParam(':o_orderStatus', $o_orderStatus);
                                            $s->execute();
                                        }
                                        catch(PDOException $e)
                                        {
                                            $e->getMessage();
                                        }
                                        while($orders = $s->fetch(PDO::FETCH_OBJ))
                                        {
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($base->dec($orders->o_orderID)); ?></td>
                                                <td><?php echo htmlspecialchars($base->dec($orders->o_formLevel)); ?></td>
                                                <td><?php echo htmlspecialchars($base->dec($orders->o_name)); ?></td>
                                                <td><?php echo htmlspecialchars($base->dec($orders->c_companyName)); ?></td>
                                                <td><?php echo htmlspecialchars($base->dec($orders->c_email)); ?></td>
                                                <td>
                                                    <?php
                                                        switch($base->dec($orders->o_orderStatus))
                                                        {
                                                            case 1:
                                                                echo '<span class="label label-info">Pending</span>';
                                                                break;
                                                            case 2:
                                                                echo '<span class="label label-warning">In Progress</span>';
                                                                break;
                                                            case 3:
                                                                echo '<span class="label label-success">Completed</span>';
                                                                break;
                                                        }
                                                    ?>
                                                </td>
                                                <td><?php $exp =  explode(" ", $base->dec($orders->o_orderDateTime)); echo htmlspecialchars($exp[0]); ?></td>
                                                <td><?php echo htmlspecialchars($base->dec($orders->o_invoice)); ?></td>
                                                <td><?php echo htmlspecialchars($base->dec($orders->o_followUp_date)); ?></td>
                                                <td>
                                                    <a href="view.php?orderID=<?php echo htmlspecialchars($base->dec($orders->o_orderID)); ?>" class="btn btn-success" id='browse_order'>Browse Order</a>
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
        <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
        <script src="../theme/js/main.js"></script>
    </body>
    <script>
        $('#li_dashboard').addClass('active');
        $(document).ready(function() 
        {
            $('#orders_in_progress_table').DataTable({"aaSorting": [], "language": {"search": "Sök:", "lengthMenu": "Visa _MENU_"}, "pagingType": "full_numbers"});
            $('#recent_orders_table').DataTable({"aaSorting": [], "language": {"search": "Sök:", "lengthMenu": "Visa _MENU_"}, "pagingType": "full_numbers"});      
        });
    </script>
</html>