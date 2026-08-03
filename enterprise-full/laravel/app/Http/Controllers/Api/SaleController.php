<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Str;
use App\Models\Sale;
use App\Models\Product;

class SaleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_no' => 'required|string',
            'cashier_uuid' => 'required|uuid',
            'items' => 'required|array|min:1',
            'items.*.product_uuid' => 'required|uuid',
            'items.*.quantity' => 'required|numeric',
            'items.*.unit_price' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {
            $saleUuid = (string) Str::uuid();
            $subtotal = 0;
            foreach($data['items'] as $it) {
                $subtotal += ($it['quantity'] * $it['unit_price']);
            }
            $total = $subtotal; // extension: taxes/discounts

            DB::table('sales')->insert([
                'uuid' => $saleUuid,
                'invoice_no' => $data['invoice_no'],
                'cashier_uuid' => $data['cashier_uuid'],
                'subtotal' => $subtotal,
                'total' => $total,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach($data['items'] as $it) {
                DB::table('sale_items')->insert([
                    'uuid' => (string) Str::uuid(),
                    'sale_uuid' => $saleUuid,
                    'product_uuid' => $it['product_uuid'],
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'total' => $it['quantity'] * $it['unit_price'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return response()->json(['success'=>true,'uuid'=>$saleUuid]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error'=>$e->getMessage()],500);
        }
    }
}
