
<?php
const STORAGE_FILE = __DIR__ . '/posts.json';
const POST_TTL_DAYS = 31;
if (!file_exists(STORAGE_FILE)) file_put_contents(STORAGE_FILE, json_encode([]));
function read_posts(){ return json_decode(file_get_contents(STORAGE_FILE), true) ?: []; }
function write_posts($posts){ file_put_contents(STORAGE_FILE, json_encode(array_values($posts), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }
function cleanup_and_get_posts(){ $all=read_posts(); $now=time(); $ttl=POST_TTL_DAYS*86400; $kept=[]; foreach($all as $p){ if($now-intval($p['created_at'])<=$ttl) $kept[]=$p; } if(count($kept)!=count($all)) write_posts($kept); return $kept; }
function uuidv4(){ $data=random_bytes(16); $data[6]=chr((ord($data[6])&0x0f)|0x40); $data[8]=chr((ord($data[8])&0x3f)|0x80); return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data),4)); }
function sanitize_text($s,$max=500){$s=trim($s??'');$s=strip_tags($s);if(mb_strlen($s)>$max)$s=mb_substr($s,0,$max);return $s;}
function ymd($ts){return gmdate('Y/m/d',$ts);}header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');$action=$_GET['api']??'';if($action==='list'){echo json_encode(['ok'=>true,'posts'=>cleanup_and_get_posts()]);exit;}$payload=json_decode(file_get_contents('php://input'),true)?:[];if($action==='add'){$subject=sanitize_text($payload['subject']??'',140);$details=sanitize_text($payload['details']??'',2000);$category=sanitize_text($payload['category']??'General',40);if($subject===''){http_response_code(400);echo json_encode(['ok'=>false,'error'=>'Subject required']);exit;} $now=time();$post=['id'=>uuidv4(),'subject'=>$subject,'details'=>$details,'category'=>$category?:'General','created_at'=>$now,'created_ymd'=>ymd($now)];$posts=cleanup_and_get_posts();array_unshift($posts,$post);write_posts($posts);echo json_encode(['ok'=>true,'post'=>$post]);exit;}if($action==='delete'){$id=sanitize_text($payload['id']??'',60);$posts=read_posts();$before=count($posts);$posts=array_values(array_filter($posts,fn($p)=>($p['id']??'')!==$id));if(count($posts)!=$before)write_posts($posts);echo json_encode(['ok'=>count($posts)!=$before]);exit;}http_response_code(404);echo json_encode(['ok'=>false,'error'=>'Unknown action']);

