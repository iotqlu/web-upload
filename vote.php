<?php
// private key and session name to store to the session
if ( !defined( 'FM_SESSION_ID')) {
    define('FM_SESSION_ID', 'filemanager');
}
session_name(FM_SESSION_ID);
session_start();

require_once "./SleekDB/Store.php";
use SleekDB\Store;
$dataDir = "./database";
$studentStore = new Store('students', $dataDir);
$voteStore = new Store('votes',$dataDir);

// private key and session name to store to the session
if ( !defined( 'FM_SESSION_ID')) {
    define('FM_SESSION_ID', 'filemanager');
}

$voter = $_SESSION[FM_SESSION_ID]['logged'];

if(isset($_GET["q"])){
    $voted = $_GET["voted"];

    $vote = $voteStore->findBy([["voter","=",$voter],"AND",["voted","=",$voted]]);

    $result = 0;
    if(!empty($vote)){
        $result = $vote[0]['result'];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['voter'=>$voter,'voted'=>$voted,'result'=>$result]);

}

if(isset($_GET["v"])){
    $result = $_GET["result"];
    $voted = $_GET["voted"];

    $vote = $voteStore->findBy([["voter","=",$voter],"AND",["voted","=",$voted]]);

    error_log(json_encode($vote),0);

    if(empty($vote)){
        $voteStore->insert(['voter'=>$voter,'voted'=>$voted,'result'=>$result]);
    }else{
        $voteStore->updateById($vote[0]['_id'],["result"=>$result]);
    }

    header('Content-Type: application/json');
    echo json_decode(['status'=>'ok']);
}



?>