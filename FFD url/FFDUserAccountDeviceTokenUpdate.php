<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    

    
    //ถ้า switch มาใช้เครื่องนี้ ให้ push notification ไปหา device อันก่อนหน้าว่า you have login in another device และ unwind to หน้า sign in

    if (isset ($_POST["userAccountID"]) && isset ($_POST["deviceToken"]))
    {
        $userAccountID = $_POST["userAccountID"];
        $deviceToken = $_POST["deviceToken"];
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
    
    
    $sql = "select * from `UserAccount` where `userAccountID` = '$userAccountID'";
    $selectedRow = getSelectedRow($sql);
    $deviceTokenOld = $selectedRow[0]["DeviceToken"];
    
    
    if($deviceTokenOld != $deviceToken)
    {
        //query statement
        $sql = "update `UserAccount` set `DeviceToken` = '$deviceToken',`ModifiedUser` = '$modifiedUser',`ModifiedDate` = '$modifiedDate'  where `userAccountID` = '$userAccountID'";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from UserAccount where userAccountID = '$userAccountID'";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'UserAccount';
        $action = 'u';
        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        //-----
        
        
        
        if($deviceTokenOld != "")
        {
            //push to only old token device
            $type = 'usernameconflict';
            $action = '-';
            $data = '-';
            $ret = doPushNotificationTaskToDevice($deviceTokenOld,$selectedRow,$type,$action);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
    }
    
    
    
    
    
    //do script successful
    //update old device token, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " .  $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();

?>
