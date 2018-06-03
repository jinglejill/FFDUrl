<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["rewardProgramID"]) && isset($_POST["type"]) && isset($_POST["startDate"]) && isset($_POST["endDate"]) && isset($_POST["salesSpent"]) && isset($_POST["receivePoint"]) && isset($_POST["pointSpent"]) && isset($_POST["discountType"]) && isset($_POST["discountAmount"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $rewardProgramID = $_POST["rewardProgramID"];
        $type = $_POST["type"];
        $startDate = $_POST["startDate"];
        $endDate = $_POST["endDate"];
        $salesSpent = $_POST["salesSpent"];
        $receivePoint = $_POST["receivePoint"];
        $pointSpent = $_POST["pointSpent"];
        $discountType = $_POST["discountType"];
        $discountAmount = $_POST["discountAmount"];
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
    $sql = "INSERT INTO RewardProgram(Type, StartDate, EndDate, SalesSpent, ReceivePoint, PointSpent, DiscountType, DiscountAmount, ModifiedUser, ModifiedDate) VALUES ('$type', '$startDate', '$endDate', '$salesSpent', '$receivePoint', '$pointSpent', '$discountType', '$discountAmount', '$modifiedUser', '$modifiedDate')";
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
    $sql = "select $rewardProgramID as RewardProgramID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'RewardProgram';
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
    $rewardProgramID = $newID;
    $sql = "select *, 1 IdInserted from RewardProgram where RewardProgramID = '$rewardProgramID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'RewardProgram';
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
    $sql = "select *, 1 IdInserted from RewardProgram where RewardProgramID = '$rewardProgramID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'RewardProgram';
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
    //delete and insert ตัวเอง, insert คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
