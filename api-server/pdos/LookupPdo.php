<?php


function getOpenStoreNoCat($latitude,$longitude,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
           select s.storeIdx,
                  s.storeCatIdx,
                CASE
                   WHEN CHAR_LENGTH(s.storeName) > 10
                       THEN concat(LEFT(s.storeName, 10), '...')
                   ELSE s.storeName
               END AS storeName,
                (select ROUND(avg(reviewStar),1) as avg
                from Review as r
                where r.storeIdx=s.storeIdx
                group by storeIdx) as storeStar,
               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount,
                CASE
                   WHEN s.deliveryFee=-1
                       THEN '무료배달'
                   ELSE concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원')
               END AS deliveryFee,
              (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) as coupon,
                (select sp.storePhoto from StorePhoto as sp where sp.sequence=1 and sp.isDeleted='N' and sp.storeIdx=s.storeIdx) as storePhoto,
               concat(ROUND((6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
                 + sin(radians(?)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s
        where s.isDeleted='N' 
                and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
              and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
                 + sin(radians(?)) * sin(radians(s.latitude)))) < 5
                and s.storeCatIdx=?
       order by s.createdAt desc limit 5 ;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$latitude,$longitude,$latitude,$latitude,$longitude,$latitude,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOpenStoreCat($userIdxInToken,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
       select s.storeIdx,
              s.storeCatIdx,
                CASE
                   WHEN CHAR_LENGTH(s.storeName) > 10
                       THEN concat(LEFT(s.storeName, 10), '...')
                   ELSE s.storeName
               END AS storeName,
                (select ROUND(avg(reviewStar),1) as avg
                from Review as r
                where r.storeIdx=s.storeIdx
                group by storeIdx) as storeStar,
               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount,
                CASE
                   WHEN s.deliveryFee=-1
                       THEN '무료배달'
                   ELSE concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원')
               END AS deliveryFee,
              (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) as coupon,
               end as coupon,
                (select sp.storePhoto from StorePhoto as sp where sp.sequence=1 and sp.isDeleted='N' and sp.storeIdx=s.storeIdx) as storePhoto,
               concat(ROUND((6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
                 + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s, UserInfo as us
        where s.isDeleted='N' and us.isDeleted='N' and us.userIdx=?
                and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
              and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
                 + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
                    and s.storeCatIdx=?
       order by s.createdAt desc limit 5 ;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdxInToken,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getCategoryName($storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select storeCatIdx,storeCatName from StoreCategory where isDeleted='N'and storeCatIdx=?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// keyword 키워드로 조회
// 비회원 키워드

// 회원 아닐때
//function getOrderByNew1NoKeyword($cheetah,$mincost,$keyword)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//    select s.storeIdx,s.createdAt
//from Store as s
//where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
//      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
//      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx)
//        and s.storeName like concat('%',if(isnull(?),'',?),'%');
//order by s.createdAt DESC;
//";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$keyword,$keyword]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}


// 추천순 회/비
function getOrderByRecommend1Keyword($cheetah,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByRecommend2Keyword($cheetah,$deliveryfee,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by s.deliveryFee asc,orderCount desc;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByRecommend3Keyword($cheetah,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByRecommend4Keyword($cheetah,$deliveryfee,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 주문많은순 회/비
function getOrderByMany1Keyword($cheetah,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByMany2Keyword($cheetah,$deliveryfee,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by orderCount desc;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByMany3Keyword($cheetah,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
        and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByMany4Keyword($cheetah,$deliveryfee,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew1Keyword($cheetah,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))     
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew2Keyword($cheetah,$deliveryfee,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by s.createdAt DESC;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew3Keyword($cheetah,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew4Keyword($cheetah,$deliveryfee,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)   
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar1Keyword($cheetah,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) 
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar2Keyword($cheetah,$deliveryfee,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) 
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by storeStar DESC;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar3Keyword($cheetah,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N'and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar4Keyword($cheetah,$deliveryfee,$mincost,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?) 
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear1Keyword($cheetah,$mincost,$userIdxInToken,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) and us.isDeleted='N' and us.userIdx=?
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear2Keyword($cheetah,$deliveryfee,$mincost,$userIdxInToken,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) and us.isDeleted='N' and us.userIdx=?
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear3Keyword($cheetah,$mincost,$userIdxInToken,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) 
        and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?) 
        and us.isDeleted='N' and us.userIdx=?
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;

";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear4Keyword($cheetah,$deliveryfee,$mincost,$userIdxInToken,$keyword)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?) and us.isDeleted='N' and us.userIdx=?
        and s.storeName like concat('%',if(isnull(?),'',?),'%')
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken,$keyword,$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//
//function getOrderByOneLookup($storeIdx,$userIdxInToken)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select s.storeIdx, s.storeName, s.isCheetah,
//                (select ROUND(avg(reviewStar),1) as avg
//                from Review as r
//                where r.storeIdx=s.storeIdx
//                group by storeIdx) as storeStar,
//               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount,
//                CASE
//                   WHEN s.deliveryFee=-1
//                       THEN '무료배달'
//                   ELSE concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원')
//               END AS deliveryFee,
//               s.deliveryTime,
//              (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
//                from Coupon as c
//                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
//                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) as coupon,
//               concat(ROUND((6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
//                 + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ,1),'km') as distance,
//               case
//                when TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
//                then 'Y'
//                else 'N'
//                end as isnewStore
//        from Store as s,UserInfo as us
//        where s.isDeleted='N' and us.isDeleted='N' and s.storeIdx=? and userIdx=?;";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([$storeIdx,$userIdxInToken]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}
//
//function getOrderByOneNoLookup($storeIdx,$latitude,$longitude)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select s.storeIdx, s.storeName, s.isCheetah,
//                (select ROUND(avg(reviewStar),1) as avg
//                from Review as r
//                where r.storeIdx=s.storeIdx
//                group by storeIdx) as storeStar,
//               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount,
//                CASE
//                   WHEN s.deliveryFee=-1
//                       THEN '무료배달'
//                   ELSE concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원')
//               END AS deliveryFee,
//               s.deliveryTime,
//              (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
//                from Coupon as c
//                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
//                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) as coupon,
//               concat(ROUND((6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
//                 + sin(radians(?)) * sin(radians(s.latitude)))) ,1),'km') as distance,
//               case
//                when TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
//                then 'Y'
//                else 'N'
//                end as isnewStore
//        from Store as s
//        where s.isDeleted='N' and s.storeIdx=? ;";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([$latitude,$longitude,$latitude,$storeIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}

// 프랜차이즈 조회

// 추천순 회/비
function getOrderByRecommend1Fran($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))   
            and s.isFranchise = 'Y'
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByRecommend2Fran($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
        and s.isFranchise = 'Y'
order by s.deliveryFee asc,orderCount desc;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByRecommend3Fran($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
        and s.isFranchise = 'Y'
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByRecommend4Fran($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
        and s.isFranchise = 'Y'
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 주문많은순 회/비
function getOrderByMany1Fran($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
        and s.isFranchise = 'Y'
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByMany2Fran($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
        and s.isFranchise = 'Y'
order by orderCount desc;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByMany3Fran($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
        and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
        and s.isFranchise = 'Y'
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByMany4Fran($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
        and s.isFranchise = 'Y'
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew1Fran($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))    
        and s.isFranchise = 'Y'
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew2Fran($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
        and s.isFranchise = 'Y'
order by s.createdAt DESC;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew3Fran($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
        and s.isFranchise = 'Y'
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew4Fran($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)      
        and s.isFranchise = 'Y'
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar1Fran($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) 
        and s.isFranchise = 'Y'
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar2Fran($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) 
        and s.isFranchise = 'Y'
order by storeStar DESC;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar3Fran($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N'and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
        and s.isFranchise = 'Y'
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar4Fran($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?) 
        and s.isFranchise = 'Y'
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear1Fran($cheetah,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) and us.isDeleted='N' and us.userIdx=?
        and s.isFranchise = 'Y'
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear2Fran($cheetah,$deliveryfee,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) and us.isDeleted='N' and us.userIdx=?
        and s.isFranchise = 'Y'
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear3Fran($cheetah,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) 
        and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?) 
        and us.isDeleted='N' and us.userIdx=?
        and s.isFranchise = 'Y'
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;

";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear4Fran($cheetah,$deliveryfee,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?) and us.isDeleted='N' and us.userIdx=?
        and s.isFranchise = 'Y'
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


// 새로 들어왔어요 조회



//function getOrderByNew1New($cheetah,$mincost,$userIdxInToken)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select s.storeIdx,s.createdAt,
//       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
//    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
//from Store as s, UserInfo as us
//where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
//      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
//      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx) and us.isDeleted='N' and us.userIdx=?
//      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
//    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
//    and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
//order by s.createdAt DESC;
//";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}

function getOrderByRecommend1New($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByRecommend2New($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by s.deliveryFee asc,orderCount desc;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByRecommend3New($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByRecommend4New($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 주문많은순 회/비
function getOrderByMany1New($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByMany2New($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by orderCount desc;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByMany3New($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
        and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByMany4New($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew1New($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew2New($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by s.createdAt DESC;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew3New($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew4New($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar1New($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) 
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar2New($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) 
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by storeStar DESC;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar3New($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N'and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar4New($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?) 
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear1New($cheetah,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) and us.isDeleted='N' and us.userIdx=?
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear2New($cheetah,$deliveryfee,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) and us.isDeleted='N' and us.userIdx=?
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear3New($cheetah,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) 
        and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?) 
        and us.isDeleted='N' and us.userIdx=?
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;

";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear4New($cheetah,$deliveryfee,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?) and us.isDeleted='N' and us.userIdx=?
         and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 카테고리 세부 조회
// 4개 코드
//
////예제
//// 추천순 회/비
//function getOrderByNew1Cat($cheetah,$mincost,$userIdxInToken,$storeCatIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select s.storeIdx,s.createdAt,
//       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
//    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
//from Store as s, UserInfo as us
//where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
//      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
//      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx) and us.isDeleted='N' and us.userIdx=?
//      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
//    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
//    and s.storeCatIdx=?
//order by s.createdAt DESC;
//";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken,$storeCatIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}


function getOrderByRecommend1Cat($cheetah,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
     and s.storeCatIdx=?    
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByRecommend2Cat($cheetah,$deliveryfee,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
     and s.storeCatIdx=?    
order by s.deliveryFee asc,orderCount desc;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByRecommend3Cat($cheetah,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
     and s.storeCatIdx=?    
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByRecommend4Cat($cheetah,$deliveryfee,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.deliveryFee,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
     and s.storeCatIdx=?    
order by s.deliveryFee asc,orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 주문많은순 회/비
function getOrderByMany1Cat($cheetah,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
     and s.storeCatIdx=?    
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByMany2Cat($cheetah,$deliveryfee,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
     and s.storeCatIdx=?    
order by orderCount desc;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByMany3Cat($cheetah,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
        and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
     and s.storeCatIdx=?    
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByMany4Cat($cheetah,$deliveryfee,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (select sum(storeIdx)
        from OrderInfo as oi
        where isDeleted='N' and oi.storeIdx=s.storeIdx
        group by storeIdx) as orderCount
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
     and s.storeCatIdx=?    
order by orderCount desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew1Cat($cheetah,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
     and s.storeCatIdx=?    
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew2Cat($cheetah,$deliveryfee,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
     and s.storeCatIdx=?    
order by s.createdAt DESC;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew3Cat($cheetah,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
     and s.storeCatIdx=?    
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew4Cat($cheetah,$deliveryfee,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
     and s.storeCatIdx=?    
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar1Cat($cheetah,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) 
     and s.storeCatIdx=?    
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar2Cat($cheetah,$deliveryfee,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) 
     and s.storeCatIdx=?    
order by storeStar DESC;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar3Cat($cheetah,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N'and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
     and s.storeCatIdx=?    
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar4Cat($cheetah,$deliveryfee,$mincost,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?) 
     and s.storeCatIdx=?    
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear1Cat($cheetah,$mincost,$userIdxInToken,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) and us.isDeleted='N' and us.userIdx=?
     and s.storeCatIdx=?    
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear2Cat($cheetah,$deliveryfee,$mincost,$userIdxInToken,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now()))) and us.isDeleted='N' and us.userIdx=?
     and s.storeCatIdx=?    
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear3Cat($cheetah,$mincost,$userIdxInToken,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) 
        and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?) 
        and us.isDeleted='N' and us.userIdx=?
     and s.storeCatIdx=?    
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;

";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear4Cat($cheetah,$deliveryfee,$mincost,$userIdxInToken,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
        (select ROUND(avg(reviewStar),1) as avg
        from Review as r
        where r.storeIdx=s.storeIdx
        group by storeIdx) as storeStar,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?) and us.isDeleted='N' and us.userIdx=?
     and s.storeCatIdx=?    
order by (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken,$storeCatIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear1NoCat($cheetah,$mincost,$latitude,$longitude,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) as distance
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
     and s.storeCatIdx=?    
order by (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude,$latitude,$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear2NoCat($cheetah,$deliveryfee,$mincost,$latitude,$longitude,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) as distance
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                from Coupon as c
                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
     and s.storeCatIdx=?  
order by (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) ;
";
    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude,$latitude,$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear3NoCat($cheetah,$mincost,$latitude,$longitude,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) as distance
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
     and s.storeCatIdx=?                         
order by (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) ;

";

    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude,$latitude,$cheetah,$cheetah,$mincost,$mincost,$storeCatIdx,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear4NoCat($cheetah,$deliveryfee,$mincost,$latitude,$longitude,$storeCatIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) as distance
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
     and s.storeCatIdx=?  
order by (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude,$latitude,$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$storeCatIdx,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

