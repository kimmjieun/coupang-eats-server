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

        case "introduceStore":
            http_response_code(200);
            if(!isValidStore($vars['storeIdx'])){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지않은 매장입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = introduceStore($vars['storeIdx']);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "매장/원산지정보 조회 성공";
            echo json_encode($res);
            break;

        case "getHome":
            http_response_code(200);
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken = getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            $latitude = $_GET['latitude'];
            $longitude = $_GET['longitude'];
            $address= $_GET['address'];
            $sort = $_GET['sort'];
            $cheetah = $_GET['cheetah'];
            $deliveryfee = $_GET['deliveryfee'];
            $mincost = $_GET['mincost'];
            $coupon = $_GET['coupon'];

            if (empty($jwt)){

                if(empty($latitude) | empty($longitude) | empty($address)){
                    $res->isSuccess = FALSE;
                    $res->code = 2010;
                    $res->message = "주소를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $latitude = floatval($latitude);
                $longitude =floatval($longitude);

                if (is_string($sort)) {
                    if (!empty((int)$sort)) {
                        $sort = (int)$sort;
                    } else {
                        $res->isSuccess = FALSE;
                        $res->code = 2002;
                        $res->message = "맞지않는데이터타입(sort)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
                if (is_string($deliveryfee)) {
                    if (!empty((int)$deliveryfee)) {
                        $deliveryfee = (int)$deliveryfee;
                    } else {
                        $res->isSuccess = FALSE;
                        $res->code = 2003;
                        $res->message = "맞지않는데이터타입(deliveryfee)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
                if (is_string($mincost)) {
                    if (!empty((int)$mincost)) {
                        $mincost = (int)$mincost;
                    } else {
                        $res->isSuccess = FALSE;
                        $res->code = 2004;
                        $res->message = "맞지않는데이터타입(mincost)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
                if (is_string($cheetah) and !empty($cheetah)) {
                    if ($cheetah != 'Y') {
                        $res->isSuccess = FALSE;
                        $res->code = 2005;
                        $res->message = "cheetah Y만 입력가능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                }
                if (is_string($coupon) and !empty($coupon)) {
                    if ($coupon != 'Y') {
                        $res->isSuccess = FALSE;
                        $res->code = 2006;
                        $res->message = "coupon Y만 입력가능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                }



                $mincostList = [5000, 10000, 12000, 15000];
                if (!in_array($mincost, $mincostList) and !empty($mincost)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2007;
                    $res->message = "최소주문비용 5000,10000,12000,15000원만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $deliveryfeeList = [-1, 1000, 2000, 3000];
                if (!in_array($deliveryfee, $deliveryfeeList) and !empty($deliveryfee)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2008;
                    $res->message = "배달비 -1(무료배달),1000,2000,3000원만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $sortList = [1, 2, 3,4,5];
                if (!in_array($sort, $sortList) and !empty($sort)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2009;
                    $res->message = "정렬 1,2,3,4,5만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }


                if (empty($sort)|$sort==1) { // 추천순=배달요금저렴한순+주문많은순
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) { // -1==무료배달
                            $storeIdxList = array();
                            $queryResult = getOrderByRecommend1($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByRecommend2($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByRecommend3($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByRecommend4($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }

                } else if ($sort == 2) { //주문많은순

                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByMany1($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByMany2($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByMany3($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByMany4($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 3) { //가까운순
                        if ($coupon = 'Y' && !empty($coupon)) {
                            if ($deliveryfee == -1) {
                                $storeIdxList = array();
                                $queryResult = getOrderByNear1No($cheetah, $mincost,$latitude,$longitude);
                                $s = 0;
                                while ($s < count($queryResult)) {
                                    array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                                }
                            } else {
                                $storeIdxList = array();
                                $queryResult = getOrderByNear2No($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                                $s = 0;
                                while ($s < count($queryResult)) {
                                    array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                                }

                            }
                        } else {

                            if ($deliveryfee == -1) {
                                $storeIdxList = array();
                                $queryResult = getOrderByNear3No($cheetah, $mincost,$latitude,$longitude);
                                $s = 0;
                                while ($s < count($queryResult)) {
                                    array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                                }
                            } else {
                                $storeIdxList = array();
                                $queryResult = getOrderByNear4No($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                                $s = 0;
                                while ($s < count($queryResult)) {
                                    array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                                }
                            }

                        }
                } else if ($sort == 4) { //별점높은순
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByStar1($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } //if(empty($deliveryfee)|$deliveryfee==1000 |$deliveryfee==2000| $deliveryfee==3000){
                        else {
                            $storeIdxList = array();
                            $queryResult = getOrderByStar2($cheetah, $deliveryfee);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByStar3($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByStar4($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }

                }else if ($sort == 5) { //신규매장순
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4($cheetah, $deliveryfee,$mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                }


                $arrayList = array();
                $j = 0;
                while (count($storeIdxList) > $j) {
                    $temp = array();
                    $temp = getOrderByOneNo($storeIdxList[$j],$latitude,$longitude);
                    $i = 0;
                    $imgList = array();
                    $queryResult = getStoreImg($storeIdxList[$j]);
                    while ($i < count($queryResult)) {

                        array_push($imgList, $queryResult[$i++]['storePhoto']);
                    }
                    $temp['img'] = $imgList;
                    array_push($arrayList, $temp);
                    $j++;
                }


                $res->result->address = $address;
                $res->result->promotion = getPromotion();
                $res->result->category = getCategory();
                $res->result->franchise = getFranchiseNo($latitude,$longitude);
                $res->result->openStore = getOpenStoreNo($latitude,$longitude);
                $res->result->mainStore = $arrayList;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "홈화면 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                    $res->isSuccess = FALSE;
                    $res->code = 2001;
                    $res->message = "유효하지 않은 토큰입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    break;
                }


                if (is_string($sort)) {
                    if (!empty((int)$sort)) {
                        $sort = (int)$sort;
                    } else {
                        $res->isSuccess = FALSE;
                        $res->code = 2002;
                        $res->message = "맞지않는데이터타입(sort)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
                if (is_string($deliveryfee)) {
                    if (!empty((int)$deliveryfee)) {
                        $deliveryfee = (int)$deliveryfee;
                    } else {
                        $res->isSuccess = FALSE;
                        $res->code = 2003;
                        $res->message = "맞지않는데이터타입(deliveryfee)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
                if (is_string($mincost)) {
                    if (!empty((int)$mincost)) {
                        $mincost = (int)$mincost;
                    } else {
                        $res->isSuccess = FALSE;
                        $res->code = 2004;
                        $res->message = "맞지않는데이터타입(mincost)";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
                if (is_string($cheetah) and !empty($cheetah)) {
                    if ($cheetah != 'Y') {
                        $res->isSuccess = FALSE;
                        $res->code = 2005;
                        $res->message = "cheetah Y만 입력가능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                }
                if (is_string($coupon) and !empty($coupon)) {
                    if ($coupon != 'Y') {
                        $res->isSuccess = FALSE;
                        $res->code = 2006;
                        $res->message = "coupon Y만 입력가능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                }



                $mincostList = [5000, 10000, 12000, 15000];
                if (!in_array($mincost, $mincostList) and !empty($mincost)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2007;
                    $res->message = "최소주문비용 5000,10000,12000,15000원만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $deliveryfeeList = [-1, 1000, 2000, 3000];
                if (!in_array($deliveryfee, $deliveryfeeList) and !empty($deliveryfee)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2008;
                    $res->message = "배달비 -1(무료배달),1000,2000,3000원만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $sortList = [1, 2, 3,4,5];
                if (!in_array($sort, $sortList) and !empty($sort)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2009;
                    $res->message = "정렬 1,2,3,4,5만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                if (empty($sort)|$sort==1) { // 추천순

                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByRecommend1($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByRecommend2($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByRecommend3($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByRecommend4($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }

                } else if ($sort == 2) { //주문많은순
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByMany1($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByMany2($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByMany3($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByMany4($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                }  else if ($sort == 3) { //가까운순
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByNear1($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByNear2($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByNear3($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByNear4($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                }else if ($sort == 4) { //별점높은순
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByStar1($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }
                        else {
                            $storeIdxList = array();
                            $queryResult = getOrderByStar2($cheetah, $deliveryfee,$mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByStar3($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByStar4($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }

                }else if ($sort == 5) { //신규매장순
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2($cheetah, $deliveryfee, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3($cheetah, $mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4($cheetah, $deliveryfee,$mincost);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }

                }

                $arrayList = array();
                $j = 0;
                while (count($storeIdxList) > $j) {
                    $temp = array();
                    $temp = getOrderByOne($storeIdxList[$j], $userIdxInToken);
                    $i = 0;
                    $imgList = array();
                    $queryResult = getStoreImg($storeIdxList[$j]);
                    while ($i < count($queryResult)) {
                        array_push($imgList, $queryResult[$i++]['storePhoto']);
                    }
                    $temp['img'] = $imgList;
                    array_push($arrayList, $temp);
                    $j++;
                }
                $res->result->address = getUserAddress($userIdxInToken)[0]['addressName'];
                $res->result->promotion = getPromotion();
                $res->result->category = getCategory();
                $res->result->franchise = getFranchise($userIdxInToken);
                $res->result->openStore = getOpenStore($userIdxInToken);
                $res->result->mainStore = $arrayList;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "홈화면 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }





        case "getStoreDetail":
            http_response_code(200);
//            $userIdxInToken=14;
            //주석해제
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken = getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;


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
            // 유저일경우 유저가 아닐경우 유저인덱스없는경우

            // 쿠폰
            // jwt가 없고 상점 쿠폰이 있을땐 2000원 쿠폰받기
            // jwt가 있고 상점 쿠폰이 없을 때 / 있을때


            // 여기부터
            if(isValidStoreCoupon($vars['storeIdx'])){
                $couponInfo=getCouponInfo($vars['storeIdx']); // 쿠폰인덱스와 쿠폰가격 가져오기
                $couponIdx=$couponInfo['couponIdx'];
                $couponPrice=$couponInfo['salePrice'];
                $res->couponInfo->couponIdx=$couponIdx;
                if(empty($jwt)){ //$jwt로 변경
                    $res->couponInfo->coupon=number_format($couponPrice).'원 쿠폰받기';
                    $res->couponInfo->hasCoupon='N';
                }
                else{ // 주석해제
                    if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                        $res->isSuccess = FALSE;
                        $res->code = 2000;
                        $res->message = "유효하지 않은 토큰입니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if(isValidUserCoupon($couponIdx,$userIdxInToken)){
                        $res->couponInfo->coupon=number_format($couponPrice).'원 쿠폰받기 완료';
                        $res->couponInfo->hasCoupon='Y';
                    }
                    else{
                        $res->couponInfo->coupon=number_format($couponPrice).'원 쿠폰받기';
                        $res->couponInfo->hasCoupon='N';
                    }
                }
           }

            // 여기까지
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
                $temp['optCategoryName']=getOptCatName($vars['menuIdx'],$catIdx)[0]['menuOptCatName'];
                $temp['isMandatory']=getOptCatName($vars['menuIdx'],$catIdx)[0]['mandatory'];
                $temp['maxSelect']=getOptCatName($vars['menuIdx'],$catIdx)[0]['maxSelect'];
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




        case "hartStore":
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

        case "getHartStore":
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


            $res->heartCount = getHartCount($userIdxInToken);
            $res->heartStore = getHartStore($userIdxInToken);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "즐겨찾기 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getPromotionAll":
            http_response_code(200);

            $res->result = getPromotionAll();
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "프로모션 전체 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getPromotionDetail":
            http_response_code(200);
            if(!isValidPromotion($vars['promotionIdx'])){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "없는 프로모션 인덱스";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getPromotionDetail($vars['promotionIdx']);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "프로모션 세부조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getCategory":
            http_response_code(200);

            $res->result = getCategory();
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "카테고리 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;





    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
