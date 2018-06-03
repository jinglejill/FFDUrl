<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    
    
    
    if(isset($_POST["date"]) && isset($_POST["expectedSales"]) && isset($_POST["salesConStartDate"]) && isset($_POST["salesConEndDate"]))
    {
        $date = $_POST["date"];
        $expectedSales = $_POST["expectedSales"];
        $salesConStartDate = $_POST["salesConStartDate"];
        $salesConEndDate = $_POST["salesConEndDate"];
    }
    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    //last check stock
    $sql = "select max(CheckDate) LastCheckDate from IngredientCheck Where date_format(CheckDate,'%Y-%m-%d') < date_format('$date','%Y-%m-%d')";
    $selectedRow = getSelectedRow($sql);
    $lastCheckDate = $selectedRow[0]['LastCheckDate'];


    
    //เช็คสตอคครั้งสุดท้าย
    $sql = "select ingredient.IngredientID, (Amount + AmountSmall/ingredient.SmallAmount) Amount from ingredientcheck LEFT JOIN ingredient ON ingredientcheck.IngredientID = ingredient.IngredientID where CheckDate = '$lastCheckDate';";
    
    
    //รวม receive stock หลังจากเช็คสตอค และน้อยกว่าวันนี้ $date
    $sql .= "select ingredient.IngredientID, sum(amount+amountSmall/ingredient.smallAmount) Amount from ingredientReceive LEFT JOIN ingredient ON ingredientreceive.IngredientID = ingredient.IngredientID where ReceiveDate > '$lastCheckDate' and date_format(ReceiveDate,'%Y-%m-%d') < date_format('$date','%Y-%m-%d') group by ingredient.IngredientID;";
    
    
    //ใช้ไปหลังเช็คสตอค และน้อยกว่าวันนี้ $date
    $sql .= "SELECT menuingredient.IngredientID,sum(menuingredient.Amount*Quantity) Amount FROM `ordertaking` LEFT JOIN receipt on ordertaking.ReceiptID = receipt.ReceiptID LEFT JOIN menuingredient ON ordertaking.MenuID = menuingredient.MenuID WHERE receiptdate > '$lastCheckDate' and date_format(ReceiptDate,'%Y-%m-%d') < date_format('$date','%Y-%m-%d') and menuingredient.IngredientID is not null GROUP BY menuingredient.IngredientID;";
    
    
    //รับเข้ามาวันนี้
    $sql .= "select ingredient.IngredientID, sum(Amount+AmountSmall/ingredient.smallAmount) Amount from ingredientreceive LEFT JOIN ingredient ON ingredientreceive.IngredientID = ingredient.IngredientID where date_format(ReceiveDate,'%Y-%m-%d') = date_format('$date','%Y-%m-%d') group by ingredient.IngredientID;";
    
    
    //stock needed by sales contribution
    $sql .= "select IngredientID,sum(a2.NumDish*menuingredient.Amount)Amount from (select menu.MenuID, $expectedSales/ifnull(a1.SpecialPrice,menu.Price)*sum(ordertaking.SpecialPrice*ordertaking.Quantity)/(select sum(ordertaking.SpecialPrice*ordertaking.Quantity) AllSales from ordertaking left join receipt ON ordertaking.ReceiptID = receipt.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID LEFT JOIN (select MenuID,SpecialPrice from specialpriceprogram WHERE date_format(specialpriceprogram.StartDate,'%Y-%m-%d') <= date_format('$date','%Y-%m-%d') and date_format(specialpriceprogram.EndDate,'%Y-%m-%d') >= date_format('$date','%Y-%m-%d'))a1 ON ordertaking.MenuID = a1.MenuID where receiptdate BETWEEN '$salesConStartDate' and '$salesConEndDate' AND menu.Status = 1 and menu.Price != 0 or a1.SpecialPrice is null and (a1.SpecialPrice is not null and a1.SpecialPrice != 0)) NumDish from ordertaking left join receipt ON ordertaking.ReceiptID = receipt.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID LEFT JOIN (select MenuID,SpecialPrice from specialpriceprogram WHERE date_format(specialpriceprogram.StartDate,'%Y-%m-%d') <= date_format('$date','%Y-%m-%d') and date_format(specialpriceprogram.EndDate,'%Y-%m-%d') >= date_format('$date','%Y-%m-%d'))a1 ON ordertaking.MenuID = a1.MenuID where receiptdate BETWEEN '$salesConStartDate' and '$salesConEndDate' AND menu.Status = 1 and menu.Price != 0 or a1.SpecialPrice is null and (a1.SpecialPrice is not null and a1.SpecialPrice != 0) GROUP BY menu.MenuID) a2 LEFT JOIN menuingredient ON a2.MenuID = menuingredient.MenuID WHERE IngredientID is not null GROUP BY IngredientID;";
    
    

    
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
