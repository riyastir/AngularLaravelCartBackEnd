<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Order;
use App\OrderItem; 
use App\Cart; 
use Illuminate\Support\Facades\Auth; 

class OrderController extends Controller 
{
	public $successStatus = 200;
	/** Post Order */
	public function postOrder(Request $request){
		$user = Auth::user();
		if(isset($user->id)){
		$user_id = $user->id;
		$items = Cart::where('user_id',$user_id)->get();
		$total = $items->sum('total_price');
		$orderData = [
			'user_id' => $user_id,
			'payment_status'=>0,
			'amount'=>$total
		];
		$orderCreate = Order::create($orderData);
		if($orderCreate->id){
		foreach($items as $item){
			$itemSet = [
				'order_id'=>$orderCreate->id,
				'product_id'=>$item->product_id,
				'unit_price'=>$item->unit_price,
				'quantity'=>$item->quantity,
				'total_price'=>$item->total_price,
				'discount_id'=>$item->discount_id
			];
			$orderItemsCreate = OrderItem::create($itemSet);
		}
		}
		$result = [
			'status' => 'Success'
		];
		}
		else{
			$result = [
				'status' => 'Unauthorized'
			];
		}
		return response()->json(['response' => $result ], $this-> successStatus); 
	}
}