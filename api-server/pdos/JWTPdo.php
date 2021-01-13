<?php

//function isValidUser($ID, $pwd){
//    $pdo = pdoSqlConnect();
//    $query = "SELECT ID, pwd as hash FROM Users WHERE ID= ?;";
//
//
//    $st = $pdo->prepare($query);
//    $st->execute([$ID]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st=null;$pdo = null;
//
//    return password_verify($pwd, $res[0]['hash']);
//
//}
//function getUserIdxByID($ID)
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT userIdx FROM Users WHERE ID = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$ID]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0]['userIdx'];
//}

function isValidUser($ID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT userId FROM UserInfo WHERE userId = ? and isDeleted='N') AS exist;";


    $st = $pdo->prepare($query);
    $st->execute([$ID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);

}

function login($mb_uid)
{

    $pdo = pdoSqlConnect();

    $query = "select userIdx from UserInfo where userId=? and isDeleted='N';";
    $st = $pdo->prepare($query);
    $st->execute([$mb_uid]);
    $userIdx=$st -> fetchColumn();
    $st = null;
    $pdo = null;
    return $userIdx;

}


function signUpKakao($mb_nickname,$mb_uid,$mb_email)
{


    $pdo = pdoSqlConnect();

    $query = "insert into UserInfo(userName,userId,email) values (?,?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$mb_nickname,$mb_uid,$mb_email]);
    $userIdx=$pdo->lastInsertId();
    $st = null;
    $pdo = null;
    return $userIdx;

}

function signUpNaver($mb_name,$mb_uid,$mb_mobile)
{

    $pdo = pdoSqlConnect();

    $query = "insert into UserInfo(userName,userId, phoneNumber) values (?,?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$mb_name,$mb_uid,$mb_mobile]);
    $userIdx=$pdo->lastInsertId();
    $st = null;
    $pdo = null;
    return $userIdx;

}


function newNaverLogin($accessToken)
{

    $me_headers = array(
        'Content-Type: application/json',
        sprintf('Authorization: Bearer %s', $accessToken)
    );

    $me_is_post = false;
    $me_ch = curl_init();
    curl_setopt($me_ch, CURLOPT_URL, "https://openapi.naver.com/v1/nid/me");
    curl_setopt($me_ch, CURLOPT_POST, $me_is_post);
    curl_setopt($me_ch, CURLOPT_HTTPHEADER, $me_headers);
    curl_setopt($me_ch, CURLOPT_RETURNTRANSFER, true);
    $me_response = curl_exec ($me_ch);
    $me_status_code = curl_getinfo($me_ch, CURLINFO_HTTP_CODE);
    curl_close ($me_ch);
    $me_responseArr = json_decode($me_response, true);
    if ($me_responseArr['response']['id']) {
        // 회원아이디(naver_ 접두사에 네이버 아이디를 붙여줌)
        $mb_uid = 'naver_' . $me_responseArr['response']['id'];
        $mb_nickname = $me_responseArr['response']['nickname']; // 닉네임
        $mb_email = $me_responseArr['response']['email']; // 이메일
        $mb_gender = $me_responseArr['response']['gender']; // 성별 F: 여성, M: 남성, U: 확인불가
        $mb_age = $me_responseArr['response']['age']; // 연령대
        $mb_birthday = $me_responseArr['response']['birthday']; // 생일(MM-DD 형식)
        $mb_profile_image = $me_responseArr['response']['profile_image']; // 프로필 이미지
        // // 멤버 DB에 토큰과 회원정보를 넣고 로그인
    }

    $pdo = pdoSqlConnect();

    $query = "insert into UserInfo(userName,userId,email) values (?,?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$mb_nickname,$mb_uid,$mb_email]);
    $userIdx=$pdo->lastInsertId();
    $st = null;
    $pdo = null;
    return $userIdx;

}

// 회원탈퇴
function deleteUser($userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE UserInfo SET isdeleted = 'Y' WHERE userIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st = null;
    $pdo = null;
      return ["userIdx"=>$userIdx];

}

function isUser($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from UserInfo where userIdx=? and  isDeleted ='N') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}