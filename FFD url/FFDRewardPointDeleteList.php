<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countRewardPoint"]))
    {
        $countRewardPoint = $_POST["countRewardPoint"];
        for($i=0; $i<$countRewardPoint; $i++)
        {
            $rewardPointID[$i] = $_POST["rewardPointID".sprintf("%02d", $i)];
            $memberID[$i] = $_POST["memberID".sprintf("%02d", $i)];
            $receiptID[$i] = $_POST["receiptID".sprintf("%02d", $i)];
            $point[$i] = $_POST["point".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
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
    
    
    
    if($countRewardPoint > 0)
    {
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from RewardPoint where RewardPointID in ('$rewardPointID[0]'";
        for($i=1; $i<$countRewardPoint; $i++)
        {
            $sql .= ",'$rewardPointID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        for($i=0; $i<$countRewardPoint; $i++)
        {
            //query statement
            $sql = "delete from RewardPoint where RewardPointID in ('$rewardPointID[0]'";
            for($i=1; $i<$countRewardPoint; $i++)
            {
                $sql .= ",'$rewardPointID[$i]'";
            }
            $sql .= ")";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
        //broadcast ไป device token อื่น
        $type = 'RewardPoint';
        $action = 'd';
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
