<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'user_id', 'employee_number', 'first_name', 'last_name', 'cin', 'cnss_number', 'birth_date', 'birth_place', 'gender', 'marital_status', 'children_count', 'is_family_head', 'email', 'phone', 'mobile', 'address', 'city', 'postal_code', 'emergency_contact_name', 'emergency_contact_phone', 'department_id', 'position_id', 'manager_id', 'hire_date', 'end_date', 'status', 'photo', 'metadata'];
    protected $casts = ['birth_date' => 'date', 'hire_date' => 'date', 'end_date' => 'date', 'is_family_head' => 'boolean', 'metadata' => 'array'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }
    public function manager(): BelongsTo { return $this->belongsTo(Employee::class, 'manager_id'); }
    public function contracts(): HasMany { return $this->hasMany(Contract::class); }
    public function activeContract(): HasOne { return $this->hasOne(Contract::class)->where('is_active', true); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function payslips(): HasMany { return $this->hasMany(Payslip::class); }
}
