<?php

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

function isValidUserType($userType)
{
    $validTypeList = ['NORMAL', 'FACEBOOK', 'KAKAO', 'NAVER'];
    return in_array($userType, $validTypeList);
}

function isValidEmailForm($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPasswordForm($password)
{
    return preg_match("/^.{8,16}$/", $password);
}

function isValidPhoneForm($phone)
{
    return preg_match("/^01[0-9]{8,9}$/", $phone);
}

function isValidBirthForm($dateOfBirth)
{
    if (is_null($dateOfBirth)){
        return true;
    }
    return preg_match("/^(19[0-9][0-9]|20\d{2})(0[0-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])$/", $dateOfBirth);
}

function isYorN($item)
{
    $YorN = ["Y", "N"];
    foreach ($item as $i) {
        if (!in_array($i, $YorN)) {
            return false;
        }
    }
    return true;
}

function getEmailByAccessToken($accessToken)
{
    $url = 'https://kapi.kakao.com/v2/user/me';
    $headerParams = [];
    $headerParams[] = 'Authorization: Bearer ' . $accessToken;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerParams);
    $res = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($res, 0, $header_size);
    $body = substr($res, $header_size);
    curl_close($ch);
    return json_decode($body)->kakao_account->email;
}

function getNameByAccessToken($accessToken)
{
    $url = 'https://kapi.kakao.com/v2/user/me';
    $headerParams = [];
    $headerParams[] = 'Authorization: Bearer ' . $accessToken;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerParams);
    $res = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($res, 0, $header_size);
    $body = substr($res, $header_size);
    curl_close($ch);
    return json_decode($body)->properties->nickname;
}

?>