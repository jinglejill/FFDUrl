<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    if (isset ($_POST["stackTrace"]))
    {
        $stackTrace = $_POST["stackTrace"];
    }
    else
    {
        $stackTrace = "-";
    }
    
    writeToLog("fail with exception: " . $stackTrace);
    $response = array('status' => '1', 'sql' => "");
    
    
    echo json_encode($response);
    exit();
?>
