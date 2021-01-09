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

        case "putInCart":
            http_response_code(200);
            $userIdxInToken=14;
//            // prod올릴때 주석해제
////            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
////            $userIdxInToken = getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
////            if (empty($jwt)){
////                $res->isSuccess = FALSE;
////                $res->code = 2000;
////                $res->message = "토큰을 입력하세요.";
////                echo json_encode($res, JSON_NUMERIC_CHECK);
////                addErrorLogs($errorLogs, $res, $req);
////                break;
////            }
////            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
////                $res->isSuccess = FALSE;
////                $res->code = 2001;
////                $res->message = "유효하지 않은 토큰입니다.";
////                echo json_encode($res, JSON_NUMERIC_CHECK);
////                addErrorLogs($errorLogs, $res, $req);
////                break;
////            }

            $storeIdx = $req->storeIdx;
            $menuIdx = $req->menuIdx;
            $quantity = $req->quantity;
            $optionList = $req->optionList;
            if (empty($quantity)){
                $quantity=1;
            }
            // 다른 스토어꺼면 같은 가게메뉴만 담을수있다
            if (empty($storeIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2015;
                $res->message = "매장을 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isDifferentStore($storeIdx,$userIdxInToken) & isCartInUser($userIdxInToken)) { //기존에 카트에 넣은것에서 비교해야대
                $res->isSuccess = FALSE;
                $res->code = 2013;
                $res->message = "같은 가게의 메뉴만 선택하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            // 이거 추가해야해 
//            if(isValidCart($storeIdx,$menuIdx,$quantity,$userIdxInToken)) { //기존에 카트에 넣은것에서 비교해야대
//                $res->isSuccess = FALSE;
//                $res->code = 2014;
//                $res->message = "중복된 메뉴";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                return;
//            }
//             밸리데이션
            if (empty($menuIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "메뉴를 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_numeric($menuIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "맞지 않는 데이터타입(메뉴)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_numeric($quantity)& !empty($quantity)){
                $res->isSuccess = FALSE;
                $res->code = 2006;
                $res->message = "맞지 않는 데이터타입(수량)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //메뉴 필수카테고리 리스트 $mandatoryCat
            $i = 0;
            $mandatoryCat = array();
            $queryResult = mandatoryCat($menuIdx);
            while ($i < count($queryResult)) {
                array_push($mandatoryCat, $queryResult[$i++]['optCatIdx']);
            }
            // 옵션리스트로 필수카테고리리스트 만들기 $inputMandatoryCat
            $l=0;
            $inputMandatoryCat=array();
            while(count($optionList)>$l) {
                array_push($inputMandatoryCat,mandatoryCatOne($menuIdx,$optionList[$l++]));
                // 옵션인덱스 에 해당하는 카테고리
            }
            $inputMandatoryCat=array_unique($inputMandatoryCat);

            // -> 메뉴옵션인덱스로 카테고리별로 몇개 담았는지 출력
            if ($mandatoryCat>$inputMandatoryCat ){
                $res->isSuccess = FALSE;
                $res->code = 2010;
                $res->message = "옵션 필수선택 하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($mandatoryCat<$inputMandatoryCat ){
                $res->isSuccess = FALSE;
                $res->code = 2012;
                $res->message = "잘못된 옵션값을 선택했습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!empty($optionList)){
                if (!is_array($optionList)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2007;
                    $res->message = "맞지 않는 데이터타입(옵션리스트)";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $j=0;
                //$option=array();
                while(count($optionList)>$j){
                    if (!is_numeric($optionList[$j])){
                        $res->isSuccess = FALSE;
                        $res->code = 2008;
                        $res->message = "맞지 않는 데이터타입(옵션인덱스)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    $j++;
                }
                $m=0;
                $inputOptCat=array();
                while(count($optionList)>$m) {
                    array_push($inputOptCat,getInputOptCat($menuIdx,$optionList[$m++]));
                    // 옵션인덱스 에 해당하는 카테고리
                }
                $inputOptCat=array_count_values($inputOptCat);

                foreach($inputOptCat as $key=>$value){
                    if($value!=getMaxSelect($menuIdx,$key)){
                        $res->isSuccess = FALSE;
                        $res->code = 2011;
                        $res->message = "맞는 개수만큼 선택하세요.(옵션)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }

            }
            $cartInfo=array();
            $cartInfo=addCart($userIdxInToken,$menuIdx,$quantity,$storeIdx);
            if (!empty($optionList)){
                $j=0;
                $option=array();
                while(count($optionList)>$j){
                    $optIdx=addOptionCart($userIdxInToken,$menuIdx,$optionList[$j]);
                    array_push($option,$optIdx);
                    //출력부분만바꿔
                    $j++;
                }
                $cartInfo['option']=$option;
            }


            $res->result=$cartInfo;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "카트 담기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getCart":
            http_response_code(200);
            $userIdxInToken=14;
        // prod올릴때 주석해제
//            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
//            $userIdxInToken = getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
//            if (empty($jwt)){
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = "토큰을 입력하세요.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                break;
//            }
//            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
//                $res->isSuccess = FALSE;
//                $res->code = 2001;
//                $res->message = "유효하지 않은 토큰입니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                break;
//            }
//            $res->result=$cartInfo;
            // 로그인되어있을때 주소설정안되어있으면
            // 유저 주소정보 건물명, 주소풀네임

            // 카트에 담은 메뉴 가게이름 , 1. ~2. ~ 3. ~ 제목, 옵션 ,
            // 할인쿠폰 내가 소유한것과 가게쿠폰이랑 같으면 즉시적용 아니면 선택하기
            // 총 주문금액
            // 배달비 할인쿠폰 가격
            //총금액
            // 요청사항 입력 api
            // 결제수단
            // 결제수단 수정하기 api
            // 결제하기 api
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "카트 보기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}