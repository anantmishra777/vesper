<div class="navi">
    <ul class='main-nav'>
        <li id="li_dashboard">
            <a href="../adminPanel" class='light'>
                <div class="ico"><i class="icon-home icon-white"></i></div>
                Översiktsvy
            </a>
        </li>

        <li id="li_clients">
            <a href="#" class='light toggle-collapsed'>
                <div class="ico"><i class="icon-th-large icon-white"></i></div>
                Client Management
                <img src="../theme/img/toggle-subnav-down.png" alt="">
            </a>
            <ul class='collapsed-nav closed' id="collapsed_nav_clients">
                <li>
                    <a href="addClient.php">
                        Add New Client
                    </a>
                </li>
                <li>
                    <a href="manageClients.php">
                        Manage Clients
                    </a>
                </li>
            </ul>
        </li>

        <li id="li_orders">
            <a href="#" class='light toggle-collapsed'>
                <div class="ico"><i class="icon-tasks icon-white"></i></div>
                Order Management
                <img src="../theme/img/toggle-subnav-down.png" alt="">
            </a>
            <ul class='collapsed-nav closed' id="collapsed_nav_orders">
                <li>
                    <a href="orders.php">
                        All Orders
                    </a>
                </li>
                <li>
                    <a href="orders.php?obstatus=1">
                        Pending Orders
                    </a>
                </li>
                <li>
                    <a href="orders.php?obstatus=2">
                        Orders In Progress
                    </a>
                </li>
                <li>
                    <a href="orders.php?obstatus=3">
                        Completed Orders
                    </a>
                </li>

            </ul>
        </li>

        <li id="li_adminSettings">
            <a href="adminSettings.php" class='light'>
                <div class="ico"><img style="height: 25px; margin-top: 0 !important;" src="../theme/img/icons/fugue/gear.png"/></div>
                Inställningar
            </a>
        </li>

        <li id="li_security">
            <a href="security.php" class='light'>
                <div class="ico"></div>
                Säkerhet
            </a>
        </li>
    </ul>
</div>