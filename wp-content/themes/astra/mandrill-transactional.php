<?php

    DEFINE('MC_API_KEY',"cQCVN00C9F8Jr24yCYKxZQ");
    DEFINE('DEFAULT_EMAIL', "keith@bcausam.co.uk");
    DEFINE('DEFAULT_NAME', "Keith");

    function test_connection() {
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

    function build_content($email_addr, $funders_array){
        $content = array(
            "key" => MC_API_KEY,
            "template_name" => "FunderListTest",
            "template_content" => array(),
            "message" => array(
                "from_email" => DEFAULT_EMAIL,
                "from_name" => DEFAULT_NAME,
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
                        "content" => $funders_array
                    ),
                )
                ),
            );
            return $content;
    }

    function postMessage($content) {
        $url = "https://mandrillapp.com/api/1.0/messages/send-template";
        $encoded_content = json_encode($content);
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

    // function postMessage($messageContent) {
    //     $arr = array(
    //         'body'  =>  $messageContent
    //     );

    //     $response = wp_remote_post("https://mandrillapp.com/api/1.0/message/send-template", array(
    //         'method' => 'POST',
    //         'body' => $arr,
    //         'headers' => array(
    //             'Content-Type' => 'application/json'
    //         )
    //     ));
    // }


    // function formatMessage($message) {
    //     $content = array(
    //         'key' => MC_API_KEY,
    //         'template' => $MC_TEMPLATE_NAME,
    //         'template_content' => template_content,
    //         'message' => $message_content,
    //     );
    // }



?>