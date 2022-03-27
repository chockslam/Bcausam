<?php

// use ElementorPro\Modules\Forms\Submissions\Database\Query;

//     define("PATH", "C:\\xampp\\htdocs\\Bcausam\\wp-content\\themes\\astra");
//     define("SERVER_PATH", "/homepages/9/d834021495/htdocs/clickandbuilds/Bcausam/wp-content/themes/astra/");

    function CopyDatabase()
    {   
        // $inputtedCharityNumber = '200054';
        
        // // TODO: Charity commission API call
        
        // // Setting headers and arguments for API request
        // $header = array(
        //     'headers' => array(
        //         'Cache-Control' => 'no-cache',
        //         'Ocp-Apim-Subscription-Key' => '6ee601d9b98f4a7eb9a73a57e7e366d1',
        //     )
        // );
        
        // // Creating the URL and adding the user inputted Charity Number
        // $url = 'https://api.charitycommission.gov.uk/register/api/allcharitydetails/';
        // $url = $url . $inputtedCharityNumber . "/0";
        
        // // Handling the response
        // $response = wp_remote_get($url, $header);
        // $body = wp_remote_retrieve_body($response);

        // // Filtering the response to just the "What" category
        // //$response = $response["body"];  
        
        // $response = json_decode($body, true);
        // gettype($response);
        // $response = $response["who_what_where"];
        
        // $validClassCodes = array();
        // foreach($response as $arr)
        // {
        //     array_push($validClassCodes, $arr['classification_code']);
        // }

        // $validClassCodes = array_filter($validClassCodes, function($code){
        //     //if(str_contains($code, '10' || '11'))
            
        //     return ($code < 200);
            
        // });
        //return $validClassCodes;

        //Init db
        global $wpdb;
        //Query the db
        $sqlQuery = "SELECT * FROM charities_classifications";
        //Prepare the sql query for safe execution
        $preparation = $wpdb->prepare($sqlQuery);
        //Retrieves an entire sql result set from database
        $result = $wpdb->get_results($preparation);
        //Create new array
        $res = array();
        //Copy the current key and value of result to key and entry
        foreach($result as $key => $entry)
        {
            //Every entry ID remove ';' from classification code
            $res[$entry->ID] = explode(';', $entry->CLASSIFICATION_CODE);
        }
        //Call query class code function, put '$validClassCodes' in for first parameter
        $finish = QueryClassCode("101", $res);
        //Return query class code function
        return $finish;

        //$end = QueryIDs($finish);
        //return $end;
    }
    
    //Function for comparing the class code
    function QueryClassCode($classCodes, $arrayOfObj) 
    {
        //Create an array for valid IDs
        $validIDs = array();
        //Copy the current key and value to id and arr
        foreach($arrayOfObj as $id => $arr)
        {
            //Create intersect array
            $x = array();
            //Intersect class code and array of objects
            $x = array_intersect($classCodes, $arr);
            //Check through size of array $x
            if(count($x) > 0)
            {
                //Push the matching IDs to the validIDs array
                array_push($validIDs, $id);
            }
        }
        //Return the validIDs array
        return $validIDs;
    }

    // function QueryIDs($arrayOfIDs)
    // {
    //     //$arrayExtract = implode(',', $arrayOfIDs);

    //     //return $arrayExtract;

    //     $chunked = array_chunk($arrayOfIDs, 10, true);
    //     //return $chunked;
        
    //     //Setting headers and arguments for API request
    //     $header = array(
    //         'headers' => array(
    //             'Cache-Control' => 'no-cache',
    //             'Ocp-Apim-Subscription-Key' => '6ee601d9b98f4a7eb9a73a57e7e366d1',
    //         )
    //     );
        
    //     //Creating the URL and adding the user inputted Charity Number
    //     $url = 'https://api.charitycommission.gov.uk/register/api/charitydetailsmulti/';

    //     $compResponse = array();
    //     $count = 0;

    //     shuffle($chunked);

    //     foreach($chunked as $tenChunkOfIDs)
    //     {
    //         $count++;
    //         if($count < 2)
    //         {
    //             $tenStringOfIDs = implode(',', $tenChunkOfIDs);
    //             $modifiedURL = $url . $tenStringOfIDs;
    
    //             // // Handling the response
    //             $response = wp_remote_get($modifiedURL, $header);
    //             $body = wp_remote_retrieve_body($response);
    
    //             $response = json_decode($body, true);
    //             gettype($response);
    //             //$response = $response->charity_name;

    //             // $response = array_filter($response, function($filtered){
    //             //     return $filtered == 'charity_name';
    //             // });

    //             // for($i = 0; $i < 10; $i++)
    //             // {
    //             //     return $response[$i][$i]["charity_name"];
    //             // }
                
    //             array_push($compResponse, $response);
    //             //gettype($response);
    //         }
    //     }
    //     return $compResponse;
    // }

?>