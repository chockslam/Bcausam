<?php
    //  GLEBS MEDVEDEVS (19012503) FILE
    define("PATH", "C:\\xampp\\htdocs\\Bcausam\\wp-content\\themes\\astra\\");
    define("CSV_SQL", "SQL.csv");
    define("FILE", "CharitiesClassification.json");
    define("SERVER_PATH", "/homepages/9/d834021495/htdocs/clickandbuilds/Bcausam/wp-content/themes/astra/");
    
    function GetJSON_local($fileName){
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $fcontent = file_get_contents($fileName);
        $json_a = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $fcontent), true);
        
        
        return $json_a;
        
    }

    /**
     * Binary search.
     * ----------LINK-------------
     */
    function fast_in_array($elem, $array) 
    {
        $top = sizeof($array) -1;
        $bot = 0;
        while($top >= $bot) 
        {
            $p = floor(($top + $bot) / 2);
            if ($array[$p] < $elem) $bot = $p + 1;
            elseif ($array[$p] > $elem) $top = $p - 1;
            else return TRUE;
        }
        return FALSE;
    }
    /**
     * Exctract information from .json file
     */
    function GetJSON_server($absPath, $fileName){
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $fcontent = file_get_contents($absPath."/".$fileName);
        
        //$fcontent = stripcslashes(trim($fcontent,'"'));
        $json_a = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $fcontent), true);
        var_dump($json_a);
        return $json_a;
    }

    /**
     * Random experiments with $wpdb
     */
    function wpdbExperiments(){
        global $wpdb; // Global database virtual handle
        $sql = "SELECT `CLASSIFICATION CODE` 
        FROM `charities_classifications`";
        $preparation = $wpdb->prepare($sql);
        $results = $wpdb->get_results($preparation);
        $array = json_decode(json_encode($results), true);
        var_dump($array[0]["CLASSIFICATION CODE"]);
    }

    /**
     * Returns array (IDs) of all funders.
     */
    function GetFunders($data){
        $res = [];
        foreach($data as $val){
          if(   $val["classification_type"] == "How" 
            &&  $val["classification_description"] == "Makes Grants To Organisations"){
              $res[] = (string) $val["registered_charity_number"]; 
            }
        }
        return $res;
    }
    
    /**
     * Return classifications of all funders as a map ([200001] => "101;107;108" [20002] => "101;102") .
     */
    function GetWhatClassification($data, $funderIDs){
        $res = [];
        foreach($data as $val){
            if( $val["classification_type"] == "What" &&
                fast_in_array($val["registered_charity_number"], $funderIDs)){
                if(!isset($res[$val["registered_charity_number"]])){
                    $res[(string)$val["registered_charity_number"]] = "";
                }
                $res[(string)$val["registered_charity_number"]] .= (string) $val["classification_code"].";";
            }
        }

        foreach($res as $key => $toTrim){
            $res[$key] = rtrim($toTrim,';');
        }
        return $res;
    }

    function getDataForDatabase(){
        $charities = GetJSON_server(SERVER_PATH, FILE);
        $funderIDs = GetFunders($charities);
        $fundersWhatClassific = GetWhatClassification($charities, $funderIDs);
        return $fundersWhatClassific;
    }
    // ------------------------------------------FUNCTIONS ABOVE THIS LINE ARE NOT USED----------------------------- 
    // ------------------------------------------FUNCTIONS ABOVE THIS LINE ARE NOT USED----------------------------- 
    // ------------------------------------------FUNCTIONS ABOVE THIS LINE ARE NOT USED----------------------------- 
    // ------------------------------------------FUNCTIONS ABOVE THIS LINE ARE NOT USED----------------------------- 
    // ------------------------------------------FUNCTIONS ABOVE THIS LINE ARE NOT USED----------------------------- 
    // ------------------------------------------FUNCTIONS ABOVE THIS LINE ARE NOT USED----------------------------- 
    // ------------------------------------------FUNCTIONS ABOVE THIS LINE ARE NOT USED----------------------------- 

//Define the function to remove the spacial character

    // code from https://linuxhint.com/remove_special_characters_string_php/ 
    function rm_special_char($str) {

        //Remove "#","'" and ";" using str_replace() function
        
        $result = str_replace( array("'", "%", "+"), '', $str);
        return $result;
        //The output after remove
        
        //echo "<br/><b>Text after remove: </b> <br/>".$result;
        
    }

    /**
     * Deleting all the records using $wpdb. 
     */
    function deleteOldRecords(){
        global $wpdb;
        $sql = "TRUNCATE TABLE `charities_classifications`";
        $preparation = $wpdb->prepare($sql);
        $q = $wpdb->query($preparation);
        return $q;
    }
    
    /**
     * Updating wpdb with new info using .csv file created externally using python script.
     */

    function wpdbUpdateServer(){
        
        // Truncate table before inserting new rows.
        deleteOldRecords();

        // Read CSV file
        $newData = file(SERVER_PATH.CSV_SQL);
        
        // Insert query format...
        $SQLupd = "INSERT into `charities_classifications` (`id`, `name`, `class_codes`, `phone`, `email`, `web`, `expenditure`)
                   VALUES ";

                                                                                                            // $wpdb - global variable that used to interact with the database.
        $place_holder = "(%d, '%s', '%s', '%s', '%s', '%s', '%s')";                                                       // create placeholder to be added to the sql query. Example format is (200001, '101;102;103')
        
        // For each line in the .csv format.
        foreach($newData as $line){
            $toInsert = str_getcsv($line);                                                  // turn .csv string into an array 
            //var_dump($toInsert);
            if(strlen($toInsert[3])<=5){
                $toInsert[3] = "Not available";
            }
            if(strlen($toInsert[4])<=5){
                $toInsert[4] = "Not available";
            }
            if(strlen($toInsert[5])<=5){
                $toInsert[5] = "Not available";
            }
            //delete "'" from strings
            $toInsert[1] = rm_special_char($toInsert[1]);
            $toInsert[2] = rm_special_char($toInsert[2]);
            $toInsert[3] = rm_special_char($toInsert[3]);
            $toInsert[4] = rm_special_char($toInsert[4]);
            $toInsert[5] = rm_special_char($toInsert[5]);
            $toInsert[6] = rm_special_char($toInsert[6]);
            
            $SQLupd = $SQLupd.sprintf($place_holder, $toInsert[0], $toInsert[1], $toInsert[2], $toInsert[3], $toInsert[4], $toInsert[5], $toInsert[6]).",";       // fill in the placeholder and add it to the 'update query format'
        }
        global $wpdb; 
        // trim last coma and, execute the prepared query, return count of affected rows
        $SQLupd = substr_replace($SQLupd ,"",-1);
        $prep = $wpdb->prepare( $SQLupd );
        $res = $wpdb->query( $prep );
        return $res;                                                                    // Returns the count of the affected rows.
    }


?>