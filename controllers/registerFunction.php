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

?>