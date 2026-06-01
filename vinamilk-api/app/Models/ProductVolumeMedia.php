<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVolumeMedia extends Model
{
    protected $table = 'product_volume_media';

    protected $fillable = [
        'product_id',
        'volume_id',
        'main_image',
        'images',
    ];

    protected $casts = [
        'images' => 'json',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function volume()
    {
        return $this->belongsTo(Volume::class);
    }
}
