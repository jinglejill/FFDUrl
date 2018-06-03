<?php
    include_once("dbConnect.php");
    setConnectionValue("FFD");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["branchID"]) && isset($_POST["dbName"]) && isset($_POST["branchNo"]) && isset($_POST["name"]) && isset($_POST["street"]) && isset($_POST["district"]) && isset($_POST["province"]) && isset($_POST["postCode"]) && isset($_POST["subDistrictID"]) && isset($_POST["districtID"]) && isset($_POST["provinceID"]) && isset($_POST["zipCodeID"]) && isset($_POST["country"]) && isset($_POST["map"]) && isset($_POST["phoneNo"]) && isset($_POST["tableNum"]) && isset($_POST["customerNumMax"]) && isset($_POST["employeePermanentNum"]) && isset($_POST["status"]) && isset($_POST["percentVat"]) && isset($_POST["customerApp"]) && isset($_POST["imageUrl"]) && isset($_POST["startDate"]) && isset($_POST["remark"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $branchID = $_POST["branchID"];
        $dbNameEdit = $_POST["dbNameEdit"];
        $branchNo = $_POST["branchNo"];
        $name = $_POST["name"];
        $street = $_POST["street"];
        $district = $_POST["district"];
        $province = $_POST["province"];
        $postCode = $_POST["postCode"];
        $subDistrictID = $_POST["subDistrictID"];
        $districtID = $_POST["districtID"];
        $provinceID = $_POST["provinceID"];
        $zipCodeID = $_POST["zipCodeID"];
        $country = $_POST["country"];
        $map = $_POST["map"];
        $phoneNo = $_POST["phoneNo"];
        $tableNum = $_POST["tableNum"];
        $customerNumMax = $_POST["customerNumMax"];
        $employeePermanentNum = $_POST["employeePermanentNum"];
        $status = $_POST["status"];
        $percentVat = $_POST["percentVat"];
        $customerApp = $_POST["customerApp"];
        $imageUrl = $_POST["imageUrl"];
        $startDate = $_POST["startDate"];
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
    
    
    
    //query statement    
     $sql = "update Branch set DbName = '$dbNameEdit', BranchNo = '$branchNo', Name = '$name', Street = '$street', District = '$district', Province = '$province', PostCode = '$postCode', SubDistrictID = '$subDistrictID', DistrictID = '$districtID', ProvinceID = '$provinceID', ZipCodeID = '$zipCodeID', Country = '$country', Map = '$map', PhoneNo = '$phoneNo', TableNum = '$tableNum', CustomerNumMax = '$customerNumMax', EmployeePermanentNum = '$employeePermanentNum', Status = '$status', PercentVat = '$percentVat', CustomerApp = '$customerApp', ImageUrl = '$imageUrl', StartDate = '$startDate', Remark = '$remark', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where BranchID = '$branchID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
//    //select row ที่แก้ไข ขึ้นมาเก็บไว้
//    $sql = "select *, 1 IdInserted from Branch where BranchID = '$branchID'";
//    $selectedRow = getSelectedRow($sql);
//
//
//
//    //broadcast ไป device token อื่น
//    $type = 'Branch';
//    $action = 'u';
//    $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//    if($ret != "")
//    {
//        mysqli_rollback($con);
//        putAlertToDevice();
//        echo json_encode($ret);
//        exit();
//    }
    //-----
    
    
    
    //do script successful
    mysqli_commit($con);
//    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
