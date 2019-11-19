<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Product; 
use Illuminate\Support\Facades\Auth; 

class ProductsController extends Controller 
{
	public $successStatus = 200;
	public function getProducts(){
		$products = Product::get();
		return response()->json(['response' => $products], $this-> successStatus); 
	}
}