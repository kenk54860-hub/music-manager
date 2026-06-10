<?php
header('Content-Type: application/json; charset=utf-8');
$dataFile = __DIR__.'/data/songs.json';
$audioDir = __DIR__.'/uploads/audio/';
$coverDir = __DIR__.'/uploads/covers/';
foreach([$audioDir,$coverDir,__DIR__.'/data'] as $d) if(!is_dir($d)) mkdir($d,0755,true);

if($_SERVER['REQUEST_METHOD']!=='POST'){echo json_encode(['success'=>false,'message'=>'Invalid']);exit;}
$audio=$_FILES['audio']??null;
if(!$audio||$audio['error']!==0){
  $em=['','Quá lớn','Quá lớn','Chưa xong','Chưa chọn file','Không có tmp','Không ghi được'];
  echo json_encode(['success'=>false,'message'=>$em[$audio['error']??4]??'Lỗi unknown']);exit;
}
$ext=strtolower(pathinfo($audio['name'],PATHINFO_EXTENSION));
if(!in_array($ext,['mp3','wav','ogg','m4a','flac','aac'])){echo json_encode(['success'=>false,'message'=>'Chỉ hỗ trợ MP3/WAV/OGG/M4A/FLAC']);exit;}
$aName=uniqid('audio_',true).'.'.$ext;
$aPath=$audioDir.$aName;
if(!move_uploaded_file($audio['tmp_name'],$aPath)){echo json_encode(['success'=>false,'message'=>'Không lưu được file']);exit;}
$coverUrl='';
$cover=$_FILES['cover']??null;
if($cover&&$cover['error']===0){
  $cExt=strtolower(pathinfo($cover['name'],PATHINFO_EXTENSION));
  if(in_array($cExt,['jpg','jpeg','png','webp','gif'])){
    $cName=uniqid('cover_',true).'.'.$cExt;
    $cPath=$coverDir.$cName;
    if(move_uploaded_file($cover['tmp_name'],$cPath)) $coverUrl='uploads/covers/'.$cName;
  }
}
$data=file_exists($dataFile)?json_decode(file_get_contents($dataFile),true):['songs'=>[]];
if(!$data)$data=['songs'=>[]];
$song=['id'=>uniqid('s_',true),'title'=>htmlspecialchars(trim($_POST['title']??''),ENT_QUOTES,'UTF-8')?:'Không tên','artist'=>htmlspecialchars(trim($_POST['artist']??''),ENT_QUOTES,'UTF-8')?:'Unknown','album'=>htmlspecialchars(trim($_POST['album']??''),ENT_QUOTES,'UTF-8'),'lyrics'=>'','audio'=>'uploads/audio/'.$aName,'cover'=>$coverUrl,'duration'=>'','uploaded'=>date('Y-m-d H:i:s'),'plays'=>0,'likes'=>false];
array_unshift($data['songs'],$song);
file_put_contents($dataFile,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['success'=>true,'song'=>$song]);
