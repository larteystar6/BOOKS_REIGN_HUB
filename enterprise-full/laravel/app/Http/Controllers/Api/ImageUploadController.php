<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'product_uuid' => 'required|uuid',
            'images.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120'
        ]);

        $product = Product::find($request->product_uuid);
        if(!$product) return response()->json(['error'=>'product_not_found'],404);

        $uploaded = [];
        foreach($request->file('images') as $file) {
            $hash = hash_file('sha256', $file->getRealPath());
            $exists = ProductImage::where('hash',$hash)->where('product_uuid',$product->uuid)->first();
            if($exists) continue;

            $safe = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $ext = $file->getClientOriginalExtension();
            $filename = $safe.'-'.time().'.'.$ext;
            $baseDir = 'public/images/'.$product->uuid;
            $thumbDir = $baseDir.'/thumbs';
            Storage::makeDirectory($baseDir);
            Storage::makeDirectory($thumbDir);

            $path = $file->storeAs($baseDir, $filename);
            $publicPath = Storage::url($path);

            // create thumbnail (using intervention/image if available)
            try {
                $img = Image::make($file->getRealPath());
                $img->fit(400,400,function($constraint){ $constraint->upsize(); });
                $thumbPath = $thumbDir.'/thumb-'.$filename;
                Storage::put($thumbPath, (string) $img->encode('jpg', 85));
            } catch (\Throwable $ex) {
                // fallback: copy original
                $thumbPath = $path;
            }

            $pi = ProductImage::create([
                'uuid' => Str::uuid()->toString(),
                'product_uuid' => $product->uuid,
                'filename' => $filename,
                'storage_path' => $path,
                'thumb_path' => $thumbPath,
                'is_primary' => false,
                'mime' => $file->getClientMimeType(),
                'filesize' => $file->getSize(),
                'hash' => $hash,
            ]);

            $uploaded[] = $pi;
        }

        return response()->json(['success'=>true,'uploaded'=>$uploaded]);
    }
}
