<?php
    
    define("PATH", "C:\\xampp\\htdocs\\Bcausam\\wp-content\\themes\\astra");
    define("FILE", "CharitiesClassification.json");

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
        $fcontent = file_get_contents($absPath."\\".$fileName);
        $json_a = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $fcontent), true);
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
        $charities = GetJSON_server(PATH, FILE);
        $funderIDs = GetFunders($charities);
        $fundersWhatClassific = GetWhatClassification($charities, $funderIDs);
        return $fundersWhatClassific;
    }

    function deleteOldRecords(){
        global $wpdb;
        $sql = "TRUNCATE TABLE `charities_classifications`";
        $preparation = $wpdb->prepare($sql);
        return $wpdb->query($preparation);
    }
    function wpdbUpdateServer(){
        
        deleteOldRecords();

        $newData = getDataForDatabase();
        //var_dump($newData);
        global $wpdb;
        foreach($newData as $key => $value){
            $wpdb->insert("charities_classifications", array(
                'ID'                  => $key,
                'CLASSIFICATION CODE' => $value
            ));
        }
    }


?>