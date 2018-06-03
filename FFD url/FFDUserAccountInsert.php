<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    
    if (isset ($_POST["userAccountID"]) && isset ($_POST["username"]) && isset ($_POST["password"]) && isset ($_POST["deviceToken"]) && isset ($_POST["fullName"]) && isset ($_POST["nickName"]) && isset ($_POST["email"]) && isset ($_POST["phoneNo"]) && isset ($_POST["lineID"]) && isset ($_POST["modifiedUser"]) && isset ($_POST["modifiedDate"]))
    {
        $userAccountID = $_POST["userAccountID"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $deviceToken = $_POST["deviceToken"];
        $fullName = $_POST["fullName"];
        $nickName = $_POST["nickName"];
        $email = $_POST["email"];
        $phoneNo = $_POST["phoneNo"];
        $lineID = $_POST["lineID"];
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
    
    
    //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
    //query statement
    for($j=0;$j<$retryNo;$j++)
    {
        $sql = "INSERT INTO `useraccount`(`UserAccountID`, `Username`, `Password`, `DeviceToken`, `FullName`, `NickName`, `Email`, `PhoneNo`, `LineID`, `ModifiedUser`, `ModifiedDate`) VALUES ('" . ($userAccountID+$j) . "','$username','$password','$deviceToken','$fullName','$nickName','$email','$phoneNo','$lineID','$modifiedUser','$modifiedDate')";
        $ret = doQueryTask($sql);
        if($ret == "")
        {
            //insert ผ่าน
            break;
        }
    }
    
    
    if($j==$retryNo)
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    else if($j != 0)
    {
        //มีการเปลีี่ยน id
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select $userAccountID as UserAccountID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'UserAccount';
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
        $userAccountID = $userAccountID+$j;
        $sql = "select *, 1 IdInserted from UserAccount where `UserAccountID` = $userAccountID";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'UserAccount';
        $action = 'i';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    else if($j == 0)
    {
        //update IdInserted
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from UserAccount where `UserAccountID` = $userAccountID";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'UserAccount';
        $action = 'u';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from UserAccount where UserAccountID = '$userAccountID'";
    $selectedRow = getSelectedRow($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'UserAccount';
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
    //update ตัวเอง สำหรับกรณี insert duplicate และ update IdInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " .  $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
