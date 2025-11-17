<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourVisit extends Model
{
    use HasFactory;
    protected $fillable = ['tour_id', 'contact_id', 'customer_id', 'name', 'address', 'latitude', 'longitude', 'planned_time', 'check_in_at', 'check_out_at', 'notes', 'status', 'order'];
    protected $casts = ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime'];
    public function tour(): BelongsTo { return $this->belongsTo(Tour::class); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
