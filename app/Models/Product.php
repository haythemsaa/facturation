<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    
    protected $fillable = ['tenant_id', 'category_id', 'code', 'barcode', 'name', 'description', 'type', 'unit', 'purchase_price', 'selling_price', 'minimum_price', 'tva_rate', 'stock_alert_threshold', 'track_stock', 'is_active', 'image', 'metadata'];
    protected $casts = ['purchase_price' => 'decimal:3', 'selling_price' => 'decimal:3', 'minimum_price' => 'decimal:3', 'stock_alert_threshold' => 'decimal:3', 'track_stock' => 'boolean', 'is_active' => 'boolean', 'metadata' => 'array'];
    
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function stocks(): HasMany { return $this->hasMany(Stock::class); }
    public function movements(): HasMany { return $this->hasMany(StockMovement::class); }
}
