<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        "tenant_id", "category_id", "brand_id", "product_line_id", "sugar_level_id",
        "name", "slug", "short_description", "description", "status",
        "nutrition_facts", "ingredients", "usage_instructions", "storage_instructions",
        "features", "comparison_table", "main_image", "features_title", "description_title", "comparison_title",
        "comparison_table_headers", "comparison_table_rows", "features_main_image", "description_image", "description_images", "is_home_featured", "is_search_featured", "card_tag_id", "home_featured_volume_id",
        "loyalty_rate"
    ];

    protected $casts = [
        'nutrition_facts' => 'array',
        'features' => 'array',
        'comparison_table' => 'array',
        'comparison_table_headers' => 'array',
        'comparison_table_rows' => 'array',
        'description_images' => 'array',
    ];

    public function cardTag()
    {
        return $this->belongsTo(Certificate::class, 'card_tag_id');
    }

    public function specialHighlights()
    {
        return $this->belongsToMany(SpecialHighlight::class, 'product_special_highlight');
    }

    public function certificates()
    {
        return $this->belongsToMany(Certificate::class);
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function productLine() { return $this->belongsTo(ProductLine::class); }
    public function sugarLevel() { return $this->belongsTo(SugarLevel::class); }
    
    public function variants() { return $this->hasMany(ProductVariant::class)->orderBy('position'); }

    public function homeFeaturedVolume()
    {
        return $this->belongsTo(ProductVolumeMedia::class, 'home_featured_volume_id');
    }
    
    public function volumeMedia()
    {
        return $this->hasMany(ProductVolumeMedia::class);
    }
    
    public function nutritionalNeeds()
    {
        return $this->belongsToMany(NutritionalNeed::class, 'product_nutritional_need');
    }

    public function ageGroups()
    {
        return $this->belongsToMany(AgeGroup::class, 'product_age_group');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function homeFeaturedVariant()
    {
        return $this->hasOne(ProductVariant::class)->orderBy('position')->limit(1);
    }
}