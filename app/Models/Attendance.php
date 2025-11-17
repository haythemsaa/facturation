<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'employee_id', 'date', 'check_in', 'check_out', 'worked_minutes', 'overtime_minutes', 'late_minutes', 'status', 'note'];
    protected $casts = ['date' => 'date'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
