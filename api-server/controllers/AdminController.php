<?php
require 'function.php';
require 'BootpayApi.php';
//require 'Singleton.php';
use Bootpay\Rest\BootpayApi;

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

        case "setOrderState":
            http_response_code(200);
            $orderState=$req->orderState;
            $adminIdx=$req->adminIdx;
            $orderIdx=$req->orderIdx;

            if(empty($orderState)|empty($adminIdx)|empty($orderIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "주문상태, 관리자인덱스, 주문인덱스를 모두 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $orderStateList=[1,2,3,4,5,6];
            if(!in_array($orderState, $orderStateList)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "주문상태는 1,2,3,4,5,6만 입력할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidAdminIdx($adminIdx,$orderIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "주문인덱스에 접근권한이 없는 관리자입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            setOrderState($adminIdx,$orderIdx,$orderState);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "주문 상태 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;




    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}