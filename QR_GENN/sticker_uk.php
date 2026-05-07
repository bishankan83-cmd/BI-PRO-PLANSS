<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

ini_set('display_errors',1);ini_set('display_startup_errors',1);error_reporting(E_ALL);
$servername="localhost";$username="planatir_task_managemen";$password="Bishan@1919";$dbname="planatir_task_managemen";
$conn=new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error){die("Connection failed: ".$conn->connect_error);}

$logged_in_user_id = $_SESSION['user'];
$logged_in_name    = $_SESSION['emp_name'] ?? 'Unknown';

function parseHtmlXls($f){$d=[];$c=file_get_contents($f);$c=preg_replace('/<\?xml[^>]*>/','',$c);$dom=new DOMDocument();@$dom->loadHTML($c);$rows=$dom->getElementsByTagName('tr');$n=0;foreach($rows as $row){$n++;if($n==1)continue;$cells=$row->getElementsByTagName('td');if($cells->length>=2){$ic=trim($cells->item(0)->textContent);$sr=trim($cells->item(1)->textContent);if(!empty($sr)&&!empty($ic))$d[]=['serial_number'=>$sr,'icode'=>$ic];}}return $d;}

function parseXlsx($f){$d=[];try{$z=new ZipArchive;if($z->open($f)!==TRUE)throw new Exception("Could not open XLSX");$ss=[];$xc=$z->getFromName("xl/sharedStrings.xml");if($xc){$x=@simplexml_load_string($xc);if($x&&isset($x->si)){foreach($x->si as $si){if(isset($si->t))$ss[]=(string)$si->t;elseif(isset($si->r)){$t='';foreach($si->r as $r){if(isset($r->t))$t.=(string)$r->t;}$ss[]=$t;}}}}$sc=$z->getFromName("xl/worksheets/sheet1.xml");$z->close();if(!$sc)throw new Exception("Could not read worksheet");$x=@simplexml_load_string($sc);if(!$x||!isset($x->sheetData)||!isset($x->sheetData->row))throw new Exception("Invalid worksheet");$n=0;foreach($x->sheetData->row as $row){$n++;if($n==1)continue;$cells=$row->c;if(!$cells||count($cells)==0)continue;$rd=['',''];foreach($cells as $cell){$cl=preg_replace('/[0-9]+/','',(string)$cell['r']);$v='';if(isset($cell->v)){$cv=(string)$cell->v;if(isset($cell['t'])&&(string)$cell['t']=='s'){$i=(int)$cv;if(isset($ss[$i]))$v=$ss[$i];}elseif(isset($cell['t'])&&(string)$cell['t']=='inlineStr'){if(isset($cell->is->t))$v=(string)$cell->is->t;}else $v=$cv;}elseif(isset($cell->is->t))$v=(string)$cell->is->t;if($cl=='A')$rd[0]=trim($v);elseif($cl=='B')$rd[1]=trim($v);}if(!empty($rd[0])&&!empty($rd[1]))$d[]=['icode'=>$rd[0],'serial_number'=>$rd[1]];}}catch(Exception $e){throw new Exception("Error parsing XLSX: ".$e->getMessage());}return $d;}

// ── FILE UPLOAD (bulk import) ──────────────────────────────────────────────
if($_SERVER["REQUEST_METHOD"]=="POST"&&isset($_FILES['excel_file'])){
  $file=$_FILES['excel_file'];$ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
  if(!in_array($ext,['xls','xlsx','csv'])){header("Location: ".$_SERVER['PHP_SELF']."?error=".urlencode("Invalid file format."));exit;}
  if($file['error']!==UPLOAD_ERR_OK){header("Location: ".$_SERVER['PHP_SELF']."?error=".urlencode("File upload error."));exit;}
  $data=[];
  try{
    if($ext=='csv'){if(($h=fopen($file['tmp_name'],"r"))!==FALSE){$n=0;while(($r=fgetcsv($h))!==FALSE){$n++;if($n==1)continue;if(count($r)>=2&&!empty($r[0])&&!empty($r[1]))$data[]=['icode'=>trim($r[0]),'serial_number'=>trim($r[1])];}fclose($h);}}
    elseif($ext=='xlsx'){$data=parseXlsx($file['tmp_name']);}
    else{$data=parseHtmlXls($file['tmp_name']);}
  }catch(Exception $e){header("Location: ".$_SERVER['PHP_SELF']."?error=".urlencode($e->getMessage()));exit;}
  if(empty($data)){header("Location: ".$_SERVER['PHP_SELF']."?error=".urlencode("No valid data found."));exit;}
  if(!$conn->query("TRUNCATE TABLE get_serial_uk")){header("Location: ".$_SERVER['PHP_SELF']."?error=".urlencode("Failed to clear existing data."));exit;}
  $sc=0;$ec=0;$errs=[];
  foreach($data as $row){
    $cs=$conn->prepare("SELECT icode FROM tire_details WHERE icode=?");$cs->bind_param("s",$row['icode']);$cs->execute();
    if($cs->get_result()->num_rows==0){$errs[]="Item code not found: ".$row['icode'];$ec++;$cs->close();continue;}$cs->close();
    $ins=$conn->prepare("INSERT INTO get_serial_uk (serial_number,icode,created_by_id,created_by_name) VALUES (?,?,?,?)");
    $ins->bind_param("ssss",$row['serial_number'],$row['icode'],$logged_in_user_id,$logged_in_name);
    if($ins->execute())$sc++;else{$ec++;$errs[]="Failed: ".$row['serial_number'];}$ins->close();
  }
  $msg="Previous data cleared. Successfully imported: $sc new records.";
  if($ec>0){$msg.=" Failed: $ec.";if(!empty($errs))$msg.=" ".implode(", ",array_slice($errs,0,5));}
  header("Location: ".$_SERVER['PHP_SELF']."?success=".urlencode($msg));exit;
}

// ── AJAX / POST ACTIONS ────────────────────────────────────────────────────
if($_SERVER["REQUEST_METHOD"]=="POST"&&isset($_POST['action'])){
  header('Content-Type: application/json');

  if($_POST['action']=='lookup_icode'){
    $ic=trim($_POST['icode']??'');if(empty($ic)){echo json_encode(['success'=>false,'error'=>'Item code is required.']);exit;}
    $s=$conn->prepare("SELECT icode,brand,description,maxload FROM tire_details WHERE icode=?");$s->bind_param("s",$ic);$s->execute();$r=$s->get_result();
    if($r->num_rows==0)echo json_encode(['success'=>false,'error'=>"Item code '$ic' not found in system."]);
    else echo json_encode(['success'=>true,'data'=>$r->fetch_assoc()]);
    $s->close();exit;
  }

  if($_POST['action']=='add_manual_entry'){
    $ic=trim($_POST['icode']??'');$sr=trim($_POST['serial_number']??'');
    if(empty($ic)||empty($sr)){echo json_encode(['success'=>false,'error'=>'Item Code and Serial Number are required.']);exit;}
    $ch=$conn->prepare("SELECT icode FROM tire_details WHERE icode=?");$ch->bind_param("s",$ic);$ch->execute();
    if($ch->get_result()->num_rows==0){echo json_encode(['success'=>false,'error'=>"Item code '$ic' not found in master list."]);exit;}$ch->close();
    $ins=$conn->prepare("INSERT INTO get_serial_uk (serial_number,icode,created_by_id,created_by_name) VALUES (?,?,?,?)");
    $ins->bind_param("ssss",$sr,$ic,$logged_in_user_id,$logged_in_name);
    if($ins->execute()){$t=$conn->query("SELECT COUNT(*) as total FROM get_serial_uk")->fetch_assoc()['total'];echo json_encode(['success'=>true,'message'=>'Record added to queue!','new_total'=>$t]);}
    else echo json_encode(['success'=>false,'error'=>'Database error: '.$ins->error]);
    $ins->close();exit;
  }

  if($_POST['action']=='delete_queue_item'){
    $id=intval($_POST['id']??0);if($id<=0){echo json_encode(['success'=>false,'error'=>'Invalid ID.']);exit;}
    $s=$conn->prepare("DELETE FROM get_serial_uk WHERE id=?");$s->bind_param("i",$id);
    if($s->execute()){$t=$conn->query("SELECT COUNT(*) as total FROM get_serial_uk")->fetch_assoc()['total'];echo json_encode(['success'=>true,'new_total'=>$t]);}
    else echo json_encode(['success'=>false,'error'=>'Delete failed.']);
    $s->close();exit;
  }

  if($_POST['action']=='update_queue_item'){
    $id=intval($_POST['id']??0);$ic=trim($_POST['icode']??'');$sr=trim($_POST['serial_number']??'');
    if($id<=0||empty($ic)||empty($sr)){echo json_encode(['success'=>false,'error'=>'Invalid data.']);exit;}
    $ch=$conn->prepare("SELECT icode FROM tire_details WHERE icode=?");$ch->bind_param("s",$ic);$ch->execute();
    if($ch->get_result()->num_rows==0){echo json_encode(['success'=>false,'error'=>"Item code '$ic' not found."]);exit;}$ch->close();
    $s=$conn->prepare("UPDATE get_serial_uk SET icode=?,serial_number=?,created_by_id=?,created_by_name=? WHERE id=?");
    $s->bind_param("ssssi",$ic,$sr,$logged_in_user_id,$logged_in_name,$id);
    if($s->execute())echo json_encode(['success'=>true,'message'=>'Updated successfully.']);
    else echo json_encode(['success'=>false,'error'=>'Update failed.']);
    $s->close();exit;
  }

  if($_POST['action']=='clear_queue'){
    if($conn->query("TRUNCATE TABLE get_serial_uk"))echo json_encode(['success'=>true]);
    else echo json_encode(['success'=>false,'error'=>'Failed to clear queue.']);exit;
  }

  // ── Save single printed label into generated_serials_uk ─────────────────
  // Inserts all fields including created_by_id and created_by_name from session
  if($_POST['action']=='save_single_generated'){
    $sr   = trim($_POST['serial_number'] ?? '');
    $ic   = trim($_POST['icode']         ?? '');
    $br   = trim($_POST['brand']         ?? '');
    $desc = trim($_POST['description']   ?? '');
    $ml   = trim($_POST['maxload']       ?? '');
    if(empty($sr)||empty($ic)){echo json_encode(['success'=>false,'error'=>'serial_number and icode are required.']);exit;}
    $today = date('Y-m-d');
    $ins=$conn->prepare(
      "INSERT INTO generated_serials_uk
         (serial_number, icode, brand, description, date, maxload, created_by_id, created_by_name)
       VALUES (?,?,?,?,?,?,?,?)"
    );
    $ins->bind_param(
      "ssssssss",
      $sr, $ic, $br, $desc, $today, $ml,
      $logged_in_user_id,   // from session
      $logged_in_name        // from session
    );
    if($ins->execute()) echo json_encode(['success'=>true]);
    else                echo json_encode(['success'=>false,'error'=>'DB error: '.$ins->error]);
    $ins->close();exit;
  }
}

// ── GET: batch data ────────────────────────────────────────────────────────
if(isset($_GET['get_batch_data'])){
  header('Content-Type: application/json');$rows=[];
  $r=$conn->query("SELECT gs.id,gs.serial_number,gs.icode AS tyre_code,td.brand,td.description,gs.date,td.maxload,gs.created_by_id,gs.created_by_name FROM get_serial_uk gs LEFT JOIN tire_details td ON gs.icode=td.icode ORDER BY gs.id");
  if($r&&$r->num_rows>0)while($row=$r->fetch_assoc())$rows[]=$row;
  echo json_encode($rows);exit;
}

// ── GET: queue list ────────────────────────────────────────────────────────
if(isset($_GET['get_queue_list'])){
  header('Content-Type: application/json');$rows=[];
  $r=$conn->query("SELECT gs.id,gs.serial_number,gs.icode,td.brand,td.description,td.maxload,gs.created_by_id,gs.created_by_name FROM get_serial_uk gs LEFT JOIN tire_details td ON gs.icode=td.icode ORDER BY gs.id");
  if($r&&$r->num_rows>0)while($row=$r->fetch_assoc())$rows[]=$row;
  echo json_encode($rows);exit;
}

$get_serial_total=$conn->query("SELECT COUNT(*) as total FROM get_serial_uk")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover,maximum-scale=5">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>Labels — Tire Label System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<style>
:root,[data-theme="dark"]{--orange:#F28018;--orange-dark:#c8660e;--orange-glow:rgba(242,128,24,.18);--orange-soft:rgba(242,128,24,.08);--bg:#0d0d0d;--surface:#161616;--surface2:#1e1e1e;--surface3:#242424;--border:rgba(255,255,255,.07);--border-hot:rgba(242,128,24,.45);--white:#fff;--off-white:#f0ede8;--text:#fff;--muted:rgba(255,255,255,.38);--dim:rgba(255,255,255,.14);--danger:#f87171;--success:#4ade80;--topbar-bg:rgba(13,13,13,.95);--hero-bg:linear-gradient(100deg,#1a0d00 0%,#0d0d0d 55%);--logo-filter:brightness(1.05) drop-shadow(0 0 6px rgba(242,128,24,.35));--logo-filter-hover:brightness(1.15) drop-shadow(0 0 10px rgba(242,128,24,.6));--noise-opacity:.6;--input-bg:#1e1e1e;--modal-bg:rgba(0,0,0,.85);--safe-top:0px}
[data-theme="light"]{--bg:#f5f4f0;--surface:#fff;--surface2:#f0ede8;--surface3:#e8e4de;--border:rgba(0,0,0,.09);--border-hot:rgba(242,128,24,.5);--white:#fff;--off-white:#1a1a1a;--text:#1a1a1a;--muted:rgba(0,0,0,.45);--dim:rgba(0,0,0,.25);--danger:#dc2626;--success:#16a34a;--topbar-bg:rgba(255,255,255,.96);--hero-bg:linear-gradient(100deg,#fff0e0 0%,#f5f4f0 55%);--logo-filter:brightness(.9) drop-shadow(0 0 6px rgba(242,128,24,.2));--logo-filter-hover:brightness(.8) drop-shadow(0 0 10px rgba(242,128,24,.4));--noise-opacity:.15;--orange-glow:rgba(242,128,24,.12);--orange-soft:rgba(242,128,24,.07);--input-bg:#f0ede8;--modal-bg:rgba(0,0,0,.6)}
*{margin:0;padding:0;box-sizing:border-box}html,body{height:100%;width:100%}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;transition:background .3s,color .3s;-webkit-font-smoothing:antialiased;-webkit-touch-callout:none;-webkit-user-select:none;user-select:none;padding-top:var(--safe-top)}
body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");pointer-events:none;z-index:0;opacity:var(--noise-opacity);transition:opacity .3s}
.topbar{position:fixed;top:0;left:0;right:0;z-index:100;background:var(--topbar-bg);backdrop-filter:blur(14px);border-bottom:1px solid var(--border);padding:0 12px;height:56px;display:flex;align-items:center;justify-content:space-between;gap:8px;transition:background .3s,border-color .3s;padding-top:max(8px,env(safe-area-inset-top));padding-left:max(12px,env(safe-area-inset-left));padding-right:max(12px,env(safe-area-inset-right))}
.brand{display:flex;align-items:center;gap:8px;flex:1;min-width:0}.brand-logo{height:32px;width:auto;object-fit:contain;filter:var(--logo-filter);transition:filter .2s ease}.brand-logo:hover{filter:var(--logo-filter-hover)}.brand-divider{width:1px;height:20px;background:var(--border);flex-shrink:0;display:none}.brand-name{font-family:'Bebas Neue',sans-serif;font-size:.9rem;letter-spacing:.06em;line-height:1;color:var(--off-white);transition:color .3s;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}
.topbar-right{display:flex;align-items:center;gap:6px;flex-shrink:0}.topbar-date{font-size:.65rem;font-weight:500;color:var(--muted);background:var(--surface2);border:1px solid var(--border);padding:5px 10px;border-radius:6px;transition:background .3s;white-space:nowrap;display:none}.topbar-date i{color:var(--orange);margin-right:4px}
.topbar-user{display:none;align-items:center;gap:5px;background:var(--surface2);border:1px solid var(--border);padding:5px 10px;border-radius:6px;font-size:.65rem;font-weight:600;color:var(--muted);white-space:nowrap}.topbar-user i{color:var(--orange)}
.theme-toggle{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:8px;background:var(--surface2);border:1px solid var(--border);color:var(--muted);font-size:.7rem;font-weight:600;font-family:'Outfit',sans-serif;cursor:pointer;transition:all .2s ease;white-space:nowrap;min-height:40px;min-width:40px;justify-content:center}.theme-toggle:active{border-color:var(--border-hot);color:var(--orange);background:var(--orange-soft)}.theme-toggle .t-icon{font-size:.85rem;transition:transform .4s ease}.theme-toggle:active .t-icon{transform:rotate(20deg)}.theme-toggle span{display:none}
.hero-strip{background:var(--hero-bg);border-bottom:1px solid var(--border);padding:20px 16px 16px;position:relative;overflow:hidden;transition:background .3s;margin-top:56px}.hero-strip::after{content:'';position:absolute;right:-80px;top:-80px;width:320px;height:320px;background:radial-gradient(circle,rgba(242,128,24,.12) 0%,transparent 70%);pointer-events:none}.hero-title{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;letter-spacing:.04em;line-height:1.1;color:var(--text)}.hero-title span{color:var(--orange)}.hero-sub{font-size:.72rem;color:var(--muted);margin-top:4px}
.hero-user-badge{display:inline-flex;align-items:center;gap:6px;margin-top:8px;background:var(--orange-soft);border:1px solid var(--border-hot);border-radius:20px;padding:4px 12px;font-size:.7rem;font-weight:600;color:var(--orange)}.hero-user-badge i{font-size:.75rem}
.page{padding:20px;position:relative;z-index:1;max-width:1400px;margin:0 auto;padding-bottom:60px}
.btn-t{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:11px 16px;border-radius:10px;font-size:.8rem;font-weight:600;font-family:'Outfit',sans-serif;border:none;cursor:pointer;text-decoration:none;transition:all .18s ease;min-height:44px;user-select:none;-webkit-user-select:none;touch-action:manipulation}.btn-t:active{transform:scale(.98)}.btn-orange{background:var(--orange);color:#fff;box-shadow:0 2px 14px var(--orange-glow)}.btn-orange:active{background:var(--orange-dark);color:#fff;box-shadow:0 4px 22px rgba(242,128,24,.45)}.btn-ghost{background:var(--surface2);border:1px solid var(--border);color:var(--off-white)}.btn-ghost:active{background:var(--surface3);color:var(--text);border-color:var(--border-hot)}.btn-danger{background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.35);color:var(--danger)}.btn-danger:active{background:rgba(248,113,113,.22);border-color:var(--danger);color:#fff}.btn-success{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.35);color:var(--success)}.btn-success:active{background:rgba(74,222,128,.22);border-color:var(--success);color:#fff}.btn-t:disabled,.btn-t[disabled]{opacity:.4;cursor:not-allowed;transform:none!important;box-shadow:none!important}.btn-count{background:rgba(255,255,255,.18);padding:2px 8px;border-radius:10px;font-size:.65rem}.btn-orange .btn-count{background:rgba(255,255,255,.22)}
.alert-bar{padding:12px 14px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:.8rem;font-weight:500;animation:slideDown .3s ease both}.alert-bar i{font-size:.9rem;flex-shrink:0}.alert-bar.success{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.3);color:#86efac}.alert-bar.success i{color:var(--success)}.alert-bar.error{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.3);color:#fca5a5}.alert-bar.error i{color:var(--danger)}.alert-bar-close{margin-left:auto;background:none;border:none;color:inherit;cursor:pointer;opacity:.5;font-size:1rem;min-width:32px;min-height:32px;display:flex;align-items:center;justify-content:center}.alert-bar-close:active{opacity:1}
@keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:none}}
.stat-row{display:grid;grid-template-columns:1fr;gap:16px;margin-bottom:24px}.stat-card{background:linear-gradient(135deg,rgba(28,15,0,.7) 0%,var(--surface) 60%);border:1px solid var(--border-hot);box-shadow:0 0 28px var(--orange-glow);border-radius:14px;padding:20px;position:relative;overflow:hidden;transition:border-color .2s,box-shadow .2s,background .3s;animation:fadeUp .4s ease both}.stat-card:active{box-shadow:0 0 40px rgba(242,128,24,.28)}.stat-label{font-size:.6rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:5px}.stat-label i{color:var(--orange)}.stat-value{font-family:'Bebas Neue',sans-serif;font-size:3.2rem;line-height:1;color:var(--orange)}.stat-note{margin-top:10px;font-size:.7rem;color:var(--muted)}.stat-bg-icon{position:absolute;right:10px;bottom:5px;font-size:4rem;opacity:.05;pointer-events:none;color:var(--orange)}
.action-bar{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:16px;transition:background .3s;animation:fadeUp .4s ease both;animation-delay:.06s}.action-bar-label{font-size:.6rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:12px}.action-buttons{display:grid;grid-template-columns:1fr;gap:10px}
.section-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:20px;transition:border-color .2s,background .3s;animation:fadeUp .4s ease both}.section-card:hover{border-color:rgba(242,128,24,.2)}.section-card.sec-1{animation-delay:.08s}.section-card.sec-2{animation-delay:.12s}.section-card.sec-3{animation-delay:.16s}.section-head{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px}.section-head-title{font-family:'Bebas Neue',sans-serif;font-size:.95rem;letter-spacing:.08em;color:var(--text);flex:1}.section-head-title i{color:var(--orange);margin-right:6px}.section-tag{font-size:.6rem;font-weight:600;letter-spacing:.06em;color:var(--muted);background:var(--surface2);border:1px solid var(--border);padding:3px 8px;border-radius:5px;white-space:nowrap}
.section-body{padding:16px}
.form-row-grid{display:grid;grid-template-columns:1fr;gap:12px}.form-group{display:flex;flex-direction:column;gap:5px}label.field-label{font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)}.form-input{background:var(--input-bg);border:1px solid var(--border);border-radius:9px;padding:12px;font-family:'Outfit',sans-serif;font-size:16px;color:var(--text);outline:none;width:100%;transition:border-color .18s,box-shadow .18s,background .3s,color .3s;-webkit-appearance:none;appearance:none;min-height:44px}.form-input::placeholder{color:var(--dim)}.form-input:focus{border-color:var(--border-hot);box-shadow:0 0 0 3px var(--orange-soft)}.form-input:active{border-color:var(--border-hot)}
.icode-status{font-size:.7rem;font-weight:600;min-height:16px;margin-top:3px;display:flex;align-items:center;gap:4px}.icode-status.valid{color:var(--success)}.icode-status.invalid{color:var(--danger)}.icode-status.loading{color:var(--orange)}.icode-valid-box{background:rgba(74,222,128,.06);border:1px solid rgba(74,222,128,.25);border-radius:8px;padding:8px 10px;margin-top:8px;font-size:.75rem;color:#86efac;display:none}
.inline-error{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.3);border-radius:8px;padding:10px 12px;margin-top:12px;font-size:.8rem;color:#fca5a5;display:none;align-items:center;gap:8px}.inline-error i{color:var(--danger);flex-shrink:0}
.warning-notice{background:rgba(242,128,24,.06);border:1px solid var(--border-hot);border-radius:9px;padding:12px 14px;margin-bottom:16px;font-size:.78rem;color:#fbbf6b;display:flex;align-items:flex-start;gap:10px}.warning-notice i{flex-shrink:0;margin-top:1px;color:var(--orange)}
.upload-zone{border:2px dashed var(--border);border-radius:12px;padding:20px;text-align:center;transition:border-color .2s,background .2s;cursor:pointer;position:relative}.upload-zone:active,.upload-zone.drag-over{border-color:var(--border-hot);background:var(--orange-soft)}.upload-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}.upload-zone-icon{font-size:2rem;color:var(--orange);opacity:.7;margin-bottom:8px}.upload-zone-label{font-size:.85rem;font-weight:600;color:var(--off-white)}.upload-zone-sub{font-size:.7rem;color:var(--muted);margin-top:4px}.upload-zone-file{font-size:.75rem;color:var(--orange);margin-top:8px;font-weight:600}.upload-row{display:grid;grid-template-columns:1fr;gap:12px}
.modal-overlay{position:fixed;inset:0;background:var(--modal-bg);backdrop-filter:blur(6px);z-index:1000;display:flex;align-items:flex-end;justify-content:center;padding:0 env(safe-area-inset-left) 0 env(safe-area-inset-right);opacity:0;pointer-events:none;transition:opacity .22s}.modal-overlay.open{opacity:1;pointer-events:all}.modal-box{background:var(--surface);border:1px solid var(--border);border-radius:20px 20px 0 0;width:100%;max-height:92vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 -20px 60px rgba(0,0,0,.6);transform:translateY(100%);transition:transform .3s cubic-bezier(.34,1.56,.64,1);border-radius:16px;margin:0 12px}.modal-overlay.open .modal-box{transform:none}.modal-box.sm,.modal-box.md{max-width:100%}.modal-head{padding:16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0}.modal-head-title{font-family:'Bebas Neue',sans-serif;font-size:1rem;letter-spacing:.08em;display:flex;align-items:center;gap:8px;color:var(--text);flex:1}.modal-head-title i{color:var(--orange)}.modal-close{background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.2rem;transition:color .15s;min-width:44px;min-height:44px;display:flex;align-items:center;justify-content:center}.modal-close:active{color:var(--text)}.modal-body{flex:1;overflow-y:auto;-webkit-overflow-scrolling:touch;padding:0}.modal-body.padded{padding:16px}.modal-foot{padding:12px 16px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;flex-shrink:0;background:var(--surface);padding-bottom:calc(12px + env(safe-area-inset-bottom))}.modal-foot-info{font-size:.7rem;color:var(--muted);display:flex;align-items:center;gap:5px;width:100%}.modal-foot-info i{color:var(--orange)}
.queue-table{width:100%;border-collapse:collapse;font-size:.85rem}.queue-table thead{display:none}.queue-table tbody tr{display:flex;flex-direction:column;border-bottom:1px solid var(--border);padding:12px 0;transition:background .12s}.queue-table tbody tr:last-child{border-bottom:none}.queue-table tbody tr:active{background:rgba(242,128,24,.04)}.queue-table tbody td{padding:4px 0;vertical-align:middle;color:var(--off-white);display:flex;align-items:center;justify-content:space-between}.queue-table tbody td:before{content:attr(data-label);font-weight:700;color:var(--muted);font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;margin-right:10px}.q-num{color:var(--dim);font-size:.7rem;font-weight:700}.q-num:before{content:"# "}.q-icode-badge{background:var(--orange-soft);color:var(--orange);border:1px solid var(--border-hot);border-radius:6px;padding:3px 8px;font-size:.72rem;font-weight:700}.q-serial{font-weight:600;color:var(--text)}.q-desc{color:var(--muted);font-size:.75rem}.q-user{color:var(--muted);font-size:.72rem;display:flex;align-items:center;gap:4px}.q-user i{color:var(--orange);font-size:.65rem}.q-empty{text-align:center;padding:40px 20px;color:var(--dim)}.q-empty i{font-size:2.5rem;display:block;margin-bottom:12px;opacity:.3}.queue-action-cell{display:flex;gap:6px;justify-content:flex-end;padding-top:8px!important}.queue-action-cell button{padding:6px 10px!important;font-size:.7rem!important}
.progress-track{height:8px;background:var(--surface2);border-radius:20px;overflow:hidden;border:1px solid var(--border);margin:16px 0}.progress-fill{height:100%;background:linear-gradient(90deg,var(--orange),#ffb060);border-radius:20px;transition:width .3s ease;position:relative;overflow:hidden}.progress-fill::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.25) 50%,transparent 100%);animation:shimmer 1.2s infinite}.progress-fill.done::after{display:none}.progress-pct{font-family:'Bebas Neue',sans-serif;font-size:2.4rem;color:var(--orange);line-height:1;text-align:center}.progress-label{text-align:center;font-size:.8rem;font-weight:600;color:var(--muted)}.progress-item{text-align:center;font-size:.75rem;color:var(--dim);margin-top:4px}.progress-status{text-align:center;font-size:.75rem;margin-top:10px;font-weight:600;min-height:18px}.progress-status.ok{color:var(--success)}.progress-status.err{color:var(--danger)}.progress-status.info{color:var(--orange)}
.spinner{width:40px;height:40px;border:3px solid var(--border);border-top-color:var(--orange);border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 16px}
.pill{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:20px;font-size:.65rem;font-weight:700}.pill-orange{background:var(--orange-glow);color:var(--orange);border:1px solid var(--border-hot)}.pill-muted{background:rgba(128,128,128,.1);color:var(--muted);border:1px solid var(--border)}
.toast{position:fixed;bottom:20px;right:20px;z-index:9999;background:var(--surface2);border:1px solid var(--border-hot);color:var(--text);padding:12px 16px;border-radius:10px;box-shadow:0 6px 24px rgba(0,0,0,.4);opacity:0;transform:translateY(16px);transition:all .28s;font-weight:600;font-size:.8rem;display:flex;align-items:center;gap:10px;pointer-events:none;max-width:90vw;word-wrap:break-word;margin-right:env(safe-area-inset-right);margin-bottom:env(safe-area-inset-bottom)}.toast.show{opacity:1;transform:none}.toast i{color:var(--orange)}
.footer-bar{margin-top:24px;padding:12px 0 4px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;font-size:.65rem;color:var(--dim);flex-wrap:wrap;gap:8px}.status-dot-wrap{display:flex;align-items:center;gap:5px}.status-dot{width:6px;height:6px;border-radius:50%;background:var(--success);box-shadow:0 0 6px var(--success)}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@keyframes shimmer{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
@keyframes spin{to{transform:rotate(360deg)}}
@media(min-width:768px){
.topbar{height:68px;padding:0 32px}.brand-divider{display:block}.brand-name{font-size:1.1rem}.topbar-date{display:block}.topbar-user{display:flex}
.hero-strip{padding:28px 32px 20px}.hero-title{font-size:2.2rem}
.page{padding:28px 32px}
.stat-row{grid-template-columns:260px 1fr;gap:20px}.stat-value{font-size:4.2rem}
.action-buttons{grid-template-columns:1fr 1fr}
.form-row-grid{grid-template-columns:1fr 1fr;gap:14px}
.upload-row{grid-template-columns:1fr auto;align-items:flex-start}
.queue-table thead{display:table-header-group}.queue-table thead th{background:var(--surface2);color:var(--muted);padding:10px 12px;font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;border:none;position:sticky;top:0;z-index:2;border-bottom:1px solid var(--border);text-align:left}
.queue-table tbody tr{display:table-row;padding:0}.queue-table tbody td{padding:10px 12px;display:table-cell;justify-content:normal}.queue-table tbody td:before{content:none}
.modal-overlay{align-items:center;padding:0}.modal-box{border-radius:16px;transform:translateY(20px);max-width:960px}.modal-overlay.open .modal-box{transform:none}}
@media(min-width:1024px){
.stat-row{grid-template-columns:280px 1fr}
.form-row-grid{grid-template-columns:1fr 1fr auto auto}
.action-buttons{grid-template-columns:auto auto;width:100%}.action-buttons .btn-t{flex:1;min-width:160px}
.theme-toggle span{display:inline}
.modal-box.sm{max-width:480px}.modal-box.md{max-width:600px}}
@media print{body{background:#fff;color:#000}.topbar,.hero-strip,.alert-bar,.footer-bar,.btn-t,.modal-overlay{display:none!important}}
</style>
</head>
<body>
<header class="topbar">
<div class="brand">
<img src="atire.png" alt="ATire Logo" class="brand-logo" onerror="this.style.display='none'">
<div class="brand-divider"></div>
<div class="brand-name">Tire Labels</div>
</div>
<div class="topbar-right">
<div class="topbar-date"><i class="fas fa-calendar-alt"></i><?php echo date('D, M d');?></div>
<div class="topbar-user"><i class="fas fa-user-circle"></i><?php echo htmlspecialchars($logged_in_name);?></div>
<a href="qr_system_dash.php" class="btn-t btn-ghost" title="Dashboard"><i class="fas fa-arrow-left"></i></a>
<a href="view_generated_label.php" class="btn-t btn-ghost" title="History"><i class="fas fa-history"></i></a>
<button class="theme-toggle" id="themeToggle" title="Toggle theme"><i class="fas fa-moon t-icon" id="themeIcon"></i><span id="themeLabel">Light</span></button>
</div>
</header>

<div class="hero-strip">
<div class="hero-title"><span>Label</span> Generator</div>
<div class="hero-sub">Generate tire labels with QR codes &amp; barcodes</div>
<div class="hero-user-badge"><i class="fas fa-user-circle"></i><?php echo htmlspecialchars($logged_in_name);?> <span style="opacity:.6;font-weight:400">&nbsp;(ID: <?php echo htmlspecialchars($logged_in_user_id);?>)</span></div>
</div>

<div class="page">
<div id="alertArea">
<?php if(isset($_GET['success'])):?><div class="alert-bar success"><i class="fas fa-check-circle"></i><span><?php echo htmlspecialchars($_GET['success']);?></span><button class="alert-bar-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button></div><?php endif;?>
<?php if(isset($_GET['error'])):?><div class="alert-bar error"><i class="fas fa-exclamation-triangle"></i><span><?php echo htmlspecialchars($_GET['error']);?></span><button class="alert-bar-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button></div><?php endif;?>
</div>

<div class="stat-row">
<div class="stat-card">
<div class="stat-label"><i class="fas fa-layer-group"></i> Pending</div>
<div class="stat-value" id="pendingCount"><?php echo number_format($get_serial_total);?></div>
<div class="stat-note"><span class="pill pill-orange"><i class="fas fa-circle" style="font-size:.35rem;"></i> Awaiting</span></div>
<i class="fas fa-layer-group stat-bg-icon"></i>
</div>
<div class="action-bar">
<div class="action-bar-label">Queue Actions</div>
<div class="action-buttons">
<button type="button" class="btn-t btn-ghost" id="btnViewQueue" <?php echo($get_serial_total==0)?'disabled':'';?>><i class="fas fa-list"></i> View Queue</button>
<button type="button" class="btn-t btn-orange" id="btnBatchGenerate" <?php echo($get_serial_total==0)?'disabled':'';?>><i class="fas fa-file-pdf"></i> Generate PDF</button>
</div>
</div>
</div>

<div class="section-card sec-1">
<div class="section-head"><div class="section-head-title"><i class="fas fa-keyboard"></i> Quick Add</div><span class="section-tag">Manual</span></div>
<div class="section-body">
<div class="form-row-grid">
<div class="form-group"><label class="field-label">Item Code</label><input type="text" class="form-input" id="manualIcode" placeholder="Enter code" autocomplete="off" inputmode="text"><div class="icode-status" id="icodeStatus"></div><div class="icode-valid-box" id="icodeValidBox"><span id="icodeValidText"></span></div></div>
<div class="form-group"><label class="field-label">Serial #</label><input type="text" class="form-input" id="manualSerial" placeholder="Enter serial" inputmode="text"></div>
<div class="form-group"><button type="button" class="btn-t btn-ghost" id="btnAddToQueue" style="align-self:flex-end"><i class="fas fa-plus"></i> Add</button></div>
<div class="form-group"><button type="button" class="btn-t btn-orange" id="btnGenerateSingle" style="align-self:flex-end"><i class="fas fa-print"></i> Print</button></div>
</div>
<div class="inline-error" id="manualErrorAlert"><i class="fas fa-exclamation-circle"></i><span id="manualErrorText"></span></div>
</div>
</div>

<div class="section-card sec-2">
<div class="section-head"><div class="section-head-title"><i class="fas fa-file-excel"></i> Bulk Import</div><span class="section-tag">Excel</span></div>
<div class="section-body">
<div class="warning-notice"><i class="fas fa-exclamation-triangle"></i><div><strong>Warning:</strong> Upload will replace all queue items.</div></div>
<form method="post" action="" enctype="multipart/form-data" id="uploadForm">
<div class="upload-row">
<div class="upload-zone" id="uploadZone">
<input type="file" name="excel_file" id="excel_file" accept=".xls,.xlsx,.csv" required>
<div class="upload-zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
<div class="upload-zone-label">Drop file or tap to browse</div>
<div class="upload-zone-sub">.xls, .xlsx, .csv — Col A: Code, Col B: Serial</div>
<div class="upload-zone-file" id="uploadFileName"></div>
</div>
<div style="display:flex;align-items:flex-end"><button type="submit" class="btn-t btn-ghost"><i class="fas fa-upload"></i> Import</button></div>
</div>
</form>
</div>
</div>

<div class="footer-bar">
<div class="status-dot-wrap"><div class="status-dot"></div><span>Online</span></div>
<div>© <?php echo date('Y');?> Tire Labels</div>
</div>
</div>

<!-- Queue Modal -->
<div class="modal-overlay" id="queueModal">
<div class="modal-box">
<div class="modal-head"><div class="modal-head-title"><i class="fas fa-list"></i> Queue <span class="pill pill-orange" id="queueModalCount">0</span></div><button class="modal-close" id="queueModalClose"><i class="fas fa-times"></i></button></div>
<div class="modal-body" id="queueModalBody"><div class="q-empty"><i class="fas fa-spinner fa-spin"></i>Loading...</div></div>
<div class="modal-foot">
<div class="modal-foot-info"><i class="fas fa-info-circle"></i> Edit or remove items — <i class="fas fa-user" style="margin-left:4px"></i> Added by column shows who created each entry</div>
<div style="display:flex;gap:8px;width:100%">
<button type="button" class="btn-t btn-danger" id="btnClearQueue" style="flex:1"><i class="fas fa-trash-alt"></i> Clear</button>
<button type="button" class="btn-t btn-ghost" id="queueModalCloseBtn" style="flex:1">Close</button>
</div>
</div>
</div>
</div>

<!-- Edit Item Modal -->
<div class="modal-overlay" id="editItemModal">
<div class="modal-box sm">
<div class="modal-head"><div class="modal-head-title"><i class="fas fa-edit"></i> Edit Item</div><button class="modal-close" id="editModalClose"><i class="fas fa-times"></i></button></div>
<div class="modal-body padded">
<input type="hidden" id="editItemId">
<div class="form-group" style="margin-bottom:14px"><label class="field-label">Item Code</label><input type="text" class="form-input" id="editIcode" placeholder="Enter code" autocomplete="off" inputmode="text"><div class="icode-status" id="editIcodeStatus"></div></div>
<div class="form-group"><label class="field-label">Serial Number</label><input type="text" class="form-input" id="editSerial" placeholder="Enter serial" inputmode="text"></div>
<div class="inline-error" id="editErrorAlert"><i class="fas fa-exclamation-circle"></i><span id="editErrorText"></span></div>
</div>
<div class="modal-foot"><button type="button" class="btn-t btn-ghost" id="editItemCancelBtn" style="flex:1">Cancel</button><button type="button" class="btn-t btn-orange" id="editItemSaveBtn" style="flex:1"><i class="fas fa-save"></i> Save</button></div>
</div>
</div>

<!-- Batch Progress Modal -->
<div class="modal-overlay" id="batchProgressModal">
<div class="modal-box md">
<div class="modal-head"><div class="modal-head-title"><i class="fas fa-cogs"></i> Generating PDFs</div></div>
<div class="modal-body padded" style="text-align:center;padding-top:24px">
<div class="progress-pct" id="batchPct">0%</div>
<div class="progress-track"><div class="progress-fill" id="batchProgressFill" style="width:0%"></div></div>
<div class="progress-label" id="batchProgressText">Loading queue...</div>
<div class="progress-item" id="batchCurrentItem"></div>
<div class="progress-status" id="archiveStatus"></div>
</div>
<div class="modal-foot"><button type="button" class="btn-t btn-ghost" id="batchCloseBtn" style="flex:1" disabled>Close</button><button type="button" class="btn-t btn-orange" id="downloadAllBtn" style="flex:1;display:none"><i class="fas fa-download"></i> Download</button></div>
</div>
</div>

<!-- Single Progress Modal -->
<div class="modal-overlay" id="singleProgressModal">
<div class="modal-box md">
<div class="modal-head"><div class="modal-head-title"><i class="fas fa-tag"></i> Generating Label</div></div>
<div class="modal-body padded" style="text-align:center;padding:32px 20px">
<div class="spinner" id="singleSpinner"></div>
<div class="progress-label" id="singleProgressText">Building PDF...</div>
</div>
<div class="modal-foot"><button type="button" class="btn-t btn-ghost" id="singleCloseBtn" style="flex:1" disabled>Close</button><button type="button" class="btn-t btn-orange" id="downloadSingleBtn" style="flex:1;display:none"><i class="fas fa-download"></i> Download</button></div>
</div>
</div>

<div class="toast" id="toastNotification"><i class="fas fa-check-circle"></i><span id="toastText"></span></div>

<script>
// ── Theme toggle ─────────────────────────────────────────────────────────
(function(){var h=document.documentElement,b=document.getElementById('themeToggle'),ic=document.getElementById('themeIcon'),lb=document.getElementById('themeLabel');function ap(t){h.setAttribute('data-theme',t);localStorage.setItem('tlsTheme',t);if(t==='dark'){ic.className='fas fa-sun t-icon';lb.textContent='Light';}else{ic.className='fas fa-moon t-icon';lb.textContent='Dark';}}var s=localStorage.getItem('tlsTheme')||'dark';ap(s);b.addEventListener('click',function(){ap(h.getAttribute('data-theme')==='dark'?'light':'dark');});})();

// ── Main app ──────────────────────────────────────────────────────────────
(function(){'use strict';

// Session values passed from PHP — used for created_by fields
var CURRENT_USER_ID   = <?php echo json_encode($logged_in_user_id); ?>;
var CURRENT_USER_NAME = <?php echo json_encode($logged_in_name); ?>;

function escH(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function escJ(s){return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'")}
function qs(s){return document.querySelector(s);}
function toast(m){qs('#toastText').textContent=m;var t=qs('#toastNotification');t.classList.add('show');setTimeout(function(){t.classList.remove('show');},2600);}
function showErr(m){qs('#manualErrorText').textContent=m;qs('#manualErrorAlert').style.display='flex';}
function hideErr(){qs('#manualErrorAlert').style.display='none';}
function openM(id){qs('#'+id).classList.add('open');}
function closeM(id){qs('#'+id).classList.remove('open');}

document.querySelectorAll('.modal-overlay').forEach(function(o){
  o.addEventListener('click',function(e){if(e.target===o&&o.id!=='batchProgressModal'&&o.id!=='singleProgressModal')closeM(o.id);});
  var ob=new MutationObserver(function(){document.body.style.overflow=o.classList.contains('open')?'hidden':'';});
  ob.observe(o,{attributes:true,attributeFilter:['class']});
});

qs('#excel_file').addEventListener('change',function(){qs('#uploadFileName').textContent=this.files[0]?'📎 '+this.files[0].name:'';});
var uz=qs('#uploadZone');
uz.addEventListener('dragover',function(e){e.preventDefault();uz.classList.add('drag-over');});
uz.addEventListener('dragleave',function(){uz.classList.remove('drag-over');});
uz.addEventListener('drop',function(){uz.classList.remove('drag-over');});
setTimeout(function(){document.querySelectorAll('.alert-bar').forEach(function(el){el.remove();});},5000);

var lastVIcode=null,lastVData=null,icodeTimer=null;
function lookupIcode(val,cb){var fd=new FormData();fd.append('action','lookup_icode');fd.append('icode',val);fetch(window.location.pathname,{method:'POST',body:fd}).then(function(r){return r.json();}).then(cb).catch(function(){cb({success:false});});}

qs('#manualIcode').addEventListener('input',function(){
  var val=this.value.trim();clearTimeout(icodeTimer);var st=qs('#icodeStatus');st.className='icode-status';st.textContent='';qs('#icodeValidBox').style.display='none';lastVIcode=null;lastVData=null;
  if(!val)return;
  st.className='icode-status loading';st.innerHTML='<i class="fas fa-spinner fa-spin"></i> Checking...';
  icodeTimer=setTimeout(function(){lookupIcode(val,function(r){st.className='icode-status';if(r.success){st.className='icode-status valid';st.innerHTML='<i class="fas fa-check-circle"></i> Valid';qs('#icodeValidText').innerHTML='<strong>'+escH(r.data.icode)+'</strong> — '+escH(r.data.description||'')+(r.data.brand?' | <em>'+escH(r.data.brand)+'</em>':'');qs('#icodeValidBox').style.display='block';lastVIcode=val;lastVData=r.data;}else{st.className='icode-status invalid';st.innerHTML='<i class="fas fa-times-circle"></i> Not found';}});},600);
});

qs('#btnAddToQueue').addEventListener('click',function(){
  var ic=qs('#manualIcode').value.trim(),sr=qs('#manualSerial').value.trim();hideErr();
  if(!ic||!sr){showErr('Both fields required.');return;}
  if(lastVIcode!==ic){showErr('Validate item code first.');return;}
  var btn=this;btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Adding...';
  var fd=new FormData();fd.append('action','add_manual_entry');fd.append('icode',ic);fd.append('serial_number',sr);
  fetch(window.location.pathname,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(r){if(r.success){updateCounts(r.new_total);toast('✓ '+sr+' added');resetForm();}else showErr(r.error);}).catch(function(){showErr('Server error.');}).finally(function(){btn.disabled=false;btn.innerHTML='<i class="fas fa-plus"></i> Add';});
});

[qs('#manualIcode'),qs('#manualSerial')].forEach(function(el){el.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();qs('#btnAddToQueue').click();}});});

qs('#btnGenerateSingle').addEventListener('click',function(){
  var ic=qs('#manualIcode').value.trim(),sr=qs('#manualSerial').value.trim();hideErr();
  if(!ic||!sr){showErr('Enter code and serial.');return;}
  if(lastVIcode===ic&&lastVData){openSingleModal(lastVData,sr);return;}
  openM('singleProgressModal');qs('#singleProgressText').textContent='Validating...';qs('#downloadSingleBtn').style.display='none';qs('#singleCloseBtn').disabled=true;qs('#singleSpinner').style.display='block';
  lookupIcode(ic,function(r){if(r.success)openSingleModal(r.data,sr);else{qs('#singleSpinner').style.display='none';qs('#singleProgressText').innerHTML='<span style="color:var(--danger)"><i class="fas fa-times-circle"></i> '+escH(r.error||'Not found')+'</span>';qs('#singleCloseBtn').disabled=false;}});
});

qs('#singleCloseBtn').addEventListener('click',function(){closeM('singleProgressModal');});
qs('#batchCloseBtn').addEventListener('click',function(){closeM('batchProgressModal');});
qs('#queueModalClose').addEventListener('click',function(){closeM('queueModal');});
qs('#queueModalCloseBtn').addEventListener('click',function(){closeM('queueModal');});
qs('#editModalClose').addEventListener('click',function(){closeM('editItemModal');});
qs('#editItemCancelBtn').addEventListener('click',function(){closeM('editItemModal');});

// ── Save single label record to generated_serials_uk ─────────────────────
// Sends serial, icode, brand, description, maxload PLUS the logged-in user's
// id and name so the PHP action can store created_by_id / created_by_name.
function saveSingleGenerated(td, sr, cb) {
  var fd = new FormData();
  fd.append('action',          'save_single_generated');
  fd.append('serial_number',   sr);
  fd.append('icode',           td.icode       || '');
  fd.append('brand',           td.brand       || '');
  fd.append('description',     td.description || '');
  fd.append('maxload',         td.maxload     || '');
  // created_by_id / created_by_name are read from the PHP session server-side;
  // no need to post them — the session is authoritative.
  fetch(window.location.pathname, {method:'POST', body:fd})
    .then(function(r){return r.json();})
    .then(function(d){if(cb)cb(d.success);})
    .catch(function(){if(cb)cb(false);});
}

function openSingleModal(td,sr){
  if(!qs('#singleProgressModal').classList.contains('open'))openM('singleProgressModal');
  qs('#singleProgressText').textContent='Building...';qs('#downloadSingleBtn').style.display='none';qs('#singleCloseBtn').disabled=true;qs('#singleSpinner').style.display='block';
  var qd={serial_number:sr,tyre_code:td.icode||'',brand:td.brand||'',description:td.description||'',maxload:td.maxload||''};
  var doc=new window.jspdf.jsPDF({orientation:'portrait',unit:'mm',format:[40,60]});
  generateSinglePdf(qd,function(){
    // ── Save to generated_serials_uk (includes created_by from session) ──
    saveSingleGenerated(td, sr, function(ok){
      qs('#singleSpinner').style.display='none';
      if(ok){
        qs('#singleProgressText').innerHTML='<span style="color:var(--success)"><i class="fas fa-check-circle"></i> Ready! Saved to history.</span>';
      } else {
        qs('#singleProgressText').innerHTML='<span style="color:var(--success)"><i class="fas fa-check-circle"></i> Ready!</span> <span style="color:var(--danger);font-size:.7rem">(history save failed)</span>';
      }
      qs('#singleCloseBtn').disabled=false;
      var sd=doc;
      var dl=qs('#downloadSingleBtn');
      dl.style.display='inline-flex';
      dl.onclick=function(){sd.save('label_'+sr+'.pdf');};
      resetForm();
    });
  },doc);
}

qs('#btnViewQueue').addEventListener('click',function(){openM('queueModal');loadQueue();});

function loadQueue(){
  qs('#queueModalBody').innerHTML='<div class="q-empty"><i class="fas fa-spinner fa-spin" style="font-size:2rem;opacity:.5;display:block;margin-bottom:12px"></i>Loading...</div>';
  fetch(window.location.pathname+'?get_queue_list=1').then(function(r){return r.json();}).then(function(data){
    if(!data||data.length===0){qs('#queueModalBody').innerHTML='<div class="q-empty"><i class="fas fa-inbox"></i>Queue is empty</div>';qs('#queueModalCount').textContent='0';return;}
    qs('#queueModalCount').textContent=data.length;
    var h='<table class="queue-table"><thead><tr><th>#</th><th>Item Code</th><th>Serial</th><th>Description</th><th>Added By</th><th>Actions</th></tr></thead><tbody>';
    data.forEach(function(row,i){
      var userCell='<span class="q-user"><i class="fas fa-user-circle"></i>'+escH(row.created_by_name||'—')+'</span>';
      h+='<tr data-id="'+row.id+'">';
      h+='<td class="q-num">'+(i+1)+'</td>';
      h+='<td><span class="q-icode-badge">'+escH(row.icode)+'</span></td>';
      h+='<td class="q-serial" data-label="Serial">'+escH(row.serial_number)+'</td>';
      h+='<td class="q-desc" data-label="Description" title="'+escH(row.description||'')+'">'+escH(row.description||'—')+'</td>';
      h+='<td data-label="Added By">'+userCell+'</td>';
      h+='<td class="queue-action-cell"><button class="btn-t btn-ghost" onclick="openEditModal('+row.id+',\''+escJ(row.icode)+'\',\''+escJ(row.serial_number)+'\')"><i class="fas fa-edit"></i></button><button class="btn-t btn-danger" onclick="deleteItem('+row.id+')"><i class="fas fa-trash"></i></button></td>';
      h+='</tr>';
    });
    h+='</tbody></table>';qs('#queueModalBody').innerHTML=h;
  }).catch(function(){qs('#queueModalBody').innerHTML='<div class="q-empty"><i class="fas fa-exclamation-circle"></i>Failed to load</div>';});
}

qs('#btnClearQueue').addEventListener('click',function(){
  if(!confirm('Clear all items?'))return;
  var fd=new FormData();fd.append('action','clear_queue');
  fetch(window.location.pathname,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(r){if(r.success){updateCounts(0);loadQueue();toast('Queue cleared.');}});
});

var editTimer=null,editLastV=null;
window.openEditModal=function(id,ic,sr){qs('#editItemId').value=id;qs('#editIcode').value=ic;qs('#editSerial').value=sr;var st=qs('#editIcodeStatus');st.className='icode-status valid';st.innerHTML='<i class="fas fa-check-circle"></i> Valid';editLastV=ic;qs('#editErrorAlert').style.display='none';openM('editItemModal');};

qs('#editIcode').addEventListener('input',function(){
  var val=this.value.trim();clearTimeout(editTimer);var st=qs('#editIcodeStatus');st.className='icode-status';st.textContent='';editLastV=null;
  if(!val)return;st.className='icode-status loading';st.innerHTML='<i class="fas fa-spinner fa-spin"></i> Checking...';
  editTimer=setTimeout(function(){lookupIcode(val,function(r){st.className='icode-status';if(r.success){st.className='icode-status valid';st.innerHTML='<i class="fas fa-check-circle"></i> Valid';editLastV=val;}else{st.className='icode-status invalid';st.innerHTML='<i class="fas fa-times-circle"></i> Not found';}});},600);
});

qs('#editItemSaveBtn').addEventListener('click',function(){
  var id=qs('#editItemId').value,ic=qs('#editIcode').value.trim(),sr=qs('#editSerial').value.trim(),er=qs('#editErrorAlert');er.style.display='none';
  if(!ic||!sr){qs('#editErrorText').textContent='Both required.';er.style.display='flex';return;}
  if(editLastV!==ic){qs('#editErrorText').textContent='Validate code.';er.style.display='flex';return;}
  var btn=this;btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i>';
  var fd=new FormData();fd.append('action','update_queue_item');fd.append('id',id);fd.append('icode',ic);fd.append('serial_number',sr);
  fetch(window.location.pathname,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(r){if(r.success){closeM('editItemModal');loadQueue();toast('✓ Updated');}else{qs('#editErrorText').textContent=r.error||'Failed.';er.style.display='flex';}}).catch(function(){qs('#editErrorText').textContent='Error.';er.style.display='flex';}).finally(function(){btn.disabled=false;btn.innerHTML='<i class="fas fa-save"></i> Save';});
});

window.deleteItem=function(id){
  if(!confirm('Delete this item?'))return;
  var fd=new FormData();fd.append('action','delete_queue_item');fd.append('id',id);
  fetch(window.location.pathname,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(r){if(r.success){updateCounts(r.new_total);loadQueue();toast('Removed.');}});
};

qs('#btnBatchGenerate').addEventListener('click',function(){
  openM('batchProgressModal');qs('#batchPct').textContent='0%';qs('#batchProgressFill').style.width='0%';qs('#batchProgressFill').classList.remove('done');qs('#batchProgressText').textContent='Loading queue...';qs('#batchCurrentItem').textContent='';var as=qs('#archiveStatus');as.className='progress-status';as.textContent='';qs('#downloadAllBtn').style.display='none';qs('#batchCloseBtn').disabled=true;
  fetch(window.location.pathname+'?get_batch_data=1').then(function(r){return r.json();}).then(function(data){
    if(!data||data.length===0){qs('#batchProgressText').textContent='No items.';qs('#batchCloseBtn').disabled=false;return;}
    generateBatch(data);
  }).catch(function(){qs('#batchProgressText').innerHTML='<span style="color:var(--danger)">Failed to load.</span>';qs('#batchCloseBtn').disabled=false;});
});

function generateBatch(data){
  var total=data.length,done=0;
  var doc=new window.jspdf.jsPDF({orientation:'portrait',unit:'mm',format:[40,60]});
  qs('#batchProgressText').textContent='Generating '+total+'...';
  function next(i){
    if(i>=total){
      qs('#batchProgressText').innerHTML='<span style="color:var(--success)"><i class="fas fa-check-circle"></i> '+done+' complete!</span>';
      qs('#batchCurrentItem').textContent='Saving to history...';
      qs('#batchProgressFill').classList.add('done');
      // ── Batch save: pass created_by_id and created_by_name to the endpoint ──
      saveGenerated(data,function(ok){
        var as=qs('#archiveStatus');
        if(ok){as.className='progress-status ok';as.innerHTML='<i class="fas fa-database"></i> Saved to history ('+total+' records)';}
        else{as.className='progress-status err';as.innerHTML='<i class="fas fa-exclamation-circle"></i> History save failed';}
        qs('#batchCurrentItem').textContent='Ready to download.';
        qs('#batchCloseBtn').disabled=false;
        var sd=doc;
        var dl=qs('#downloadAllBtn');
        dl.style.display='inline-flex';
        dl.onclick=function(){sd.save('batch_'+Date.now()+'.pdf');clearQ();};
      });
      return;
    }
    var qd=data[i];qs('#batchCurrentItem').innerHTML='<strong>'+(i+1)+'/'+total+'</strong>: '+escH(qd.serial_number||'?');
    generateSinglePdf(qd,function(){if(i<total-1)doc.addPage([40,60],'portrait');done++;var p=Math.round((done/total)*100);qs('#batchProgressFill').style.width=p+'%';qs('#batchPct').textContent=p+'%';setTimeout(function(){next(i+1);},200);},doc);
  }
  next(0);
}

// ── Batch save to save_generated_uk.php ──────────────────────────────────
// Passes created_by_id and created_by_name so the endpoint stores them.
function saveGenerated(data,cb){
  var payload={
    records:         data,
    created_by_id:   CURRENT_USER_ID,
    created_by_name: CURRENT_USER_NAME
  };
  fetch('save_generated_uk.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
    .then(function(r){return r.json();})
    .then(function(d){if(cb)cb(d.success);})
    .catch(function(){if(cb)cb(false);});
}

function clearQ(){var fd=new FormData();fd.append('action','clear_queue');fetch(window.location.pathname,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(r){if(r.success){updateCounts(0);toast('✓ Cleared.');}});}

function generateSinglePdf(qd,callback,doc){
  try{
    var W=40,H=60,margin=1.5,iL=margin+1,iW=W-(iL*2);
    var tmp=document.createElement('div');tmp.style.cssText='position:absolute;left:-9999px;top:-9999px;';document.body.appendChild(tmp);
    var qrText=JSON.stringify({'InventoryID':qd.tyre_code||'','LotSerialNbr':qd.serial_number||'','TB':qd.description||''});
    new QRCode(tmp,{text:qrText,width:450,height:450,colorDark:'#000000',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M});
    var bc=document.createElement('canvas');
    JsBarcode(bc,qd.serial_number||'N/A',{format:'CODE128',width:2.0,height:50,displayValue:false,margin:4,background:'#ffffff',lineColor:'#000000'});
    setTimeout(function(){
      var qc=tmp.querySelector('canvas');if(!qc){document.body.removeChild(tmp);if(callback)callback();return;}
      var qi=qc.toDataURL('image/png'),bi=bc.toDataURL('image/png');document.body.removeChild(tmp);
      var qs2=30,qx=(W-qs2)/2,qt=3.0;
      doc.setFont('helvetica','bold');doc.setFontSize(7.5);doc.setTextColor(30,30,30);
      var lp='TIRE CODE-',vp=qd.tyre_code||'N/A',ft=lp+vp,tw=doc.getTextWidth(ft),tx=qx+(qs2/2)-(tw/2);
      doc.text(lp,tx,qt-1.0);doc.setTextColor(20,20,20);doc.text(vp,tx+doc.getTextWidth(lp),qt-1.0);
      doc.addImage(qi,'PNG',qx,qt,qs2,qs2);
      var ty=qt+qs2+2.8;
      doc.setFont('helvetica','bold');doc.setFontSize(10);doc.setTextColor(20,20,20);var st=String(qd.serial_number||''),sw=doc.getTextWidth(st),sx=qx+(qs2/2)-(sw/2);doc.text(st,sx,ty);ty+=2.5;
      doc.setFont('helvetica','normal');doc.setFontSize(7);doc.setTextColor(20,20,20);doc.text('Tire size & brand -',iL,ty);ty+=2.3;
      var desc=String(qd.description||'');doc.setFont('helvetica','bold');doc.setFontSize(7);doc.setTextColor(20,20,20);var dl=doc.splitTextToSize(desc,iW).slice(0,2);doc.text(dl,iL,ty);ty+=(dl.length*2.5);
      doc.setFont('helvetica','normal');doc.setFontSize(7);doc.setTextColor(20,20,20);var mll='Max Load-',mllw=doc.getTextWidth(mll);doc.text(mll,iL,ty);doc.setFont('helvetica','bold');doc.setFontSize(7);doc.setTextColor(20,20,20);doc.text(qd.maxload?(String(qd.maxload)+' kgs'):'—',iL+mllw,ty);ty+=2.3;
      doc.setFont('helvetica','bold');doc.setFontSize(6);doc.setTextColor(20,20,20);doc.text('MADE IN SRI LANKA',iL,ty);
      var bh=10,bw=W-(margin*2)-1,bx=margin+0.5,by=H-bh-margin-0.5;
      doc.addImage(bi,'PNG',bx,by,bw,bh);
      if(callback)callback();
    },500);
  }catch(e){console.error('PDF error:',e);if(callback)callback();}
}

function updateCounts(total){qs('#pendingCount').textContent=total;qs('#queueModalCount').textContent=total;var h=total>0;qs('#btnBatchGenerate').disabled=!h;qs('#btnViewQueue').disabled=!h;}
function resetForm(){qs('#manualIcode').value='';qs('#manualSerial').value='';var st=qs('#icodeStatus');st.className='icode-status';st.textContent='';qs('#icodeValidBox').style.display='none';hideErr();lastVIcode=null;lastVData=null;qs('#manualIcode').focus();}
})();
</script>
</body>
</html>
<?php $conn->close();?>