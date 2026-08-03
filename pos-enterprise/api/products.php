<?php
// api/products.php - minimal product endpoints for images and listing
require_once __DIR__ . '/../config/db.php';

function respond($data, $code=200) {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = get_db();

// GET /api/products.php?action=list&page=1
if($method === 'GET' && $_GET['action'] === 'list') {
  $page = max(1, intval($_GET['page'] ?? 1));
  $per = min(200, intval($_GET['per'] ?? 50));
  $offset = ($page-1)*$per;
  $stmt = $pdo->prepare('SELECT uuid,sku,name,description,price,created_at FROM products WHERE status != 9 LIMIT :per OFFSET :off');
  $stmt->bindValue(':per', $per, PDO::PARAM_INT);
  $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $rows = $stmt->fetchAll();
  respond(['data'=>$rows]);
}

// GET images: /api/products.php?action=images&product_uuid=...
if($method === 'GET' && $_GET['action'] === 'images') {
  if(empty($_GET['product_uuid'])) respond(['error'=>'product_uuid required'],400);
  $p = $_GET['product_uuid'];
  $stmt = $pdo->prepare('SELECT uuid,filename,storage_path,thumb_path,is_primary,mime,filesize,created_at FROM product_images WHERE product_uuid = :p AND status != 9 ORDER BY is_primary DESC, created_at ASC');
  $stmt->execute([':p'=>$p]);
  $rows = $stmt->fetchAll();
  respond(['images'=>$rows]);
}

// POST set primary: body { action: set_primary, image_uuid: ... }
if($method === 'POST') {
  $body = json_decode(file_get_contents('php://input'), true);
  if(!$body || !isset($body['action'])) respond(['error'=>'invalid_request'],400);
  if($body['action'] === 'set_primary') {
    if(empty($body['image_uuid'])) respond(['error'=>'image_uuid required'],400);
    // find product_uuid
    $stmt = $pdo->prepare('SELECT product_uuid FROM product_images WHERE uuid = :u LIMIT 1');
    $stmt->execute([':u'=>$body['image_uuid']]);
    $r = $stmt->fetch();
    if(!$r) respond(['error'=>'not_found'],404);
    $product_uuid = $r['product_uuid'];
    try {
      $pdo->beginTransaction();
      $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_uuid = :p')->execute([':p'=>$product_uuid]);
      $pdo->prepare('UPDATE product_images SET is_primary = 1 WHERE uuid = :u')->execute([':u'=>$body['image_uuid']]);
      $pdo->commit();
      respond(['success'=>true]);
    } catch(Exception $ex) {
      if($pdo->inTransaction()) $pdo->rollBack();
      respond(['error'=>$ex->getMessage()],500);
    }
  } elseif($body['action'] === 'delete') {
    if(empty($body['image_uuid'])) respond(['error'=>'image_uuid required'],400);
    try {
      $stmt = $pdo->prepare('UPDATE product_images SET status = 9 WHERE uuid = :u');
      $stmt->execute([':u'=>$body['image_uuid']]);
      respond(['success'=>true]);
    } catch(Exception $ex) {
      respond(['error'=>$ex->getMessage()],500);
    }
  }
}

respond(['error'=>'not_implemented'],501);
