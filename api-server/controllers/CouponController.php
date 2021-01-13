<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json;charset=utf-8');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
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

        case "receiveCoupon":
            http_response_code(200);
//            $userIdxInToken=14;
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken = getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if (empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰을 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isValidStore($vars['storeIdx'])){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지않은 매장인덱스";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(empty($req->couponIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "쿠폰인덱스를 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isValidUserCoupon($req->couponIdx,$userIdxInToken)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "이미 있는 쿠폰입니다. ";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = receiveCoupon($req->couponIdx,$userIdxInToken);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "쿠폰 받기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getUserCoupon":
            http_response_code(200);
//            $userIdxInToken=14;
//            echo $userIdxInToken;
//            break;
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken = getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if (empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰을 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isValidUserIdx($userIdxInToken)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지않은 유저인덱스";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getUserCoupon($userIdxInToken);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "쿠폰 조회하기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}