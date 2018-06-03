<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countAddress"]))
    {
        $countAddress = $_POST["countAddress"];
        for($i=0; $i<$countAddress; $i++)
        {
            $addressID[$i] = $_POST["addressID".sprintf("%02d", $i)];
            $memberID[$i] = $_POST["memberID".sprintf("%02d", $i)];
            $street[$i] = $_POST["street".sprintf("%02d", $i)];
            $postCode[$i] = $_POST["postCode".sprintf("%02d", $i)];
            $country[$i] = $_POST["country".sprintf("%02d", $i)];
            $keyAddressFlag[$i] = $_POST["keyAddressFlag".sprintf("%02d", $i)];
            $deliveryAddressFlag[$i] = $_POST["deliveryAddressFlag".sprintf("%02d", $i)];
            $taxAddressFlag[$i] = $_POST["taxAddressFlag".sprintf("%02d", $i)];
            $deliveryCustomerName[$i] = $_POST["deliveryCustomerName".sprintf("%02d", $i)];
            $deliveryPhoneNo[$i] = $_POST["deliveryPhoneNo".sprintf("%02d", $i)];
            $taxCustomerName[$i] = $_POST["taxCustomerName".sprintf("%02d", $i)];
            $taxPhoneNo[$i] = $_POST["taxPhoneNo".sprintf("%02d", $i)];
            $taxID[$i] = $_POST["taxID".sprintf("%02d", $i)];
            $modifiedUser[$i] = $_POST["modifiedUser".sprintf("%02d", $i)];
            $modifiedDate[$i] = $_POST["modifiedDate".sprintf("%02d", $i)];
        }
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    if($countAddress > 0)
    {
        for($i=0; $i<$countAddress; $i++)
        {
            //query statement
            $sql = "update Address set MemberID = '$memberID[$i]', Street = '$street[$i]', PostCode = '$postCode[$i]', Country = '$country[$i]', KeyAddressFlag = '$keyAddressFlag[$i]', DeliveryAddressFlag = '$deliveryAddressFlag[$i]', TaxAddressFlag = '$taxAddressFlag[$i]', DeliveryCustomerName = '$deliveryCustomerName[$i]', DeliveryPhoneNo = '$deliveryPhoneNo[$i]', TaxCustomerName = '$taxCustomerName[$i]', TaxPhoneNo = '$taxPhoneNo[$i]', TaxID = '$taxID[$i]', ModifiedUser = '$modifiedUser[$i]', ModifiedDate = '$modifiedDate[$i]' where AddressID = '$addressID[$i]'";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from Address where AddressID in ('$addressID[0]'";
        for($i=1; $i<$countAddress; $i++)
        {
            $sql .= ",'$addressID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'Address';
        $action = 'u';
        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
    //do script successful
    //update ตัวเอง สำหรับกรณี insert duplicate และ update IdInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();

?>
