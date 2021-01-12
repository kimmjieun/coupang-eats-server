<?php

function receiveCoupon($couponIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into UserCoupon(couponIdx,userIdx) values (?,?);";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$couponIdx,$userIdx]);
    $st = null;
    $pdo = null;

    return ['couponIdx'=>$couponIdx,'userIdx'=>$userIdx];
}
function isValidUserCoupon($couponIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (select couponIdx from UserCoupon where isDeleted='N' and couponIdx=? and userIdx=?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$couponIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isValidStoreCoupon($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select exists (select couponIdx from StoreCoupon where isDeleted='N' and storeIdx=?
            and couponIdx in (select couponIdx
                                from Coupon
                                where isDeleted='N'and date(expiredAt) >= date(now()) )) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}
function getCouponInfo($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select couponIdx, salePrice
from Coupon
where couponIdx = (select couponIdx from StoreCoupon where isDeleted='N' and storeIdx=?) and date(expiredAt) >= date(now()) ;

    ";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]; // 배열에 첫번쨰원소만

}

function isValidUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from UserInfo where userIdx = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getUserCoupon($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select couponIdx, couponTitle,
       case
           when date(expiredAt) >= date(now())
           then concat(date_format(expiredAt,'%m/%d'),'까지')
           else '기간만료'
        end as expiredAt,
       concat(cast(FORMAT(salePrice, 0) as char), '원 할인') as salePrice,
       concat(cast(FORMAT(minPrice, 0) as char), '원 이상 주문시') as minCost
from Coupon
where couponIdx in (select  couponIdx
                from UserCoupon
                where isDeleted='N' and userIdx=?)
order by expiredAt asc;    
    ";



        $st1 = $pdo->prepare($query);


        $st1->execute([$userIdx]);


        $st1->execute([$userIdx]);
        $st1->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st1->fetchAll();


    $st1 = null;
    $pdo = null;

    return $res;
}
