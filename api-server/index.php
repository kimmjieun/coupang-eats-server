<?php

require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/JWTPdo.php';
require './pdos/StorePdo.php';
require './pdos/AddressPdo.php';
require './pdos/CartPdo.php';
require './pdos/PayPdo.php';
require './pdos/CouponPdo.php';
require './pdos/LookupPdo.php';
require './pdos/AdminPdo.php';
require './vendor/autoload.php';


use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;
//
date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   JWT   ****************** */
//    $r->addRoute('POST', '/jwt', ['JWTController', 'createJwt']);   // JWT 생성: 로그인 + 해싱된 패스워드 검증 내용 추가
//    $r->addRoute('GET', '/jwt', ['JWTController', 'validateJwt']);  // JWT 유효성 검사

    /* ******************   Test   ****************** */
//    $r->addRoute('GET', '/', ['IndexController', 'index']);
//    $r->addRoute('GET', '/users', ['IndexController', 'getUsers']);
//    $r->addRoute('GET', '/users/{userIdx}', ['IndexController', 'getUserDetail']);
//    $r->addRoute('POST', '/user', ['IndexController', 'createUser']); // 비밀번호 해싱 예시 추가

    /* ********************************* User ********************************* */

    $r->addRoute('GET', '/pay-method', ['IndexController', 'getPayMethod']);
//    $r->addRoute('GET', '/order/{orderIdx}', ['IndexController', 'getPayMethod']);

    /* ********************************* Store ********************************* */
    // 홈화면조회
    $r->addRoute('GET', '/home', ['StoreController', 'getHome']);
    // 매장 세부 조회
    $r->addRoute('GET', '/stores/{storeIdx}', ['StoreController', 'getStoreDetail']);
    // 메뉴 세부조회
    $r->addRoute('GET', '/menus/{menuIdx}', ['StoreController', 'getMenuOption']);
    // 즐겨찾기 추가
    $r->addRoute('POST', '/stores/heart', ['StoreController', 'hartStore']);
    // 즐겨찾기 조회
    $r->addRoute('GET', '/hearts', ['StoreController', 'getHartStore']);
    // 프로모션 조회
    $r->addRoute('GET', '/promotions', ['StoreController', 'getPromotionAll']);
    // 프로모션 세부조회
    $r->addRoute('GET', '/promotions/{promotionIdx}', ['StoreController', 'getPromotionDetail']);
    // 카테고리 조회 API
    $r->addRoute('GET', '/category', ['StoreController', 'getCategory']);
    // 매장/원산지 정보조회 API
    $r->addRoute('GET', '/stores/{storeIdx}/info', ['StoreController', 'introduceStore']);

    /* ********************************* jwt ********************************* */
    // 카카오 로그인
    $r->addRoute('POST', '/kakao-login', ['JWTController', 'createKakaoJwt']); // 바디
    // 네이버 로그인
    $r->addRoute('POST', '/naver-login', ['JWTController', 'createNaverJwt']); // 바디
    // 회원 탈퇴
    $r->addRoute('GET', '/deleted-user', ['JWTController', 'deleteUser']);
    // 자동 로그인
    $r->addRoute('GET', '/auto-login', ['JWTController', 'autoLogin']);
    /* ********************************* address ********************************* */
    // 주소 설정 api
    $r->addRoute('PATCH', '/address', ['AddressController', 'setAddress']);

    /* ********************************* cart ********************************* */
    // 카트 담기
    $r->addRoute('POST', '/carts', ['CartController', 'addCart']);
    // 새로 카트 담기
    $r->addRoute('POST', '/newcarts', ['CartController', 'addNewCart']);
    // 카트보기
    $r->addRoute('GET', '/carts', ['CartController', 'getCart']);

    // 주문내역조회
    $r->addRoute('GET', '/order', ['CartController', 'getOrderDetail']);

    /* ********************************* pay ********************************* */
    $r->addRoute('GET', '/access-token', ['PayController', 'getAccessToken']);
    // 포스트로바꿔
    $r->addRoute('POST', '/verification', ['PayController', 'getVerification']);


    // 주문/결제 취소하기
    $r->addRoute('POST', '/order/cancellation', ['PayController', 'getCancellation']);
    // 주문/결제 하기
    $r->addRoute('POST', '/order', ['PayController', 'makeOrder']);

    /* ********************************* coupon ********************************* */
    // 쿠폰받기 API
    $r->addRoute('POST', '/stores/{storeIdx}/coupon', ['CouponController', 'receiveCoupon']);
    // 쿠폰조회 API
    $r->addRoute('GET', '/coupons', ['CouponController', 'getUserCoupon']);


    /* ********************************* lookup ********************************* */
    // 인기프랜차이즈 조회-원래방식으로
    $r->addRoute('GET', '/franchise-stores', ['LookupController', 'getFranchiseStore']);
    // 새로들어왔어요 조회-다른방식으로
    $r->addRoute('GET', '/new-stores', ['LookupController', 'getNewStore']);
    // 카테고리 세부조회
    $r->addRoute('GET', '/category/{categoryIdx}', ['LookupController', 'getCategoryDetail']);
    // 검색어로 조회
    $r->addRoute('GET', '/keyword', ['LookupController', 'getKewordStore']);

    /* ********************************* admin ********************************* */
    // 주문상태관리
    $r->addRoute('PATCH', '/order-state', ['AdminController', 'setOrderState']);

//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'JWTController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/JWTController.php';
                break;
            case 'StoreController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/StoreController.php';
                break;
            case 'AddressController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/AddressController.php';
                break;
            case 'CartController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/CartController.php';
                break;
            case 'PayController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/PayController.php';
                break;
            case 'LookupController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/LookupController.php';
                break;
            case 'CouponController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/CouponController.php';
                break;
            case 'AdminController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/AdminController.php';
                break;
        }

        break;
}
