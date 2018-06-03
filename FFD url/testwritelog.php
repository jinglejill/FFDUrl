<?php    
    include_once("dbConnect.php");
    setConnectionValue('MAMARIN5');
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    
    if(isset($_POST["tableTakingID"]) && isset($_POST["customerTableID"]) && isset($_POST["servingPerson"]) && isset($_POST["receiptID"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $tableTakingID = $_POST["tableTakingID"];
        $customerTableID = $_POST["customerTableID"];
        $servingPerson = $_POST["servingPerson"];
        $receiptID = $_POST["receiptID"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    //query statement
    $sql = "update TableTaking set CustomerTableID = '$customerTableID', ServingPerson = '$servingPerson', ReceiptID = '$receiptID', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where TableTakingID = '$tableTakingID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from tabletaking where TableTakingID = '$tableTakingID'";
    $selectedRow = getSelectedRow($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'TableTaking';
    $action = 'u';
    $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " .  $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
