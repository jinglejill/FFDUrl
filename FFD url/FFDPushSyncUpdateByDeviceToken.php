<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    if (isset ($_POST["deviceToken"])
        )
    {
        $deviceToken = $_POST["deviceToken"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    //ไม่ต้อง push notification เพราะใน app ไม่มีเรียกใช้ column ที่ update นี้
    $sql = "UPDATE `PushSync` SET `TimeSynced` = now() WHERE DeviceToken = '$deviceToken' and TimeSynced = '0000-00-00 00:00:00'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    mysqli_close($con);
    writeToLog("eof: " . basename(__FILE__) . ", user: " .  $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
