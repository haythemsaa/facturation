<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractAmendment extends Model
{
    use HasFactory;
    protected $fillable = ['contract_id', 'effective_date', 'type', 'description', 'changes'];
    protected $casts = ['effective_date' => 'date', 'changes' => 'array'];
    public function contract(): BelongsTo { return $this->belongsTo(Contract::class); }
}
