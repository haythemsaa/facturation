<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTarget extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'user_id', 'period_type', 'start_date', 'end_date', 'target_amount', 'target_deals', 'achieved_amount', 'achieved_deals'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'target_amount' => 'decimal:3', 'achieved_amount' => 'decimal:3'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
