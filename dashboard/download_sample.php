<?php
    session_start();

    //include config file
    include '../class/config.php';

    //including mysql functions file
    include '../class/class.base.php';
    $base = new base();

    if( htmlspecialchars($_GET['url']) )
    {
        //logging download report activity
        $insert_log = array('l_user' => $_SESSION['email'],
                            'l_logDescription' => 'File downloaded by '.$_SESSION['email'].PHP_EOL.'(File URL: '.htmlspecialchars($_GET['url']).')',
                            'l_logType' => 'download_file',
                            'l_accessLevel' => 0,
                            'l_sourceFile' => BASE_URL.'/dashboard/myOrders.php',
                            'l_dateTime' => date('d-M-Y H:i:s'));
        $base->insert('logs', $insert_log);


        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: inline; filename=\"" . basename(htmlspecialchars($_GET['url'])) . "\""); 
        readfile(htmlspecialchars($_GET['url']));
    }
    else
        header('Location:index.php');
?>