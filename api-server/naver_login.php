<?php
$client_id = "V7ZEMy9Rx_53eTHYSK54"; // 위에서 발급받은 Client ID 입력
$redirectURI = urlencode('https://test.coupang-eats.shop/naver_callback.php'); //자신의 Callback URL 입력
$state = "RAMDOM_STATE";
$apiURL = "https://nid.naver.com/oauth2.0/authorize?response_type=code&client_id=".$client_id."&redirect_uri=".$redirectURI."&state=".$state;
?>
<a href="<?php echo $apiURL ?>"><img height="50" src="http://static.nid.naver.com/oauth/small_g_in.PNG"/></a>