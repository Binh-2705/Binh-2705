<?php

class AIService{

private $apiKey;

public function __construct(){
$this->apiKey = $this->resolveApiKey();
}

private function resolveApiKey(){
$key = getenv("OPENAI_API_KEY");

if($key){
return trim($key);
}

$envFiles = [
__DIR__ . "/../.env",
__DIR__ . "/../laravel_app/.env"
];

foreach($envFiles as $envFile){
if(!is_readable($envFile)){
continue;
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if($lines === false){
continue;
}

foreach($lines as $line){
if(strpos(trim($line), '#') === 0 || strpos($line, '=') === false){
continue;
}

list($name, $value) = explode('=', $line, 2);
if(trim($name) === 'OPENAI_API_KEY'){
return trim($value, " \t\n\r\0\x0B\"'");
}
}
}

return "";
}

public function phanTichCV($cvText){

if($this->apiKey === ""){
return ["error"=>"Missing OPENAI_API_KEY environment variable."];
}

$url = "https://api.openai.com/v1/chat/completions";

$cvText = preg_replace('/[^\PC\s]/u', '', $cvText);
$cvText = trim($cvText);
$cvText = substr($cvText,0,3000);

$data = [
"model"=>"gpt-4o-mini",
"messages"=>[
[
"role"=>"system",
"content"=>"Bạn là chuyên gia HR đánh giá CV."
],
[
"role"=>"user",
"content"=>"Phân tích CV sau và đánh giá mức độ phù hợp với lập trình viên PHP:\n".$cvText
]
]
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
"Content-Type: application/json",
"Authorization: Bearer ".$this->apiKey
]);

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

$response = curl_exec($ch);

if($response === false){
return ["error"=>curl_error($ch)];
}

curl_close($ch);

return json_decode($response,true);

}

}