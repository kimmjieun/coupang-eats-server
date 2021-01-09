<?php

require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/JWTPdo.php';
require './pdos/StorePdo.php';
require './pdos/AddressPdo.php';
require './pdos/CartPdo.php';
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
    $r->addRoute('POST', '/jwt', ['JWTController', 'createJwt']);   // JWT 생성: 로그인 + 해싱된 패스워드 검증 내용 추가
    $r->addRoute('GET', '/jwt', ['JWTController', 'validateJwt']);  // JWT 유효성 검사

    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    $r->addRoute('GET', '/users', ['IndexController', 'getUsers']);
    $r->addRoute('GET', '/users/{userIdx}', ['IndexController', 'getUserDetail']);
    $r->addRoute('POST', '/user', ['IndexController', 'createUser']); // 비밀번호 해싱 예시 추가

    /* ********************************* Store ********************************* */
  //  $r->addRoute('GET', '/stores', ['StoreController', 'getStore']);
    // 홈화면조회
    //$r->addRoute('GET', '/home', ['StoreController', 'getHome']);
    // 홈화면조회
    $r->addRoute('GET', '/home', ['StoreController', 'getHome2']);
    // 골라먹는맛집 세부 조회
    $r->addRoute('GET', '/stores/{storeIdx}', ['StoreController', 'getStoreDetail']);
    // 인기프랜차이즈 조회
    //$r->addRoute('GET', '/franchise-stores', ['StoreController', 'getFranchiseStore']);
    // 새로들어왔어요 조회
    //$r->addRoute('GET', '/new-stores', ['StoreController', 'getNewStore']);
    // 메뉴 세부조회
    $r->addRoute('GET', '/menus/{menuIdx}', ['StoreController', 'getMenuOption']);
    // 카트담기
    $r->addRoute('POST', '/cart', ['StoreController', 'createCart']);
    // 즐겨찾기 추가
    $r->addRoute('POST', '/stores/hart', ['StoreController', 'hartStore']);
    /* ********************************* jwt ********************************* */
    $r->addRoute('POST', '/kakao-login', ['JWTController', 'createKakaoJwt']); // 바디
    $r->addRoute('POST', '/naver-login', ['JWTController', 'createNaverJwt']); // 바디

    /* ********************************* address ********************************* */
    // 주소 설정 api
    $r->addRoute('PATCH', '/address', ['AddressController', 'setAddress']);

    /* ********************************* cart ********************************* */
    // 카트담기
//    $r->addRoute('POST', '/cart', ['CartController', 'createCart']);
    $r->addRoute('POST', '/carts', ['CartController', 'putInCart']);
    // 카트보기
    $r->addRoute('GET', '/carts', ['CartController', 'getCart']);

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
        }

        break;
}
