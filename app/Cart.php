<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Cart extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'quantity', 'unit_price', 'total_price', 'discount_id',
    ];
	
	public function productDetails()
    {

        return $this->hasOne('App\Product','id','product_id');

    }
}
