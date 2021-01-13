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

        case "addCart":
            http_response_code(200);
//            $userIdxInToken=14;
//            // prod올릴때 주석해제
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
                $res->code = 2002;
                $res->message = "매장을 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isDifferentStore($storeIdx,$userIdxInToken) & isCartInUser($userIdxInToken)) { //기존에 카트에 넣은것에서 비교해야대
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "같은 가게의 메뉴만 선택하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            // 이거 추가해야해 
            if(isValidCart($storeIdx,$menuIdx,$quantity,$userIdxInToken)) { //기존에 카트에 넣은것에서 비교해야대
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "중복된 메뉴";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
//             밸리데이션
            if (empty($menuIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "메뉴를 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_numeric($menuIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2006;
                $res->message = "맞지 않는 데이터타입(메뉴)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_numeric($quantity)& !empty($quantity)){
                $res->isSuccess = FALSE;
                $res->code = 2007;
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
                if(isValidMandatoryCatOne($menuIdx,$optionList[$l])){
                    array_push($inputMandatoryCat,mandatoryCatOne($menuIdx,$optionList[$l]));
                    // 옵션인덱스 에 해당하는 카테고리
                }
                $l++;
            }
            $inputMandatoryCat=array_unique($inputMandatoryCat);

            // -> 메뉴옵션인덱스로 카테고리별로 몇개 담았는지 출력
            if ($mandatoryCat>$inputMandatoryCat ){
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "옵션 필수선택 하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($mandatoryCat<$inputMandatoryCat ){
                $res->isSuccess = FALSE;
                $res->code = 2009;
                $res->message = "잘못된 옵션값을 선택했습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!empty($optionList)){
                if (!is_array($optionList)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2010;
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
                        $res->code = 2011;
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
                        $res->code = 2012;
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

        case "addNewCart":
            http_response_code(200);
//            $userIdxInToken=14;
//            // prod올릴때 주석해제
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
                $res->code = 2002;
                $res->message = "매장을 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isDifferentStore($storeIdx,$userIdxInToken) & isCartInUser($userIdxInToken)) { //기존에 카트에 넣은것에서 비교해야대
                // 카트비우기
                deleteCart($userIdxInToken);
            }
            // 이거 추가해야해
            if(isValidCart($storeIdx,$menuIdx,$quantity,$userIdxInToken)) { //기존에 카트에 넣은것에서 비교해야대
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "중복된 메뉴";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
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
                if(isValidMandatoryCatOne($menuIdx,$optionList[$l])){
                    array_push($inputMandatoryCat,mandatoryCatOne($menuIdx,$optionList[$l]));
                    // 옵션인덱스 에 해당하는 카테고리
                }
                $l++;

            }
            $inputMandatoryCat=array_unique($inputMandatoryCat);

            // -> 메뉴옵션인덱스로 카테고리별로 몇개 담았는지 출력
//            echo $mandatoryCat.$inputMandatoryCat;
//            break;


            if (count($mandatoryCat)>count($inputMandatoryCat) ){
                $res->isSuccess = FALSE;
                $res->code = 2007;
                $res->message = "옵션 필수선택 하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if (count($mandatoryCat)<count($inputMandatoryCat)){
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "잘못된 옵션값을 선택했습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!empty($optionList)){
                if (!is_array($optionList)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2009;
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
                        $res->code = 2010;
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
            $res->message = "카트 새로 담기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getCart":
            http_response_code(200);
//            $userIdxInToken=14;
        // prod올릴때 주석해제
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
//            $res->result=$cartInfo;
            // 로그인되어있을때 주소설정안되어있으면
            // 유저 주소정보 건물명, 주소풀네임

            // 카트에 담은 메뉴 가게이ㅇㄷ , 1. ~2. ~ 3. ~ 제목, 옵션 ,
            // 할인쿠폰 내가 소유한것과 가게쿠폰이랑 같으면 즉시적용 아니면 선택하기
            // 총 주문금액
            // 배달비 할인쿠폰 가격
            //총금액
            // 요청사항 입력 api
            // 결제수단
            // 결제수단 수정하기 api
            // 결제하기 api-
            //최소주문이상해야함

            // 최종
            $cartResult =getCartList($userIdxInToken);
            $s = 0;
            $orderPrice=0;
            while ($s < count($cartResult)) {
                $menuIdx= $cartResult[$s]['menuIdx'];
                // 옵션
                $optionResult=getOption($menuIdx,$userIdxInToken);
                $i=0;
                $optionList= array();
                $optionPrice=0;
                while ($i< count($optionResult)) {
                    if(empty($optionResult[$i]['optPrice'])){
                        $optionName=$optionResult[$i]['menuOptName'];
                    }
                    else{
                        $optionName=$optionResult[$i]['menuOptName'].'(+'.number_format($optionResult[$i]['optPrice']).'원)';
                    }
                    $optionIdx=$optionResult[$i]['optIdx'];
                    $optionPrice+=getMenuOptionPrice($optionIdx);
                    array_push($optionList, $optionName);
                    $i++;
                }
                $cartResult[$s]['option']=$optionList;


                //가격계산 -> 메뉴 + 옵션가
                $price=getMenuPrice($menuIdx);
                $menuPrice=$price+$optionPrice;
                $cartResult[$s]['price']=number_format($menuPrice).'원';
                $orderPrice+=$menuPrice;
                $s++;

            }
//            while ($i< count($optionResult)) {
//                $optionName=$optionResult[$i]['optionName'];
//                $optionIdx=$optionResult[$i]['optIdx'];
//                $optionPrice+=getMenuOptionPrice($optionIdx);
//                array_push($optionList, $optionName);
//                $i++;
//            }
            $deliverFee=getDeliveryFee($userIdxInToken);
            $couponPrice=getCoupon($userIdxInToken);
            if(empty($couponPrice)){
                $couponPrice=0;
            }
            $storeInfo=getStore($userIdxInToken);

            $res->deliveryaddress=getDeliveryAddress($userIdxInToken);
            $res->storeName=$storeInfo[0]['storeName'];
            $res->minOrderCost=number_format($storeInfo[0]['minOrderCost']).'원';
            $res->cartList= $cartResult; // 수량, 메뉴이름, 옵션이름(리스트로),총가격-서브쿼리
            $res->payPrice->orderPrice= number_format($orderPrice).'원';
            if($deliverFee==-1){
                $res->payPrice->deliveryFee=0;
            }
            else{
                $res->payPrice->deliveryFee= '+'.number_format($deliverFee).'원';
            }
            // 쿠폰부분다시
            if (!empty($couponPrice)){
                $res->payPrice->couponPrice='-'.number_format($couponPrice).'원';
            }
            $res->payPrice->TotalPrice= number_format($orderPrice+$deliverFee-$couponPrice).'원';
            $res->payment=getPayment($userIdxInToken);

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "카트 조회하기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


//        case "makeOrder":
//            http_response_code(200);
////            $userIdxInToken=14;
//            // prod올릴때 주석해제
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
//            // 바디값 받아오기
//            $toStore=$req->toStore;
//            $noPlastic=$req->noPlastic;
//            $deliveryReqIdx=$req->deliveryReqIdx;
//            if(empty($deliveryReqIdx)){
//                $deliveryReqIdx=1;
//            }
//            if(!empty($noPlastic)){
//                if($noPlastic!='Y'){
//                    $res->isSuccess = FALSE;
//                    $res->code = 2002;
//                    $res->message = "플라스틱제외요청은 Y로만 해주세요.";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    break;
//                }
//            }
//
//            //주문금액구하기
//            $cartResult =getCartList($userIdxInToken);
//            if(empty($cartResult)){
//                $res->isSuccess = FALSE;
//                $res->code = 3000;
//                $res->message = "카트에 값이 없습니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//            $s = 0;
//            $orderPrice=0;
//            while ($s < count($cartResult)) {
//                $menuIdx= $cartResult[$s]['menuIdx'];
//                // 옵션
//                $optionResult=getOption($menuIdx,$userIdxInToken);
//                $i=0;
//                $optionList= array();
//                $optionPrice=0;
//                while ($i< count($optionResult)) {
//                    if(empty($optionResult[$i]['optPrice'])){
//                        $optionName=$optionResult[$i]['menuOptName'];
//                    }
//                    else{
//                        $optionName=$optionResult[$i]['menuOptName'].'(+'.number_format($optionResult[$i]['optPrice']).'원)';
//                    }
//                    $optionIdx=$optionResult[$i]['optIdx'];
//                    $optionPrice+=getMenuOptionPrice($optionIdx);
//                    array_push($optionList, $optionName);
//                    $i++;
//                }
//                $cartResult[$s]['option']=$optionList;
//
//                //가격계산 -> 메뉴 + 옵션가
//                $price=getMenuPrice($menuIdx);
//                $menuPrice=$price+$optionPrice;
//                $cartResult[$s]['price']=number_format($menuPrice).'원';
//                $orderPrice+=$menuPrice;
//                $s++;
//            }
//            //배달비 받아오기
//            $deliverFee=getDeliveryFee($userIdxInToken);
//            if($deliverFee==-1){
//                $deliverFee=0;
//            }
//            // 쿠폰비용 받아오기
//            $couponPrice=getCoupon($userIdxInToken);
//            if(empty($couponPrice)){
//                $couponPrice=0;
//            }
//            // 총금액
//            $totalPrice=$orderPrice+$deliverFee-$couponPrice;
//            // 결제정보 받아오기
//            $paymentIdx=getPayment($userIdxInToken)['paymentIdx'];//결제인덱스
//            if(empty($paymentIdx)){
//                $res->isSuccess = FALSE;
//                $res->code = 3001;
//                $res->message = "결제수단을 설정하세요.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//            // 주문 정보 넣기
//            $storeIdx=getStoreIdx($userIdxInToken)['storeIdx'];
//            //메뉴정보
//            $orderMenu=getCart($userIdxInToken);
//            if(empty($orderMenu)){
//                $res->isSuccess = FALSE;
//                $res->code = 3002;
//                $res->message = "카트에서 메뉴를 가져올 수 없습니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//            //메뉴옵션정보
//            $orderMenuOption=getCartOption($userIdxInToken);
////            if(empty($orderMenuOption)){
////                $res->isSuccess = FALSE;
////                $res->code = 3003;
////                $res->message = "카트에서 메뉴옵션을 가져올 수 없습니다.";
////                echo json_encode($res, JSON_NUMERIC_CHECK);
////                break;
////            }
//            $orderState=1;
//
//
////            //주문하기 똑같은 값으로 또 요청한다면 이미 주문했습니다? 아니야 또 주문할수있잖아
//
//            $storeInfo=getStore($userIdxInToken);
//            if(empty($storeInfo)){
//                $res->isSuccess = FALSE;
//                $res->code = 3003;
//                $res->message = "최소주문금액정보가 없습니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//            $minOrderCost=$storeInfo[0]['minOrderCost'];
//            if($totalPrice<$minOrderCost){
//                $res->isSuccess = FALSE;
//                $res->code = 3004;
//                $res->message = "최소주문금액을 맞추세요.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            // 가격제한$orderIdx
//            $orderIdx=addOrderInfo($storeIdx,$userIdxInToken,$paymentIdx,$totalPrice,$toStore,$noPlastic,$deliveryReqIdx,$orderState,
//                $orderMenu,$orderMenuOption); //주문정보내고 인덱스얻기
//            $res->result->orderIdx =$orderIdx;
//            $res->isSuccess = TRUE;
//            $res->code = 1000;
//            $res->message = "주문하기 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;


        case "getOrderDetail":
            http_response_code(200);
//            $userIdxInToken=14;
            // prod올릴때 주석해제
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
            $category = isset($_GET['category']) ? $_GET['category'] : '';
            if(empty($category)){
                $res->isSuccess = TRUE;
                $res->code = 2000;
                $res->message = "주문내역 옵션을 입력하세요(1:과거주문내역 or 2:준비중)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($category==1){
                $orderStateList=[5,6];
            }
            else{
                $orderStateList=[1,2,3,4];
            }
            $orderInfo=getOrderInfo($userIdxInToken,$orderStateList);
            // 이 오더인포를 반복문으로 돌려서 오더인덱스에 해당하는 것들 붙이게끔
            $i=0;
            $menuList=array();
            while(count($orderInfo)>$i) {
                $orderList = array();
                $orderIdx = $orderInfo[$i]['orderIdx'];
                $orderMenuResult = getOrderMenu($orderIdx); //메뉴여러개
//                echo $orderMenuResult.'ddd';
                $j = 0;
                $optionList=array();
                while ($j < count($orderMenuResult)) {
                    $menuIdx = $orderMenuResult[$j]['menuIdx'];
                    $ordeMenuOptionList = getOrderMenuOption($orderIdx, $menuIdx);
                    $k=0;
                    $optionNameList=array();
                    while(count($ordeMenuOptionList)>$k){
                        $menuOptName=$ordeMenuOptionList[$k]['menuOptName'];
                        array_push($optionNameList,$menuOptName);
                        $k++;
                    }
                    $orderMenuResult[$j]['optionNameList']=$optionNameList;
                    $j++;
                }
                array_push($menuList,$orderMenuResult);
                $orderInfo[$i]['menuList']=$menuList;

                $i++;
            }

            $res->result = $orderInfo;

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "주문내역 조회하기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}