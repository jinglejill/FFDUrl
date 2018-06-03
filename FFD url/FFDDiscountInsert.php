<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["discountID"]) && isset($_POST["discountAmount"]) && isset($_POST["discountType"]) && isset($_POST["remark"]) && isset($_POST["orderNo"]) && isset($_POST["status"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $discountID = $_POST["discountID"];
        $discountAmount = $_POST["discountAmount"];
        $discountType = $_POST["discountType"];
        $remark = $_POST["remark"];
        $orderNo = $_POST["orderNo"];
        $status = $_POST["status"];
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
    $sql = "INSERT INTO Discount(DiscountAmount, DiscountType, Remark, OrderNo, Status, ModifiedUser, ModifiedDate) VALUES ('$discountAmount', '$discountType', '$remark', '$orderNo', '$status', '$modifiedUser', '$modifiedDate')";
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
    $sql = "select $discountID as DiscountID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'Discount';
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
    $discountID = $newID;
    $sql = "select *, 1 IdInserted from Discount where DiscountID = '$discountID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'Discount';
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
    $sql = "select *, 1 IdInserted from Discount where DiscountID = '$discountID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'Discount';
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
