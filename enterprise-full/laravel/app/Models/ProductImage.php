<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductImage extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['product_uuid','filename','storage_path','thumb_path','is_primary','mime','filesize','hash','status'];

    public function product() {
        return $this->belongsTo(Product::class,'product_uuid','uuid');
    }
}
