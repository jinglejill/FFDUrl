<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    
    
    
    if(isset($_POST["startDate"]) && isset($_POST["endDate"]))
    {
        $startDate = $_POST["startDate"];
        $endDate = $_POST["endDate"];        
    }
    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    //last check stock
    $sql = "select max(CheckDate) LastCheckDate from IngredientCheck Where date_format(CheckDate,'%Y-%m-%d') < date_format('$startDate','%Y-%m-%d')";
    $selectedRow = getSelectedRow($sql);
    $lastCheckDate = $selectedRow[0]['LastCheckDate'];


    
    //เช็คสตอคครั้งสุดท้าย
    $sql = "select ingredient.IngredientID, (Amount + AmountSmall/ingredient.SmallAmount) Amount from ingredientcheck LEFT JOIN ingredient ON ingredientcheck.IngredientID = ingredient.IngredientID where CheckDate = '$lastCheckDate';";
    
    
    //รวม receive stock หลังจากเช็คสตอค และน้อยกว่าวันนี้ $date
    $sql .= "select ingredient.IngredientID, sum(amount+amountSmall/ingredient.smallAmount) Amount from ingredientReceive LEFT JOIN ingredient ON ingredientreceive.IngredientID = ingredient.IngredientID where ReceiveDate > '$lastCheckDate' and date_format(ReceiveDate,'%Y-%m-%d') < date_format('$startDate','%Y-%m-%d') group by ingredient.IngredientID;";
    
    
    //ใช้ไปหลังเช็คสตอค และน้อยกว่าวันนี้ $startDate
    $sql .= "SELECT menuingredient.IngredientID,sum(menuingredient.Amount*Quantity) Amount FROM `ordertaking` LEFT JOIN receipt on ordertaking.ReceiptID = receipt.ReceiptID LEFT JOIN menuingredient ON ordertaking.MenuID = menuingredient.MenuID WHERE date_format(ReceiptDate,'%Y-%m-%d') > date_format('$lastCheckDate','%Y-%m-%d') and date_format(ReceiptDate,'%Y-%m-%d') < date_format('$startDate','%Y-%m-%d') and menuingredient.IngredientID is not null GROUP BY menuingredient.IngredientID;";
    
    
    //รับเข้ามาระหว่างวันที่
    $sql .= "select ingredientreceive.IngredientID, sum(Amount+AmountSmall/Ingredient.SmallAmount) Amount from ingredientreceive LEFT JOIN ingredient ON ingredientreceive.IngredientID = ingredient.IngredientID where date_format(ReceiveDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') group by ingredientreceive.IngredientID;";
    
    
    //เช็คสตอควันที่
    $sql .= "select ingredient.IngredientID,ifnull(a1.Amount,0)Amount,ifnull(a1.AmountSmall,0)AmountSmall from ingredient LEFT JOIN (select IngredientID, Amount, AmountSmall from ingredientcheck where date_format(CheckDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d'))a1 ON ingredient.IngredientID = a1.IngredientID WHERE STATUS = 1;";
    
    
    
    //ปริมาณใช้จริง ระหว่างวันที่ startDate and endDate
    $sql .= "SELECT menuingredient.IngredientID,sum(menuingredient.Amount*Quantity) Amount FROM `ordertaking` LEFT JOIN receipt on ordertaking.ReceiptID = receipt.ReceiptID LEFT JOIN menuingredient ON ordertaking.MenuID = menuingredient.MenuID WHERE date_format(ReceiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and menuingredient.IngredientID is not null GROUP BY menuingredient.IngredientID;";
    
    
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
