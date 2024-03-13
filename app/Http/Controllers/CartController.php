<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request){
        $product = Product::with('product_images')->find($request->id);
        
        if($product == null) {
            return response()->json([
                'status'=> false,
                'message'=> 'Product Not Found',
            ]);
        }

        $status = false;
        $message = '';

        if (Cart::count() > 0) {
            
            $cartContent = Cart::content();

            $productAlreadyExist = false;

            foreach ($cartContent as $item){
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }

            }

            if($productAlreadyExist == false){
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty
                ($product->product_images)) ? $product->product_images->first() : '']);

                $status = true;
                $message = '<strong>'.$product->title.'</strong> added in your cart successfully';
                session()->flash('success',$message);

            } else {
                $status = false;
                $message = $product->title.' already in cart';
                
        }

        } else {
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
            $status = true;
            $message =  '<strong>'.$product->title.'</strong> added in your cart successfully';
            session()->flash('success',$message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function cart(){
        $cartContent = Cart::content();

        $data['cartContent'] = $cartContent;
        return view('front.cart', $data);

    }

    public function updateCart(Request $request){

        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        //check Quantity
        $product = Product::find($itemInfo->id);

        if ($product->track_qty == 'Yes') {
            if ($product->qty >= $qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart Updated Successfully';
                $status = true;
                session()->flash('success',$message);
            } else {
                $message = 'Requested quantity('.$qty.') not avaialable in stock';
                $status = false;
                session()->flash('error',$message);
            }
        } else {

            Cart::update($rowId, $qty);
                $message = 'Cart Updated Successfully';
                $status = true;
                session()->flash('success',$message);
         }
        
        return response()->json([
            'status'=> $status,
            'message'=> $message

        ]);

    }

        public function deleteItem(Request $request){
            $rowId = $request->rowId;
            $itemInfo = Cart::get($rowId);

            if ($itemInfo == null) {
                $message = 'Item Not Found in Cart';
                session()->flash('error',$message);

                return response()->json([
                    'status'=> false,
                    'message'=> $message
        
                ]);
            }

                Cart::remove($request->rowId);
                $successmessage = 'Item Removed from the Cart Successfully';
                session()->flash('success',$successmessage);
                
                return response()->json([
                    'status'=> false,
                    'message'=> $successmessage
        
                ]);

        }

        public function checkout(){
            if(Cart::count() == 0){
                return redirect()->route('front.cart');
            }
    
            if (Auth::check() == false) {
                if (!session()->has('url.intended')) {
                    session(['url.intended' => url()->current()]);
                }
                return redirect()->route('account.login');
            }
            
            $customerAddress = CustomerAddress::where('user_id', Auth::user()->id)->first();
            session()->forget('url.intended');
            $countries = Country::orderBy('name', 'ASC')->get();
    
            $totalShippingCharge = 0;
            $grandTotal = 0;
    
            if ($customerAddress != null) {
                $userCountry = $customerAddress->country_id;
                $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();
    
                if ($shippingInfo != null) {
                    $totalQty = 0;
                    foreach (Cart::content() as $item) {
                        $totalQty += $item->qty;
                    }
                    $totalShippingCharge = $totalQty * $shippingInfo->amount;
                    $grandTotal = $totalShippingCharge + Cart::subtotal(2, '.', '');
                }
            } else {
                $grandTotal = Cart::subtotal(2, '.', '');
            }
    
            return view('front.checkout', [
                'countries' => $countries,
                'customerAddress' => $customerAddress,
                'totalShippingCharge' => $totalShippingCharge,
                'grandTotal' => $grandTotal,
            ]);
        }
    

        public function processCheckout(Request $request){

            $validator = Validator::make($request->all(),[
                'first_name' => 'required|min:4',
                'last_name' => 'required',
                'email' => 'required|email',
                'country_id' => 'required',
                'address' => 'required|min:30',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required',
                'mobile' => 'required',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Please, Fix the errors',
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }
        
            $user = Auth::user();
        
            CustomerAddress::updateOrCreate([
                'user_id' => $user->id
            ], [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country_id,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]);
        if($request->payment_method == 'cod'){
            // Save the order
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2, '.', '');

            $totalQty = 0;
            $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();

            foreach (Cart::content() as  $item) {
                $totalQty += $item->qty;
            }
            
            if ($shippingInfo != null) {          
                $shipping = $totalQty*$shippingInfo->amount;
                $grandTotal = $subTotal+ $shipping;

            } else{
                $shippingInfo = ShippingCharge::where('country_id', 'rest_o_world')->first();
                $shipping = $totalQty*$shippingInfo->amount;

                $grandTotal = $subTotal+ $shipping;

            }

            $order = new Order;
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->payment_status = 'Not Paid';
            $order->status = 'Pending';
            $order->user_id = $user->id;
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->country_id = $request->country_id;
            $order->address = $request->address;
            $order->apartment = $request->apartment;
            $order->state = $request->state;
            $order->city = $request->city;
            $order->zip = $request->zip;
            $order->notes = $request->notes;
            $order->save();
        
            
            //step 4

            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem;
                $orderItem ->product_id = $item->id;
                $orderItem ->order_id = $order->id;
                $orderItem ->name = $item->name;
                $orderItem ->qty = $item->qty;
                $orderItem ->price = $item->price;
                $orderItem ->total = $item->price*$item->qty;
                $orderItem->save();
            
                //update stock
                $productData = Product::find($item->id);
                if ($productData->track_qty == 'Yes') {

                    $currentQty = $productData->qty;
                    $updatedQty = $currentQty-$item->qty;
                    $productData->qty = $updatedQty;
                    $productData->save();    
                }

                
            }


            //sent order email
            orderEmail($order->id, 'customer');


            
            session()->flash('success', 'Order placed successfully');
            
            Cart::destroy();

            return response()->json([
                'message' => 'Order Saved Successfully',
                'orderId' => $order->id,
                'status' => true,
            ]);

        }
    }
        
        public function thankyou($id){

            return view('front.thanks',[
                'id' => $id,
            ]);
        }

        public function getOrderSummary(Request $request){
            $subTotal = Cart::subtotal(2, '.', '');
           if ($request->country_id > 0 ) {

                
                $totalQty = 0;
                $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();

                    foreach (Cart::content() as  $item) {
                        $totalQty += $item->qty;
                    }

                if ($shippingInfo != null) {
                
                    
                    $shippingCharge = $totalQty*$shippingInfo->amount;
                    $grandTotal = $subTotal+ $shippingCharge;

                    return response()->json([

                        'status' => true,
                        'grandTotal' => $grandTotal,
                        'shippingCharge' => $shippingCharge,

                    ]);
                } else{
                    $shippingInfo = ShippingCharge::where('country_id', 'rest_o_world')->first();
                    $shippingCharge = $totalQty*$shippingInfo->amount;

                    $grandTotal = $subTotal+ $shippingCharge;

                    return response()->json([

                        'status' => true,
                        'grandTotal' =>  number_format($grandTotal,2),
                        'shippingCharge' =>  number_format($shippingCharge,2),

                    ]);

                }

           } else {


            return response()->json([

                'status' => true,
                'grandTotal' => number_format($subTotal,2),
                'shippingCharge' => number_format(0,2),

            ]);


           }
      }

}

