<?php

//READ
function test()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Test;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function testDetail($testNo)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Test WHERE no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$testNo]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


function testPost($name)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Test (name) VALUES (?);";

    $st = $pdo->prepare($query);
    $st->execute([$name]);

    $st = null;
    $pdo = null;

}


function isValidUser($id, $pw)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE email= ? AND password = ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);

}

function isValidUserIdxEmail($userIdx, $email)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userIdx= ? AND email = ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx, $email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);

}

// CREATE
function createUser($userType,
                    $email,
                    $password,
                    $name,
                    $phone,
                    $dateOfBirth,
                    $AgreeOnService,
                    $AgreeOnPrivate)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO User (userType, email, password, name, phone, dateOfBirth, AgreeOnService, AgreeOnPrivate) VALUES (?, ? ,?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userType,
        $email,
        $password,
        $name,
        $phone,
        $dateOfBirth,
        $AgreeOnService,
        $AgreeOnPrivate]);

    $st = null;
    $pdo = null;

}


//    READ
function isRedundantEmail($email)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE email=?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}


//    READ
function getUserIdxByEmail($email)
{
    $pdo = pdoSqlConnect();
    $query = "select userIdx from User where email = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userIdx'];
}

//    READ
function getUserNameByUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select name from User where userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res[0]['name'];
}

//    READ
function getBanner()
{
    $pdo = pdoSqlConnect();
    $query = "select bannerIdx, bannerName, bannerUrl from Banner;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//    READ
function getRecommendedProd()
{
    $pdo = pdoSqlConnect();
    // 여기 쿼리에 where에 카테고리를 추가하고, 많이 팔리고, 최신 순서대로 정렬한다.
    $query = "select P.productIdx,
       imgUrl                                                                                       thumbnailUrl,
       concat(discountRatio, '%')                                                                as discountRatio,
       format(if(discountRatio != 0, round(price * 0.01 * (100 - discountRatio), -1), price), 0) as displayedPrice,
       M.marketIdx,
       marketName,
       productName,
       concat(format(purchaseCnt, 0), '개 구매중')                                                      purchaseCnt,
       if(EXISTS(select * from ProductHeart), 'N', 'N')                                             isMyHeart,
       isHotDeal,
       (if(timestampdiff(day, P.createdAt, now()) <= 3, 'Y', 'N'))                               as isNew
from Product P
         left join (select productIdx, imgUrl from ProductImg where isThumnail = 'Y') PI on P.productIdx = PI.productIdx
         left join Market M on P.marketIdx = M.marketIdx
         left join (select productIdx, sum(number) as purchaseCnt
                    from Orders
                             inner join ProductStock PS on Orders.detailedProductIdx = PS.detailedProductIdx
                    where 100 <= orderStatus < 210
                    group by productIdx) purchseCntInfo on P.productIdx = purchseCntInfo.productIdx
order by purchaseCnt DESC, P.createdAt DESC;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//    READ
function getRecommendedProdByCate($categoryIdx)
{
    $pdo = pdoSqlConnect();
    // 여기 쿼리에 where에 카테고리를 추가하고, 많이 팔리고, 최신 순서대로 정렬한다.
    $query = "select P.productIdx,
       imgUrl                                                                                       thumbnailUrl,
       concat(discountRatio, '%')                                                                as discountRatio,
       format(if(discountRatio != 0, round(price * 0.01 * (100 - discountRatio), -1), price), 0) as displayedPrice,
       M.marketIdx,
       marketName,
       productName,
       concat(format(purchaseCnt, 0), '개 구매중')                                                      purchaseCnt,
       if(EXISTS(select * from ProductHeart), 'N', 'N')                                             isMyHeart,
       isHotDeal,
       (if(timestampdiff(day, P.createdAt, now()) <= 3, 'Y', 'N'))                               as isNew
from Product P
         left join (select productIdx, imgUrl from ProductImg where isThumnail = 'Y') PI on P.productIdx = PI.productIdx
         left join Market M on P.marketIdx = M.marketIdx
         left join (select productIdx, sum(number) as purchaseCnt
                    from Orders
                             inner join ProductStock PS on Orders.detailedProductIdx = PS.detailedProductIdx
                    where 100 <= orderStatus < 210
                    group by productIdx) purchseCntInfo on P.productIdx = purchseCntInfo.productIdx
where categoryIdx=?
order by purchaseCnt DESC, P.createdAt DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$categoryIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//    READ
function getLastestViewedCategory($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select categoryIdx
from Product P
         inner join VisitHistory VH on P.productIdx = VH.productIdx
where VH.visitorIdx = ?
order by VH.visitTime DESC
limit 1;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    if (empty($res)){
        return false;
    }

    return intval($res[0]['categoryIdx']);
}


// CREATE
function createVisitHistory($visitorIdx, $productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into VisitHistory (visitorIdx, productIdx) values (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$visitorIdx, $productIdx]);

    $st = null;
    $pdo = null;

}

// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
