<?php
header('Content-Type: application/json; charset=utf-8');
$dataFile=__DIR__.'/data/songs.json';
function load($f){return file_exists($f)?json_decode(file_get_contents($f),true)??['songs'=>[]] :['songs'=>[]];}
$action=$_GET['action']??'list';
switch($action){
  case 'list': echo json_encode(load($dataFile)); break;
  case 'play':
    $id=$_GET['id']??''; $data=load($dataFile);
    foreach($data['songs'] as &$s) if($s['id']===$id){$s['plays']++;break;} unset($s);
    file_put_contents($dataFile,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo json_encode(['success'=>true]); break;
  case 'like':
    $id=$_GET['id']??''; $data=load($dataFile);
    foreach($data['songs'] as &$s) if($s['id']===$id){$s['likes']=!($s['likes']??false);break;} unset($s);
    file_put_contents($dataFile,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo json_encode(['success'=>true]); break;
  case 'lyrics':
    $id=$_GET['id']??''; $lyrics=htmlspecialchars(trim($_POST['lyrics']??''),ENT_QUOTES,'UTF-8');
    $data=load($dataFile);
    foreach($data['songs'] as &$s) if($s['id']===$id){$s['lyrics']=$lyrics;break;} unset($s);
    file_put_contents($dataFile,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo json_encode(['success'=>true]); break;
  case 'duration':
    $id=$_GET['id']??''; $dur=htmlspecialchars($_POST['duration']??'',ENT_QUOTES,'UTF-8');
    $data=load($dataFile);
    foreach($data['songs'] as &$s) if($s['id']===$id){$s['duration']=$dur;break;} unset($s);
    file_put_contents($dataFile,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo json_encode(['success'=>true]); break;
}
