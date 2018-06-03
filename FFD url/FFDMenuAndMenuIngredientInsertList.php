<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["menuID"]) && isset($_POST["menuCode"]) && isset($_POST["titleThai"]) && isset($_POST["price"]) && isset($_POST["menuTypeID"]) && isset($_POST["subMenuTypeID"]) && isset($_POST["subMenuType2ID"]) && isset($_POST["subMenuType3ID"]) && isset($_POST["imageUrl"]) && isset($_POST["color"]) && isset($_POST["orderNo"]) && isset($_POST["status"]) && isset($_POST["remark"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $menuID = $_POST["menuID"];
        $menuCode = $_POST["menuCode"];
        $titleThai = $_POST["titleThai"];
        $price = $_POST["price"];
        $menuTypeID = $_POST["menuTypeID"];
        $subMenuTypeID = $_POST["subMenuTypeID"];
        $subMenuType2ID = $_POST["subMenuType2ID"];
        $subMenuType3ID = $_POST["subMenuType3ID"];
        $imageUrl = $_POST["imageUrl"];
        $color = $_POST["color"];
        $orderNo = $_POST["orderNo"];
        $status = $_POST["status"];
        $remark = $_POST["remark"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
    }
    
    if (isset($_POST["countMiMenuIngredient"]))
    {
        $countMiMenuIngredient = $_POST["countMiMenuIngredient"];
        for($i=0; $i<$countMiMenuIngredient; $i++)
        {
            $miMenuIngredientID[$i] = $_POST["miMenuIngredientID".sprintf("%02d", $i)];
            $miMenuID[$i] = $_POST["miMenuID".sprintf("%02d", $i)];
            $miIngredientID[$i] = $_POST["miIngredientID".sprintf("%02d", $i)];
            $miAmount[$i] = $_POST["miAmount".sprintf("%02d", $i)];
            $miModifiedUser[$i] = $_POST["miModifiedUser".sprintf("%02d", $i)];
            $miModifiedDate[$i] = $_POST["miModifiedDate".sprintf("%02d", $i)];
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
    
    
    
    //query statement
    $sql = "INSERT INTO Menu(MenuCode, TitleThai, Price, MenuTypeID, SubMenuTypeID, SubMenuType2ID, SubMenuType3ID,ImageUrl, Color, OrderNo, Status, Remark, ModifiedUser, ModifiedDate) VALUES ('$menuCode', '$titleThai', '$price', '$menuTypeID', '$subMenuTypeID', '$subMenuType2ID', '$subMenuType3ID', '$imageUrl', '$color', '$orderNo', '$status', '$remark', '$modifiedUser', '$modifiedDate')";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //insert ผ่าน
    $newID = mysqli_insert_id($con);
    
    
    
    //device ตัวเอง ลบแล้ว insert
    //sync generated id back to app
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select $menuID as MenuID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'Menu';
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
    $menuID = $newID;
    $sql = "select *, 1 IdInserted from Menu where MenuID = '$menuID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'Menu';
    $action = 'i';
    $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //****device อื่น insert
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from Menu where MenuID = '$menuID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'Menu';
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
    
    
    //menuIngredient
    if($countMiMenuIngredient > 0)
    {
        for($k=0; $k<$countMiMenuIngredient; $k++)
        {
            //query statement
            $sql = "INSERT INTO MenuIngredient(MenuID, IngredientID, Amount, ModifiedUser, ModifiedDate) VALUES ('$menuID', '$miIngredientID[$k]', '$miAmount[$k]', '$miModifiedUser[$k]', '$miModifiedDate[$k]')";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
            
            
            
            //insert ผ่าน
            $newID = mysqli_insert_id($con);
            
            
            
            //**********sync device token ตัวเอง delete old id and insert newID
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select $miMenuIngredientID[$k] as MenuIngredientID, 1 as ReplaceSelf, '$miModifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'MenuIngredient';
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
            $miMenuIngredientID[$k] = $newID;
            $sql = "select *, 1 IdInserted from MenuIngredient where MenuIngredientID = '$miMenuIngredientID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'MenuIngredient';
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
        
        
        
        //**********sync device token อื่น
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from MenuIngredient where MenuIngredientID in ('$miMenuIngredientID[0]'";
        for($i=1; $i<$countMiMenuIngredient; $i++)
        {
            $sql .= ",'$miMenuIngredientID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'MenuIngredient';
        $action = 'i';
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
    //delete and insert ตัวเอง, insert คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
