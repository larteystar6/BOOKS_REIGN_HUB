<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['sku','name','description','price','status'];

    public function images() {
        return $this->hasMany(ProductImage::class,'product_uuid','uuid');
    }
}
