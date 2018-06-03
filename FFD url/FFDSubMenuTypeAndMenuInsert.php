<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["sMTSubMenuTypeID"]) && isset($_POST["sMTMenuTypeID"]) && isset($_POST["sMTName"]) && isset($_POST["sMTOrderNo"]) && isset($_POST["sMTStatus"]) && isset($_POST["sMTModifiedUser"]) && isset($_POST["sMTModifiedDate"]) &&
    
       isset($_POST["menuID"]) && isset($_POST["menuCode"]) && isset($_POST["titleThai"]) && isset($_POST["price"]) && isset($_POST["menuTypeID"]) && isset($_POST["subMenuTypeID"]) && isset($_POST["subMenuType2ID"]) && isset($_POST["subMenuType3ID"]) && isset($_POST["imageUrl"]) && isset($_POST["color"]) && isset($_POST["orderNo"]) && isset($_POST["status"]) && isset($_POST["remark"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"])
    )
    {
        $sMTSubMenuTypeID = $_POST["sMTSubMenuTypeID"];
        $sMTMenuTypeID = $_POST["sMTMenuTypeID"];
        $sMTName = $_POST["sMTName"];
        $sMTOrderNo = $_POST["sMTOrderNo"];
        $sMTStatus = $_POST["sMTStatus"];
        $sMTModifiedUser = $_POST["sMTModifiedUser"];
        $sMTModifiedDate = $_POST["sMTModifiedDate"];
        
        
        
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
        $sql = "INSERT INTO SubMenuType(SubMenuTypeID, MenuTypeID, Name, OrderNo, Status, ModifiedUser, ModifiedDate) VALUES ('" . ($subMenuTypeID+$j) . "', '$menuTypeID', '$sMTName', '$sMTOrderNo', '$sMTStatus', '$modifiedUser', '$modifiedDate')";
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
        $sql = "select $subMenuTypeID as SubMenuTypeID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'SubMenuType';
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
        $subMenuTypeID = $subMenuTypeID+$j;
        $sql = "select *, 1 IdInserted from SubMenuType where SubMenuTypeID = '$subMenuTypeID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'SubMenuType';
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
        $sql = "select *, 1 IdInserted from SubMenuType where SubMenuTypeID = '$subMenuTypeID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'SubMenuType';
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
    $sql = "select *, 1 IdInserted from SubMenuType where SubMenuTypeID = '$subMenuTypeID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'SubMenuType';
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
    
    
    
    //part menu
    //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
    //query statement
    for($j=0;$j<$retryNo;$j++)
    {
        $sql = "INSERT INTO Menu(MenuID, MenuCode, TitleThai, Price, MenuTypeID, SubMenuTypeID, SubMenuType2ID, SubMenuType3ID, ImageUrl, Color, OrderNo, Status, Remark, ModifiedUser, ModifiedDate) VALUES ('" . ($menuID+$j) . "', '$menuCode', '$titleThai', '$price', '$menuTypeID', '$subMenuTypeID', '$subMenuType2ID', '$subMenuType3ID', '$imageUrl', '$color', '$orderNo', '$status', '$remark', '$modifiedUser', '$modifiedDate')";
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
        $menuID = $menuID+$j;
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
    }
    else if($j == 0)
    {
        //update IdInserted
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from Menu where MenuID = '$menuID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'Menu';
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
