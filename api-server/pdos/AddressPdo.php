<?php
function setCurrentAddress($latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
        select address,buildingName
        from Address
        where latitude=? and longitude=? and isDeleted='N';
";
//    $query = "
//        select address,buildingName
//        from Address
//        where isDeleted='N';
//";
    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];

}

//function getCurrentAddress($latitude,$longitude)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select addressIdx,address,buildingName
//        from Address
//        where latitude=? and longitude=? and isDeleted='N';
//";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$latitude,$longitude]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//
//}


function isValidLatLon($latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
    select EXISTS(
        select address,buildingName
        from Address
        where latitude=? and longitude=? and isDeleted='N')exist;";

    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function updateDeliveryAddress($address,$buildingName)
{
    $pdo = pdoSqlConnect();
    $query = "update User set deliveryAddress = ? , deliveryBuildingName=? where isDeleted='N' and userIdx=1;";

    $st = $pdo->prepare($query);
    $st->execute([$address,$buildingName]);

    $st = null;
    $pdo = null;

}



function isValidKeyword($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
    select EXISTS(
        select * from Address 
        where buildingname like concat('%',?,'%') or address like concat('%',?,'%') 
              and isDeleted='N')exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);

}


function getKeywordAddress($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
        select addressIdx,buildingname,address from Address
        where buildingname like concat('%',?,'%') or address like concat('%',?,'%') 
              and isDeleted='N';";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function isValidAddressIdx($keyword,$addressIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
    select EXISTS(
        select addressIdx,buildingname,address from Address
        where buildingname like concat('%',?,'%') or address like concat('%',?,'%') 
              and addressIdx=? and isDeleted='N')exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword,$keyword,$addressIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}
function getSelectedAddress($keyword,$addressIdx)
{
    $pdo = pdoSqlConnect();
//    $query = "
//        select buildingname,address from Address
//        where addressIdx=? and isDeleted='N';";
    $query = "
        select buildingname,address from Address
        where buildingname like concat('%',?,'%') or address like concat('%',?,'%') 
              and addressIdx=? and isDeleted='N'";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$keyword,$keyword,$addressIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
