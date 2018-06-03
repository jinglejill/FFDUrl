<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["specialPriceProgramID"]) && isset($_POST["menuID"]) && isset($_POST["startDate"]) && isset($_POST["endDate"]) && isset($_POST["specialPrice"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $specialPriceProgramID = $_POST["specialPriceProgramID"];
        $menuID = $_POST["menuID"];
        $startDate = $_POST["startDate"];
        $endDate = $_POST["endDate"];
        $specialPrice = $_POST["specialPrice"];
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
    $sql = "select * from SpecialPriceProgram where SpecialPriceProgramID = '$specialPriceProgramID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //query statement
    $sql = "delete from SpecialPriceProgram where SpecialPriceProgramID = '$specialPriceProgramID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //broadcast ไป device token อื่น
    $type = 'SpecialPriceProgram';
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
