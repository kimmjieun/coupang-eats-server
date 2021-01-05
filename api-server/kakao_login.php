<?php
$restAPIKey = "d2161e3077e54149ce8ec738e955c547";

$callbackUrl = urlencode("https://test.coupang-eats.shop/kakao_callback.php");
$kakaoUrl = "https://kauth.kakao.com/oauth/authorize?client_id=".$restAPIKey."&redirect_uri=".$callbackUrl."&response_type=code";

?>
<html>

<body>

<a href="<?=$kakaoUrl?>"> 카카오톡 로그인</a>

</body>

</html>
