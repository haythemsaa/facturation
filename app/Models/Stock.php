<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'product_id', 'warehouse_id', 'quantity', 'reserved_quantity', 'available_quantity'];
    protected $casts = ['quantity' => 'decimal:3', 'reserved_quantity' => 'decimal:3', 'available_quantity' => 'decimal:3'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}
