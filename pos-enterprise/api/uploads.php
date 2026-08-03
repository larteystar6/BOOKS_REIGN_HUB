<?php
// api/uploads.php - multipart/form-data handler for product images
require_once __DIR__ . '/../config/db.php';

function respond($data, $code=200) {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

// Basic input
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(['error'=>'method_not_allowed'],405);
}

if(!isset($_POST['product_uuid'])) {
  respond(['error'=>'product_uuid_required'],400);
}
$product_uuid = $_POST['product_uuid'];

$maxBytes = getenv('UPLOAD_MAX_BYTES') ? intval(getenv('UPLOAD_MAX_BYTES')) : 5*1024*1024; // 5MB default

if(empty($_FILES['images'])) {
  respond(['error'=>'no_files'],400);
}

$pdo = get_db();
try {
  $pdo->beginTransaction();
  $uploaded = [];
  foreach($_FILES['images']['tmp_name'] as $index => $tmpPath) {
    $origName = $_FILES['images']['name'][$index];
    $size = $_FILES['images']['size'][$index];
    if($size <= 0 || $size > $maxBytes) {
      continue;
    }
    // validate mime
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);
    if(!in_array($mime, ['image/jpeg','image/png','image/gif','image/webp'])) {
      continue;
    }
    // compute hash for duplicate detection
    $hash = hash_file('sha256', $tmpPath);
    $stmt = $pdo->prepare('SELECT uuid FROM product_images WHERE hash = :hash AND product_uuid = :product_uuid LIMIT 1');
    $stmt->execute([':hash'=>$hash, ':product_uuid'=>$product_uuid]);
    if($stmt->fetch()) {
      // duplicate for this product; skip
      continue;
    }
    // create storage dirs
    $baseDir = __DIR__ . '/../storage/images/' . $product_uuid;
    if(!is_dir($baseDir)) mkdir($baseDir, 0755, true);
    $thumbDir = $baseDir . '/thumbs';
    if(!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

    // safe filename
    $ext = pathinfo($origName, PATHINFO_EXTENSION);
    $safe = preg_replace('/[^A-Za-z0-9-_\.]/','_', pathinfo($origName, PATHINFO_FILENAME));
    $filename = $safe . '-' . time() . '.' . $ext;
    $target = $baseDir . '/' . $filename;
    if(!move_uploaded_file($tmpPath, $target)) {
      continue;
    }
    chmod($target, 0644);

    // get dimensions
    list($width, $height) = getimagesize($target);

    // generate thumbnail (max 400x400)
    $thumbPath = $thumbDir . '/thumb-' . $filename;
    $ok = false;
    if(function_exists('imagecreatefromstring')) {
      $data = file_get_contents($target);
      $img = @imagecreatefromstring($data);
      if($img !== false) {
        $tw = 400; $th = 400;
        $w = imagesx($img); $h = imagesy($img);
        $ratio = min($tw / $w, $th / $h, 1);
        $nw = max(1, floor($w * $ratio)); $nh = max(1, floor($h * $ratio));
        $dst = imagecreatetruecolor($nw, $nh);
        // preserve transparency for png/gif
        if(in_array($mime, ['image/png','image/gif','image/webp'])) {
          imagealphablending($dst, false);
          imagesavealpha($dst, true);
          $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
          imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
        }
        imagecopyresampled($dst, $img, 0,0,0,0, $nw, $nh, $w, $h);
        imagejpeg($dst, $thumbPath, 85);
        imagedestroy($dst);
        imagedestroy($img);
        chmod($thumbPath, 0644);
        $ok = true;
      }
    }

    // store DB record
    function uuid_v4() {
      $data = openssl_random_pseudo_bytes(16);
      $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
      $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
      return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data),4));
    }
    $imgUuid = uuid_v4();
    $stmt = $pdo->prepare('INSERT INTO product_images (uuid, product_uuid, filename, storage_path, thumb_path, mime, filesize, width, height, hash, created_by) VALUES (:uuid,:product,:filename,:storage,:thumb,:mime,:size,:w,:h,:hash,:created_by)');
    $publicStoragePath = 'storage/images/' . $product_uuid . '/' . $filename;
    $publicThumbPath = 'storage/images/' . $product_uuid . '/thumbs/' . 'thumb-' . $filename;
    $stmt->execute([
      ':uuid'=>$imgUuid,
      ':product'=>$product_uuid,
      ':filename'=>$filename,
      ':storage'=>$publicStoragePath,
      ':thumb'=>$publicThumbPath,
      ':mime'=>$mime,
      ':size'=>$size,
      ':w'=>$width,
      ':h'=>$height,
      ':hash'=>$hash,
      ':created_by'=>null
    ]);

    $uploaded[] = [
      'uuid'=>$imgUuid,
      'filename'=>$filename,
      'storage_path'=>$publicStoragePath,
      'thumb_path'=>$publicThumbPath,
      'mime'=>$mime,
      'filesize'=>$size,
      'width'=>$width,
      'height'=>$height
    ];
  } # foreach files
  $pdo->commit();
  respond(['success'=>true,'uploaded'=>$uploaded]);
} catch(Exception $ex) {
  if($pdo->inTransaction()) $pdo->rollBack();
  respond(['error'=>$ex->getMessage()],500);
}
