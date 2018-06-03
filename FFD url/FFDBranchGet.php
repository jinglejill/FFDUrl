<?php
    include_once("dbConnect.php");
    setConnectionValue("FFD");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    
    
    
    if(isset($_POST["username"]))
    {
        $username = $_POST["username"];        
    }
    else
    {
        $username = $_GET["username"];
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    $sql = "select * from credentials where username = '" . $username . "'";
    $selectedRow = getSelectedRow($sql);
    $credentialsID = $selectedRow[0]["CredentialsID"];
    
    
    $sql = "select Branch.*, 1 as IdInserted from credentialsdb left join branch on credentialsdb.dbName = branch.dbName  where credentialsdb.credentialsID = $credentialsID";
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;
    
    
    
    // Close connections
    mysqli_close($con);
?>
