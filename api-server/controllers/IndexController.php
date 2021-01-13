<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
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

        case "getPayMethod":
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
                break;
            }
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $res->result = getPayMethod($userIdxInToken);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "결제수단 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

//        case "getUsers":
//            http_response_code(200);
//
////            $res->result = getUsers();
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "테스트 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;
//        /*
//         * API No. 5
//         * API Name : 테스트 Path Variable API
//         * 마지막 수정 날짜 : 19.04.29
//         */
//        case "getUserDetail":
//            http_response_code(200);
//
//            $res->result = getUserDetail($vars["userIdx"]);
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "테스트 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;
//        /*
//         * API No. 6
//         * API Name : 테스트 Body & Insert API
//         * 마지막 수정 날짜 : 19.04.29
//         */
//        case "createUser":
//            http_response_code(200);
//
//            // Packet의 Body에서 데이터를 파싱합니다.
//           // $userID = $req->userID;
//           // $pwd_hash = password_hash($req->pwd, PASSWORD_DEFAULT); // Password Hash
//            $name = $req->name;
//
//            $res->result = createUser($name);
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "테스트 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
