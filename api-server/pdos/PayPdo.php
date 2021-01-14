<?php



function getOrderIdx($orderIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select orderState,userIdx,orderPrice from OrderInfo where isDeleted = 'N' and orderIdx=? ;";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res;
}

function deleteOrder($orderIdx)
{
    $pdo = pdoSqlConnect();


    $queryDeleteOrder="update OrderInfo set isDeleted='Y' where isDeleted='N' and orderIdx=?;";
    $queryDeleteOrderDetail="update OrderDetail set isDeleted='Y' where isDeleted='N' and orderIdx=?;";
    $queryDeleteOrderOptionDetail="update OrderDetail set isDeleted='Y' where isDeleted='N' and orderIdx=?;";


    try {

        $st1 = $pdo->prepare($queryDeleteOrder);
        $st2 = $pdo->prepare($queryDeleteOrderDetail);
        $st3 = $pdo->prepare($queryDeleteOrderOptionDetail);

        $pdo->beginTransaction();

        $st1->execute([$orderIdx]);
        $st2->execute([$orderIdx]);
        $st3->execute([$orderIdx]);


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

    $st1 = null;
    $st2 = null;
    $st3 = null;


}

function getUserName($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select userName from UserInfo where isDeleted='N' and userIdx=?";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchColumn();

    $st = null;
    $pdo = null;


    return $res;
}


function addOrderInfo($storeIdx,$userIdxInToken,$paymentIdx,$totalPrice,$toStore,$noPlastic,$deliveryReqIdx,$orderState,
                      $orderMenu,$orderMenuOption)
{
    $pdo = pdoSqlConnect();

    // 여기부터
    $qurtyOrderInfo="
insert into OrderInfo (storeIdx,userIdx,paymentIdx,orderPrice,toStore,noPlastic,deliveryReqIdx,orderState)
values(?,?,?,?,?,if(isnull(?),'N',?),?,?);";
    $queryOrderMenu="insert into OrderDetail (orderIdx,menuIdx,quantity) values(?,?,?);";
    $queryOrderMenuOption="insert into OrderOptionDetail (orderIdx,menuIdx,menuOptIdx) values(?,?,?);";
    $querydeleteCart="update Cart set isDeleted='Y' where userIdx=? and isDeleted='N';";
    $querydeleteCartOption="update OptionCart set isDeleted='Y' where userIdx=? and isDeleted='N';";



    try {

        $st1 = $pdo->prepare($qurtyOrderInfo);
        $st2 = $pdo->prepare($queryOrderMenu);
        $st3 = $pdo->prepare($queryOrderMenuOption);
        $st4 = $pdo->prepare($querydeleteCart);
        $st5 = $pdo->prepare($querydeleteCartOption);
        $pdo->beginTransaction();

        $st1->execute([$storeIdx,$userIdxInToken,$paymentIdx,$totalPrice,
            $toStore,$noPlastic,$noPlastic,$deliveryReqIdx,$orderState]);
        $orderIdx = $pdo->lastInsertId();

        $i=0;
        while(count($orderMenu)>$i){
            $st2->execute([$orderIdx, $orderMenu[$i]['menuIdx'], $orderMenu[$i]['quantity']]);
            $i++;
        }
        $st4->execute([$userIdxInToken]);
        if(!empty($orderMenuOption)){
            $j=0;
            while(count($orderMenuOption)>$j){
                $st3->execute([$orderIdx, $orderMenuOption[$j]['menuIdx'], $orderMenuOption[$j]['optIdx']]);
                $j++;
            }
            $st5->execute([$userIdxInToken]);
        }





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

    $st1 = null;
    $st2 = null;
    $st3 = null;
    $st4 = null;
    $st5 = null;
    $pdo = null;
    return $orderIdx;

}
