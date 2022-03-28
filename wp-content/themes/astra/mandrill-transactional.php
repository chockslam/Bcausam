<?php

    DEFINE('MC_API_KEY',"cQCVN00C9F8Jr24yCYKxZQ");

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
    //         'key' => $MC_API_KEY,
    //         'template' => $MC_TEMPLATE_NAME,
    //         'template_content' => template_content,
    //         'message' => message_content,
    //         'async' => false,
    //         'ip_pool' => 'Main Pool',
    //         'send_at' => 'test'
    //     );
    // }



?>