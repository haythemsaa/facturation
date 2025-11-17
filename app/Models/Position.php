<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'title', 'description', 'department_id', 'min_salary', 'max_salary', 'is_active'];
    protected $casts = ['min_salary' => 'decimal:3', 'max_salary' => 'decimal:3', 'is_active' => 'boolean'];
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function employees(): HasMany { return $this->hasMany(Employee::class); }
}
