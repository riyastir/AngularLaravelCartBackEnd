<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Cart;
use App\Product;
use App\Discount;
use App\Role;
use App\User;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
	public $successStatus = 200;
	public function addToCart(Request $request)
	{	
		$user = Auth::user();
		if(isset($user->id)){
		$customer_id = $request->user_id;
		$user = User::where('id', $customer_id)->first();
		$role_details = Role::find($user->role_id);
		$role_disc = $role_details->discount;
		$product_details = Product::find($request->product_id);
		$unit_price = $product_details->base_price;
		$discount_details = Discount::where('product_id', $request->product_id)->where('role_id', $role_details->id)->first();
		$total_price = 0;
		$discount = 0;
		$free_qty = 0;
		$cartItems = Cart::where('user_id', $request->user_id)->where('product_id', $request->product_id)->get();
		/* Special Pricing Check */
		if (!isset($discount_details)) {
			$reduction = ($role_disc / 100) * $unit_price;
			$reduction = round($reduction, 2);
			$unit_price = $unit_price - $reduction;
		} else {
			$reduction = 0;
		}
		//If Product already in cart then add quantity and check the discount conditions
		if (count($cartItems) > 0) {
			$request->quantity = $request->quantity + $cartItems[0]->quantity;
		}
		if (isset($discount_details)) {
			if ($discount_details->method == 'Flat') {
				if ($request->quantity >= $discount_details->min_qty) {
					$discount = $unit_price - $discount_details->flat_price;
					$unit_price = $discount_details->flat_price;
				} else {
					$discount = 0;
				}
			} else if ($discount_details->method == 'Extra') {
				if (count($cartItems) > 0) {
					$discount_set = $cartItems[0]->quantity / $discount_details->min_qty;

					if ($discount_set >= 1) {
						$free_qty = $discount_details->free_qty * $discount_set;
						$free_qty = (int) $free_qty;
						$request->quantity = $request->quantity - (int) $free_qty;
					} else {
						$free_qty = 0;
					}
				}
				$discount_set = $request->quantity / $discount_details->min_qty;
				if ($discount_set >= 1) {
					$free_qty = $discount_details->free_qty * $discount_set;
					$free_qty = (int) $free_qty;
					$request->quantity = $request->quantity + (int) $free_qty;
				} else {
					$free_qty = 0;
				}
			}
		}
		$discount_id = @$discount_details->id;
		$total_price = ($request->quantity - $free_qty) * $unit_price;
		$total_price = round($total_price, 2);
		//If Product already in cart then add quantity and check the discount conditions
		if (count($cartItems) > 0) {
			$cartId = $cartItems[0]->id;

			$data = [
				'quantity' => $request->quantity,
				'unit_price' => $unit_price,
				'total_price' => $total_price,
				'discount_id' => $discount_id,
			];

			$cartUpdate = Cart::where('id', $cartId)->update($data);
			if ($cartUpdate) {
				$result = [
					'user_id' => $request->user_id,
					'product_id' => $request->product_id,
					'role_disc' => $role_disc,
					'reduction' => $reduction,
					'quantity' => $request->quantity,
					'unit_price' => $unit_price,
					'total_price' => $total_price,
					'free_qty' => $free_qty,
					'discount' => $discount,
					'discount_id' => $discount_id,
				];
			} else {
				$result = [
					'error' => 1
				];
			}
		} else {
			$data = [
				'user_id' => $request->user_id,
				'product_id' => $request->product_id,
				'quantity' => $request->quantity,
				'unit_price' => $unit_price,
				'total_price' => $total_price,
				'discount_id' => $discount_id,
			];
			$cart = Cart::create($data);
			if ($cart->id) {
				$result = [
					'user_id' => $request->user_id,
					'product_id' => $request->product_id,
					'role_disc' => $role_disc,
					'reduction' => $reduction,
					'quantity' => $request->quantity,
					'unit_price' => $unit_price,
					'total_price' => $total_price,
					'free_qty' => $free_qty,
					'discount' => $discount,
					'discount_id' => $discount_id,
				];
			} else {
				$result = [
					'error' => 1
				];
			}
		}
	}
	else{
		$result = [
			'error' => 'Unauthorized'
		];
	}
		//$cart = Cart::get();
		return response()->json(['response' => $result], $this->successStatus);
	}

	public function viewCart(Request $request)
	{	
		$user = Auth::user();
		if(isset($user->id)){
		$user_id = $request->user_id;
		$cartItems = Cart::where('user_id', $user_id)->with('productDetails')->get();
		$total = $cartItems->sum('total_price');
		$result = [
			'items' => $cartItems,
			'count' => count($cartItems),
			'total'	=> number_format($total, 2)
		];
		}
		else{
			$result = [
				'error' => 'Unauthorized'
			];
		}
		return response()->json(['response' => $result], $this->successStatus);
	}

	public function clearCart(Request $request){
		
		$user = Auth::user();
		if(isset($user->id)){
		$user_id = $request->user_id;
		/* Clear Cart Items */
		if($user_id != ''){
		$cartItems =  Cart::where('user_id', $user_id)->delete();
		if($cartItems){
			$result = [
				'status' => 'Cart Cleared'
			];
		}
		else{
			$result = [
				'status' => 'Something went wrong'
			];
		}
		}
		else{
			$result = [
				'status' => 'Something went wrong'
			];
		}
		}
		else{
			$result = [
				'error' => 'Unauthorized'
			];
		}
		return response()->json(['response' => $result], $this->successStatus);
	}

	public function removeItem(Request $request){
		$user = Auth::user();
		if(isset($user->id)){
		$user_id = $request->user_id;
		$product_id = $request->product_id;

		/* Clear Cart Item */
		if($user_id != ''){
			$cartItems =  Cart::where('user_id', $user_id)->where('product_id', $product_id)->delete();
			if($cartItems){
				$result = [
					'status' => 'Item removed'
				];
			}
			else{
				$result = [
					'status' => 'Something went wrong'
				];
			}
			}
			else{
				$result = [
					'status' => 'Something went wrong'
				];
			}
		}
		else{
			$result = [
				'error' => 'Unauthorized'
			];
		}
			return response()->json(['response' => $result], $this->successStatus);
	}
}
