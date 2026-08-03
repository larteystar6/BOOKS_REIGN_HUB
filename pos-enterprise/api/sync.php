<?php
// api/sync.php - basic sync endpoint for queued sales and image metadata
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if(!$input || !isset($input['type']) || !isset($input['payload'])) {
  http_response_code(400); echo json_encode(['error'=>'invalid_request']); exit;
}
$pdo = get_db();
try {
  $pdo->beginTransaction();
  if($input['type'] === 'sale') {
    $sale = $input['payload'];
    $stmt = $pdo->prepare('SELECT uuid FROM sales WHERE invoice_no = :inv LIMIT 1');
    $stmt->execute([':inv'=>$sale['invoice_no'] ?? '']);
    if($stmt->fetch()) { $pdo->commit(); echo json_encode(['success'=>true,'message'=>'already_exists']); exit; }
    $uuid = $sale['uuid'] ?? bin2hex(random_bytes(16));
    $s = $pdo->prepare('INSERT INTO sales (uuid,invoice_no,customer_uuid,cashier_uuid,subtotal,discount,tax,total,created_at) VALUES (:uuid,:inv,:customer,:cashier,:subtotal,:discount,:tax,:total,:created_at)');
    $s->execute([
      ':uuid'=>$uuid, ':inv'=>$sale['invoice_no'], ':customer'=>$sale['customer_uuid'] ?? null, ':cashier'=>$sale['cashier_uuid'] ?? null,
      ':subtotal'=>$sale['subtotal'] ?? 0, ':discount'=>$sale['discount'] ?? 0, ':tax'=>$sale['tax'] ?? 0, ':total'=>$sale['total'] ?? 0, ':created_at'=>$sale['created_at'] ?? date('Y-m-d H:i:s')
    ]);
    foreach($sale['items'] ?? [] as $it) {
      $stmt = $pdo->prepare('INSERT INTO sale_items (uuid,sale_uuid,product_uuid,quantity,unit_price,total,created_at) VALUES (UUID(),:sale,:product,:qty,:up,:tot,:created_at)');
      $stmt->execute([':sale'=>$uuid,':product'=>$it['product_uuid'],':qty'=>$it['quantity'],':up'=>$it['unit_price'],':tot'=>$it['total'],':created_at'=>date('Y-m-d H:i:s')]);
    }
    $pdo->commit();
    echo json_encode(['success'=>true,'uuid'=>$uuid]);
    exit;
  }

  $pdo->commit();
  echo json_encode(['success'=>true]);
} catch(Exception $ex) {
  if($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500); echo json_encode(['error'=>$ex->getMessage()]);
}
