<?php
header('Content-Type: application/json; charset=utf-8');
if($_SERVER['REQUEST_METHOD']!=='POST'){echo json_encode(['success'=>false]);exit;}
$id=$_POST['id']??''; $dataFile=__DIR__.'/data/songs.json';
if(!$id||!file_exists($dataFile)){echo json_encode(['success'=>false,'message'=>'Không tìm thấy']);exit;}
$data=json_decode(file_get_contents($dataFile),true);
foreach($data['songs'] as $i=>$s){
  if($s['id']===$id){
    if(!empty($s['audio'])&&file_exists(__DIR__.'/'.$s['audio'])) unlink(__DIR__.'/'.$s['audio']);
    if(!empty($s['cover'])&&file_exists(__DIR__.'/'.$s['cover'])) unlink(__DIR__.'/'.$s['cover']);
    array_splice($data['songs'],$i,1); break;
  }
}
file_put_contents($dataFile,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['success'=>true]);
