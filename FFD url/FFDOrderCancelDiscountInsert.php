<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["orderCancelDiscountID"]) && isset($_POST["orderTakingID"]) && isset($_POST["type"]) && isset($_POST["discountType"]) && isset($_POST["discountAmount"]) && isset($_POST["reason"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $orderCancelDiscountID = $_POST["orderCancelDiscountID"];
        $orderTakingID = $_POST["orderTakingID"];
        $type = $_POST["type"];
        $discountType = $_POST["discountType"];
        $discountAmount = $_POST["discountAmount"];
        $reason = $_POST["reason"];
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
    $sql = "INSERT INTO OrderCancelDiscount(OrderTakingID, Type, DiscountType, DiscountAmount, Reason, ModifiedUser, ModifiedDate) VALUES ('$orderTakingID', '$type', '$discountType', '$discountAmount', '$reason', '$modifiedUser', '$modifiedDate')";
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
    $sql = "select $orderCancelDiscountID as OrderCancelDiscountID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'OrderCancelDiscount';
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
    $orderCancelDiscountID = $newID;
    $sql = "select *, 1 IdInserted from OrderCancelDiscount where OrderCancelDiscountID = '$orderCancelDiscountID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'OrderCancelDiscount';
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
    $sql = "select *, 1 IdInserted from OrderCancelDiscount where OrderCancelDiscountID = '$orderCancelDiscountID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'OrderCancelDiscount';
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
