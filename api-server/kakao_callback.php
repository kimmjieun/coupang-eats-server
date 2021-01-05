<?php
//버전1
$CLIENT_ID     = "d2161e3077e54149ce8ec738e955c547";
$REDIRECT_URI  = urlencode("https://test.coupang-eats.shop/kakao_callback.php"); ;
$TOKEN_API_URL = "https://kauth.kakao.com/oauth/token";

$code   = $_GET["code"];
$params = sprintf( 'grant_type=authorization_code&client_id=%s&redirect_uri=%s&code=%s', $CLIENT_ID, $REDIRECT_URI, $code);

$opts = array(
    CURLOPT_URL => $TOKEN_API_URL,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSLVERSION => 1,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $params,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false
);

$curlSession = curl_init();
curl_setopt_array($curlSession, $opts);
$accessTokenJson = curl_exec($curlSession);
curl_close($curlSession);

echo $accessTokenJson;

$accessToken= json_decode($accessTokenJson)->access_token;
echo  "<br>액세스토큰:".$accessToken;

$USER_API_URL= "https://kapi.kakao.com/v2/user/me";
$opts = array( CURLOPT_URL => $USER_API_URL,
    CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSLVERSION => 1,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array( "Authorization: Bearer " . $accessToken ) );

$curlSession = curl_init();
curl_setopt_array($curlSession, $opts);
$accessUserJson = curl_exec($curlSession);
curl_close($curlSession);

$me_responseArr = json_decode($accessUserJson, true);
if ($me_responseArr['id']) {
    $mb_uid = 'kakao_'.$me_responseArr['id'];
    //$nickname = 'kakao_'.$me_responseArr['nickname'];
    $registered_at = $me_responseArr['registered_at'];
    $mb_nickname = $me_responseArr['properties']['nickname']; // 닉네임
    $mb_profile_image = $me_responseArr['properties']['profile_image']; // 프로필 이미지
    $mb_thumbnail_image = $me_responseArr['properties']['thumbnail_image']; // 프로필 이미지
    $mb_email = $me_responseArr['kakao_account']['email']; // 이메일
    $mb_gender = $me_responseArr['kakao_account']['gender']; // 성별 female/male
    $mb_age = $me_responseArr['kakao_account']['age_range']; // 연령대
    $mb_birthday = $me_responseArr['kakao_account']['birthday']; // 생일
    echo "<br><br> mb_uid : " . $mb_uid;
    //echo "<br><br> nickname : " . $nickname;
    echo "<br> mb_nickname : " . $mb_nickname;
    echo "<br> mb_profile_image : " . $mb_profile_image;
    echo "<br> mb_thumbnail_image : " . $mb_thumbnail_image;
    echo "<br> mb_email : " . $mb_email;
    echo "<br> mb_gender:" . $mb_gender;
    echo "<br> mb_age:" . $mb_age;
    echo "<br> mb_birthday:" . $mb_birthday;

}

