<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrDocument extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'employee_id', 'type', 'title', 'description', 'file_path', 'issue_date', 'expiry_date', 'uploaded_by'];
    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function uploadedBy(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
}
