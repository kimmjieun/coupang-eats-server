<?php

function deleteCart($userIdxInToken)
{
    $pdo = pdoSqlConnect();

    $querydeleteCart="update Cart set isDeleted='Y' where userIdx=? and isDeleted='N';";
    $querydeleteCartOption="update OptionCart set isDeleted='Y' where userIdx=? and isDeleted='N';";

    try {
        $st4 = $pdo->prepare($querydeleteCart);
        $st5 = $pdo->prepare($querydeleteCartOption);
        $pdo->beginTransaction();

        $st4->execute([$userIdxInToken]);
        $st5->execute([$userIdxInToken]);

        $pdo->commit();
    }
    catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
            // If we got here our two data updates are not in the database
        }
        //throw $e;
        return $e->getMessage();
    }


    $st4 = null;
    $st5 = null;
    $pdo = null;

}

function isValidCart($storeIdx,$menuIdx,$quantity,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select storeIdx from Cart 
                    where isDeleted ='N' and storeIdx = ? and menuIdx=? and quantity =? and userIdx=?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$storeIdx,$menuIdx,$quantity,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
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

function isValidMandatoryCatOne($menuIdx,$menuOptIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select exists(        select o.optCatIdx
        from MenuOption as o
        join MenuOptionCat as oc on o.optCatIdx=oc.optCatIdx and o.menuIdx=oc.menuIdx
        where o.isDeleted='N' and o.menuIdx=? and o.menuOptIdx=? and oc.mandatory='Y')exist";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx,$menuOptIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);


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




function getDeliveryAddress($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select deliveryBuildingName, concat(deliveryAddress, ' ',deliveryAddressDetail) as deliveryAddress
from UserInfo
where isDeleted='N' and userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}

function getStore($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select distinct
(select s.storeIdx from Store as s where s.storeIdx=c.storeIdx ) as storeIdx,
(select s.storeName from Store as s where s.storeIdx=c.storeIdx ) as storeName,
(select s.minOrderCost from Store as s where s.storeIdx=c.storeIdx ) as minOrderCost
from Cart as c
where c.isDeleted='N' and c.userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}

function getStoreIdx($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select distinct
(select s.storeIdx from Store as s where s.storeIdx=c.storeIdx ) as storeIdx
from Cart as c
where c.isDeleted='N' and c.userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;
    return $res;
}

function getCartList($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select (select menuIdx from Menu as m where m.isDeleted='N' and c.menuIdx=m.menuIdx) as menuIdx,
       c.quantity,
       (select menuName from Menu as m where m.isDeleted='N' and c.menuIdx=m.menuIdx) as menuName
from Cart as c
where c.isDeleted='N' and c.userIdx=? ; ";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}

function getOption($menuIdx,$userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select oc.optIdx,mo.menuOptName,mo.optPrice
from OptionCart as oc
join MenuOption as mo on mo.menuOptIdx=oc.optIdx
where oc.isDeleted='N' and oc.menuIdx=? and oc.userIdx=? ;";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}

function getMenuPrice($menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select menuPrice from Menu where isDeleted='N' and menuIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;


    return $res;
}

function getMenuOptionPrice($optIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
    select optPrice from MenuOption where isDeleted='N' and menuOptIdx=?;
";

    $st = $pdo->prepare($query);
    $st->execute([$optIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;


    return $res;
}

function getDeliveryFee($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select distinct
(select deliveryFee from Store as s where s.storeIdx=c.storeIdx ) as storeName
from Cart as c
where c.isDeleted='N' and c.userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;


    return $res;
}
function getCouponCount($userIdxInToken,$storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select count(*) as couponCount
from
    ((select couponIdx,salePrice,minPrice
    from Coupon
    where isDeleted='N'and date(expiredAt) >= date(now())
            and couponIdx =(select uc.couponIdx
                            from UserCoupon as uc
                            join StoreCoupon as sc on sc.couponIdx = uc.couponIdx
                            where  uc.isDeleted= 'N' and uc.userIdx= ? and sc.storeIdx =?))
    union
    (select couponIdx,salePrice,minPrice
    from Coupon
    where isDeleted='N'and date(expiredAt) >= date(now())
            and couponIdx in(select couponIdx from UserCoupon where isDeleted='N' and userIdx= ?))) as Coupon;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken,$storeIdx,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;


    return $res;
}

function getCoupon($userIdxInToken,$storeIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
(select couponIdx,salePrice,minPrice
    from Coupon
    where isDeleted='N'and date(expiredAt) >= date(now())
            and couponIdx =(select uc.couponIdx
                            from UserCoupon as uc
                            join StoreCoupon as sc on sc.couponIdx = uc.couponIdx
                            where  uc.isDeleted= 'N' and uc.userIdx= ? and sc.storeIdx =?))
union
(select couponIdx,salePrice,minPrice
from Coupon
where isDeleted='N'and date(expiredAt) >= date(now()) and isEatsCoupon='Y'
        and couponIdx in (select couponIdx from UserCoupon where isDeleted='N' and userIdx= ?));";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken,$storeIdx,$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}

function getPayment($userIdxInToken)
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
order by createdAt desc limit 1;
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res[0]; // 결과 0인것하나
}

function getCart($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select storeIdx,menuIdx,quantity
from Cart
where isDeleted='N' and userIdx=?;
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res; //결과 전체
}

function getCartOption($userIdxInToken)
{
    $pdo = pdoSqlConnect();
    $query = "
select menuIdx,optIdx
from OptionCart
where isDeleted='N' and userIdx=?;
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res; //결과 전체
}
//
//function addOrderInfo($storeIdx,$userIdxInToken,$paymentIdx,$totalPrice,$toStore,$noPlastic,$deliveryReqIdx,$orderState,
//                      $orderMenu,$orderMenuOption)
//{
//    $pdo = pdoSqlConnect();
//
//    // 여기부터
//    $qurtyOrderInfo="
//insert into OrderInfo (storeIdx,userIdx,paymentIdx,orderPrice,toStore,noPlastic,deliveryReqIdx,orderState)
//values(?,?,?,?,?,if(isnull(?),'N',?),?,?);";
//    $queryOrderMenu="insert into OrderDetail (orderIdx,menuIdx,quantity) values(?,?,?);";
//    $queryOrderMenuOption="insert into OrderOptionDetail (orderIdx,menuIdx,menuOptIdx) values(?,?,?);";
//    $querydeleteCart="update Cart set isDeleted='Y' where userIdx=? and isDeleted='N';";
//    $querydeleteCartOption="update OptionCart set isDeleted='Y' where userIdx=? and isDeleted='N';";
//
//
//
//    try {
//
//        $st1 = $pdo->prepare($qurtyOrderInfo);
//        $st2 = $pdo->prepare($queryOrderMenu);
//        $st3 = $pdo->prepare($queryOrderMenuOption);
//        $st4 = $pdo->prepare($querydeleteCart);
//        $st5 = $pdo->prepare($querydeleteCartOption);
//        $pdo->beginTransaction();
//
//        $st1->execute([$storeIdx,$userIdxInToken,$paymentIdx,$totalPrice,
//                        $toStore,$noPlastic,$noPlastic,$deliveryReqIdx,$orderState]);
//        $orderIdx = $pdo->lastInsertId();
////삭제
//        $i=0;
//        while(count($orderMenu)>$i){
//            $st2->execute([$orderIdx, $orderMenu[$i]['menuIdx'], $orderMenu[$i]['quantity']]);
//            $i++;
//        }
//        $st4->execute([$userIdxInToken]);
//        if(!empty($orderMenuOption)){
//            $j=0;
//            while(count($orderMenuOption)>$j){
//                $st3->execute([$orderIdx, $orderMenuOption[$j]['menuIdx'], $orderMenuOption[$j]['optIdx']]);
//                $j++;
//            }
//            $st5->execute([$userIdxInToken]);
//        }
//
//
//
//
//
//        $pdo->commit();
//    }
//    catch (PDOException $e) {
//        if ($pdo->inTransaction()) {
//            $pdo->rollback();
//            // If we got here our two data updates are not in the database
//        }
//        //throw $e;
//        return $e->getMessage();
//    }
//
//    $st1 = null;
//    $st2 = null;
//    $st3 = null;
//    $st4 = null;
//    $st5 = null;
//    $pdo = null;
//    return $orderIdx;
//
//}

function getOrderInfo($userIdxInToken,$orderStateList)
{
    $pdo = pdoSqlConnect();
    $query = "
select oi.orderIdx,
       oi.storeIdx,
       (select s.storeName
        from Store as s
        where s.storeIdx=oi.storeIdx) as storeName,
      (select sp.storePhoto
        from StorePhoto as sp
        where sp.storeIdx=oi.storeIdx and sp.sequence=1) as storePhoto,
        concat(cast(FORMAT(oi.orderPrice, 0) as char), '원') as orderPrice,
       oi.orderState,
       (select os.orderStateName
        from OrderState as os
        where os.orderStateIdx = oi.orderState) as orderStateName,
       date_format(oi.orderTime,'%Y-%m-%d %H:%i') as orderTime
from OrderInfo as oi
where oi.userIdx = ? and oi.orderState in  (".implode(',',$orderStateList).")
order by oi.createdAt desc;";

//    $query = "
//select oi.orderIdx,
//       oi.storeIdx,
//       (select s.storeName
//        from Store as s
//        where s.storeIdx=oi.storeIdx) as storeName,
//      (select sp.storePhoto
//        from StorePhoto as sp
//        where sp.storeIdx=oi.storeIdx and sp.sequence=1) as storePhoto,
//        concat(cast(FORMAT(oi.orderPrice, 0) as char), '원') as orderPrice,
//       oi.orderState,
//       (select os.orderStateName
//        from OrderState as os
//        where os.orderStateIdx = oi.orderState) as orderStateName,
//       date_format(oi.orderTime,'%Y-%m-%d %H:%i') as orderTime
//from OrderInfo as oi
//where oi.isDeleted='N' and oi.userIdx = ? and oi.orderState in  (".implode(',',$orderStateList).");";
    $st = $pdo->prepare($query);
    $st->execute([$userIdxInToken]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}


function getOrderMenu($orderIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select (select menuIdx from Menu as m where m.isDeleted='N' and o.menuIdx=m.menuIdx) as menuIdx,
       o.quantity,
       (select menuName from Menu as m where m.isDeleted='N' and o.menuIdx=m.menuIdx) as menuName
from OrderDetail as o
where o.orderIdx= ?; ";

//    $query = "
//select (select menuIdx from Menu as m where m.isDeleted='N' and o.menuIdx=m.menuIdx) as menuIdx,
//       o.quantity,
//       (select menuName from Menu as m where m.isDeleted='N' and o.menuIdx=m.menuIdx) as menuName
//from OrderDetail as o
//where o.isDeleted='N' and o.orderIdx= ?; ";
    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}

function getOrderMenuOption($orderIdx,$menuIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select oo.menuOptIdx,
       (select menuOptName from MenuOption as mo where mo.isDeleted='N' and oo.menuOptIdx=mo.menuOptIdx) as menuOptName
from OrderOptionDetail as oo
where oo.orderIdx= ? and oo.menuIdx=?;";

//    $query = "
//select oo.menuOptIdx,
//       (select menuOptName from MenuOption as mo where mo.isDeleted='N' and oo.menuOptIdx=mo.menuOptIdx) as menuOptName
//from OrderOptionDetail as oo
//where oo.isDeleted='N' and oo.orderIdx= ? and oo.menuIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$orderIdx,$menuIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}
