<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'account_id',
        'user_id',
        'quantity',
        'value'
    ];


    public function products() : BelongsTo
    {

        return $this->belongsTo(Product::class, 'product_id');
    }
}
