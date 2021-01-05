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

        case "getStore":
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
            $sort = $_GET['sort'];
            $cheetah = $_GET['cheetah'];
            $deliveryfee = $_GET['deliveryfee'];
            $mincost = $_GET['mincost'];
            $coupon = $_GET['coupon'];

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

            if($sort == 1){
                //주문많은순
                $arrayList = array();
                $storeIdx = getStoreLastIdx();
                while($storeIdx>0){
                    $temp=array();
                    if(isValidStore($storeIdx)){ // 솔직히 필요없어보여
                        if (!empty(getStoreOne($storeIdx))){ // 삭제해도되나
                            $temp=getChoiceStoreOne($storeIdx);
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
            }
            else if ($sort == 2){
                //가까운순
                $arrayList = array();
                $storeIdx = getStoreLastIdx();
                while($storeIdx>0){
                    $temp=array();
                    if(isValidStore($storeIdx)){ // 솔직히 필요없어보여
                        if (!empty(getChoiceStoreOne($storeIdx,$cheetah,$deliveryfee,$mincost,$mincost,$coupon))){ // 삭제해도되나
                            $temp=getChoiceStoreOne($storeIdx,$cheetah,$deliveryfee,$mincost,$mincost,$coupon);
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
            }
            else if($sort == 3){
                //별점 높은순
            }
            else if($sort == 4){
                //신규매장순
            }
            else{
                // 정렬없음
            }

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "골라먹는 맛집 조회 성공";
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

        case "getFranchiseStoreDetail":
            http_response_code(200);
//            if(!isValidStore($vars['storeIdx'])){
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = "유효하지않은 매장입니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
            if(!isValidFranchise($vars['storeIdx'])){
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
            $res->message = "인기프랜차이즈 세부 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getNewStoreDetail":
            http_response_code(200);
//            if(!isValidStore($vars['storeIdx'])){
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = "유효하지않은 매장입니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
            if(!isValidNewStore($vars['storeIdx'])){
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
            $res->message = "새로들어왔어요 세부 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
