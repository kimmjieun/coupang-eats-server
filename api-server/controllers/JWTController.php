<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 20.08.29
         */
        case "createJwt":
            http_response_code(200);

            // 1) 로그인 시 email, password 받기
            if (!isValidUser($req->userID, $req->pwd)) { // JWTPdo.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 아이디 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 2) JWT 발급
            // Payload에 맞게 다시 설정 요함, 아래는 Payload에 userIdx를 넣기 위한 과정
            $userIdx = getUserIdxByID($req->userID);  // JWTPdo.php 에 구현
            $jwt = getJWT($userIdx, JWT_SECRET_KEY); // function.php 에 구현

            $res->result->jwt = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 2
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 20.08.29
         */
        case "validateJwt":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // 1) JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 토큰입니다"; 
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 2) JWT Payload 반환
            http_response_code(200);
            $res->result = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



        case "createKakaoJwt":

            $accessToken=$req->accessToken;

            $app_url= "https://kapi.kakao.com/v2/user/me";
            $opts = array( CURLOPT_URL => $app_url,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array( "Authorization: Bearer ".$accessToken ) );
            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $result = curl_exec($ch);
            curl_close($ch);
            $me_responseArr = json_decode($result, true);
            if ($me_responseArr['id']) {
                $mb_uid = 'kakao_'.$me_responseArr['id'];
                $nickname = 'kakao_'.$me_responseArr['nickname'];
                $mb_nickname = $me_responseArr['properties']['nickname']; // 닉네임
                $mb_email = $me_responseArr['kakao_account']['email']; // 이메일
                $registered_at = $me_responseArr['registered_at'];
                $mb_profile_image = $me_responseArr['properties']['profile_image']; // 프로필 이미지
                $mb_thumbnail_image = $me_responseArr['properties']['thumbnail_image']; // 프로필 이미지
                $mb_gender = $me_responseArr['kakao_account']['gender']; // 성별 female/male
                $mb_age = $me_responseArr['kakao_account']['age_range']; // 연령대
                $mb_birthday = $me_responseArr['kakao_account']['birthday']; // 생일
//                echo "<br><br> mb_uid : " . $mb_uid;
//                echo "<br><br> nickname : " . $nickname;
//                echo "<br> mb_nickname : " . $mb_nickname;
//                echo "<br> mb_profile_image : " . $mb_profile_image;
//                echo "<br> mb_thumbnail_image : " . $mb_thumbnail_image;
//                echo "<br> mb_email : " . $mb_email;
//                echo "<br> mb_gender:" . $mb_gender;
//                echo "<br> mb_age:" . $mb_age;
//                echo "<br> mb_birthday:" . $mb_birthday;
                if (isValidUser($mb_uid)) { // JWTPdo.php 에 구현
                    $userIdx=login($mb_uid); // 함수를 바꿔 유저idx로 가져오는
                    //echo $mb_uid;
                    //echo 'userIdx'.$userIdx;
                    //break;
                    $jwt = getJWT($userIdx, JWT_SECRET_KEY);
                    $res->result->jwt = $jwt;
                    $res->isSuccess = TRUE;
                    $res->code = 1000;
                    $res->message = "카카오 로그인 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else{
                    $userIdx=signUp($mb_nickname,$mb_uid,$mb_email);
                    //echo 'userIdx'.$userIdx;
                    $jwt = getJWT($userIdx, JWT_SECRET_KEY); // function.php 에 구현
                    $res->result->jwt = $jwt;
                    $res->isSuccess = TRUE;
                    $res->code = 1001;
                    $res->message = "카카오 회원가입 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            else{
                $res->isSuccess = TRUE;
                $res->code = 2000;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


        case "createNaverJwt":

            $accessToken=$req->accessToken;
            $token = $accessToken;
            $header = "Bearer ".$token; // Bearer 다음에 공백 추가
            $url = "https://openapi.naver.com/v1/nid/me";
            $is_post = false;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, $is_post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers = array();
            $headers[] = "Authorization: ".$header;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec ($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            echo "dddddd".$response;
            break;
//            break;
//            echo "status_code:".$status_code."<br>";
//            curl_close ($ch);
//            if($status_code == 200) {
//                echo $response;
//            } else {
//                echo "Error 내용:".$response;
//            }
//            break;



}
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
