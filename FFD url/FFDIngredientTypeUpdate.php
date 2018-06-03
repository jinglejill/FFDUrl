<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["ingredientTypeID"]) && isset($_POST["name"]) && isset($_POST["orderNo"]) && isset($_POST["status"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $ingredientTypeID = $_POST["ingredientTypeID"];
        $name = $_POST["name"];
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
    $sql = "update IngredientType set Name = '$name', OrderNo = '$orderNo', Status = '$status', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where IngredientTypeID = '$ingredientTypeID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from IngredientType where IngredientTypeID = '$ingredientTypeID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'IngredientType';
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
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
