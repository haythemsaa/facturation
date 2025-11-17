<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'name', 'color', 'type'];
    public function contacts(): MorphToMany { return $this->morphedByMany(Contact::class, 'taggable'); }
    public function opportunities(): MorphToMany { return $this->morphedByMany(Opportunity::class, 'taggable'); }
}
