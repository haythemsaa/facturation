<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLine extends Model
{
    use HasFactory;
    protected $fillable = ['document_id', 'product_id', 'description', 'quantity', 'unit_price', 'discount_rate', 'discount_amount', 'tva_rate', 'tva_amount', 'total_ht', 'total_ttc', 'line_order'];
    protected $casts = ['quantity' => 'decimal:3', 'unit_price' => 'decimal:3', 'discount_rate' => 'decimal:2', 'discount_amount' => 'decimal:3', 'tva_rate' => 'decimal:2', 'tva_amount' => 'decimal:3', 'total_ht' => 'decimal:3', 'total_ttc' => 'decimal:3'];
    public function document(): BelongsTo { return $this->belongsTo(Document::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
