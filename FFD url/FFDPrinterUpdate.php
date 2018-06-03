<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["printerID"]) && isset($_POST["code"]) && isset($_POST["name"]) && isset($_POST["menuTypeIDListInText"]) && isset($_POST["model"]) && isset($_POST["portName"]) && isset($_POST["macAddress"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $printerID = $_POST["printerID"];
        $code = $_POST["code"];
        $name = $_POST["name"];
        $menuTypeIDListInText = $_POST["menuTypeIDListInText"];
        $model = $_POST["model"];
        $portName = $_POST["portName"];
        $macAddress = $_POST["macAddress"];
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
    $sql = "update Printer set Code = '$code', Name = '$name', MenuTypeIDListInText = '$menuTypeIDListInText', Model = '$model', PortName = '$portName', MacAddress = '$macAddress', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where PrinterID = '$printerID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from Printer where PrinterID = '$printerID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'Printer';
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
