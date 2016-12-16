<?php

	// Necessary Token Variables for API access!
	$hubVerifyToken = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
	$accessToken = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
	$youtube_api_key = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
	
	$regex = "/(hello)|(hi)/";

	if ($_REQUEST['hub_verify_token'] === $hubVerifyToken) {
	  echo $_REQUEST['hub_challenge'];
	  exit;
	}

	$input = json_decode(file_get_contents('php://input'), true);
	
	$senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
	$messageText = $input['entry'][0]['messaging'][0]['message']['text'];
	
	$answer = "I don't understand.";
	
	$url = 'https://graph.facebook.com/v2.8/'.$senderId.'?access_token='. $accessToken;
	$context = stream_context_create(array(
	  'http' => array(
		 'ignore_errors'=>true,
		 'method'=>'GET'
	   )
	));
	
	$response = json_decode(file_get_contents($url, false, $context), true);
	
	if(preg_match($regex, $messageText)) {
		$answer = "Hello ". $response['first_name'] . "!\n\nWhat can this bot do ?\nI have been programmed to make your life simple, by sending you your favourite song!\n\nYou just have to send the name of song that you want to download and i will send you that song as an audio message!";
	
		$response = [
			'recipient' => [
				'id' => $senderId
				],
			'message' => [
					'text' => $answer
				]    
			];
		
	}
	else {
		$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults=5&q=". urlencode($messageText) . "&safeSearch=none&key=" . $youtube_api_key;
		$context = stream_context_create(array(
		  'http' => array(
			 'ignore_errors'=>true,
			 'method'=>'GET'
		   )
		));
		
		$video_result_data = json_decode(file_get_contents($url, false, $context), true);
		
		$video_id = $video_result_data['items'][0]['id']['videoId'];
		$count = 0;
		while(empty($video_result_data['items'][$count]['id']['videoId'])) {
			$count++;
			if($count > 5) break;
		}
		
		$video_id = $video_result_data['items'][0]['id']['videoId'];
		$video_name = $video_result_data['items'][0]['snippet']['title'];
	
		$base_url = "https://www.youtubeinmp3.com/fetch/?format=JSON&video=https://www.youtube.com/watch?v=". $video_id;
		$download_url = json_decode(file_get_contents($base_url, false, $context), true);
	
		$download_link = urldecode($download_url['link']);
	
		$response = [
			'recipient' => [
				'id' => $senderId
				],
			'message' => [ 
					"attachment" => [
						"type"=>"template",
						"payload"=> [
							"template_type" => "button",
							"text" => "Hi ".$response['first_name']."!\nHere's your song! 🎼🎧\n".$video_name,
							"buttons" => [[
								"type" => "web_url",
								"url" => $download_link,
								"title" => "Download Song"
						]]
					]
				]
			]
		];
		
		// echo $download_url['link'];
	}

	$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	
	if(!empty($input['entry'][0]['messaging'][0]['message'])) {
		curl_exec($ch);
	}
	curl_close($ch);
?>