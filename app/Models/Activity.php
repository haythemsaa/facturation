<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'type', 'subject', 'description', 'contact_id', 'opportunity_id', 'user_id', 'scheduled_at', 'completed_at', 'status', 'duration', 'priority', 'result', 'metadata'];
    protected $casts = ['scheduled_at' => 'datetime', 'completed_at' => 'datetime', 'metadata' => 'array'];
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function opportunity(): BelongsTo { return $this->belongsTo(Opportunity::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
