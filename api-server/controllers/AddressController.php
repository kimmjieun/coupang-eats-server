
<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json;charset=utf-8');
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

//        case "getCurrentAddress":
//            http_response_code(200);
//            if(empty($req->latitude)| empty($req->longitude)){
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = "위도, 경도를 입력하세요. ";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                break;
//            }
//            if(!isValidLatLon($req->latitude,$req->longitude)){
//                $res->isSuccess = FALSE;
//                $res->code = 2001;
//                $res->message = "해당 위치가 등록되어있지않습니다. ";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                break;
//            }
//            if(gettype($req->addressDetail)!='string'){
//                $res->isSuccess = FALSE;
//                $res->code = 2002;
//                $res->message = "맞지않는 데이터타입";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                break;
//            }
//            $res->result=getCurrentAddress($req->latitude,$req->longitude);
//            //$address=$result['address'].' '.$req->addressDetail;
//            //$buildingName=$result['buildingName'];
//            //updateDeliveryAddress($address,$buildingName);
//
//            $res->isSuccess = TRUE;
//            $res->code = 1000;
//            $res->message = "현재위치 주소 조회 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;

        case "setCurrentAddress":
            http_response_code(200);
            if(empty($req->latitude)| empty($req->longitude)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "위도, 경도를 입력하세요. ";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            if(!isValidLatLon($req->latitude,$req->longitude)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "해당 위치가 등록되어있지않습니다. ";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            if(gettype($req->addressDetail)!='string'){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "맞지않는 데이터타입";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $result=setCurrentAddress($req->latitude,$req->longitude);
            $address=$result['address'].' '.$req->addressDetail;
            $buildingName=$result['buildingName'];
            updateDeliveryAddress($address,$buildingName);

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "현재위치로 주소 설정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getKeywordAddress":
            http_response_code(200);
            $keyword = $_GET['keyword'];
            if(!isValidKeyword($keyword)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "검색결과가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $res->result=getKeywordAddress($keyword);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "키워드 주소 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "setSelectedAddress":
            http_response_code(200);
            $keyword = $_GET['keyword'];
            // addressIdx 입력받으면 그걸 등록
            if(empty($keyword)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "키워드를 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            if(empty($req->addressIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "주소인덱스를 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            if(gettype($req->addressDetail)!='string'|gettype($req->addressIdx)!='integer'){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "맞지않는 데이터타입";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(!isValidAddressIdx($keyword,$req->addressIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "키워드 조회되지 않은 인덱스";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            $result=getSelectedAddress($keyword,$req->addressIdx);
            $address=$result['address'].' '.$req->addressDetail;
            $buildingName=$result['buildingName'];
            updateDeliveryAddress($address,$buildingName);

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "키워드 주소 설정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}


