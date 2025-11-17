<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['employee_id', 'type', 'reference', 'start_date', 'end_date', 'base_salary', 'working_hours_per_week', 'vacation_days_per_year', 'terms', 'is_active'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'base_salary' => 'decimal:3', 'is_active' => 'boolean'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function amendments(): HasMany { return $this->hasMany(ContractAmendment::class); }
}
