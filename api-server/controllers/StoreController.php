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

//            $arrayList = array();
//            $townLifeIdx = getTownLifeIndex();
//            while($townLifeIdx>0){
//                $temp=(Object)array();
//                if(isValidTownLife($townLifeIdx)){
//                    if (!empty(getContentOne($townLifeIdx,$userIdxInToken))){ // 삭제해도되나
//                        $temp=getContentOne($townLifeIdx,$userIdxInToken);
//                        if(hasImage($townLifeIdx)) {
//                            $i=0;
//                            $img_arr=array();
//                            $queryResult=getContentImg($townLifeIdx);
//                            while($i<count($queryResult)){
//                                array_push($img_arr,$queryResult[$i++]['townLifePhotoUrl']);
//                            }
//                            $temp['img_arr']=$img_arr;
//                        }
//                        array_push($arrayList,$temp);
//                    }
//
//                }
//                $townLifeIdx = $townLifeIdx-1;
//
//            }

            $res->result->promotion = getPromotion();
            $res->result->category = getCategory();
            $res->result->franchise = getFranchise();
            $res->result->openStore = getOpenStore();
            $res->result->mainStore = $arrayList; //getStore();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "홈 화면 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;




    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
