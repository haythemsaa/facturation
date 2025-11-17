<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'warehouse_id', 'reference', 'date', 'status', 'note', 'user_id'];
    protected $casts = ['date' => 'date'];
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function lines(): HasMany { return $this->hasMany(InventoryLine::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
