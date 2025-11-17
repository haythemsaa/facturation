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
}
