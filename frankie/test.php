<?php
    try{
        //Get db connection script
        require_once("dbConnection.php");
        //Get connection to the db
        $dbConn = getConnection();

        //Variable to store sql query
        //Query the db 
        $sqlQuery = "SELECT * FROM charities_classifications";

        //Set array
        $array = array();

        //Result of the query
        $queryResult = $dbConn->query($sqlQuery);

        //Loop through the results of the query
        while($rowObj = $queryResult->fetchObject())
        {
            //Assign each row returned into an array
            $array[] = $rowObj;
        }

        
    }
    //Catch for query failed
    catch (Exception $e){
        echo "<p>Query failed: ".$e->getMessage()."</p>\n";
    }

    function queryClassCode($classCode, $dbClassCode) 
    {
        if($classCode == $dbClassCode)
        {
            
        }
    }
?>