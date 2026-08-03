<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Sale extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['invoice_no','cashier_uuid','subtotal','total','status'];

    public function items() {
        return $this->hasMany(SaleItem::class,'sale_uuid','uuid');
    }
}
