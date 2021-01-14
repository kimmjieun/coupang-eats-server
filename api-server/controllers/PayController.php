<?php
require 'function.php';
require 'BootpayApi.php';
//require 'Singleton.php';
require_once('autoload.php');
spl_autoload_register('BootpayAutoload');
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

        case "getAccessToken":
            http_response_code(200);

            $bootpay = BootpayApi::setConfig(
                "59a4d32b396fa607c2e75e00",
                "t3UENPWvsUort5WG0BFVk2+yBzmlt3UDvhDH2Uwp0oA="
            );

            $response = $bootpay->requestAccessToken();
            if ($response->status === 200) {
                $res->result->token = $response->data->token;
                $res->result->server_time = $response->data->server_time;
                $res->result->expired_at= $response->data->expired_at;
            }
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "액세스토큰 발급 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getCancellation":
            http_response_code(200);
            $receiptId=$req->receiptId;
            $orderIdx=$req->orderIdx;
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
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            if (empty($receiptId)) {
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "receiptId를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            if (empty($orderIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "주문인덱스를 입력하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }


            require_once('autoload.php');
            spl_autoload_register('BootpayAutoload');


            $bootpay = BootpayApi::setConfig(
                '59a4d32b396fa607c2e75e00',
                't3UENPWvsUort5WG0BFVk2+yBzmlt3UDvhDH2Uwp0oA='
            );

            $response = $bootpay->requestAccessToken();

            if ($response->status === 200) {
                echo '토큰받기성공';
                $orderInfo=getOrderIdx($orderIdx);
                if(empty($orderInfo)){
                    $res->isSuccess = FALSE;
                    $res->code = 4000;
                    $res->message = "주문정보를 가져올 수 없습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if($orderInfo[0]['orderState']!=1){
                    $res->isSuccess = FALSE;
                    $res->code = 2004;
                    $res->message = "주문취소할 수 없는 상태입니다.(주문수락,배달준비중,배달중,배달완료,주문취소)";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $name=getUserName($orderInfo[0]['userIdx']);
                $result = $bootpay->cancel($receiptId, $orderInfo[0]['orderPrice'], $name, '단순변심');
                //receiptId,가격,이름,취소사유-> 이값은 서버가넣는건지?
                // 결제 취소가 되었다면
                if ($result->status === 200) {
                    // 주문상태 바꾸기
                    // 주문 테이블 다 Y으로 바꾸기
                    deleteOrder($req->orderIdx);
                    $res->isSuccess = TRUE;
                    $res->code = 1000;
                    $res->message = "주문/결제 취소 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else{
                    $res->isSuccess = TRUE;
                    $res->code = 3000;
                    $res->message = "주문/결제 취소 실패";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            else{
                echo '실패';
                $res->isSuccess = TRUE;
                $res->code = 3001;
                $res->message = "status가 200이 아닙니다.(액세스토큰받기실패)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


        case "makeOrder":
            http_response_code(200);
            // 바디값 받아오기
            $toStore=$req->toStore;
            $noPlastic=$req->noPlastic;
            $deliveryReqIdx=$req->deliveryReqIdx;
            $receiptId = $req->receiptId;

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
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }

            if(empty($deliveryReqIdx)){
                $deliveryReqIdx=1;
            }
            if(!empty($noPlastic)){
                if($noPlastic!='Y'){
                    $res->isSuccess = FALSE;
                    $res->code = 2002;
                    $res->message = "플라스틱제외요청은 Y로만 해주세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            //주문금액구하기
            $cartResult =getCartList($userIdxInToken);
            if(empty($cartResult)){
                $res->isSuccess = FALSE;
                $res->code = 4000;
                $res->message = "카트에 값이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
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
            //배달비 받아오기
            $deliverFee=getDeliveryFee($userIdxInToken);
            if($deliverFee==-1){
                $deliverFee=0;
            }
            // 주문 정보 넣기
            $storeIdx=getStoreIdx($userIdxInToken)['storeIdx'];

            // 쿠폰 비용받아오기
            $couponLists=getCoupon($userIdxInToken,$storeIdx);
//            $couponIdx=$couponLists[0]['couponIdx'];

            if (!empty($couponLists[0]['minPrice'])){ // 최소주문액
                if($orderPrice>=$couponLists[0]['minPrice']){
                    $couponPrice=$couponLists[0]['salePrice'];
                }
                else{
                    $couponPrice=0;
                }
            }
            else{
                $couponPrice=$couponLists[0]['salePrice'];
            }


            if(empty($couponPrice)){
                $couponPrice=0;
            }

            // 총금액
            $totalPrice=$orderPrice+$deliverFee-$couponPrice;
            // 결제정보 받아오기
            $paymentIdx=getPayment($userIdxInToken)['paymentIdx'];//결제인덱스
            if(empty($paymentIdx)){
                $res->isSuccess = FALSE;
                $res->code = 4001;
                $res->message = "결제수단을 설정하세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            //메뉴정보
            $orderMenu=getCart($userIdxInToken);
            if(empty($orderMenu)){
                $res->isSuccess = FALSE;
                $res->code = 4002;
                $res->message = "카트에서 메뉴를 가져올 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            //메뉴옵션정보
            $orderMenuOption=getCartOption($userIdxInToken);

            $orderState=1;



            $storeInfo=getStore($userIdxInToken);
            if(empty($storeInfo)){
                $res->isSuccess = FALSE;
                $res->code = 4003;
                $res->message = "최소주문금액정보가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $minOrderCost=$storeInfo[0]['minOrderCost'];
            if($totalPrice<$minOrderCost){
                $res->isSuccess = FALSE;
                $res->code = 4004;
                $res->message = "최소주문금액을 맞추세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
//            $res->result->storeIdx=$storeIdx;
//            $res->result->userIdxInToken=$userIdxInToken;
//            $res->result->paymentIdx=$paymentIdx;
//            $res->result->totalPrice=$totalPrice;
//            $res->result->orderState=$orderState;
//            $res->result->orderMenu=$orderMenu;
//            $res->result->orderMenuOption=$orderMenuOption;
//            $res->isSuccess = FALSE;
//            $res->code = 4004;
//            $res->message = "주문정보조회성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;

//            echo 'ddd';

//            require_once('autoload.php');
//            spl_autoload_register('BootpayAutoload');

            $bootpay = BootpayApi::setConfig(
                '',
                ''
            );

//            echo 'dddd2';
            $response = $bootpay->requestAccessToken();

//            echo 'ddd3';


            if ($response->status === 200) {
                $result = $bootpay->verify($receiptId);
//                echo $result->data->price.'|'.$totalPrice;
//                var_dump($result);
//                return;
                // 원래 주문했던 금액이 일치하는가?
                // 그리고 결제 상태가 완료 상태인가?
                if ($result->data->price === $totalPrice && $result->data->status === 1) {
                    // TODO: 이곳이 상품 지급 혹은 결제 완료 처리를 하는 로직으로 사용하면 됩니다.
                    $orderIdx=addOrderInfo($storeIdx,$userIdxInToken,$paymentIdx,$totalPrice,$toStore,$noPlastic,$deliveryReqIdx,$orderState,
                        $orderMenu,$orderMenuOption); //주문정보 주문테이블에 저장하고 인덱스얻기
                    $res->result->orderIdx =$orderIdx;
                    $res->isSuccess = TRUE;
                    $res->code = 1000;
                    $res->message = "주문하기 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else{
                    $res->isSuccess = FALSE;
                    $res->code = 3001;
                    $res->message = "결제금액이 일치하지않거나 status가 1이 아닙니다.(검증실패)";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            else{
                $res->isSuccess = TRUE;
                $res->code = 3002;
                $res->message = "부트페이 access token 발행 실패";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }





    }
} catch (\Exception $e) {
return getSQLErrorException($errorLogs, $e, $req);
}