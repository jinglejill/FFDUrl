<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    

    if(isset($_POST["branchID"]))
    {
        $branchID = $_POST["branchID"];
    }
    if(isset($_POST["disputeID"]) && isset($_POST["receiptID"]) && isset($_POST["disputeReasonID"]) && isset($_POST["refundAmount"]) && isset($_POST["detail"]) && isset($_POST["phoneNo"]) && isset($_POST["type"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $disputeID = $_POST["disputeID"];
        $receiptID = $_POST["receiptID"];
        $disputeReasonID = $_POST["disputeReasonID"];
        $refundAmount = $_POST["refundAmount"];
        $detail = $_POST["detail"];
        $phoneNo = $_POST["phoneNo"];
        $type = $_POST["type"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
    }
    else
    {
        $branchID = $_GET["branchID"];
        
        
        $disputeID = $_GET["disputeID"];
        $receiptID = $_GET["receiptID"];
        $disputeReasonID = '';
        $refundAmount = $_GET["refundAmount"];
        $detail = $_GET["detail"];
        $phoneNo = '';
        $type = '3';
        $modifiedUser = 'admin';
        $modifiedDate = date('Y-m-d H:i:s');
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    
    //dispute
    //query statement
    $sql = "INSERT INTO Dispute(ReceiptID, DisputeReasonID, RefundAmount, Detail, PhoneNo, Type, ModifiedUser, ModifiedDate) VALUES ('$receiptID', '$disputeReasonID', '$refundAmount', '$detail', '$phoneNo', '$type', '$modifiedUser', '$modifiedDate')";
    $ret = doQueryTask($sql);
    $disputeID = mysqli_insert_id($con);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    $status = $type == 1?7:8;
    
    //receipt
    $sql = "update receipt set status = '$status',statusRoute=concat(statusRoute,',','$status'), modifiedUser = '$modifiedUser', modifiedDate = '$modifiedDate' where receiptID = '$receiptID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    
    //get pushSync Device in ffd
    $sql = "select DbName,DeviceTokenReceiveOrder from FFD.branch where branchID = '$branchID'";
    $selectedRow = getSelectedRow($sql);
    $pushSyncDbName = $selectedRow[0]["DbName"];
    $pushSyncDeviceTokenReceiveOrder = $selectedRow[0]["DeviceTokenReceiveOrder"];
    
    
    
    
    //push receipt to ffd
    $sql = "select '$branchID' BranchID, Receipt.*, 1 IdInserted from Receipt where ReceiptID = '$receiptID'";
    $selectedRow = getSelectedRow($sql);
    $receiptList = $selectedRow;
    
    
    //broadcast ไป device token อื่น
    $type = 'Receipt';
    $action = 'i';
    $ret = doPushNotificationTaskWithDbName($pushSyncDeviceTokenReceiveOrder,$selectedRow,$type,$action,$pushSyncDbName);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    //push dispute to ffd
    $sql = "select *, 1 IdInserted from Dispute where DisputeID = '$disputeID'";
    $selectedRow = getSelectedRow($sql);
    $receiptList = $selectedRow;
    
    
    //broadcast ไป device token อื่น
    $type = 'Dispute';
    $action = 'i';
    $ret = doPushNotificationTaskWithDbName($pushSyncDeviceTokenReceiveOrder,$selectedRow,$type,$action,$pushSyncDbName);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    
    
    
    
    //do script successful
    mysqli_commit($con);
    $msg = $type == 1?"Order cancel request":"Open dispute request";
    sendPushNotificationToDeviceWithPath($pushSyncDeviceTokenReceiveOrder,'./../../FFD/MAMARIN5/','jill',$msg,$receiptID,'cancelOrder',0);
    
    
    
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
