<?php

    // Author: Ben Palmer 
    // Northumbria University 19005151

    DEFINE('MC_API_KEY',"#############"); // Set the Mandrill API Key here
    DEFINE('DEFAULT_EMAIL', "###########"); // Set the default sending email address here
    DEFINE('DEFAULT_NAME', "##########"); // Set the default sending name here

    function test_connection() {
        // Tests the connection to MailChimp servers and the validity of the API key
        $url = "https://mandrillapp.com/api/1.0/users/ping";
        $content = array(
            "key" => MC_API_KEY,
        );
        $content = json_encode($content);
        $q = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => $content,
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ));
        $response = wp_remote_retrieve_body($q);
        $response = json_decode($response, true);
        if ($response == "PONG!") {
            return true;
        } else {
            return false;
        }
    }

    function test_email($email_addr) {
        // Sends a test email to the address provided
        $url = "https://mandrillapp.com/api/1.0/messages/send-template";
        $content = array(
            "key" => MC_API_KEY,
            "template_name" => "FunderListTest",
            "template_content" => array(),
            "message" => array(
                "from_email" => "keith@bcausam.co.uk",
                "from_name" => "Keith",
                "subject" => "Test Email",
                "to" => array(
                    array(
                        "email" => $email_addr,
                        "name" => "Test User",
                        "type" => "to"
                    )
                ),
                "merge" => true,
                "merge_language" => "handlebars",
                "global_merge_vars" => array(
                    array(
                        "name" => "funders",
                        "content" => array(
                            array(
                                "name" => "Funder Name",
                                "email" => "test1"
                            ),
                            array(
                                "name" => "Funder Name",
                                "email" => "test2"
                            )
                        )
                    )
                ),
                "attachments" => array(
                    array(
                        "type" => "text/csv",
                        "name" => "funders.csv",
                        "content" => base64_encode("email,name,description\ntest1@test.com,test1,test email\ntest2@test.com,test2,test email")
                    )
                )
            ),
        );
            
        $content = json_encode($content);
        $q = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => $content,
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-MC-MergeLanguage' => 'handlebars'
            )
        ));
        $response = wp_remote_retrieve_body($q);
        $response = json_decode($response, true);
        return $response;
    }

    function build_content($email_addr, $charity_number, $funders_array, $csv){
        // Builds the content that will be sent to the email address provided
        // $email_addr = email address to send to
        // $charitynumber = charity number of recipient
        // $funders_array = array of funders to send to email address
        // $csv = csv file to attach to email

        $content = array(
            "key" => MC_API_KEY,
            "template_name" => "FunderList",
            "template_content" => array(),
            "message" => array(
                "from_email" => "keith@bcausam.co.uk",
                "from_name" => "Keith",
                "subject" => "Your Funders List",
                "to" => array(
                    array(
                        "email" => $email_addr,
                        "name" => "Charity User",
                        "type" => "to"
                    )
                ),
                "merge" => true,
                "merge_language" => "handlebars",
                "global_merge_vars" => array(
                    array(
                        "name" => "recipient_number",
                        "content" => $charity_number
                    ),
                    array(
                        "name" => "funders",
                        "content" => $funders_array
                    )
                ),
                "attachments" => array(
                    array(
                        "type" => "text/csv",
                        "name" => "funders.csv",
                        "content" => base64_encode($csv)
                    )
                )
            ),
        );
        return $content;
    }

    function post_message($content) {
        // Posts the email content constructed using build_content() to Mandrill servers
        $url = "https://mandrillapp.com/api/1.0/messages/send-template";
        $encoded_content = json_encode($content);
        $q = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => $encoded_content,
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-MC-MergeLanguage' => 'handlebars'
            )
        ));
        $response = wp_remote_retrieve_body($q);
        $response = json_decode($response, true);
        if (array_key_exists("code", $response)) {
            return false;
        } else {
            return true;
        }
    }

    function build_csv($funders_array){
        // Builds the CSV file that will be attached to the email
        // Inputs:
        //    $funders_array: An array of the funders that the user has selected
        // Returns the CSV file
        $keys = array_keys($funders_array[0]);
        $csv = "Funder ID,Name,Contact Email,Website Address,Contact telephone\n";
        foreach($funders_array as $funder){
            $csv .= $funder['id'] . "," . str_replace(","," ",$funder['name']) . "," . $funder['email'] . "," . $funder['web'] . "," . $funder['phone'] . "\n";
        }
        return $csv;
    }
?>