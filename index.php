<?php

    $hubVerifyToken = 'amarlovesmango';
    $accessToken = "EAAB2nwgUkfEBAJ4QQZBFx2c7HXTii1FLfTjp2wnJNkCITjXxuOyfnZAop8FZAyKGSuVynoCTjLR0LZCStzbkaJGXkbCk42whboUOcWZCQFHcRhuJxcFF3SJI8RVmVP7ajNLipxKPaSO8Vof8pHBsidw2sBJcjow4ZBssVFKMyH7gZDZD";

    if ($_REQUEST['hub_verify_token'] === $hubVerifyToken) {
      echo $_REQUEST['hub_challenge'];
      exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $messageText = $input['entry'][0]['messaging'][0]['message']['text'];
    
    $answer = "I don't understand. Ask me 'hi'.";
    
    if($messageText == "hi") {
        $answer = "Hello";
    }
    
    $response = [
        'recipient' => [
            'id' => $senderId 
            ],
        'message' => [ 
            'text' => $answer 
            ]
    ];
    
    $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
    
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    if(!empty($input['entry'][0]['messaging'][0]['message'])) {
        curl_exec($ch);
    }
    curl_close($ch);
