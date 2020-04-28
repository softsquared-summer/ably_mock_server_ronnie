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


function isValidUser($id, $pw){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userId= ? AND userPw = ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
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
                        $AgreeOnPrivate){
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
    function isRedundantEmail($email){
        $pdo = pdoSqlConnect();
        $query = "SELECT EXISTS(SELECT * FROM User WHERE email=?) AS exist;";


        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $st->execute([$email]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;
        $pdo = null;

        return intval($res[0]["exist"]);
    }


//    READ
function getUserIdxByEmail($email){
    $pdo = pdoSqlConnect();
    $query = "select userIdx from User where email=?;";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]);
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
