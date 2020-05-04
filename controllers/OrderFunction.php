<?php

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

function isValidProductInfo($productInfo)
{
    foreach ($productInfo as $item) {
        if (!isValidDetailedProductIdx($item->detailedProductIdx) or !isValidNumber($item->number, $item->detailedProductIdx)) {
            return false;
        }
    }
    return true;
}

function isRedundantProductInfo($productInfo)
{
    $arr = [];
    foreach ($productInfo as $item) {
        array_push($arr, $item->detailedProductIdx);
    }
    $arr = array_count_values($arr);
    foreach ($arr as $item) {
        if ($item>1){
            return true;
        }
    }
    return false;
}

function isValidPaymentType($paymentType)
{
    $valid = ['TOSS', 'CARD', 'DEPOSIT', 'PHONE'];
    return in_array($paymentType, $valid);
}

function isValidRefundBank($refundBank)
{
    $valid = ["KB국민은행", "SC제일은행", "경남은행", "광주은행", "기업은행", "농협", "대구은행", "부산은행", "산업은행",
        "새마을금고", "수협", "신한은행", "신협", "외환은행", "우리은행", "우체국", "전북은행", "카카오뱅크", "케이뱅크",
        "하나은행(서울은행)", "한국씨티은행(한미은행)", "제주은행"];
    return in_array($refundBank, $valid);
}

function createOrderNum($orderDate, $orderIdx, $userIdx){
    $result = $orderDate.'-'.$orderIdx.'-'.$userIdx;
    return $result;
}


?>