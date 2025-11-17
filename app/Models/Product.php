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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeOfCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeTrackedStock($query)
    {
        return $query->where('track_stock', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('stocks', function ($q) {
            $q->whereRaw('quantity <= stock_alert_threshold');
        });
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }
}
