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
        $insert_log = array('l_user' => $base->enc($_SESSION['admin_email']),
                            'l_logDescription' => $base->enc('File downloaded by '.$_SESSION['admin_email'].PHP_EOL.'(File URL: '.htmlspecialchars($_GET['url']).')'),
                            'l_logType' => $base->enc('download_file'),
                            'l_accessLevel' => $base->enc('1'),
                            'l_sourceFile' => $base->enc(BASE_URL.'/dashboard/view.php'),
                            'l_dateTime' => $base->enc(date('d-M-Y H:i:s')));
        $base->insert('logs', $insert_log);

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"" . basename(htmlspecialchars($_GET['url'])) . "\""); 
        readfile(htmlspecialchars($_GET['url']));
    }
    else
        header('Location:index.php');     
    exit;
?>