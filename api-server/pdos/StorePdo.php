<?php
function getTest()
{
    $pdo = pdoSqlConnect();
    $query = "select * from table_name where column_1=2;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

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
        where s.isDeleted='N' ;";

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

function filterAllCoupon($storeIdx,$cheetah,$deliveryfee,$mincost) //여기
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
        where s.isDeleted='N' and s.storeIdx=? and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee>=if(isnull(?),s.deliveryFee,?)
          and s.minOrderCost>=if(isnull(?),s.minOrderCost,?)
         and EXISTS(select couponIdx from StoreCoupon where storeIdx=?);
";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$cheetah,$cheetah,$deliveryfee,$deliveryfee,$mincost,$mincost,$storeIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


function filterAll($storeIdx,$cheetah,$deliveryfee,$mincost) //여기
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
        where s.isDeleted='N' and s.storeIdx=? and s.isCheetah=if(isnull(?),s.isCheetah,?) and s.deliveryFee<=if(isnull(?),s.deliveryFee,?)
          and s.minOrderCost>=if(isnull(?),s.minOrderCost,?);";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$cheetah,$cheetah,$deliveryfee,$deliveryfee,$mincost,$mincost]);
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

function getMenuOption($storeIdx)
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
        where menuIdx =? and menuOptCatIdx=? and isDeleted='N';";
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

function getStoreIdx($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select storeIdx from Menu where isDeleted='N'and menuIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;


    return $res;
}

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

