<?php

require "vendor/autoload.php";
require "services/readCV.php";
require "services/AIService.php";

$text = docNoiDungCV("uploads/cv/1772982351_Binh-Nguyen-TopCV.vn-080326.220531.pdf");

$ai = new AIService();

$result = $ai->phanTichCV($text);

echo "<pre>";

if(isset($result['choices'][0]['message']['content'])){
    echo $result['choices'][0]['message']['content'];
}else{
    echo "Lỗi từ API:\n";
    print_r($result);
}