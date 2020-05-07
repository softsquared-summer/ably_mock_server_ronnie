<?php
require 'function.php';
require 'LoginFunction.php';
require 'OrderFunction.php';

const JWT_SECRET_KEY = "Ronnie's Secret key";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
        /*
* API No. 11
* API Name : 상품 주문 API
* 마지막 수정 날짜 : 20.05.04
*/
        case "createOrder":
            http_response_code(200);

            // 토큰 검증하고 userIdx 뽑아내기

            if (!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "토큰을 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            $orderIdx = getNextOrderIdx($userIdx);
            $productInfo = $req->productInfo;
            $paymentType = $req->paymentType;
            $refundBank = $req->refundBank;
            $refundOwner = $req->refundOwner;
            $refundAccount = $req->refundAccount;
            $receiver = $req->receiver;
            $postalCode = $req->postalCode;
            $address = $req->address;
            $detailedAddress = $req->detailedAddress;
            $phone = $req->phone;
            $message = $req->message;
            $depositBank = $req->depositBank;
            $depositor = $req->depositor;
            $cashReceipt = $req->cashReceipt;


//            validation

            // 추후에 detailedProductIdx 중복성 체크하기

            if (!isValidProductInfo($productInfo) or isRedundantProductInfo($productInfo)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "상품정보가 올바르지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 결제방법 validation
            if (!isValidPaymentType($paymentType)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "결제 유형이 올바르지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 환불 은행 validation
            if (!isValidRefundBank($refundBank)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "은행명 올바르지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 환블 예금주, 환블 계좌 validation
            // 예금주는 string 값인지 체크, 계좌는 번호만 입력할것
            if (!is_string($refundOwner)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "refundOwner 데이터 타입 체크";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            if (!preg_match("/^[0-9]/i", $refundAccount)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "환불 계좌는 번호만 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

//             수령자 체크
            if (!is_string($receiver)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "receiver 데이터 타입 체크";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

//            우편번호, 주소, 상세주소, 연락처, 메세지
            if (!preg_match("/^[0-9]/i", $postalCode)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "우편번호는 번호만 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 주소는 일단 스트링인지만 체크
            if (!is_string($address) or !is_string($detailedAddress)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "주소는 문자열로 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 연락처
            if (!isValidPhoneForm($phone)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "휴대폰 번호는 숫자만 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 메세지
            if (!is_string($message)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "배송 메세지는 문자열로 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 무통장 계좌
            if ($paymentType == "DEPOSIT" and !isValidDepositBank($depositBank)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "입금 은행을 확인하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 입금자
            if ($paymentType == "DEPOSIT" and !is_string($depositor)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "입금자 명을 확인하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 현금 영수증
            if ($paymentType == "DEPOSIT" and !isValidCashReceipt($cashReceipt)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "현금 영수증 여부를 확인하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            // Orders 테이블에 데이터 넣기, 재고 빼기
            foreach ($productInfo as $item) {
                $detailedProductIdx = $item->detailedProductIdx;
                $number = $item->number;

                if ($paymentType == 'DEPOSIT') {
                    $orderStatus = 100;
                } else {
                    $orderStatus = 110;
                }

                createOrderInfo($orderIdx, $userIdx, $detailedProductIdx, $number, $paymentType, $refundBank, $refundOwner, $refundAccount, $depositBank, $depositor, $cashReceipt, $orderStatus);
                takeOutStock($number, $detailedProductIdx);
            }

            //  Delivery 테이블에 데이터 넣기
            createDeliveryInfo($orderIdx, $userIdx, $detailedProductIdx, $receiver, $postalCode, $address, $detailedAddress, $phone, $message);


            $result = [];
            $result['orderNum'] = createOrderNum(date("Ymd"), $orderIdx, $userIdx);
            $res->result = $result;
            $res->isSuccess = true;
            $res->code = 100;
            $res->message = "주문 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;

        /*
* API No. 15
* API Name : 주문 상세 조회 API
* 마지막 수정 날짜 : 20.05.05
*/
        case "getOrderDetail":
            http_response_code(200);

            // 토큰 검사
            if (!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "토큰을 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            // orderNum은 20200505-1-1 각각 날짜-주문인덱스-유저인덱스
            $orderNum = $vars['orderNum'];
            $orderDate = explode('-', $orderNum)[0];
            $orderIdx = explode('-', $orderNum)[1];
            $orderUserIdx = explode('-', $orderNum)[2];


            if ($orderUserIdx != $userIdx) {
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "조회 권한이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


//            $res->result->drawerIdx = $drawerIdx;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;

        /*
* API No. 16
* API Name : 주문 목록 조회 API
* 마지막 수정 날짜 : 20.05.05
*/
        case "getOrders":
            http_response_code(200);

            // 토큰 검사
            if (!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "토큰을 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            $result = [];
            $result = getShippingInfoByUserIDx($userIdx);

            // 유저 인덱스로 주문 번호와, 날짜를 뽑아내보자
            $orderInfo = getOrderNumDateByUserIdx($userIdx);
            if (empty($orderInfo)) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "주문목록이 없어요!";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 각 주문 번호에 해당하는 상품 정보를 추가하자.
            for ($i = 0; $i < sizeof($orderInfo); $i++) {
                $orderNum = $orderInfo[$i]['orderNum'];
                $orderInfo[$i]['productInfo'] = getProductInfoByOrderNum($orderNum);
            }

            $orderInfo = array_reverse($orderInfo);
            $result['orderInfo'] = $orderInfo;

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;


        /*
* API No. 17
* API Name : 주문 상태 변경 API
* 마지막 수정 날짜 : 20.05.06
*/
        case "modifyOrderStatus":
            http_response_code(200);

            // 주문번호 검증
            $orderNum = $vars['orderNum'];

            if (!isValidOrderNum($orderNum)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "올바른 주문번호가 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 변경하고 싶은 상태
            $reqStatusName = $req->statusName;
            // 상태명 validation
            if (!isValidOrderName($reqStatusName)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "올바른 상태명이 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            // 현재 상태에 따라 validation할 것
            $nowStatusCode = getOrderStatusByOrderNum($orderNum);
            $reqStatusCode = getStatusCodeByStatusName($reqStatusName);


            switch ($reqStatusName) {
                case "취소 요청":
                    if ($nowStatusCode >= 200) {
                        $res->isSuccess = false;
                        $res->code = 200;
                        $res->message = "배송 중에는 취소 요청이 불가능합니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        exit;
                    }
                    break;
                case "반품 요청":
                    if ($nowStatusCode < 200) {
                        $res->isSuccess = false;
                        $res->code = 200;
                        $res->message = "반품 요청은 배송 시작 이후부터 가능합니다.";
                        echojson_encode($res, JSON_NUMERIC_CHECK);
                        exit;
                    }
                    break;

                default:
                    break;
            }

            updateStatusCode($orderNum, $reqStatusCode);
            $result = [];
            $result['orderNum'] = $orderNum;
            $result['statusName'] = $reqStatusName;
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "요청하신 상태로 변경했습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
