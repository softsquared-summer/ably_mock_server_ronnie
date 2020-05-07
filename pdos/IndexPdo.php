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

function isValidProductIdx($productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Product WHERE productIdx = ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$productIdx]);
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
function getRecommendedProd($userIdx)
{
    $pdo = pdoSqlConnect();
    // 여기 쿼리에 where에 카테고리를 추가하고, 많이 팔리고, 최신 순서대로 정렬한다.
    $query = "select P.productIdx,
       imgUrl                                                                                       thumbnailUrl,
       concat(discountRatio, '%')                                                                as discountRatio,
       format(if(discountRatio != 0, round(price * 0.01 * (100 - discountRatio), -1), price), 0) as displayedPrice,
       M.marketIdx,
       marketName,
       if(char_length(productName) > 15, concat(left(productName, 15), '…'), productName)           productName,
       ifnull(concat(format(purchaseCnt, 0), '개 구매중'), 0)                                                      purchaseCnt,
       if(isnull(hearterIdx), 'N', 'Y')                                                             isMyHeart,
       isHotDeal,
       (if(timestampdiff(day, P.createdAt, now()) <= 3, 'Y', 'N'))                               as isNew
from Product P
         inner join ProductImg PI on P.productIdx = PI.productIdx and isThumnail = 'Y'
         inner join Market M on P.marketIdx = M.marketIdx
         left join (select productIdx, sum(number) as purchaseCnt
                    from Orders
                             inner join ProductStock PS on Orders.detailedProductIdx = PS.detailedProductIdx
                    where 100 <= orderStatus < 210
                    group by productIdx) purchseCntInfo on P.productIdx = purchseCntInfo.productIdx
         left join ProductHeart PH on P.productIdx = PH.productIdx and PH.isDeleted = 'N' and hearterIdx = ?
order by purchaseCnt DESC, P.createdAt DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//    READ
function getRecommendedProdByCate($userIdx, $parents)
{
    $pdo = pdoSqlConnect();
    // 여기 쿼리에 where에 카테고리를 추가하고, 많이 팔리고, 최신 순서대로 정렬한다.
    $query = "select P.productIdx,
       imgUrl                                                                                       thumbnailUrl,
       concat(discountRatio, '%')                                                                as discountRatio,
       format(if(discountRatio != 0, round(price * 0.01 * (100 - discountRatio), -1), price), 0) as displayedPrice,
       M.marketIdx,
       marketName,
       if(char_length(productName) > 15, concat(left(productName, 15), '…'), productName)           productName,
       ifnull(concat(format(purchaseCnt, 0), '개 구매중'), 0)                                           purchaseCnt,
       if(isnull(hearterIdx), 'N', 'Y')                                                             isMyHeart,
       isHotDeal,
       (if(timestampdiff(day, P.createdAt, now()) <= 3, 'Y', 'N'))                               as isNew
from Product P
         inner join ProductImg PI on P.productIdx = PI.productIdx and isThumnail = 'Y'
         inner join Market M on P.marketIdx = M.marketIdx
         left join (select productIdx, sum(number) as purchaseCnt
                    from Orders
                             inner join ProductStock PS on Orders.detailedProductIdx = PS.detailedProductIdx
                    where 100 <= orderStatus < 210
                    group by productIdx) purchseCntInfo on P.productIdx = purchseCntInfo.productIdx
         left join ProductHeart PH on P.productIdx = PH.productIdx and PH.isDeleted = 'N' and hearterIdx = ?
         inner join ProductCategory PC on P.categoryIdx = PC.categoryIdx and parents = ?
order by purchaseCnt DESC, P.createdAt DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $parents]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//    READ
function getLastestViewedCategoryParents($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select parents
from ProductCategory
         inner join (select categoryIdx
                     from Product P
                              inner join VisitHistory VH on P.productIdx = VH.productIdx
                     where VH.visitorIdx = ?
                     order by VH.visitTime DESC
                     limit 1) categoryInfo on ProductCategory.categoryIdx = categoryInfo.categoryIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    if (empty($res)) {
        return false;
    }

    return intval($res[0]['parents']);
}

//    READ
function getNewProducts($userIdx)
{
    $pdo = pdoSqlConnect();
    // 여기 쿼리에 where에 카테고리를 추가하고, 많이 팔리고, 최신 순서대로 정렬한다.
    $query = "select P.productIdx,
       imgUrl                                                                                       thumbnailUrl,
       concat(discountRatio, '%')                                                                as discountRatio,
       format(if(discountRatio != 0, round(price * 0.01 * (100 - discountRatio), -1), price), 0) as displayedPrice,
       M.marketIdx,
       marketName,
       if(char_length(productName) > 15, concat(left(productName, 15), '…'), productName)           productName,
#        concat(format(purchaseCnt, 0), '개 구매중')                                                      purchaseCnt,
       if(isnull(hearterIdx), 'N', 'Y')                                                             isMyHeart,
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
         left join (select hearterIdx, productIdx from ProductHeart where hearterIdx = ? and isDeleted = 'N') HeartInfo
                   on P.productIdx = HeartInfo.productIdx
where timestampdiff(day, P.createdAt, now()) <= 3
order by P.createdAt DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//    READ
function getNewBestProducts($userIdx)
{
    $pdo = pdoSqlConnect();
    // 여기 쿼리에 where에 카테고리를 추가하고, 많이 팔리고, 최신 순서대로 정렬한다.
    $query = "select P.productIdx,
       imgUrl                                                                                       thumbnailUrl,
       concat(discountRatio, '%')                                                                as discountRatio,
       format(if(discountRatio != 0, round(price * 0.01 * (100 - discountRatio), -1), price), 0) as displayedPrice,
       M.marketIdx,
       marketName,
       if(char_length(productName) > 15, concat(left(productName, 15), '…'), productName)           productName,
       ifnull(concat(format(purchaseCnt, 0), '개 구매중'), 0)                                                      purchaseCnt,
       if(isnull(hearterIdx), 'N', 'Y')                                                             isMyHeart,
       isHotDeal,
       (if(timestampdiff(day, P.createdAt, now()) <= 3, 'Y', 'N'))                               as isNew
from Product P
         left join (select productIdx, imgUrl from ProductImg where isThumnail = 'Y') PI on P.productIdx = PI.productIdx
         left join Market M on P.marketIdx = M.marketIdx
         left join (select productIdx, sum(number) as purchaseCnt
                    from Orders
                             inner join ProductStock PS on Orders.detailedProductIdx = PS.detailedProductIdx
                    where 100 <= orderStatus and orderStatus < 210
                       or orderStatus = 333
                    group by productIdx) purchseCntInfo on P.productIdx = purchseCntInfo.productIdx
         left join (select hearterIdx, productIdx from ProductHeart where hearterIdx = ? and isDeleted = 'N') HeartInfo
                   on P.productIdx = HeartInfo.productIdx
where timestampdiff(day, P.createdAt, now()) <= 3
#   and !isnull(purchaseCnt)
order by purchaseCnt DESC
limit 10;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//    READ
function getUsers()
{
    $pdo = pdoSqlConnect();

    $query = "select userIdx, userType, email, password, name, phone, dateOfBirth from User where isDeleted='N';";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


//    READ
function isRedundantPhone($phone)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from User where phone = ?) as exist";

    $st = $pdo->prepare($query);
    $st->execute([$phone]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
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

//    READ
function getProductDetail($productIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select P.productIdx,
       if(char_length(productName) > 15, concat(left(productName, 15), '…'),
          productName)                                  productName,
       concat(discountRatio, '%') as                    discountRatio,
       concat(format(if(discountRatio != 0, round(price * 0.01 * (100 - discountRatio), -1), price), 0),
              '원')                as                    displayedPrice,
       concat(format(price, -1), '원')                   price,
       concat('4734-', P.productIdx)                    productCode,
       ifnull(purchaseCnt, 0)                           purchaseCnt,
       contents,
       if(EXISTS(select * from ProductHeart), 'N', 'N') isMyHeart,
       M.*
from Product P
         left join (select M.marketIdx,
                           marketName,
                           group_concat(concat('#', tagName) separator ' ') marketHashTags,
                           profileImgUrl as                                 marketThumbnailUrl
                    from Market M
                             left join HashTag HT on M.marketIdx = HT.marketIdx
                    group by M.marketIdx) M on P.marketIdx = M.marketIdx
         left join (select productIdx, count(*) purchaseCnt
                    from Orders
                             inner join ProductStock PS on Orders.detailedProductIdx = PS.detailedProductIdx
                    group by productIdx) purchaseCntInfo on P.productIdx = purchaseCntInfo.productIdx
where P.productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res[0];
}

//    READ
function getMainImgListByProductIdx($productIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select productIdx, group_concat(imgUrl separator ' ') mainImgUrl
from ProductImg
where ProductImg.isMain = 'Y'
  and productIdx = ?
group by productIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    $res = explode(' ', $res[0]['mainImgUrl']);
    return $res;
}

//    READ
function getNormalImgListByProductIdx($productIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select productIdx, group_concat(imgUrl separator ' ') normalImgUrl
from ProductImg
where ProductImg.isMain = 'N'
   and productIdx = ?
group by productIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    $res = explode(' ', $res[0]['normalImgUrl']);
    return $res;
}

//    READ
function getOptions($productIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select detailedProductIdx, firstOption, secondOption, detailedPrice, if(stock <= 0, '품절', stock) stock
from ProductStock
where productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;
}

//    READ
function getSecondOptions($productIdx, $firstOption)
{
    $pdo = pdoSqlConnect();

    $query = "select detailedProductIdx, firstOption, secondOption, detailedPrice, if(stock <= 0, '품절', stock) stock
from ProductStock
where productIdx = ? and firstOption = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx, $firstOption]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;
}

//    READ 유저의 최신 orderIdx 뽑아내기
function getNextOrderIdx($userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select ifnull(max(orderIdx), 0)+1 nextOrderIdx from Orders where date_format(orderDate, '%Y-%m-%d')=date_format(now(), '%Y-%m-%d') and userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['nextOrderIdx']);
}

// CREATE
function createOrderInfo($orderIdx, $userIdx, $detailedProductIdx, $number, $paymentType, $refundBank, $refundOwner, $refundAccount, $depositBank, $depositor, $cashReceipt, $orderStatus)
{
    $pdo = pdoSqlConnect();

    switch ($paymentType) {
        case "DEPOSIT":
            $query = "insert into Orders (orderIdx, userIdx, detailedProductIdx, number, paymentType, refundBank, refundOwner, refundAccount,
                    depositBank, depositor, cashReceipt, orderStatus)
values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

            $st = $pdo->prepare($query);
            $st->execute([$orderIdx, $userIdx, $detailedProductIdx, $number, $paymentType, $refundBank, $refundOwner, $refundAccount, $depositBank, $depositor, $cashReceipt, $orderStatus]);
            break;


        default:
            $query = "insert into Orders (orderIdx, userIdx, detailedProductIdx, number, paymentType, refundBank, refundOwner, refundAccount, orderStatus)
values (?, ?, ?, ?, ?, ?, ?, ?, ?);";

            $st = $pdo->prepare($query);
            $st->execute([$orderIdx, $userIdx, $detailedProductIdx, $number, $paymentType, $refundBank, $refundOwner, $refundAccount, $orderStatus]);
            break;
    }
    $st = null;
    $pdo = null;

}

// CREATE
function createDeliveryInfo($orderIdx, $userIdx, $detailedProductIdx, $receiverName, $postalCode, $address, $detailedAddress, $phone, $message)
{
    $pdo = pdoSqlConnect();
    $query = "insert into Delivery (orderIdx, userIdx, detailedProductIdx, receiverName, postalCode, address, detailedAddress, phone, message) values  (?,?,?,?,?,?,?,?,?)";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx, $userIdx, $detailedProductIdx, $receiverName, $postalCode, $address, $detailedAddress, $phone, $message]);

    $st = null;
    $pdo = null;
}

// CREATE
function takeOutStock($number, $detailedProductIdx)
{
    $pdo = pdoSqlConnect();
    $query = "update ProductStock set stock = stock - ? where detailedProductIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$number, $detailedProductIdx]);

    $st = null;
    $pdo = null;
}

//    READ 유효한 상세 상품 인덱스 확인
function isValidDetailedProductIdx($detailedProductIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select *
              from ProductStock
              where detailedProductIdx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$detailedProductIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);
}

//    READ 상품 재고 확인
function isValidNumber($number, $detailedProductIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select if(stock>=?, 1, 0) as isOk
from ProductStock where detailedProductIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$number, $detailedProductIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['isOk']);
}

// CREATE
function createProductHearts($hearterIdx, $productIdx, $drawerIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into ProductHeart (hearterIdx, productIdx, drawerIdx) values (?, ?, ifnull(?, -1));";

    $st = $pdo->prepare($query);
    $st->execute([$hearterIdx, $productIdx, $drawerIdx]);

    $st = null;
    $pdo = null;
}

//    READ 유효한 서랍 인덱스 확인
function isValidDrawerIdx($userIdx, $drawerIdx)
{
    if ($drawerIdx == -1 or is_null($drawerIdx)) {
        return true;
    }
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select *
              from ProductHeartDrawer
              where userIdx = ? and drawerIdx = ? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $drawerIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);
}

//    READ 이미 따봉을 누르고 있었는지 검사
function isAlreadyHeart($hearterIdx, $productIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select * from ProductHeart where hearterIdx = ? and productIdx = ? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$hearterIdx, $productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);
}

// DELETE 따봉 삭제
function deleteHeart($hearterIdx, $productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "update ProductHeart set isDeleted='Y' where hearterIdx=? and productIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$hearterIdx, $productIdx]);

    $st = null;
    $pdo = null;
}


//    READ 서랍 인덱스로 서랍 이름 가져오기
function getDrawerNameByDrawerIdx($userIdx, $drawerIdx)
{
    if ($drawerIdx == -1 or is_null($drawerIdx)) {
        return "기본 서랍";
    }

    $pdo = pdoSqlConnect();
    $query = "select drawerName from ProductHeartDrawer where userIdx=? and drawerIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $drawerIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res[0]['drawerName'];
}

//    READ 유저의 최신 drawerIdx 뽑아내기
function getNextDrawerIdx($userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select ifnull(max(drawerIdx), 0)+1 drawerIdx from ProductHeartDrawer where userIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['drawerIdx']);
}

// CREATE
function createDrawer($userIdx, $drawerIdx, $drawerName)
{
    $pdo = pdoSqlConnect();
    $query = "insert into ProductHeartDrawer (userIdx, drawerIdx, drawerName) values (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $drawerIdx, $drawerName]);

    $st = null;
    $pdo = null;
}


//    READ 서랍명 중복 체크
function isRedundantDrawerName($drawerName, $userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select * from ProductHeartDrawer where drawerName = ? and userIdx=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$drawerName, $userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);
}

//    READ 유저 인덱스로 주문 번호, 날짜 얻어오기
function getOrderNumDateByUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select distinct concat(date_format(orderDate, '%Y%m%d'), '-', OrderIdx, '-', userIdx) orderNum,
                date_format(orderDate, '%Y.%m.%d')                                    orderDate
from Orders
where userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;
}

//    READ 주문번호로 상품관련 정보 얻어오기
function getProductInfoByOrderNum($orderNum)
{
    $pdo = pdoSqlConnect();

    $query = "select productIdx,
       detailedProductIdx,
       thumbnailUrl,
       concat(format(detailedPrice, -1), '원') detailedPrice,
       productName,
       firstOption,
       secondOption,
       orderStatus,
       statusName
from (select concat(date_format(orderDate, '%Y%m%d'), '-', OrderIdx, '-', userIdx) orderNum,
             userIdx,
             orderDate,
             orderIdx,
             Orders.orderStatus,
             statusName,
             productInfo.*
      from Orders
               inner join (select P.productIdx,
                                  productName,
                                  detailedProductIdx,
                                  firstOption,
                                  secondOption,
                                  detailedPrice,
                                  thumbnailUrl
                           from ProductStock
                                    inner join Product P on P.productIdx = ProductStock.productIdx
                                    inner join (select productIdx, imgUrl thumbnailUrl
                                                from ProductImg
                                                where isThumnail = 'Y') thumbnailInfo
                                               on ProductStock.productIdx = thumbnailInfo.productIdx) productInfo
                          on Orders.detailedProductIdx = productInfo.detailedProductIdx
               inner join OrderStatusCode on orderStatus = OrderStatusCode.statusCode) productInfo
where productInfo.orderNum = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderNum]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;
}

//    READ 주문번호로 상품관련 정보 얻어오기
function getShippingInfoByUserIDx($userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select count(if(200 <= orderStatus and orderStatus < 210, 1, null)) shipping,
       count(if(210 <= orderStatus and orderStatus < 220, 1, null)) shipped,
       count(if(500 <= orderStatus and orderStatus < 800, 1, null)) cancel
from Orders
where userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res[0];
}

//    READ 유저 인덱스로 서랍 목록 가져오기
function getDrawersByUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select drawerInfo.drawerIdx, drawerName, concat('찜한 상품 ', productCnt, '개') productCnt, thumbnailUrl
from (select ProductHeart.drawerIdx,
             ifnull(drawerName, '기본 서랍') drawerName,
             count(*)                    productCnt
      from ProductHeart
               left join (select userIdx, drawerIdx, drawerName from ProductHeartDrawer where isDeleted = 'N') PHD
                         on ProductHeart.hearterIdx = PHD.userIdx and ProductHeart.drawerIdx = PHD.drawerIdx
      where ProductHeart.isDeleted = 'N'
        and hearterIdx = ?
      group by ProductHeart.drawerIdx) drawerInfo
         inner join (select drawerIdx, group_concat(thumbnailUrl) thumbnailUrl
                     from (select hearterIdx, drawerIdx, PI.productIdx, imgUrl thumbnailUrl, ProductHeart.createdAt
                           from ProductHeart
                                    inner join ProductImg PI on ProductHeart.productIdx = PI.productIdx
                           where ProductHeart.isDeleted = 'N'
                             and PI.isDeleted = 'N'
                             and isThumnail = 'Y'
                             and hearterIdx = ?
                           order by drawerIdx, ProductHeart.createdAt DESC) T
                     group by drawerIdx) thumbnailInfo on drawerInfo.drawerIdx = thumbnailInfo.drawerIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;
}


//    READ 유저 인덱스로 서랍 목록 가져오기
function getDrawerDetail($userIdx, $drawerIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select thumbnailUrl, discountRatio, displayedPrice, ProductHeart.productIdx, productName
from ProductHeart
         inner join (select productIdx,
                            discountRatio,
                            format(if(discountRatio != 0, round(price * 0.01 * (100 - discountRatio), -1), price),
                                   0) as displayedPrice,
                            productName
                     from Product) ProductInfo on ProductHeart.productIdx = ProductInfo.productIdx
         inner join (select productIdx, imgUrl thumbnailUrl
                     from ProductImg
                     where isThumnail = 'Y'
                       and isDeleted = 'N') ImgInfo on ProductHeart.productIdx = ImgInfo.productIdx
where isDeleted = 'N'
  and hearterIdx = ?
  and drawerIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $drawerIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;
}

//    READ 주문번호로 상품관련 정보 얻어오기
function getDrawerProductCnt($userIdx, $drawerIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select count(*) productCnt
from ProductHeart
where ProductHeart.isDeleted = 'N'
  and hearterIdx = ?
  and drawerIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $drawerIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res[0]['productCnt'];
}


// DELETE
function deleteDrawer($userIdx, $drawerIdx)
{
    $pdo = pdoSqlConnect();
    $query = "update ProductHeartDrawer set isDeleted='Y' where userIdx=? and drawerIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $drawerIdx]);

    $st = null;
    $pdo = null;
}

// DELETE
function deleteDrawerProducts($hearterIdx, $drawerIdx)
{
    $pdo = pdoSqlConnect();
    $query = "update ProductHeart set isDeleted='Y' where hearterIdx=? and drawerIdx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$hearterIdx, $drawerIdx]);

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
