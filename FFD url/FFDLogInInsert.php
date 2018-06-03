<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    if (isset ($_POST["logInID"]) &&
        isset ($_POST["username"]) &&
        isset ($_POST["status"]) &&
        isset ($_POST["deviceToken"]) &&
        isset ($_POST["modifiedUser"]) &&
        isset ($_POST["modifiedDate"]))
    {
        $logInID = $_POST["logInID"];
        $username = $_POST["username"];
        $status = $_POST["status"];
        $deviceToken = $_POST["deviceToken"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
    }

    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
//    writeToLog('test modifiedDate : ' . $modifiedDate);
//    $modifiedDate->add(new DateInterval('PT1S')); // adds 674165 secs
////    echo $date->getTimestamp();
//    writeToLog('test modifiedDate new : ' . $modifiedDate);
//    
//    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
    //query statement
    for($j=0;$j<$retryNo;$j++)
    {
        $sql = "INSERT INTO `login`(`LogInID`, `Username`, `Status`, `DeviceToken`, `ModifiedUser`, `ModifiedDate`) VALUES ('" . ($logInID+$j) . "','$username','$status','$deviceToken','$modifiedUser','$modifiedDate')";
        writeToLog($sql);
        $ret = doQueryTask($sql);
        if($ret == "")
        {
            //insert ผ่าน
            break;
        }
    }
    
    
    if($j==$retryNo)
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    else if($j != 0)
    {
        //มีการเปลีี่ยน id
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select $logInID as LogInID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'LogIn';
        $action = 'd';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $logInID = $logInID+$j;
        $sql = "select *, 1 IdInserted from LogIn where `LogInID` = '$logInID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'LogIn';
        $action = 'i';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    else if($j == 0)
    {
        //update IdInserted
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from LogIn where `LogInID` = '$logInID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'LogIn';
        $action = 'u';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from LogIn where `LogInID` = '$logInID'";
    $selectedRow = getSelectedRow($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'LogIn';
    $action = 'i';
    $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    //do script successful
    //update ตัวเอง สำหรับกรณี insert duplicate และ update IdInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " .  $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();

?>
