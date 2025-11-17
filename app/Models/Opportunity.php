<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'title', 'description', 'contact_id', 'pipeline_id', 'stage_id', 'value', 'probability', 'expected_close_date', 'closed_date', 'status', 'lost_reason', 'assigned_to', 'metadata'];
    protected $casts = ['value' => 'decimal:3', 'expected_close_date' => 'date', 'closed_date' => 'date', 'metadata' => 'array'];
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function pipeline(): BelongsTo { return $this->belongsTo(Pipeline::class); }
    public function stage(): BelongsTo { return $this->belongsTo(PipelineStage::class, 'stage_id'); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function activities(): HasMany { return $this->hasMany(Activity::class); }
    public function tags(): MorphToMany { return $this->morphToMany(Tag::class, 'taggable'); }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeInPipeline($query, int $pipelineId)
    {
        return $query->where('pipeline_id', $pipelineId);
    }

    public function scopeInStage($query, int $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    public function scopeHighValue($query, float $minValue = 10000)
    {
        return $query->where('value', '>=', $minValue);
    }

    public function scopeHighProbability($query, int $minProbability = 70)
    {
        return $query->where('probability', '>=', $minProbability);
    }

    public function scopeClosingSoon($query, int $days = 30)
    {
        return $query->where('status', 'open')
            ->whereBetween('expected_close_date', [now(), now()->addDays($days)]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'open')
            ->where('expected_close_date', '<', now());
    }
}
