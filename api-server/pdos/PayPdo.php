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
