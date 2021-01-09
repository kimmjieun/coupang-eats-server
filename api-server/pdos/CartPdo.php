<?php


function isDifferentStore($storeIdx,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select storeIdx from Cart where isDeleted ='N' and storeIdx = ? and userIdx=? ) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}
function isCartInUser($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select storeIdx from Cart where isDeleted ='N' and userIdx=? ) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function mandatoryCat($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select optCatIdx from MenuOptionCat where menuIdx=? and mandatory='Y';";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}

function mandatoryCatOne($menuIdx,$menuOptIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select o.optCatIdx
        from MenuOption as o
        join MenuOptionCat as oc on o.optCatIdx=oc.optCatIdx and o.menuIdx=oc.menuIdx
        where o.isDeleted='N' and o.menuIdx=? and o.menuOptIdx=? and oc.mandatory='Y';";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx,$menuOptIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;


    return $res;
}


function getInputOptCat($menuIdx,$menuOptIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select o.optCatIdx
        from MenuOption as o
        join MenuOptionCat as oc on o.optCatIdx=oc.optCatIdx and o.menuIdx=oc.menuIdx
        where o.isDeleted='N' and o.menuIdx=? and o.menuOptIdx=? ;";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx,$menuOptIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;


    return $res;
}


function getMaxSelect($menuIdx,$menuOptIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select oc.maxSelect
        from MenuOption as o
        join MenuOptionCat as oc on o.optCatIdx=oc.optCatIdx and o.menuIdx=oc.menuIdx
        where o.isDeleted='N' and  oc.isDeleted='N' and  o.menuIdx=? and o.menuOptIdx=? ;";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx,$menuOptIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;


    return $res;
}

function addCart($userIdxInToken,$menuIdx,$quantity,$storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into Cart(userIdx,menuIdx,quantity,storeIdx) values(?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken,$menuIdx,$quantity,$storeIdx]);

    $st = null;
    $pdo = null;

    return ['userIdx'=>$userIdxInToken,'storeIdx'=>$storeIdx,'menuIdx'=>$menuIdx,'quantity'=>$quantity];
}

function addOptionCart($userIdxInToken,$menuIdx,$optionIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into OptionCart(userIdx,menuIdx,optIdx) values(?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken,$menuIdx,$optionIdx]);

    $st = null;
    $pdo = null;

    return $optionIdx;
}


