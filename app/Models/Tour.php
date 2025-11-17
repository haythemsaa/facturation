<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'name', 'description', 'user_id', 'tour_date', 'start_time', 'end_time', 'status', 'metadata'];
    protected $casts = ['tour_date' => 'date', 'metadata' => 'array'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function visits(): HasMany { return $this->hasMany(TourVisit::class)->orderBy('order'); }
}
