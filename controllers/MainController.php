<?php
require 'function.php';
require 'registerFunction.php';


const JWT_SECRET_KEY = "Ronnie's Secret key";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
         * API No. 0
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 19.04.25
         */
        case "validateJwt":
            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            http_response_code(200);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "createJwt":
            // jwt 유효성 검사
            http_response_code(200);

            if (!isValidUser($req->id, $req->pw)) {
                $res->isSuccess = FALSE;
                $res->code = 100;
                $res->message = "유효하지 않은 아이디 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            //페이로드에 맞게 다시 설정 요함
            $jwt = getJWToken($req->id, $req->pw, JWT_SECRET_KEY);
            $res->result->jwt = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

//            ====================================================

//    에이블리 코드

        /*
             * API No. 1
             * API Name : 회원가입 API (회원가입)
             * 마지막 수정 날짜 : 20.04.27
             */
        case "createUser":

            http_response_code(200);
            // 유저 타입 검증
            if (!isValidUserType($req->userType)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유저 타입이 올바르지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            switch ($req->userType) {
                case "NORMAL":
                    // 이메일 중복 검사, 중복 시에 아이디, 비번 찾기로 이동함
                    if (isRedundantEmail($req->email)) {
                        $res->isSuccess = FALSE;
                        $res->code = 202;
                        $res->message = "이미 가입된 이메일입니다. 아이디, 비밀번호 찾기로 이동하시겠어요?";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                    // 이메일 형식 검사
                    if (!isValidEmailForm($req->email)) {
                        $res->isSuccess = FALSE;
                        $res->code = 203;
                        $res->message = "이메일 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                    // 비밀번호 검사 8~16의 문자열
                    if (!is_string($req->password) or !isValidPasswordForm($req->password)) {
                        $res->isSuccess = FALSE;
                        $res->code = 204;
                        $res->message = "비밀번호 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                    // 이름 null 검사
                    if (is_null($req->name)){
                        $res->isSuccess = FALSE;
                        $res->code = 205;
                        $res->message = "이름이 입력되지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                    // 폰 번호 검사
                    if (!is_string($req->phone) or !isValidPhoneForm($req->phone)) {
                        $res->isSuccess = FALSE;
                        $res->code = 206;
                        $res->message = "휴대폰 번호 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                    // 생년월일 검사
                    if (!isValidBirthForm($req->dateOfBirth)) {
                        $res->isSuccess = FALSE;
                        $res->code = 207;
                        $res->message = "생년월일 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                    // 동의항목 검사
                    if (!isYorN([$req->AgreeOnService, $req->AgreeOnPrivate])) {
                        $res->isSuccess = FALSE;
                        $res->code = 208;
                        $res->message = "동의 항목은 Y 또는 N으로 해주세요.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                    // 동의 여부 검사
                    if ($req->AgreeOnService == 'N' or $req->AgreeOnPrivate == 'N') {
                        $res->isSuccess = FALSE;
                        $res->code = 209;
                        $res->message = "필수 동의 항목에 체크해주세요.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                    // 회원가입 DB 삽입
                    createUser($req->userType,
                        $req->email,
                        $req->password,
                        $req->name,
                        $req->phone,
                        $req->dateOfBirth,
                        $req->AgreeOnService,
                        $req->AgreeOnPrivate);

                    // jwt 발급
                    $userIdx = getUserIdxByEmail($req->email);

                    $jwt = getJWToken($userIdx, $req->email, $req->password, JWT_SECRET_KEY);

                    $res->result = $jwt;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "회원가입 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                case "KAKAO":
                    echo "카카오 소셜회원가입";
                    break;

                case "FACEBOOK":
                    echo "페이스북 소셜로그인 X";
                    break;

                case "NAVER":
                    echo "네이버 소셜로그인 X";
                    break;

            }


    }

} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
