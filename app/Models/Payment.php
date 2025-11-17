<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'document_id', 'payment_date', 'amount', 'method', 'reference', 'note', 'user_id'];
    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:3'];
    public function document(): BelongsTo { return $this->belongsTo(Document::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
