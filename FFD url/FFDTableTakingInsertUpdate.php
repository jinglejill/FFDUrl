<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
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
    
    
    $sql = "select * from TableTaking where CustomerTableID = '$customerTableID' and ReceiptID = '$receiptID'";
    $selectedRow = getSelectedRow($sql);
    if(sizeof($selectedRow)>0)
    {
        //update
        //query statement
        $sql = "update TableTaking set CustomerTableID = '$customerTableID', ServingPerson = '$servingPerson', ReceiptID = '$receiptID', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where CustomerTableID = '$customerTableID' and ReceiptID = '$receiptID'";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from tabletaking where CustomerTableID = '$customerTableID' and ReceiptID = '$receiptID'";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'TableTaking';
        $action = 'u';
        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    else
    {
        //insert
        //query statement
        $sql = "INSERT INTO TableTaking(CustomerTableID, ServingPerson, ReceiptID, ModifiedUser, ModifiedDate) VALUES ('$customerTableID', '$servingPerson', '$receiptID', '$modifiedUser', '$modifiedDate')";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        //insert ผ่าน
        $newID = mysqli_insert_id($con);
        
        
        
        //device ตัวเอง ลบแล้ว insert
        //sync generated id back to app
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select $tableTakingID as TableTakingID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'TableTaking';
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
        $tableTakingID = $newID;
        $sql = "select *, 1 IdInserted from TableTaking where `TableTakingID` = '$tableTakingID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'TableTaking';
        $action = 'i';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        
        //****device อื่น insert
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from TableTaking where `TableTakingID` = '$tableTakingID'";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'TableTaking';
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
    }
    
    
    
    
    
    
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
