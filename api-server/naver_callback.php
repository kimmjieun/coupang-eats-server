


<?php


$client_id = "V7ZEMy9Rx_53eTHYSK54";   //ClientID 입력
$client_secret = "iXkNfM4sbC"; //Client Secret 입력

$code = $_GET["code"];
$state = $_GET["state"];
$redirectURI = urlencode('https://test.coupang-eats.shop/naver_callback.php'); // 현재 Callback Url 입력
$url = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code&client_id=".$client_id."&client_secret=".$client_secret."&redirect_uri=".$redirectURI."&code=".$code."&state=".$state;
$is_post = false;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, $is_post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = array();
$response = curl_exec ($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "status_code:".$status_code;

curl_close ($ch);
//
//if($status_code == 200) {
//    echo $response;
//    $responseArr = json_decode($response, true);
//    $_SESSION['naver_access_token'] = $responseArr['access_token'];
//    $_SESSION['naver_refresh_token'] = $responseArr['refresh_token'];
//
//    $me_headers = array(
//        'Content-Type: application/json',
//        sprintf('Authorization: Bearer %s', $responseArr['access_token'])
//    );
//
//    $me_is_post = false;
//    $me_ch = curl_init();
//    curl_setopt($me_ch, CURLOPT_URL, "https://openapi.naver.com/v1/nid/me");
//    curl_setopt($me_ch, CURLOPT_POST, $me_is_post);
//    curl_setopt($me_ch, CURLOPT_HTTPHEADER, $me_headers);
//    curl_setopt($me_ch, CURLOPT_RETURNTRANSFER, true);
//    $me_response = curl_exec ($me_ch);
//    $me_status_code = curl_getinfo($me_ch, CURLINFO_HTTP_CODE);
//    curl_close ($me_ch);
//    $me_responseArr = json_decode($me_response, true);
//    if ($me_responseArr['response']['id']) {
//        // 회원아이디(naver_ 접두사에 네이버 아이디를 붙여줌)
//        $mb_uid = 'naver_' . $me_responseArr['response']['id'];
//        $mb_nickname = $me_responseArr['response']['nickname']; // 닉네임
//        $mb_email = $me_responseArr['response']['email']; // 이메일
//        $mb_gender = $me_responseArr['response']['gender']; // 성별 F: 여성, M: 남성, U: 확인불가
//        $mb_age = $me_responseArr['response']['age']; // 연령대
//        $mb_birthday = $me_responseArr['response']['birthday']; // 생일(MM-DD 형식)
//        $mb_profile_image = $me_responseArr['response']['profile_image']; // 프로필 이미지
//        // // 멤버 DB에 토큰과 회원정보를 넣고 로그인
//        echo "<br> mb_uid: " . $mb_uid;
//        echo "<br> mb_nickname: " . $mb_nickname;
//        echo "<br> mb_email: " . $mb_email;
//        echo "<br> mb_gender: " . $mb_gender;
//        echo "<br> mb_age: " . $mb_age;
//        echo "<br> mb_birthday: " . $mb_birthday;
//        echo "<br> mb_profile_image: " . $mb_profile_image;
//    }
//}
//else {
//    echo "Error 내용:".$response;
//}
