<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Product; 
use App\Discount; 
use Illuminate\Support\Facades\Auth; 

class ProductsController extends Controller 
{
	public $successStatus = 200;
	public function getProducts(){
		$user = Auth::user();
		$products = Product::get();
		$role_id = $user->role_id;
		$result = array();
		foreach($products as $p){
			$text = null;
			@$discount_details = Discount::where('product_id', $p->id)->where('role_id', $role_id)->get();
			if(@$discount_details[0]->method == 'Flat'){
				if($discount_details[0]->min_qty == 1){
					$text = 'Price dropped to RM '.$discount_details[0]->flat_price.' per unit';;
				}
				else{
					$text = 'Buy '.$discount_details[0]->min_qty.' or more RM '.$discount_details[0]->flat_price.' per unit';
				}
			}
			elseif(@$discount_details[0]->method == 'Extra'){
				$total_qty = $discount_details[0]->free_qty + $discount_details[0]->min_qty;
				$text = 'Get '.$total_qty.' for '.$discount_details[0]->min_qty.' deal';
			}

			$product = [
				'id'=>$p->id,
				'name'=>$p->name,
				'base_price'=>$p->base_price,
				'image'=>$p->image,
				'discount_id'=> @$discount_details[0]->id,
				'discount_text'=> $text,
			];
			$result[] = $product;
		}
		
		return response()->json(['response' => $result], $this-> successStatus); 
	}
}