<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}
