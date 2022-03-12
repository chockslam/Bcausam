<?php
    try{
        //Init db
        global $wpdb;

        //Query the db 
        $sqlQuery = "SELECT * FROM charities_classifications";
        
        //Prepare the sql query for safe execution
        $preparation = $wpdb->prepare($sqlQuery);

        //Retrieves an entire sql result set from database
        $result = $wpdb->get_results($preparation);

        //Set array
        $array = array();

        //Loop through each result of the sql query
        //Set that as value
        foreach($result as $value)
        {
            //Assign each value returned into an array
            $array[] = $value;
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