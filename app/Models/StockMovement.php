<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'product_id', 'warehouse_id', 'type', 'quantity', 'cost', 'reference', 'note', 'user_id', 'movement_date'];
    protected $casts = ['quantity' => 'decimal:3', 'cost' => 'decimal:3', 'movement_date' => 'date'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
