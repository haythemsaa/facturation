<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'name', 'description', 'manager_id', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }
    public function employees(): HasMany { return $this->hasMany(Employee::class); }
    public function positions(): HasMany { return $this->hasMany(Position::class); }
}
