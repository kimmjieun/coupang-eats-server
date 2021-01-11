<?php


function getPromotion()
{
    $pdo = pdoSqlConnect();
    $query = "select promotionIdx, title, promotionPhoto from Promotion where isDeleted='N';";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getCategory()
{
    $pdo = pdoSqlConnect();
    $query = "select storeCatIdx, storeCatName from StoreCategory where isDeleted='N';";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getFranchiseNo($latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
        select s.storeIdx,
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
               case
                   when exists(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       then (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       else '-1'
               end as coupon,
                (select sp.storePhoto from StorePhoto as sp where sp.sequence=1 and sp.isDeleted='N' and sp.storeIdx=s.storeIdx) as storePhoto,
               concat(ROUND((6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
                 + sin(radians(?)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s
        where s.isDeleted='N' and isFranchise='Y'
                  and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
        order by s.createdAt desc limit 5;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$latitude,$longitude,$latitude,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOpenStoreNo($latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
           select s.storeIdx,
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
               case
                   when exists(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       then (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       else '-1'
               end as coupon,
                (select sp.storePhoto from StorePhoto as sp where sp.sequence=1 and sp.isDeleted='N' and sp.storeIdx=s.storeIdx) as storePhoto,
               concat(ROUND((6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
                 + sin(radians(?)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s
        where s.isDeleted='N' 
                and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
              and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
                 + sin(radians(?)) * sin(radians(s.latitude)))) < 5
       order by s.createdAt desc limit 5 ;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$latitude,$longitude,$latitude,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getFranchise($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
       select s.storeIdx,
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
               case
                   when exists(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       then (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       else '-1'
               end as coupon,
                (select sp.storePhoto from StorePhoto as sp where sp.sequence=1 and sp.isDeleted='N' and sp.storeIdx=s.storeIdx) as storePhoto,
               concat(ROUND((6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
                 + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s, UserInfo as us
        where s.isDeleted='N' and isFranchise='Y'  and us.isDeleted='N' and us.userIdx=? 
              and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
                 + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
       order by s.createdAt desc limit 5 ;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOpenStore($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
       select s.storeIdx,
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
               case
                   when exists(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       then (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       else '-1'
               end as coupon,
                (select sp.storePhoto from StorePhoto as sp where sp.sequence=1 and sp.isDeleted='N' and sp.storeIdx=s.storeIdx) as storePhoto,
               concat(ROUND((6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
                 + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s, UserInfo as us
        where s.isDeleted='N' and us.isDeleted='N' and us.userIdx=?
                and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30
              and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
                 + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
       order by s.createdAt desc limit 5 ;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
//
//function getStore()
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select s.storeIdx,s.storeName, s.storeStar,
//               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount,
//               concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원') as deliveryFee,
//               s.deliveryTime,
//               case
//                   when exists(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
//                                from Coupon as c
//                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
//                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
//                       then (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
//                                from Coupon as c
//                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
//                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
//                       else '-1'
//               end as coupon,
//                concat(ROUND((6371 *acos(cos(radians(37.3343011791693)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(126.86714856212525))
//                    + sin(radians(37.3343011791693)) * sin(radians(s.latitude)))) ,1),'km') as distance
//        from Store as s
//        where s.isDeleted='N';";
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

function test($mincost)
{
    $pdo = pdoSqlConnect();
    $query = "select storeIdx,minOrderCost from Store where  isDeleted='N' and if(isnull(?),minOrderCost,?);
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$mincost,$mincost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getUserAddress($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select userIdx,
    CASE
       WHEN CHAR_LENGTH(deliveryBuildingName) > 15
           THEN concat(LEFT(deliveryBuildingName, 15), '...')
       ELSE deliveryBuildingName
   END AS addressName
from UserInfo
where isDeleted='N' and userIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;


}
function getOrderByNew()
{
    $pdo = pdoSqlConnect();
    $query = "
        select storeIdx,createdAt
        from Store where isDeleted='N'
        order by createdAt DESC;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function getOrderByNear()
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx ,
       concat(ROUND((6371 *acos(cos(radians(37.3343011791693)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(126.86714856212525))
    + sin(radians(37.3343011791693)) * sin(radians(s.latitude)))) ,1),'km') as distance
from Store as s where isDeleted='N'
order by (6371 *acos(cos(radians(37.3343011791693)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(126.86714856212525))
    + sin(radians(37.3343011791693)) * sin(radians(s.latitude)))) ;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderBy1($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx)
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

function getOrderBy2($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx)
order by s.createdAt DESC;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderBy3($cheetah,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
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

function getOrderBy4($cheetah,$deliveryfee,$mincost)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
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
function getOrderByNew1($cheetah,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "select s.storeIdx,s.createdAt,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew2($cheetah,$deliveryfee,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
order by s.createdAt DESC;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew3($cheetah,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew4($cheetah,$deliveryfee,$mincost,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt,
       (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) as distance
from Store as s, UserInfo as us
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar1($cheetah,$mincost,$userIdxInToken)
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
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar2($cheetah,$deliveryfee,$mincost,$userIdxInToken)
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
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
order by storeStar DESC;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar3($cheetah,$mincost,$userIdxInToken)
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
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar4($cheetah,$deliveryfee,$mincost,$userIdxInToken)
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
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee<=if(isnull(?),s.deliveryFee,?) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear1($cheetah,$mincost,$userIdxInToken)
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
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
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

function getOrderByNear2($cheetah,$deliveryfee,$mincost,$userIdxInToken)
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
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
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
function getOrderByNear3($cheetah,$mincost,$userIdxInToken)
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
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?) and us.isDeleted='N' and us.userIdx=?
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
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

function getOrderByNear4($cheetah,$deliveryfee,$mincost,$userIdxInToken)
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
      and (6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
    + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) < 5
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
function getOrderByStar()
{
    $pdo = pdoSqlConnect();
    $query = "
        select s.storeIdx ,
                (select ROUND(avg(reviewStar),1) as avg
                        from Review as r
                        where r.storeIdx=s.storeIdx
                        group by storeIdx) as storeStar
        from Store as s where isDeleted='N'
        order by storeStar DESC;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByOne($storeIdx,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
        select s.storeIdx, s.storeName, s.isCheetah,
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
               s.deliveryTime,
               case
                   when exists(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       then (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       else '-1'
               end as coupon,
               concat(ROUND((6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
                 + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s,UserInfo as us
        where s.isDeleted='N' and us.isDeleted='N' and s.storeIdx=? and userIdx=?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$storeIdx,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getOrderByOneNo($storeIdx,$latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
        select s.storeIdx, s.storeName, s.isCheetah,
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
               s.deliveryTime,
               case
                   when exists(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       then (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       else '-1'
               end as coupon,
               concat(ROUND((6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
                 + sin(radians(?)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s
        where s.isDeleted='N' and s.storeIdx=? ;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$longitude,$longitude,$longitude,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


// 회원 아닐때
function getOrderByNew1No($cheetah,$mincost,$latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
    select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx)
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew2No($cheetah,$deliveryfee,$mincost,$latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx)
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by s.createdAt DESC;
";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNew3No($cheetah,$mincost,$latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNew4No($cheetah,$deliveryfee,$mincost,$latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,s.createdAt
from Store as s
where s.isDeleted='N' and s.isCheetah=if(isnull(?),s.isCheetah,?)
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and s.deliveryFee=if(isnull(?),s.deliveryFee,?)
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by s.createdAt DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar1No($cheetah,$mincost,$latitude,$longitude)
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
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx)
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar2No($cheetah,$deliveryfee,$mincost,$latitude,$longitude)
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
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx)
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by storeStar DESC;

";
    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByStar3No($cheetah,$mincost,$latitude,$longitude)
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
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by storeStar DESC;

";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByStar4No($cheetah,$deliveryfee,$mincost,$latitude,$longitude)
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
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by storeStar DESC;
";

    $st = $pdo->prepare($query);
    $st->execute([$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear1No($cheetah,$mincost,$latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) as distance
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx)
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude,$latitude,$cheetah,$cheetah,$mincost,$mincost,$latitude,$longitude,$latitude,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear2No($cheetah,$deliveryfee,$mincost,$latitude,$longitude)
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
      and EXISTS(select sc.couponIdx from StoreCoupon as sc where sc.storeIdx=s.storeIdx)
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) ;
";
    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude,$latitude,$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$latitude,$longitude,$latitude,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getOrderByNear3No($cheetah,$mincost,$latitude,$longitude)
{
    $pdo = pdoSqlConnect();
    $query = "
select s.storeIdx,
       (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) as distance
from Store as s
where s.isDeleted='N'  and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee =-1
      and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) ;

";

    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude,$latitude,$cheetah,$cheetah,$mincost,$mincost,$latitude,$longitude,$latitude,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOrderByNear4No($cheetah,$deliveryfee,$mincost,$latitude,$longitude)
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
      and (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) < 5
order by (6371 *acos(cos(radians(?)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(?))
    + sin(radians(?)) * sin(radians(s.latitude)))) ;
";

    $st = $pdo->prepare($query);
    $st->execute([$latitude,$longitude,$latitude,$cheetah,$cheetah,$mincost,$mincost,$deliveryfee,$deliveryfee,$latitude,$longitude,$latitude,$latitude,$longitude,$latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//
//function getStoreFranchiseLastIdx(){
//    $pdo = pdoSqlConnect();
//    $query = "select storeIdx from Store where isDeleted ='N' and isFranchise= 'Y' order by createdAt desc limit 1;";
//
//    $st = $pdo->prepare($query);
//    $st->execute();
//
//    $row = $st -> fetchColumn(); // 컬럼하나의 값만!
//    //$st = null;
//    $pdo = null;
//    return $row;
//}

function isValidStore($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select storeIdx from Store where isDeleted ='N' and storeIdx = ? ) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isValidMenu($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select menuIdx from Menu where isDeleted ='N' and menuIdx = ? ) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}



function isValidFranchise($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select storeIdx 
                            from Store 
                            where isDeleted ='N' and storeIdx = ?  and isFranchise='Y') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}
function isValidNewStore($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select storeIdx 
                            from Store 
                            where isDeleted ='N' and storeIdx = ?  
                              and TIMESTAMPDIFF(DAY, createdAt, current_timestamp())<30 ) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}



function getStoreImg($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT storePhoto FROM StorePhoto WHERE storeIdx = ? and isDeleted='N';";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;
}
function getMenuImg($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select menuPhoto from MenuPhoto where menuIdx=? and isDeleted='N';";
    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;
}

function getStoreInfo($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select s.storeName, s.storeStar,s.deliveryTime,
               concat(cast(FORMAT(s.deliveryFee, 0) as char), '원') as deliveryFee,
               concat(cast(FORMAT(s.minOrderCost, 0) as char), '원') as minOrderCost,
               (select ROUND(avg(reviewStar),1) as avg
                from Review
                where storeIdx=?
                group by storeIdx) as storeStar,
               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount
        from Store as s
        where s.storeIdx=? and s.isDeleted='N';";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;
}

function getMenuInfo($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select menuName, menuDetail,
               concat(cast(FORMAT(menuPrice, 0) as char), '원') as menuPrice
        from Menu
        where menuIdx=? and isDeleted='N';";
    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;
}


function getPhotoReview($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select r.reviewIdx, r.content, r.reviewStar, rp.reviewPhoto
        from Review as r
        join ReviewPhoto as rp on r.reviewIdx=rp.reviewIdx
        where r.storeIdx=? and rp.sequence=1 and r.isDeleted='N' and rp.isDeleted='N'
        ORDER BY r.createdAt DESC LIMIT 3;";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;
}




function getCatCount($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select count(*) as catCount
        from MenuCategory
        where storeIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;


}

function getOptCatCount($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select count(*) as menuOptCatIdx
        from MenuOptionCat
        where menuIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;


}
function getCatName($storeIdx,$catIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select menuCatName
        from MenuCategory
        where storeIdx=? and menuCatIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$catIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;


}

function getOptCatName($menuIdx,$catIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select menuOptCatName from MenuOptionCat
        where menuIdx =? and optCatIdx=? and isDeleted='N';";
    $st = $pdo->prepare($query);
    $st->execute([$menuIdx,$catIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;


}
function getCatDetail($storeIdx,$catIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select menuCatDetail
        from MenuCategory
        where storeIdx=? and menuCatIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$catIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;


}
function getMenuCategory($storeIdx,$catIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select menuIdx, menuname, menuDetail,
               concat(cast(FORMAT(menuPrice, 0) as char), '원') as menuPrice,
               menuThumbnail
        from Menu
        where storeIdx=? and menuCatIdx =? and isDeleted='N' ;";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$catIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;
}

function getOptMenuCategory($menuIdx,$catIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select menuOptIdx, menuOptName,
               concat(cast(FORMAT(optPrice, 0) as char), '원') as menuPrice
        from MenuOption
        where menuIdx=? and optCatIdx =? and isDeleted='N' ;    
";
    $st = $pdo->prepare($query);
    $st->execute([$menuIdx,$catIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;
}
function hartStore($storeIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Hart (storeIdx,userIdx) VALUES (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$userIdx]);

    $st = null;
    $pdo = null;

    return ['storeIdx'=>$storeIdx,'userIdx'=>$userIdx];
}



function deleteHart($storeIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "update Hart set isDeleted='Y' where storeIdx=? and userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$userIdx]);

    $st = null;
    $pdo = null;

    return "성공";
}

function isHart($storeIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select storeIdx from Hart where storeIdx = ? and userIdx=? and isDeleted='N') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function getHartStore($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
       select s.storeIdx,
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
               s.deliveryTime ,
               case
                   when exists(select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       then (select concat(cast(FORMAT(c.salePrice, 0) as char), '원 할인쿠폰')
                                from Coupon as c
                                where c.couponIdx=(select sc.couponIdx from StoreCoupon as sc
                                                    where s.storeIdx = sc.storeIdx and date(c.expiredAt) >= date(now())))
                       else '-1'
               end as coupon,
                (select sp.storePhoto from StorePhoto as sp where sp.sequence=1 and sp.isDeleted='N' and sp.storeIdx=s.storeIdx) as storePhoto,
               concat(ROUND((6371 *acos(cos(radians(us.deliveryLat)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(us.deliveryLon))
                 + sin(radians(us.deliveryLat)) * sin(radians(s.latitude)))) ,1),'km') as distance,
              s.isCheetah
        from Store as s, UserInfo as us
        where s.isDeleted='N' and us.isDeleted='N' and us.userIdx=?
            and s.storeIdx IN (select storeIdx
                            from Hart
                            where userIdx=? and isDeleted='N')
       order by s.createdAt desc ;    
    ";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

function getHartCount($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select count(storeIdx) as count
from Hart
where userIdx=? and isDeleted='N';
    ";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();
    $st = null;
    $pdo = null;

    return $res;
}

function getPromotionDetail($promotionIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select title,promotionDetailPhoto
from Promotion
where isDeleted='N' and promotionIdx=? ;
    ";

    $st = $pdo->prepare($query);
    $st->execute([$promotionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;

}
function isValidPromotion($promotionIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select promotionIdx from Promotion where isDeleted ='N' and promotionIdx = ? ) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$promotionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}




//function getStoreIntroduce($storeIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select storeName, telephoneNumber,address, representation, corporateNumber,businessName, officeHour,
//       storeIntroduce, originInfo,nutrientInfo,allergyInfo from Store where isDeleted='N' and storeIdx=?;";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([$storeIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
