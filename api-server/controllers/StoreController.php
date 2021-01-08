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

        /* **************** store ********************8 */
        case "test":
            echo 'ddd';
            $res->message = getTest();
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getHome":
            http_response_code(200);

            $arrayList = array();
            $storeIdx = getStoreLastIdx();
            while($storeIdx>0){
                $temp=array();
                if(isValidStore($storeIdx)){ // 솔직히 필요없어보여
                    if (!empty(getStoreOne($storeIdx))){ // 삭제해도되나
                        $temp=getStoreOne($storeIdx);

                        $i=0;
                        $img_arr=array();
                        $queryResult=getStoreImg($storeIdx);
                        while($i<count($queryResult)){
                            array_push($img_arr,$queryResult[$i++]['storePhoto']);
                        }
                        $temp['img_arr']=$img_arr;

                        array_push($arrayList,$temp);
                    }

                }
                $storeIdx = $storeIdx-1;

            }
            $res->result->promotion = getPromotion();
            $res->result->category = getCategory();
            $res->result->franchise = getFranchise();
            $res->result->openStore = getOpenStore();
            $res->result->mainStore = $arrayList; //getStore();
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "매장 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getChoiceStore":
            http_response_code(200);
            // 정렬 매장정렬
            // 치타배달 Y N
            //배달비 무료배달 1000 2000 3000 /전체가 디폴트
            //최소주문 5000 10000 12000 15000 이하 / 전체
            //할인쿠폰 할인쿠폰 유무
            //중복검색가능
            //$sort = $_GET['sort'];
            $cheetah = $_GET['cheetah'];
            $deliveryfee = $_GET['deliveryfee'];
            $mincost = $_GET['mincost'];
            $coupon = $_GET['coupon'];
//            echo $cheetah.'|'.$deliveryfee.'|'.$mincost.'|'.$coupon;
//            break;

//            if ($cheetah != 'Y' or $cheetah !='N' and !isnull($cheetah)){ //널 도가능
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = "치타 Y,N만 입력가능 ";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//
//            if ($mincost != 5000 or $mincost !=10000 or $mincost != 12000 or $mincost !=15000 and !isnull($mincost)){
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = "치타 Y,N만 입력가능 ";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }

        // 여기부터
//            $temp=getChoiceStoreOneCoupon(1,$cheetah,$deliveryfee,$mincost);
//            $res->result=$temp;
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            return;
            //$cheetah $deliveryfee $mincost $coupon
            $arrayList = array();
            $storeIdx = getStoreLastIdx();
            while($storeIdx>0){
                $temp=array();
                if(isValidStore($storeIdx)){ // 솔직히 필요없어보여 필요
                    if($coupon=='Y'){
                        if(!empty(filterAllCoupon($storeIdx,$cheetah,$deliveryfee,$mincost))){
                            $temp=filterAllCoupon($storeIdx,$cheetah,$deliveryfee,$mincost);
                            $i=0;
                            $img_arr=array();
                            $queryResult=getStoreImg($storeIdx);
                            while($i<count($queryResult)){
                                array_push($img_arr,$queryResult[$i++]['storePhoto']);
                            }
                            $temp['img']=$img_arr;
                            array_push($arrayList,$temp);
                        }
                    }
                    else{
                        if(!empty(filterAll($storeIdx,$cheetah,$deliveryfee,$mincost))){
                            $temp=filterAll($storeIdx,$cheetah,$deliveryfee,$mincost);
                            $i=0;
                            $img_arr=array();
                            $queryResult=getStoreImg($storeIdx);
                            while($i<count($queryResult)){
                                array_push($img_arr,$queryResult[$i++]['storePhoto']);
                            }
                            $temp['img']=$img_arr;
                            array_push($arrayList,$temp);
                        }

                    }
//                    if($coupon=='Y' & !empty(filterAllCoupon($storeIdx,$cheetah,$deliveryfee,$mincost))){
//                        $temp=filterAllCoupon($storeIdx,$cheetah,$deliveryfee,$mincost);
//                        $i=0;
//                        $img_arr=array();
//                        $queryResult=getStoreImg($storeIdx);
//                        while($i<count($queryResult)){
//                            array_push($img_arr,$queryResult[$i++]['storePhoto']);
//                        }
//                        $temp['img']=$img_arr;
//                        array_push($arrayList,$temp);
//                    }
//                    else if(!empty(filterAll($storeIdx,$cheetah,$deliveryfee,$mincost))){
//                        $temp=filterAll($storeIdx,$cheetah,$deliveryfee,$mincost);
//                        $i=0;
//                        $img_arr=array();
//                        $queryResult=getStoreImg($storeIdx);
//                        while($i<count($queryResult)){
//                            array_push($img_arr,$queryResult[$i++]['storePhoto']);
//                        }
//                        $temp['img']=$img_arr;
//                        array_push($arrayList,$temp);
//                    }

                    //원본
                   //if (!empty(getStoreOne($storeIdx))){ // 삭제해도되나 필요
//
//                        if($coupon=='Y'){
//                            $temp=getChoiceStoreOneCoupon($storeIdx,$cheetah,$deliveryfee,$mincost);
//
//                        }
//                        else {
//                            $temp=getChoiceStoreOne($storeIdx,$cheetah,$deliveryfee,$mincost);
//
//                        }
//                         //여기;
//                        $i=0;
//                        $img_arr=array();
//                        $queryResult=getStoreImg($storeIdx);
//                        while($i<count($queryResult)){
//                            array_push($img_arr,$queryResult[$i++]['storePhoto']);
//                        }
//                        $temp['img']=$img_arr;
//                        array_push($arrayList,$temp);
                   // }

                }
                $storeIdx = $storeIdx-1;
            }
            $res->result=$arrayList;



//            if ($sort == 1){
//                //주문많은순
//            }
//            else if ($sort == 2){
//                //가까운순
//                $arrayList = array();
//                $storeIdx = getStoreLastIdx();
//                while($storeIdx>0){
//                    $temp=array();
//                    if(isValidStore($storeIdx)){ // 솔직히 필요없어보여
//                        if (!empty(getChoiceStoreOne($storeIdx,$cheetah,$deliveryfee,$mincost,$mincost,$coupon))){ // 삭제해도되나
//                            $temp=getChoiceStoreOne($storeIdx,$cheetah,$deliveryfee,$mincost,$mincost,$coupon);
//                            $i=0;
//                            $img_arr=array();
//                            $queryResult=getStoreImg($storeIdx);
//                            while($i<count($queryResult)){
//                                array_push($img_arr,$queryResult[$i++]['storePhoto']);
//                            }
//                            $temp['img_arr']=$img_arr;
//                            array_push($arrayList,$temp);
//                        }
//
//                    }
//                    $storeIdx = $storeIdx-1;
//
//                }
//            }
//            else if($sort == 3){
//                //별점 높은순
//            }
//            else if($sort == 4){
//                //신규매장순
//            }
//            else{
//                // 정렬없음
//            }

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "홈조회(검색알고리즘) 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getStoreDetail":
            http_response_code(200);
            if(!isValidStore($vars['storeIdx'])){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지않은 매장입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $i=0;
            $img_arr=array();
            $queryResult=getStoreImg($vars['storeIdx']);
            while($i<count($queryResult)){
                array_push($img_arr,$queryResult[$i++]['storePhoto']);
            }


            $res->storePhoto = $img_arr;
            $res->storeInfo = getStoreInfo($vars['storeIdx']); // 스토어정보 하나씩 가져와야해
            $res->photoReview = getPhotoReview($vars['storeIdx']); // 포토리뷰

            $catCount= getCatCount($vars['storeIdx']);
            $catIdx=1;
            $arrayList = array();

            while($catCount>=$catIdx){
                $temp=array();

                $temp['categoryIdx']=$catIdx;
                $temp['categoryName']=getCatName($vars['storeIdx'],$catIdx);
                $temp['categoryDetail']=getCatDetail($vars['storeIdx'],$catIdx);
                $temp['menuList'] =getMenuCategory($vars['storeIdx'],$catIdx);
                array_push($arrayList,$temp);
                $catIdx++;
            }


            $res->categoryMenu=$arrayList;

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "매장 세부 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getFranchiseStore":
            http_response_code(200);
            //주문많은순
            $arrayList = array();
            $storeIdx = getFranchiseStoreLastIdx();
            while($storeIdx>0) {
                $temp = array();
                if (isValidStore($storeIdx)) { // 솔직히 필요없어보여
                    if (!empty(getStoreOne($storeIdx))) { // 삭제해도되나
                        $temp = getChoiceStoreOne($storeIdx);
                        $i = 0;
                        $img_arr = array();
                        $queryResult = getStoreImg($storeIdx);
                        while ($i < count($queryResult)) {
                            array_push($img_arr, $queryResult[$i++]['storePhoto']);
                        }
                        $temp['img_arr'] = $img_arr;
                        array_push($arrayList, $temp);
                    }

                }
                $storeIdx = $storeIdx - 1;

            }

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "인기프랜차이즈 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getMenuOption":
            http_response_code(200);
            if(!isValidMenu($vars['menuIdx'])){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지않은 메뉴입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            //메뉴 사진 , 메뉴설명 ,가격, 카테고리별 옵션, 가격, 카테고리명 카테고리인덱스

            $i=0;
            $img_arr=array();
            $queryResult=getMenuImg($vars['menuIdx']);
            while($i<count($queryResult)){
                array_push($img_arr,$queryResult[$i++]['menuPhoto']);
            }
            //메뉴이미지
            $res->menuPhoto = $img_arr;
            //메뉴정보
            $res->menuInfo = getMenuInfo($vars['menuIdx']); // 스토어정보 하나씩 가져와야해

            $catCount= getOptCatCount($vars['menuIdx']);
            $catIdx=1;
            $arrayList = array();
            while($catCount>=$catIdx){
                $temp=array();

                $temp['optCategoryIdx']=$catIdx;
                $temp['optCategoryName']=getOptCatName($vars['menuIdx'],$catIdx);
                $temp['optmenuList'] =getOptMenuCategory($vars['menuIdx'],$catIdx);
                array_push($arrayList,$temp);
                $catIdx++;
            }
            //옵션메뉴
            $res->optCategoryMenu=$arrayList;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "메뉴 세부 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createCart":
            http_response_code(200);
            $userIdxInToken=1;
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
//            $cart=$req->cart;
//            if (empty($cart)){
//                $res->isSuccess = FALSE;
//                $res->code = 2002;
//                $res->message = "카트에 넣을 값을 입력하세요.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                break;
//            }
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
            if(isValidCart($storeIdx,$menuIdx,$quantity,$userIdxInToken)) { //기존에 카트에 넣은것에서 비교해야대
                $res->isSuccess = FALSE;
                $res->code = 2014;
                $res->message = "중복된 메뉴";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            // 밸리데이션
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

//        case "createCart":
//            http_response_code(200);
//            $userIdxInToken=1;
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
//            $cart=$req->cart;
//            if (empty($cart)){
//                $res->isSuccess = FALSE;
//                $res->code = 2002;
//                $res->message = "카트에 넣을 값을 입력하세요.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                break;
//            }
//            if (!is_array($cart)){
//                $res->isSuccess = FALSE;
//                $res->code = 2003;
//                $res->message = "맞지 않는 데이터타입(카트)";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                break;
//            }
//            // 밸리데이션
//            $ii=1;
//            $storeIdx = getStoreIdx($cart[0]->menuIdx);
//            while(count($cart)>$ii) {
//                if($storeIdx!=getStoreIdx($cart[$ii++]->menuIdx))
//                    $res->isSuccess = FALSE;
//                    $res->code = 2013;
//                    $res->message = "같은 가게의 메뉴만 선택하세요.";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    addErrorLogs($errorLogs, $res, $req);
//                    return;
//            }
//            $i=0;
//            //$cartInfo=array();
//            while(count($cart)>$i) {
//                $menuIdx = $cart[$i]->menuIdx;
//                $quantity = $cart[$i]->quantity;
//                $optionList = $cart[$i]->optionList; // array로 출력됨
//
//                if (empty($menuIdx)){
//                    $res->isSuccess = FALSE;
//                    $res->code = 2004;
//                    $res->message = "메뉴를 입력하세요.";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    addErrorLogs($errorLogs, $res, $req);
//                    return;
//                }
//                if (!is_numeric($menuIdx)){
//                    $res->isSuccess = FALSE;
//                    $res->code = 2005;
//                    $res->message = "맞지 않는 데이터타입(메뉴)";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    addErrorLogs($errorLogs, $res, $req);
//                    return;
//                }
//                if (!is_numeric($quantity)){
//                    $res->isSuccess = FALSE;
//                    $res->code = 2006;
//                    $res->message = "맞지 않는 데이터타입(수량)";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    addErrorLogs($errorLogs, $res, $req);
//                    return;
//                }
////                $cartInfo[$i]= addCart($userIdxInToken,$menuIdx,$quantity);
//                // 얘 위치도 바꿔야대!!!
//
//
//                //1. 들어온 메뉴옵션필수카테고리갯수와 저위에서 만든 필수선택카테고리 리스트갯수와 동일한지
//                // -> 메뉴옵션인덱스로 필수카테고리인덱스 리스트만들기
//                // -> 메뉴인덱스로 필수카테고리 인덱스 만들기
//
//                //메뉴 필수카테고리 리스트 $mandatoryCat
//                $i = 0;
//                $mandatoryCat = array();
//                $queryResult = mandatoryCat($menuIdx);
//                while ($i < count($queryResult)) {
//                    array_push($mandatoryCat, $queryResult[$i++]['optCatIdx']);
//                }
//
//                // 옵션리스트로 필수카테고리리스트 만들기 $inputMandatoryCat
//                $l=0;
//                $inputMandatoryCat=array();
//                while(count($optionList)>$l) {
//                    array_push($inputMandatoryCat,mandatoryCatOne($menuIdx,$optionList[$l++]));
//                    // 옵션인덱스 에 해당하는 카테고리
//                }
//                $inputMandatoryCat=array_unique($inputMandatoryCat);
//
//                // -> 메뉴옵션인덱스로 카테고리별로 몇개 담았는지 출력
//                if ($mandatoryCat>$inputMandatoryCat ){
//                    $res->isSuccess = FALSE;
//                    $res->code = 2010;
//                    $res->message = "옵션 필수선택 하세요";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    addErrorLogs($errorLogs, $res, $req);
//                    return;
//                }
//                if ($mandatoryCat<$inputMandatoryCat ){
//                    $res->isSuccess = FALSE;
//                    $res->code = 2012;
//                    $res->message = "잘못된 옵션값을 선택했습니다.";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    addErrorLogs($errorLogs, $res, $req);
//                    return;
//                }
//
//                // maxselect
//                //2. 카테고리안에서 맞지않는 개수로 선택했을때 맞는 개수만큼 입력하세요
//                // -> 메뉴옵션인덱스로 카테고리별로 몇개 담았는지 출력
//                // -> 메뉴옵션인덱스로 해당카테고리 맥스 셀렉트수 가져오기
//
//                // 새로
//                if(!empty($optionList)){
//                    if (!is_array($optionList)) {
//                        $res->isSuccess = FALSE;
//                        $res->code = 2007;
//                        $res->message = "맞지 않는 데이터타입(옵션리스트)";
//                        echo json_encode($res, JSON_NUMERIC_CHECK);
//                        addErrorLogs($errorLogs, $res, $req);
//                        return;
//                    }
//                    $j=0;
//                    //$option=array();
//                    while(count($optionList)>$j){
//                        if (!is_numeric($optionList[$j])){
//                            $res->isSuccess = FALSE;
//                            $res->code = 2008;
//                            $res->message = "맞지 않는 데이터타입(옵션인덱스)";
//                            echo json_encode($res, JSON_NUMERIC_CHECK);
//                            addErrorLogs($errorLogs, $res, $req);
//                            return;
//                        }
//                        $j++;
//                    }
//                    $m=0;
//                    $inputOptCat=array();
//                    while(count($optionList)>$m) {
//                        array_push($inputOptCat,getInputOptCat($menuIdx,$optionList[$m++]));
//                        // 옵션인덱스 에 해당하는 카테고리
//                    }
//                    $inputOptCat=array_count_values($inputOptCat);
//                    foreach($inputOptCat as $key=>$value){
//                        if($value!=getMaxSelect($menuIdx,$key)){
//                            $res->isSuccess = FALSE;
//                            $res->code = 2011;
//                            $res->message = "맞는 개수만큼 선택하세요.(옵션)";
//                            echo json_encode($res, JSON_NUMERIC_CHECK);
//                            addErrorLogs($errorLogs, $res, $req);
//                            return;
//                        }
//                    }
//                }
//
//                $i++;
//            }
//
//            // 삽입코드
//            $i=0;
//            $cartInfo=array();
//            while(count($cart)>$i) {
//                $menuIdx = $cart[$i]->menuIdx;
//                $quantity = $cart[$i]->quantity;
//                $optionList = $cart[$i]->optionList; // array로 출력됨
//
//                $cartInfo[$i]= addCart($userIdxInToken,$menuIdx,$quantity);
//                if (!empty($optionList)){
//                    $j=0;
//                    $option=array();
//                    while(count($optionList)>$j){
//                        $optIdx=addOptionCart($userIdxInToken,$menuIdx,$optionList[$j]);
//                        array_push($option,$optIdx);
//                        //출력부분만바꿔
//                        $j++;
//                    }
//                    $cartInfo[$i]['option']=$option;
//                }
//
//
//                $i++;
//            }
//
//            $res->result=$cartInfo;
//            $res->isSuccess = TRUE;
//            $res->code = 1000;
//            $res->message = "카트 담기 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;

        case "hartStore":
            http_response_code(200);
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
            // 가져온토큰을 어떻게 처리할지 넘기긴 하는데 밸리데이션
            if(!isValidStore($req->storeIdx)){ // product 테이블에 인덱스 있는지
                $res->isSuccess = False;
                $res->code = 2002;
                $res->message = "유효 하지 않은 인덱스";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            //$userIdx=1;

            if(isHart($req->storeIdx,$userIdxInToken)){
                $res->result=deleteHart($req->storeIdx,$userIdxInToken);
                $res->isSuccess = TRUE;
                $res->code = 1001;
                $res->message = "즐겨찾기 취소했습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = hartStore($req->storeIdx,$userIdxInToken);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "즐겨찾기 추가 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
