<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'name', 'description', 'is_active', 'is_default'];
    protected $casts = ['is_active' => 'boolean', 'is_default' => 'boolean'];
    public function stages(): HasMany { return $this->hasMany(PipelineStage::class)->orderBy('order'); }
    public function opportunities(): HasMany { return $this->hasMany(Opportunity::class); }
}
