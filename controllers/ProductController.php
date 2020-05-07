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
         * API No. 0
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "test":
            http_response_code(200);
            $res->result = test();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testDetail":
            http_response_code(200);
            $res->result = testDetail($vars["testNo"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testPost":
            http_response_code(200);
            $res->result = testPost($req->name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

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

            // 페이지 넘버
            $page = $_GET['page'] * 10;

//          비회원의 추천상품 조회는 가장 잘 팔리는 순서대로 반환
            if (!isset($_SERVER['HTTP_X_ACCESS_TOKEN'])) {
                $res->result = getRecommendedProd(null);
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
            $parentsCategory = getLastestViewedCategoryParents($userIdx);

            // 최근 본 상품이 없다면, 그냥 비회원과 마찬가지로 조회한다.
            if ($parentsCategory == false) {
                $res->result = getRecommendedProd($userIdx);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 최근 본 상품이 있다면 추천해서 조회
            $res->result = getRecommendedProdByCate($userIdx, $parentsCategory);
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
            // 회원이 접속할때와 비회원이 들어올때를 나누자.
            if (!isset($_SERVER['HTTP_X_ACCESS_TOKEN'])) {
                $res->result = getNewProducts(null);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

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

            $res->result = getNewProducts($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;

        /*
* API No. 6
* API Name : 신상품 베스트 조회 API
* 마지막 수정 날짜 : 20.05.01
*/
        case "getNewBestProducts":
            http_response_code(200);
            // 회원이 접속할때와 비회원이 들어올때를 나누자.
            if (!isset($_SERVER['HTTP_X_ACCESS_TOKEN'])) {
                $res->result = getNewBestProducts(null);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

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

            $res->result = getNewBestProducts($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
* API No. 9
* API Name : 상품 상세조회 API
* 마지막 수정 날짜 : 20.05.02
*/
        case "getProductDetail":
            http_response_code(200);

            // path variable 유효성 검사
            $productIdx = $vars['productIdx'];
            if (!isValidProductIdx($productIdx)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "유효한 요청이 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 회원이라면 히스토리에 추가하기
            if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']) and isValidHeader($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY)) {
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
            if (!isValidProductIdx($productIdx)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "유효한 요청이 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if (isset($_GET['firstOption'])) {
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
* API No. 12
* API Name : 상품 찜 API
* 마지막 수정 날짜 : 20.05.04
*/
        case "createProductHearts":
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

            // path variable 유효성 검사
            $productIdx = $vars['productIdx'];
            if (!isValidProductIdx($productIdx)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "유효한 요청이 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $hearterIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

//            해당 유저가, 해당 게시글에 따봉을 누르고 있다면 삭제 프로세스
            if (isAlreadyHeart($hearterIdx, $productIdx)) {
                deleteHeart($hearterIdx, $productIdx);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "찜 삭제 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


//            아래 코드는 생성할때 만드는 거임.
            $drawerIdx = $req->drawerIdx;
            if ($drawerIdx == -1 or is_null($drawerIdx)) {
                $drawerIdx = -1;
            }


            // 서랍 검증
            if (!isValidDrawerIdx($hearterIdx, $drawerIdx)) {
                $res->isSuccess = false;
                $res->code = 202;
                $res->message = "서랍 인덱스를 확인하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            createProductHearts($hearterIdx, $productIdx, $drawerIdx);
            $result = [];
            $result['drawerIdx'] = $drawerIdx;
            $result['drawerName'] = getDrawerNameByDrawerIdx($hearterIdx, $drawerIdx);
            $res->isSuccess = $result;
            $res->code = 100;
            $res->message = "찜 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;

        /*
* API No. 13
* API Name : 서랍 생성 API
* 마지막 수정 날짜 : 20.05.04
*/
        case "createDrawer":
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
            $drawerIdx = getNextDrawerIdx($userIdx);

            // 서랍 이름 validation
            $drawerName = $req->drawerName;
            if (!is_string($drawerName)) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "drawerName 데이터 타입 확인해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            // 서랍 이름 중복 체크
            if (isRedundantDrawerName($drawerName, $userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "이미 중복되는 서랍 이름이 있어요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            createDrawer($userIdx, $drawerIdx, $drawerName);
            $result['drawerIdx'] = $drawerIdx;
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;

        /*
* API No. 14
* API Name : 서랍 목록 조회 API
* 마지막 수정 날짜 : 20.05.05
*/
        case "getDrawers":
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

            // 유저인덱스를 기반으로 자신의 서랍 목록을 조회한다.
            $result=getDrawersByUserIdx($userIdx);


            // 섬네일을 최대 4개, 리스트 형태로 반환해
            for ($i = 0; $i < sizeof($result); $i++) {
                $thumbList = explode(',', $result[$i]['thumbnailUrl']);
                if (sizeof($thumbList) > 4) {
                    $result[$i]['thumbnailUrl'] = array_slice($thumbList, 0, 4);
                } else {
                    $result[$i]['thumbnailUrl'] = $thumbList;
                }
            }


            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;


        /*
* API No. 18
* API Name : 서랍 상세 조회 API
* 마지막 수정 날짜 : 20.05.06
*/
        case "getDrawerDetail":
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

            // drawerIdx 검증
            if (!isValidDrawerIdx($userIdx, $vars['drawerIdx'])) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 서랍 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $drawerIdx = $vars['drawerIdx'];

            $result = [];
            $result['userIdx'] = $userIdx;
            $result['drawerIdx'] = $drawerIdx;
            $result['productCnt'] = getDrawerProductCnt($userIdx, $drawerIdx);
            $result['productInfo'] = getDrawerDetail($userIdx, $drawerIdx);

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;


        /*
* API No. 19
* API Name : 서랍 삭제 API
* 마지막 수정 날짜 : 20.05.06
*/
        case "deleteDrawer":
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
            $drawerIdx = $vars['drawerIdx'];

            // drawerIdx 검증
            if (!isValidDrawerIdx($userIdx, $drawerIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 서랍 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            // 기본 서랍은 삭제 불가
            if ($drawerIdx == -1) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "기본 서랍은 삭제할 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // ProductHeartDrawer에 있는 서랍 삭제
            deleteDrawer($userIdx, $drawerIdx);
            // ProductHeart에서 해당 서랍에 물건도 다 삭제
            deleteDrawerProducts($userIdx, $drawerIdx);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
