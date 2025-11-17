<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLine extends Model
{
    use HasFactory;
    protected $fillable = ['inventory_id', 'product_id', 'theoretical_quantity', 'counted_quantity', 'difference', 'note'];
    protected $casts = ['theoretical_quantity' => 'decimal:3', 'counted_quantity' => 'decimal:3', 'difference' => 'decimal:3'];
    public function inventory(): BelongsTo { return $this->belongsTo(Inventory::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
