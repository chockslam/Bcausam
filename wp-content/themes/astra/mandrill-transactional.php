<?php

    function test() {
        $
    }

    function postMessage($messageContent) {
        $arr = array(
            'body'  =>  $messageContent
        );

        $response = wp_remote_post("https://mandrillapp.com/api/1.0/message/send-template", $arr);
        return $response
    }


    function formatMessage($message) {
        $content = array(
            'key' => $MC_API_KEY,
            'template' => $MC_TEMPLATE_NAME,
            'template_content' => template_content,
            'message' => message_content,
            'async' => false,
            'ip_pool' => ,
            'send_at' => 
        )
    }


    function 

?>