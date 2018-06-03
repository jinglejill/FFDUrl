<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["addressID"]) && isset($_POST["memberID"]) && isset($_POST["street"]) && isset($_POST["postCode"]) && isset($_POST["country"]) && isset($_POST["keyAddressFlag"]) && isset($_POST["deliveryAddressFlag"]) && isset($_POST["taxAddressFlag"]) && isset($_POST["deliveryCustomerName"]) && isset($_POST["deliveryPhoneNo"]) && isset($_POST["taxCustomerName"]) && isset($_POST["taxPhoneNo"]) && isset($_POST["taxID"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $addressID = $_POST["addressID"];
        $memberID = $_POST["memberID"];
        $street = $_POST["street"];
        $postCode = $_POST["postCode"];
        $country = $_POST["country"];
        $keyAddressFlag = $_POST["keyAddressFlag"];
        $deliveryAddressFlag = $_POST["deliveryAddressFlag"];
        $taxAddressFlag = $_POST["taxAddressFlag"];
        $deliveryCustomerName = $_POST["deliveryCustomerName"];
        $deliveryPhoneNo = $_POST["deliveryPhoneNo"];
        $taxCustomerName = $_POST["taxCustomerName"];
        $taxPhoneNo = $_POST["taxPhoneNo"];
        $taxID = $_POST["taxID"];
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
        $sql = "INSERT INTO Address(AddressID, MemberID, Street, PostCode, Country, KeyAddressFlag, DeliveryAddressFlag, `TaxAddressFlag`, `DeliveryCustomerName`, `DeliveryPhoneNo`, `TaxCustomerName`, `TaxPhoneNo`, `TaxID`, ModifiedUser, ModifiedDate) VALUES ('" . ($addressID+$j) . "', '$memberID', '$street', '$postCode', '$country', '$keyAddressFlag', '$deliveryAddressFlag', '$taxAddressFlag', '$deliveryCustomerName', '$deliveryPhoneNo', '$taxCustomerName', '$taxPhoneNo', '$taxID', '$modifiedUser', '$modifiedDate')";
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
        //มีการเปลี่ยน id
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select $addressID as AddressID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'Address';
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
        $addressID = $addressID+$j;
        $sql = "select *, 1 IdInserted from Address where AddressID = '$addressID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'Address';
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
        $sql = "select *, 1 IdInserted from Address where AddressID = '$addressID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'Address';
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
    $sql = "select *, 1 IdInserted from Address where AddressID = '$addressID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'Address';
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
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
