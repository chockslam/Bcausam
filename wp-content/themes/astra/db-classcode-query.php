<?php

// use ElementorPro\Modules\Forms\Submissions\Database\Query;

//     define("PATH", "C:\\xampp\\htdocs\\Bcausam\\wp-content\\themes\\astra");
//     define("SERVER_PATH", "/homepages/9/d834021495/htdocs/clickandbuilds/Bcausam/wp-content/themes/astra/");

    function callAPI($url, $header)
    {
        $response = wp_remote_get($url, $header);

        
        // $decodedReponse = json_decode($response, true);

        // print_r($response);
        
        // print_r($response->errors['http_request_failed'][0]);

        $body = wp_remote_retrieve_body($response);
        return $body;
        
        // if($response->errors['http_request_failed'][0])
        // {
        //     callAPI($url, $header);
            
        // }
        // else{
        //     $body = wp_remote_retrieve_body($response);
        //     return $body;
        // }
    }

    function CopyDatabase($inputtedCharityNumber){   
        $testNumber = '200017';
        
        // TODO: Charity commission API call
        
        // Setting headers and arguments for API request
        $header = array(
            'headers' => array(
                'Cache-Control' => 'no-cache',
                'Ocp-Apim-Subscription-Key' => '###########',
            ),
            'timeout' => 30,
        );
        
        // Creating the URL and adding the user inputted Charity Number
        $url = 'https://api.charitycommission.gov.uk/register/api/allcharitydetails/';

        // Real call
        $url = $url . $inputtedCharityNumber . "/0";

        // Test call
        // $url = $url . $testNumber . "/0";
        
        // Handling the response
    
        $body = callAPI($url, $header);

        //Filtering the response to just the "What" category
        
        $response = json_decode($body, true);
        

        $response = $response["who_what_where"];
        
        $validClassCodes = array();
        foreach($response as $arr)
        {
            array_push($validClassCodes, $arr['classification_code']);
        }

        $validClassCodes = array_filter($validClassCodes, function($code){
            //if(str_contains($code, '10' || '11'))
            
            return ($code < 200);
            
        });
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
            $res[$entry->id] = explode(';', $entry->class_codes);
        }
        // return $res;
        //Call query class code function, put '$validClassCodes' in for first parameter
        $finish = QueryClassCode($validClassCodes, $res);
        //Return query class code function
        //return $finish;

        $end = QueryIDs($finish);
        return $end;
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

    //Change function so it does not query api to db
    function QueryIDs($arrayOfActiveIDs)
    {
        //Init db
        global $wpdb;
        //Query the db
        $sqlQuery = "SELECT * FROM charities_classifications WHERE id IN (". implode(',', $arrayOfActiveIDs) .")";
        //Prepare the sql query for safe execution
        $preparation = $wpdb->prepare($sqlQuery);
        //Retrieves an entire sql result set from database
        $result = $wpdb->get_results($preparation);
        //return $result;
        //Create new array
        $res = array();
        //Converting into associative array
        $res = json_decode(json_encode($result), true);
        return $res;
        
        // echo "<table>";
        // echo "<tr>";
        // echo "<th>Name</th>";
        // echo "<th>Number</th>";
        // echo "<th>Contact info</th>";
        // echo "<th>Expenditure</th>";
        // echo "</tr>";
        // for($i = 0; $i < count($res); $i++)
        // {
        //     echo "<tr>";
        //     echo "<td>" .  $res[$i]['name'] . "</td>";
        //     echo "<td>" .  $res[$i]['id'] . "</td>";
        //     echo "<td>Email: " .  $res[$i]['email'] . 
        //             "<br>Tel. Number: " .  $res[$i]['phone'] . 
        //             "<br>Web: " .  $res[$i]['web'] . "</td>";
        //     echo "<td>" . $res[$i]['expenditure'] . "</td>";
        //     echo "</tr>";
        // }
        // echo "</table>";
    }

?>