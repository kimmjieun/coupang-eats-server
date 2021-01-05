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
            $res->message = getTest();
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getStore":
            http_response_code(200);

            $arrayList = array();
            $storeIdx = getStoreLastIdx();
            while($storeIdx>0){
                $temp=(Object)array();
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


//        case "getStoreDetail":
//            http_response_code(200);
//
//            $i=0;
//            $img_arr=array();
//            $queryResult=getStoreImg($vars['storeIdx']);
//            while($i<count($queryResult)){
//                array_push($img_arr,$queryResult[$i++]['storePhoto']);
//            }
//            $res->result->storePhoto = $img_arr;
//            $res->result->storeInfo = getStoreInfo($vars['storeIdx']); // 스토어정보 하나씩 가져와야해
//            $res->result->photoReview = getPhotoReview($vars['storeIdx']); // 포토리뷰
//            // 카테고리 반복문하고싶다
//
//            //카테고리개수세기
//            // 카테고리 1일때
////            $catCount= getCatCount($vars['storeIdx']);
////            $catIdx=1;
////            while($catCount>=$catIdx){
////                $res->result->categoryname=1
////                getMenuCategory($vars['storeIdx'],$catIdx);
////            }
//            $res->result->menuCategory1 = getMenuCat1($vars['storeIdx'],);
//            $res->result->menuCategory2 = getMenuCat2($vars['storeIdx']);
//            $res->isSuccess = TRUE;
//            $res->code = 1000;
//            $res->message = "매장 세부 조회 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
