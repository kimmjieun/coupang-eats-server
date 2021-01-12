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


        case "getFranchiseStore":
            http_response_code(200);
//            //회원 비회원ㄴ 다가능
            //            $userIdxInToken=14;
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
//                $latitude = $_GET['latitude'];
//                $longitude = $_GET['longitude'];
//                $address= $_GET['address'];
                if(empty($latitude) | empty($longitude) | empty($address)){
                    $res->isSuccess = FALSE;
                    $res->code = 2010;
                    $res->message = "주소를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $latitude = floatval($latitude);
                $longitude =floatval($longitude);
//                $sort = $_GET['sort'];
//                $cheetah = $_GET['cheetah'];
//                $deliveryfee = $_GET['deliveryfee'];
//                $mincost = $_GET['mincost'];
//                $coupon = $_GET['coupon'];

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
//                    echo 'int'.$deliveryfee;
                    } else {
//                    echo 'string'.$deliveryfee;
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
//                    echo 'int'.$mincost;
                    } else {
//                    echo 'string'.$mincost;
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

                if ($cheetah != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2005;
                    $res->message = "치타 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if ($coupon != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2006;
                    $res->message = "쿠폰 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
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

                $sortList = [1, 2, 3];
                if (!in_array($sort, $sortList) and !empty($sort)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2009;
                    $res->message = "정렬 1,2,3만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
//            echo '성공';
//            break;
                //4개 한번에 넣어서 할수있게
                if (empty($sort)) { // 신규매장순
//                    echo 'notsort';

                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1NoFran($cheetah, $mincost, $userIdxInToken,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2NoFran($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3NoFran($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4NoFran($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
//                                echo $queryResult[$s++]['storeIdx'];
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
//                            break;
                        }

                    }

                } else if ($sort == 1) { //신규매장순
//                echo 'sort1신규매장순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1NoFran($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2NoFran($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3NoFran($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4NoFran($cheetah, $deliveryfee,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 2) { //별점높은순
//                echo '별점높은순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar1';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar1NoFran($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } //if(empty($deliveryfee)|$deliveryfee==1000 |$deliveryfee==2000| $deliveryfee==3000){
                        else {
//                        echo 'getOrderByStar2';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar2NoFran($cheetah, $deliveryfee,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar3NoFran($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar4NoFran($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 3) { //가까운순
//                echo '33가까운순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNear1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear1NoFran($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNear2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear2NoFran($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear3NoFran($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear4NoFran($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
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
                    $temp = getOrderByOneNoLookup($storeIdxList[$j],$latitude,$longitude);
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


                $res->result = $arrayList;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "인기프랜차이즈 조회 성공";
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
//
//                $sort = $_GET['sort'];
//                $cheetah = $_GET['cheetah'];
//                $deliveryfee = $_GET['deliveryfee'];
//                $mincost = $_GET['mincost'];
//                $coupon = $_GET['coupon'];

                if (is_string($sort)) {
                    if (!empty((int)$sort)) {
                        $sort = (int)$sort;
//                    echo 'int'.$sort;
                    } else {
//                    echo 'string'.$sort;
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
//                    echo 'int'.$deliveryfee;
                    } else {
//                    echo 'string'.$deliveryfee;
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
//                    echo 'int'.$mincost;
                    } else {
//                    echo 'string'.$mincost;
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

                if ($cheetah != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2005;
                    $res->message = "치타 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if ($coupon != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2006;
                    $res->message = "쿠폰 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
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

                $sortList = [1, 2, 3];
                if (!in_array($sort, $sortList) and !empty($sort)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2009;
                    $res->message = "정렬 1,2,3만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
//            echo '성공';
//            break;
                //4개 한번에 넣어서 할수있게
                if (empty($sort)) { // 신규매장순
//                echo 'notsort';

                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1Fran($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2Fran($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3Fran($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4Fran($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }

                } else if ($sort == 1) { //신규매장순
//                echo 'sort1신규매장순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1Fran($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2Fran($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3Fran($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4Fran($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 2) { //별점높은순
//                echo '별점높은순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar1';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar1Fran($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } //if(empty($deliveryfee)|$deliveryfee==1000 |$deliveryfee==2000| $deliveryfee==3000){
                        else {
//                        echo 'getOrderByStar2';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar2Fran($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar3Fran($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar4Fran($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 3) { //가까운순
//                echo '33가까운순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNear1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear1Fran($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNear2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear2Fran($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear3Fran($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear4Fran($cheetah, $deliveryfee, $mincost, $userIdxInToken);
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
                    $temp = getOrderByOneLookup($storeIdxList[$j], $userIdxInToken);
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
                $res->result = $arrayList;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "인기프랜차이즈 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        case "getNewStore":
            http_response_code(200);
            $userIdxInToken=14;
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
//                $latitude = $_GET['latitude'];
//                $longitude = $_GET['longitude'];
//                $address= $_GET['address'];
                if(empty($latitude) | empty($longitude) | empty($address)){
                    $res->isSuccess = FALSE;
                    $res->code = 2010;
                    $res->message = "주소를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $latitude = floatval($latitude);
                $longitude =floatval($longitude);
//                $sort = $_GET['sort'];
//                $cheetah = $_GET['cheetah'];
//                $deliveryfee = $_GET['deliveryfee'];
//                $mincost = $_GET['mincost'];
//                $coupon = $_GET['coupon'];

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
//                    echo 'int'.$deliveryfee;
                    } else {
//                    echo 'string'.$deliveryfee;
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
//                    echo 'int'.$mincost;
                    } else {
//                    echo 'string'.$mincost;
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

                if ($cheetah != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2005;
                    $res->message = "치타 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if ($coupon != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2006;
                    $res->message = "쿠폰 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
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

                $sortList = [1, 2, 3];
                if (!in_array($sort, $sortList) and !empty($sort)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2009;
                    $res->message = "정렬 1,2,3만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
//            echo '성공';
//            break;
                //4개 한번에 넣어서 할수있게
                if (empty($sort)) { // 신규매장순
//                    echo 'notsort';

                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1NoNew($cheetah, $mincost, $userIdxInToken,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2NoNew($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3NoNew($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4NoNew($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
//                                echo $queryResult[$s++]['storeIdx'];
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
//                            break;
                        }

                    }

                } else if ($sort == 1) { //신규매장순
//                echo 'sort1신규매장순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1NoNew($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2NoNew($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3NoNew($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4NoNew($cheetah, $deliveryfee,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 2) { //별점높은순
//                echo '별점높은순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar1';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar1NoNew($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } //if(empty($deliveryfee)|$deliveryfee==1000 |$deliveryfee==2000| $deliveryfee==3000){
                        else {
//                        echo 'getOrderByStar2';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar2NoNew($cheetah, $deliveryfee,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar3NoNew($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar4NoNew($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 3) { //가까운순
//                echo '33가까운순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNear1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear1NoNew($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNear2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear2NoNew($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear3NoNew($cheetah, $mincost,$latitude,$longitude);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear4NoNew($cheetah, $deliveryfee, $mincost,$latitude,$longitude);
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
                    $temp = getOrderByOneNoLookup($storeIdxList[$j],$latitude,$longitude);
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


                $res->result = $arrayList;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "새로들어왔어요 조회 성공";
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

                $sort = $_GET['sort'];
                $cheetah = $_GET['cheetah'];
                $deliveryfee = $_GET['deliveryfee'];
                $mincost = $_GET['mincost'];
                $coupon = $_GET['coupon'];

                if (is_string($sort)) {
                    if (!empty((int)$sort)) {
                        $sort = (int)$sort;
//                    echo 'int'.$sort;
                    } else {
//                    echo 'string'.$sort;
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
//                    echo 'int'.$deliveryfee;
                    } else {
//                    echo 'string'.$deliveryfee;
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
//                    echo 'int'.$mincost;
                    } else {
//                    echo 'string'.$mincost;
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

                if ($cheetah != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2005;
                    $res->message = "치타 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if ($coupon != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2006;
                    $res->message = "쿠폰 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
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

                $sortList = [1, 2, 3];
                if (!in_array($sort, $sortList) and !empty($sort)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2009;
                    $res->message = "정렬 1,2,3만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
//            echo '성공';
//            break;
                //4개 한번에 넣어서 할수있게
                if (empty($sort)) { // 신규매장순
//                echo 'notsort';

                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1New($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2New($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3New($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4New($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }

                } else if ($sort == 1) { //신규매장순
//                echo 'sort1신규매장순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1New($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2New($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3New($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4New($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 2) { //별점높은순
//                echo '별점높은순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar1';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar1New($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } //if(empty($deliveryfee)|$deliveryfee==1000 |$deliveryfee==2000| $deliveryfee==3000){
                        else {
//                        echo 'getOrderByStar2';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar2New($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar3New($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar4New($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 3) { //가까운순
//                echo '33가까운순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNear1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear1New($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNear2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear2New($cheetah, $deliveryfee, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear3New($cheetah, $mincost, $userIdxInToken);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear4New($cheetah, $deliveryfee, $mincost, $userIdxInToken);
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
                    $temp = getOrderByOneLookup($storeIdxList[$j], $userIdxInToken);
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
                $res->result = $arrayList;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "새로들어왔어요 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
//

        case "getCategoryDetail":
            http_response_code(200);
//            $userIdxInToken=14;
//            echo 'dd'.$vars['storeCatIdx'];
//            break;
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
//                $latitude = $_GET['latitude'];
//                $longitude = $_GET['longitude'];
//                $address= $_GET['address'];
                if(empty($latitude) | empty($longitude) | empty($address)){
                    $res->isSuccess = FALSE;
                    $res->code = 2010;
                    $res->message = "주소를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $latitude = floatval($latitude);
                $longitude =floatval($longitude);
//                $sort = $_GET['sort'];
//                $cheetah = $_GET['cheetah'];
//                $deliveryfee = $_GET['deliveryfee'];
//                $mincost = $_GET['mincost'];
//                $coupon = $_GET['coupon'];
                if(empty($vars['storeCatIdx'])){
                    $res->isSuccess = FALSE;
                    $res->code = 2011;
                    $res->message = "매장카테고리를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
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
//                    echo 'int'.$deliveryfee;
                    } else {
//                    echo 'string'.$deliveryfee;
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
//                    echo 'int'.$mincost;
                    } else {
//                    echo 'string'.$mincost;
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

                if ($cheetah != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2005;
                    $res->message = "치타 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if ($coupon != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2006;
                    $res->message = "쿠폰 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
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

                $sortList = [1, 2, 3];
                if (!in_array($sort, $sortList) and !empty($sort)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2009;
                    $res->message = "정렬 1,2,3만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
//            echo '성공';
//            break;
                //4개 한번에 넣어서 할수있게
                if (empty($sort)) { // 신규매장순
//                    echo 'notsort';

                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1NoCat($cheetah, $mincost, $userIdxInToken,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2NoCat($cheetah, $deliveryfee, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3NoCat($cheetah, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4NoCat($cheetah, $deliveryfee, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
//                                echo $queryResult[$s++]['storeIdx'];
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
//                            break;
                        }

                    }

                } else if ($sort == 1) { //신규매장순
//                echo 'sort1신규매장순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1NoCat($cheetah, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2NoCat($cheetah, $deliveryfee, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3NoCat($cheetah, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4NoCat($cheetah, $deliveryfee,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 2) { //별점높은순
//                echo '별점높은순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar1';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar1NoCat($cheetah, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } //if(empty($deliveryfee)|$deliveryfee==1000 |$deliveryfee==2000| $deliveryfee==3000){
                        else {
//                        echo 'getOrderByStar2';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar2NoCat($cheetah, $deliveryfee,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar3NoCat($cheetah, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar4NoCat($cheetah, $deliveryfee, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 3) { //가까운순
//                echo '33가까운순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNear1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear1NoCat($cheetah, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNear2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear2NoCat($cheetah, $deliveryfee, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear3NoCat($cheetah, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear4NoCat($cheetah, $deliveryfee, $mincost,$latitude,$longitude,$vars['storeCatIdx']);
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
                    $temp = getOrderByOneNoLookup($storeIdxList[$j],$latitude,$longitude);
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

                $res->result->currentCategory = getCategoryName($vars['storeCatIdx']);
                $res->result->category = getCategory();
                $res->result->openStore = getOpenStoreNoCat($latitude,$longitude,$vars['storeCatIdx']);
                $res->result->categoryStore = $arrayList;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "카테고리 세부 조회 성공";
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
                if(empty($vars['storeCatIdx'])){
                    $res->isSuccess = FALSE;
                    $res->code = 2011;
                    $res->message = "매장카테고리를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $sort = $_GET['sort'];
                $cheetah = $_GET['cheetah'];
                $deliveryfee = $_GET['deliveryfee'];
                $mincost = $_GET['mincost'];
                $coupon = $_GET['coupon'];


                if (is_string($sort)) {
                    if (!empty((int)$sort)) {
                        $sort = (int)$sort;
//                    echo 'int'.$sort;
                    } else {
//                    echo 'string'.$sort;
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
//                    echo 'int'.$deliveryfee;
                    } else {
//                    echo 'string'.$deliveryfee;
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
//                    echo 'int'.$mincost;
                    } else {
//                    echo 'string'.$mincost;
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

                if ($cheetah != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2005;
                    $res->message = "치타 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if ($coupon != 'Y' and !empty($cheetah)) { //널 도가능
                    $res->isSuccess = FALSE;
                    $res->code = 2006;
                    $res->message = "쿠폰 Y만 입력가능 ";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
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

                $sortList = [1, 2, 3];
                if (!in_array($sort, $sortList) and !empty($sort)) {
                    $res->isSuccess = FALSE;
                    $res->code = 2009;
                    $res->message = "정렬 1,2,3만 입력가능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
//            echo '성공';
//            break;
                //4개 한번에 넣어서 할수있게
                if (empty($sort)) { // 신규매장순
//                echo 'notsort';

                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1Cat($cheetah, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2Cat($cheetah, $deliveryfee, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3Cat($cheetah, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4Cat($cheetah, $deliveryfee, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }

                } else if ($sort == 1) { //신규매장순
//                echo 'sort1신규매장순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew1Cat($cheetah, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew2Cat($cheetah, $deliveryfee, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNew3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew3Cat($cheetah, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNew4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNew4Cat($cheetah, $deliveryfee, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 2) { //별점높은순
//                echo '별점높은순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar1';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar1Cat($cheetah, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } //if(empty($deliveryfee)|$deliveryfee==1000 |$deliveryfee==2000| $deliveryfee==3000){
                        else {
//                        echo 'getOrderByStar2';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar2Cat($cheetah, $deliveryfee, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar3Cat($cheetah, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByStar4Cat($cheetah, $deliveryfee, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        }

                    }
                } else if ($sort == 3) { //가까운순
//                echo '33가까운순 ';
                    if ($coupon = 'Y' && !empty($coupon)) {
                        if ($deliveryfee == -1) {
//                        echo 'getOrderByNear1';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear1Cat($cheetah, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByNear2';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear2Cat($cheetah, $deliveryfee, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }

                        }
                    } else {

                        if ($deliveryfee == -1) {
//                        echo 'getOrderByStar3';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear3Cat($cheetah, $mincost, $userIdxInToken,$vars['storeCatIdx']);
                            $s = 0;
                            while ($s < count($queryResult)) {
                                array_push($storeIdxList, $queryResult[$s++]['storeIdx']);
                            }
                        } else {
//                        echo 'getOrderByStar4';
                            $storeIdxList = array();
                            $queryResult = getOrderByNear4Cat($cheetah, $deliveryfee, $mincost, $userIdxInToken,$vars['storeCatIdx']);
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
                    $temp = getOrderByOneLookup($storeIdxList[$j], $userIdxInToken);
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
                $res->result->categoryname = getCategoryName($vars['storeCatIdx']);
                $res->result->category = getCategory();
                $res->result->openStore = getOpenStoreCat($userIdxInToken,$vars['storeCatIdx']);
                $res->result->categoryStore = $arrayList;
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "카테고리 세부 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }







}
} catch (\Exception $e) {
return getSQLErrorException($errorLogs, $e, $req);
}