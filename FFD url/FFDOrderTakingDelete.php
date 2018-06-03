<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["orderTakingID"]) && isset($_POST["customerTableID"]) && isset($_POST["menuID"]) && isset($_POST["quantity"]) && isset($_POST["specialPrice"]) && isset($_POST["price"]) && isset($_POST["takeAway"]) && isset($_POST["noteIDListInText"]) && isset($_POST["orderNo"]) && isset($_POST["status"]) && isset($_POST["receiptID"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $orderTakingID = $_POST["orderTakingID"];
        $customerTableID = $_POST["customerTableID"];
        $menuID = $_POST["menuID"];
        $quantity = $_POST["quantity"];
        $specialPrice = $_POST["specialPrice"];
        $price = $_POST["price"];
        $takeAway = $_POST["takeAway"];
        $noteIDListInText = $_POST["noteIDListInText"];
        $orderNo = $_POST["orderNo"];
        $status = $_POST["status"];
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
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select * from OrderTaking where OrderTakingID = '$orderTakingID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //query statement
    $sql = "delete from OrderTaking where OrderTakingID = '$orderTakingID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //broadcast ไป device token อื่น
    $type = 'OrderTaking';
    $action = 'd';
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
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
