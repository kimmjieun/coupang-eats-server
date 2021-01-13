<?php
function setOrderState($adminIdx,$orderIdx,$orderState)
{
$pdo = pdoSqlConnect();
$query = "
update OrderInfo as oi set orderState=?
where isDeleted= 'N' and orderIdx=?
  and  oi.storeIdx=(select storeIdx from Admin where isDeleted='N' and adminIdx=?);";

$st = $pdo->prepare($query);
$st->execute([$orderState,$orderIdx,$adminIdx]);

$st = null;
$pdo = null;

}
function isValidAdminIdx($adminIdx,$orderIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS( 
select storeIdx
from OrderInfo
where isDeleted='N'and orderIdx=?
and storeIdx=(select storeIdx from Admin where isDeleted='N' and adminIdx=?)) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx,$adminIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}