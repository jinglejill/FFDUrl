<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countMenu"]))
    {
        $countMenu = $_POST["countMenu"];
        for($i=0; $i<$countMenu; $i++)
        {
            $menuID[$i] = $_POST["menuID".sprintf("%02d", $i)];
            $menuCode[$i] = $_POST["menuCode".sprintf("%02d", $i)];
            $titleThai[$i] = $_POST["titleThai".sprintf("%02d", $i)];
            $price[$i] = $_POST["price".sprintf("%02d", $i)];
            $menuTypeID[$i] = $_POST["menuTypeID".sprintf("%02d", $i)];
            $subMenuTypeID[$i] = $_POST["subMenuTypeID".sprintf("%02d", $i)];
            $subMenuType2ID[$i] = $_POST["subMenuType2ID".sprintf("%02d", $i)];
            $subMenuType3ID[$i] = $_POST["subMenuType3ID".sprintf("%02d", $i)];
            $imageUrl[$i] = $_POST["imageUrl".sprintf("%02d", $i)];
            $color[$i] = $_POST["color".sprintf("%02d", $i)];
            $orderNo[$i] = $_POST["orderNo".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
            $remark[$i] = $_POST["remark".sprintf("%02d", $i)];
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
    
    
    
    if($countMenu > 0)
    {
        for($i=0; $i<$countMenu; $i++)
        {
            //query statement
            $sql = "update Menu set MenuCode = '$menuCode[$i]', TitleThai = '$titleThai[$i]', Price = '$price[$i]', MenuTypeID = '$menuTypeID[$i]', SubMenuTypeID = '$subMenuTypeID[$i]', SubMenuType2ID = '$subMenuType2ID[$i]', SubMenuType3ID = '$subMenuType3ID[$i]', ImageUrl = '$imageUrl[$i]', Color = '$color[$i]', OrderNo = '$orderNo[$i]', Status = '$status[$i]', Remark = '$remark[$i]', ModifiedUser = '$modifiedUser[$i]', ModifiedDate = '$modifiedDate[$i]' where MenuID = '$menuID[$i]'";
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
        $sql = "select *, 1 IdInserted from Menu where MenuID in ('$menuID[0]'";
        for($i=1; $i<$countMenu; $i++)
        {
            $sql .= ",'$menuID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'Menu';
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
