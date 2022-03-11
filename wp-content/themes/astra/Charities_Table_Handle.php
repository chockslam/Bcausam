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
    function GetJSON_server($absPath, $fileName){
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $fcontent = file_get_contents($absPath."\\".$fileName);
        $json_a = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $fcontent), true);
        return $json_a;
    }

    function wpdbExperiments(){
        global $wpdb; // Global database virtual handle
        $sql = "SELECT `CLASSIFICATION CODE` 
        FROM `charities_classifications`";
        $preparation = $wpdb->prepare($sql);
        $results = $wpdb->get_results($preparation);
        $array = json_decode(json_encode($results), true);
        var_dump($array[0]["CLASSIFICATION CODE"]);
    }
    function wpdbUpdateServer(){
        $file = GetJSON_server(PATH, FILE);
        global $wpdb;
        
        
    }


?>