<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'type', 'number', 'date', 'due_date', 'customer_id', 'supplier_id', 'warehouse_id', 'subtotal', 'discount_amount', 'discount_rate', 'total_ht', 'total_tva', 'timbre_fiscal', 'total_ttc', 'status', 'note', 'terms', 'related_document_id', 'user_id'];
    protected $casts = ['date' => 'date', 'due_date' => 'date', 'subtotal' => 'decimal:3', 'discount_amount' => 'decimal:3', 'discount_rate' => 'decimal:2', 'total_ht' => 'decimal:3', 'total_tva' => 'decimal:3', 'timbre_fiscal' => 'decimal:3', 'total_ttc' => 'decimal:3'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function lines(): HasMany { return $this->hasMany(DocumentLine::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
