<?php
require 'function.php';
require 'LoginFunction.php';


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
            return;
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
            return;

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
                return;
            }

            // 유저 타입에 따른 처리
            switch ($req->userType) {
                // 일반 이메일 회원가입
                case "NORMAL":
                    // 이메일 중복 검사, 중복 시에 아이디, 비번 찾기로 이동함
                    if (isRedundantEmail($req->email)) {
                        $res->isSuccess = FALSE;
                        $res->code = 202;
                        $res->message = "이미 가입된 이메일입니다. 아이디, 비밀번호 찾기로 이동하시겠어요?";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 이메일 형식 검사
                    if (!isValidEmailForm($req->email)) {
                        $res->isSuccess = FALSE;
                        $res->code = 203;
                        $res->message = "이메일 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 비밀번호 검사 8~16의 문자열
                    if (!is_string($req->password) or !isValidPasswordForm($req->password)) {
                        $res->isSuccess = FALSE;
                        $res->code = 204;
                        $res->message = "비밀번호 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 이름 null 검사
                    if (is_null($req->name)) {
                        $res->isSuccess = FALSE;
                        $res->code = 205;
                        $res->message = "이름이 입력되지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 폰번호 중복 검사
                    if (isRedundantPhone($req->phone)){
                        $res->isSuccess = FALSE;
                        $res->code = 202;
                        $res->message = "이미 가입된 휴대폰입니다. 아이디, 비밀번호 찾기로 이동하시겠어요?";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 폰 번호 검사
                    if (!is_string($req->phone) or !isValidPhoneForm($req->phone)) {
                        $res->isSuccess = FALSE;
                        $res->code = 206;
                        $res->message = "휴대폰 번호 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 생년월일 검사
                    if (!isValidBirthForm($req->dateOfBirth)) {
                        $res->isSuccess = FALSE;
                        $res->code = 207;
                        $res->message = "생년월일 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 동의항목 검사
                    if (!isYorN([$req->AgreeOnService, $req->AgreeOnPrivate])) {
                        $res->isSuccess = FALSE;
                        $res->code = 208;
                        $res->message = "동의 항목은 Y 또는 N으로 해주세요.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 동의 여부 검사
                    if ($req->AgreeOnService == 'N' or $req->AgreeOnPrivate == 'N') {
                        $res->isSuccess = FALSE;
                        $res->code = 209;
                        $res->message = "필수 동의 항목에 체크해주세요.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
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
                    return;

                // 카카오 소셜 회원가입, 이메일 제공 동의 했다고 가정하고 하는거임.
                case "KAKAO":

                    // 엑세스 토큰을 이용해서 이메일을 알아낸다.
                    $accessToken = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                    // 토큰 정보 validation
                    $email = getEmailByAccessToken($accessToken);
                    $name = getNameByAccessToken($accessToken);


                    // 이미 가입된 회원인지 검사한다.
                    if (isRedundantEmail($email)) {
                        $res->isSuccess = FALSE;
                        $res->code = 202;
                        $res->message = "이미 가입된 이메일입니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // body 데이터 validation

                    // body의 email은 null이어야 한다.
                    if (!is_null($req->email)) {
                        $res->isSuccess = FALSE;
                        $res->code = 203;
                        $res->message = "소셜 로그인 요청시에는 email에 null을 넣어주세요.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // password도 null이어야 한다.
                    if (!is_null($req->password)) {
                        $res->isSuccess = FALSE;
                        $res->code = 203;
                        $res->message = "소셜 로그인 요청시에는 password에 null을 넣어주세요.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // name도 null이야 한다.
                    if (!is_null($req->name)) {
                        $res->isSuccess = FALSE;
                        $res->code = 203;
                        $res->message = "소셜 로그인 요청시에는 name null을 넣어주세요.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // phone 형식 검사
                    if (!is_string($req->phone) or !isValidPhoneForm($req->phone)) {
                        $res->isSuccess = FALSE;
                        $res->code = 206;
                        $res->message = "휴대폰 번호 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 생년월일 검사
                    if (!isValidBirthForm($req->dateOfBirth)) {
                        $res->isSuccess = FALSE;
                        $res->code = 207;
                        $res->message = "생년월일 형식이 올바르지 않습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 동의항목 검사
                    if (!isYorN([$req->AgreeOnService, $req->AgreeOnPrivate])) {
                        $res->isSuccess = FALSE;
                        $res->code = 208;
                        $res->message = "동의 항목은 Y 또는 N으로 해주세요.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    // 동의 여부 검사
                    if ($req->AgreeOnService == 'N' or $req->AgreeOnPrivate == 'N') {
                        $res->isSuccess = FALSE;
                        $res->code = 209;
                        $res->message = "필수 동의 항목에 체크해주세요.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    createUser($req->userType,
                        $email,
                        $req->password,
                        $name,
                        $req->phone,
                        $req->dateOfBirth,
                        $req->AgreeOnService,
                        $req->AgreeOnPrivate);
                    $res->isSuccess = true;
                    $res->code = 100;
                    $res->message = "회원가입 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;

                case "FACEBOOK":
                    echo "페이스북 소셜로그인 X";
                    return;

                case "NAVER":
                    echo "네이버 소셜로그인 X";
                    return;

            }
        /*
     * API No. 2
     * API Name : 로그인 API (로그인)
     * 마지막 수정 날짜 : 20.04.29
     */

        case "createLogin":

            http_response_code(200);


            // 유저 타입 검증
            if (!isValidUserType($req->userType)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유저 타입이 올바르지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            switch ($req->userType) {
                case "NORMAL":

                    if (!isValidUser($req->email, $req->password)) {
                        $res->code = 202;
                        $res->message = "가입되어 있지 않은 이메일입니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    $userIdx = getUserIdxByEmail($req->email);
                    $jwt = getJWToken($userIdx, $req->email, JWT_SECRET_KEY);
                    $result = [];
                    $result['userIdx'] = $userIdx;
                    $result['userName'] = getUserNameByUserIdx($userIdx);
                    $result['jwt'] = $jwt;

                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "로그인 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;

                case "KAKAO":
                    echo "카카오 로그인 구현중";
                    return;

                case "NAVER":
                    echo "우리 네이버 안하기로 했잖아,,";
                    return;

                case "FACEBOOK":
                    echo "우리 페이스북 안하기로 했잖아..";
                    return;

            }

        /*
     * API No. 3
     * API Name : 배너 API
     * 마지막 수정 날짜 : 20.04.29
     */
        case "getBanner":
            http_response_code(200);
            $res->result = getBanner();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
     * API No. 4
     * API Name : 추천 상품 조회 API
     * 마지막 수정 날짜 : 20.05.01
     */
        case "getRecommendedProducts":
            http_response_code(200);

//          비회원의 추천상품 조회는 가장 잘 팔리는 순서대로 반환
            if (!isset($_SERVER['HTTP_X_ACCESS_TOKEN'])) {
                $res->result = getRecommendedProd();
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

            // 회원의 경우 가장 최근에 본 상품의 카테고리의 부모 카테고리에 속한 아이템들 중에서 가장 많이 팔리는 순으로 반환
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            $category = getLastestViewedCategory($userIdx);

            // 최근 본 상품이 없다면, 그냥 비회원과 마찬가지로 조회한다.
            if ($category==false){
                $res->result = getRecommendedProd();
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 최근 본 상품이 있다면 추천해서 조회
            $res->result = getRecommendedProdByCate($category);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
* API No. 5
* API Name : 신상품 조회 API
* 마지막 수정 날짜 : 20.05.01
*/
        case "getNewProducts":
            http_response_code(200);
            $res->result = getNewProducts();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
* API No. 6
* API Name : 신상품 베스트 조회 API
* 마지막 수정 날짜 : 20.05.01
*/
        case "getNewBestProducts":
            http_response_code(200);
            $res->result = getNewBestProducts();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
* API No. 7
* API Name : 유저목록 조회 API
* 마지막 수정 날짜 : 20.05.01
*/
        case "getUsers":
            http_response_code(200);
            $res->result = getUsers();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
* API No. 8
* API Name : 토큰 검증 API
* 마지막 수정 날짜 : 20.05.02
*/
        case "validJwt":
            // jwt 유효성 검사


            if (!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $res->isSuccess = FALSE;
                $res->code = 202;
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
            http_response_code(200);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;


        /*
* API No. 9
* API Name : 상품 상세조회 API
* 마지막 수정 날짜 : 20.05.02
*/
        case "getProductDetail":
            http_response_code(200);

            // path variable 유효성 검사
            $productIdx = $vars['productIdx'];
            if (!isValidProductIdx($productIdx)){
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "유효한 요청이 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 회원이라면 히스토리에 추가하기
            if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']) and isValidHeader($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY)){
                $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
                $visitorIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
                createVisitHistory($visitorIdx, $productIdx);
            }

            $result = getProductDetail($productIdx);
            $result['mainImgUrlList'] = $mainImgUrlList = getMainImgListByProductIdx($productIdx);
            $result['normalImgUrlList'] = $normalImgUrlList = getNormalImgListByProductIdx($productIdx);

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;

        /*
* API No. 10
* API Name : 상품 옵션 조회 API
* 마지막 수정 날짜 : 20.05.03
*/
        case "getOptions":
            http_response_code(200);

            // path variable 유효성 검사
            $productIdx = $vars['productIdx'];
            if (!isValidProductIdx($productIdx)){
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "유효한 요청이 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if (isset($_GET['firstOption'])){
                $res->result = getSecondOptions($productIdx, $_GET['firstOption']);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $res->result = getOptions($productIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;


        /*
* API No. 101
* API Name : 상품 주문 API
* 마지막 수정 날짜 : 20.05.04
*/
        case "createOrder":
            http_response_code(200);

            // 토큰검증 및 userIdx 뽑아내기
            // res
            echo $req->fortest;
            echo $req->mother[0]->son1;


//            $res->result = getOptions();
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            return;
    }

} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
