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


function getFranchise()
{
    $pdo = pdoSqlConnect();
    $query = "
        select s.storeIdx,
                CASE
                   WHEN CHAR_LENGTH(s.storeName) > 10
                       THEN concat(LEFT(s.storeName, 10), '...')
                   ELSE s.storeName
               END AS storeName,
               s.storeStar,
               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount,
               concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원') as deliveryFee,
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
                concat(ROUND((6371 *acos(cos(radians(37.3343011791693)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(126.86714856212525))
                    + sin(radians(37.3343011791693)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s
        where s.isDeleted='N' and isFranchise='Y';";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOpenStore()
{
    $pdo = pdoSqlConnect();
    $query = "
        select s.storeIdx,
                CASE
                   WHEN CHAR_LENGTH(s.storeName) > 10
                       THEN concat(LEFT(s.storeName, 10), '...')
                   ELSE s.storeName
               END AS storeName,
               concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원') as deliveryFee,
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
                concat(ROUND((6371 *acos(cos(radians(37.3343011791693)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(126.86714856212525))
                    + sin(radians(37.3343011791693)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s
        where s.isDeleted='N' and TIMESTAMPDIFF(DAY, s.createdAt, current_timestamp())<30 ;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getStore()
{
    $pdo = pdoSqlConnect();
    $query = "
        select s.storeIdx,s.storeName, s.storeStar,
               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount,
               concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원') as deliveryFee,
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
                concat(ROUND((6371 *acos(cos(radians(37.3343011791693)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(126.86714856212525))
                    + sin(radians(37.3343011791693)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s
        where s.isDeleted='N';";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getStoreLastIdx(){
    $pdo = pdoSqlConnect();
    $query = "select storeIdx from Store where isDeleted ='N' order by createdAt desc limit 1;";

    $st = $pdo->prepare($query);
    $st->execute();

    $row = $st -> fetchColumn(); // 컬럼하나의 값만!
    //$st = null;
    $pdo = null;
    return $row;
}

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

function getStoreOne($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
        select s.storeIdx, s.storeName, s.storeStar,
               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount,
               concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원') as deliveryFee,
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
                concat(ROUND((6371 *acos(cos(radians(37.3343011791693)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(126.86714856212525))
                    + sin(radians(37.3343011791693)) * sin(radians(s.latitude)))) ,1),'km') as distance
        from Store as s
        where s.isDeleted='N' and s.storeIdx=?;
";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
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


function getStoreThumbnail($storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT storeThumbnail FROM StoreThumbnail WHERE storeIdx = ? and isDeleted='N';";
    $st = $pdo->prepare($query);
    $st->execute([$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    // fetcho one으로 해서 for문돌리기 행전체수만큼

    $st = null;
    $pdo = null;

    return $res;
}
//function getStoreInfo($storeIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select s.storeName, s.storeStar,s.deliveryTime,
//               concat('배달비 ',cast(FORMAT(s.deliveryFee, 0) as char), '원') as deliveryFee,
//               concat('최소주문 ',cast(FORMAT(s.minOrderCost, 0) as char), '원') as minOrderCost,
//               (select count(*) from Review as r  where s.storeIdx=r.storeIdx and isDeleted='N') as reviewCount
//        from Store as s
//        where s.storeIdx=? and s.isDeleted='N';";
//    $st = $pdo->prepare($query);
//    $st->execute([$storeIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//    // fetcho one으로 해서 for문돌리기 행전체수만큼
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//function getPhotoReview($storeIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select r.reviewIdx, r.content, r.reviewStar, rp.reviewPhoto
//        from Review as r
//        join ReviewPhoto as rp on r.reviewIdx=rp.reviewIdx
//        where r.storeIdx=? and rp.sequence=1 and r.isDeleted='N' and rp.isDeleted='N'
//        ORDER BY r.createdAt DESC LIMIT 3;";
//    $st = $pdo->prepare($query);
//    $st->execute([$storeIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//    // fetcho one으로 해서 for문돌리기 행전체수만큼
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//function getMenuCat1($storeIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select menuIdx, menuname, menuDetail, menuCatIdx,
//               concat(cast(FORMAT(menuPrice, 0) as char), '원') as menuPrice
//        from Menu
//        where storeIdx=? and menuCatIdx =1 and isDeleted='N' ;";
//    $st = $pdo->prepare($query);
//    $st->execute([$storeIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//    // fetcho one으로 해서 for문돌리기 행전체수만큼
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//function getMenuCat2($storeIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select menuIdx, menuname, menuDetail, menuCatIdx,
//               concat(cast(FORMAT(menuPrice, 0) as char), '원') as menuPrice
//        from Menu
//        where storeIdx=? and menuCatIdx =2 and isDeleted='N' ;";
//    $st = $pdo->prepare($query);
//    $st->execute([$storeIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//    // fetcho one으로 해서 for문돌리기 행전체수만큼
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//
//
//}


//
//function getCatCount($storeIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select count(*) as catCount
//        from MenuCategory
//        where storeIdx=?;";
//    $st = $pdo->prepare($query);
//    $st->execute([$storeIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//    // fetcho one으로 해서 for문돌리기 행전체수만큼
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//
//
//}
//
//function getMenuCategory($storeIdx,$catIdx)
//{
//    $pdo = pdoSqlConnect();
//    $query = "
//        select menuIdx, menuname, menuDetail, menuCatIdx,
//               concat(cast(FORMAT(menuPrice, 0) as char), '원') as menuPrice
//        from Menu
//        where storeIdx=? and menuCatIdx =? and isDeleted='N' ;";
//    $st = $pdo->prepare($query);
//    $st->execute([$storeIdx,$catIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//    // fetcho one으로 해서 for문돌리기 행전체수만큼
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}

