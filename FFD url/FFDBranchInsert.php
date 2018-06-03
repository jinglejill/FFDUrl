<?php
    include_once("dbConnect.php");
    setConnectionValue("FFD");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["branchID"]) && isset($_POST["dbName"]) && isset($_POST["branchNo"]) && isset($_POST["name"]) && isset($_POST["street"]) && isset($_POST["district"]) && isset($_POST["province"]) && isset($_POST["postCode"]) && isset($_POST["subDistrictID"]) && isset($_POST["districtID"]) && isset($_POST["provinceID"]) && isset($_POST["zipCodeID"]) && isset($_POST["country"]) && isset($_POST["map"]) && isset($_POST["phoneNo"]) && isset($_POST["tableNum"]) && isset($_POST["customerNumMax"]) && isset($_POST["employeePermanentNum"]) && isset($_POST["status"]) && isset($_POST["percentVat"]) && isset($_POST["customerApp"]) && isset($_POST["imageUrl"]) && isset($_POST["startDate"]) && isset($_POST["remark"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $branchID = $_POST["branchID"];
        $dbName = $_POST["dbName"];
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
    if(isset($_POST["username"]))
    {
        $username = $_POST["username"];
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
//    $sql = "select * from credentials where username = '" . $username . "'";
    $sql = "select * from credentialsDB where credentialsID in (select credentialsID from credentials where username = '" . $username . "')";
    $selectedRow = getSelectedRow($sql);
    $credentialsID = $selectedRow[0]["CredentialsID"];
    $dbNameCount = sizeof($selectedRow);
    $j=1;
    for($i=$dbNameCount+1; $i<$dbNameCount+10; $i++)
    {
        $dbNameNew = (string)$username  . "_" . (string)($dbNameCount + $j);
        $sql = "insert into DbNameAll (DbName) values('" . $dbNameNew . "')";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            $response = array('status' => '2', 'msg' => 'คำเตือน!! เพิ่มสาขาไม่สำเร็จ');
            echo json_encode($response);
            exit();
        }
        else
        {
            break;
        }
        $j++;
    }
    
    
    //query statement
    $sql = "INSERT INTO Branch(DbName, BranchNo, Name, Street, District, Province, PostCode, SubDistrictID, DistrictID, ProvinceID, ZipCodeID, Country, Map, PhoneNo, TableNum, CustomerNumMax, EmployeePermanentNum, Status, PercentVat, CustomerApp, ImageUrl, StartDate, Remark, ModifiedUser, ModifiedDate) VALUES ('$dbNameNew', '$branchNo', '$name', '$street', '$district', '$province', '$postCode', '$subDistrictID', '$districtID', '$provinceID', '$zipCodeID', '$country', '$map', '$phoneNo', '$tableNum', '$customerNumMax', '$employeePermanentNum', '$status', '$percentVat', '$customerApp', '$imageUrl', '$startDate', '$remark', '$modifiedUser', '$modifiedDate')";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //credentialsDb
    $sql = "INSERT INTO CredentialsDb(CredentialsID, DbName) VALUES ('$credentialsID', '$dbNameNew')";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    

    
   
    //create table
    $sql = file_get_contents('./createDbAndTable.sql');
    $sql = str_replace('#dbName#',$dbNameNew,$sql);
    $ret = doMultiQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }

    
    
    
    
    //do script successful
    //delete and insert ตัวเอง, insert คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
