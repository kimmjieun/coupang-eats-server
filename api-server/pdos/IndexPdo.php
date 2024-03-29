<?php


function getPayMethod($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select paymentIdx,CASE
           WHEN CHAR_LENGTH(cardName) > 13
               THEN concat(LEFT(cardName, 13), '...', '****',substring(cardNumber,13,3),'*')
           ELSE concat(cardName, '****',substring(cardNumber,13,3),'*')
       END AS paymentMethod
from PaymentMethod
where isDeleted='N' and userIdx=?
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res; // 결과 0인것하나
}


//
////READ
//function getUsers()
//{
//    $pdo = pdoSqlConnect();
//    $query = "select * from testTable;";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
////READ
//function getUserDetail($userIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select * from testTable where no = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$userIdx]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}
//
////READ
////function isValidUserIdx($userIdx)
////{
////    $pdo = pdoSqlConnect();
////    $query = "select EXISTS(select * from Users where userIdx = ?) exist;";
////
////    $st = $pdo->prepare($query);
////    $st->execute([$userIdx]);
////    //    $st->execute();
////    $st->setFetchMode(PDO::FETCH_ASSOC);
////    $res = $st->fetchAll();
////
////    $st = null;
////    $pdo = null;
////
////    return $res[0]['exist'];
////}
//
//
//function createUser($name)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO testTable (name) VALUES (?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name]);
//
//    $st = null;
//    $pdo = null;
//
//}
//
//
//// CREATE
////    function addMaintenance($message){
////        $pdo = pdoSqlConnect();
////        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
////
////        $st = $pdo->prepare($query);
////        $st->execute([$message]);
////
////        $st = null;
////        $pdo = null;
////
////    }
//
//
//// UPDATE
////    function updateMaintenanceStatus($message, $status, $no){
////        $pdo = pdoSqlConnect();
////        $query = "UPDATE MAINTENANCE
////                        SET MESSAGE = ?,
////                            STATUS  = ?
////                        WHERE NO = ?";
////
////        $st = $pdo->prepare($query);
////        $st->execute([$message, $status, $no]);
////        $st = null;
////        $pdo = null;
////    }
//
//// RETURN BOOLEAN
////    function isRedundantEmail($email){
////        $pdo = pdoSqlConnect();
////        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
////
////
////        $st = $pdo->prepare($query);
////        //    $st->execute([$param,$param]);
////        $st->execute([$email]);
////        $st->setFetchMode(PDO::FETCH_ASSOC);
////        $res = $st->fetchAll();
////
////        $st=null;$pdo = null;
////
////        return intval($res[0]["exist"]);
////
////    }
