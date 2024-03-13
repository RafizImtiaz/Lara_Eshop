<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ShippingCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    public function create(){
        $countries = Country::get();
        $shippingCharges = ShippingCharge::select('shipping_charges.*','countries.name')
                            ->leftJoin('countries','countries.id','shipping_charges.country_id')->get();


        $data['countries'] = $countries;
        $data['shippingCharges'] = $shippingCharges;

        return view('admin.shipping.create',$data);
    }

    public function store(Request $request){

        

        $validator = Validator::make($request->all(),[
            'country' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($validator->passes()) {
            $count = ShippingCharge::where('country_id',$request->country)->count();
        if ( $count > 0) {
        Session()->flash('error','Shipping already exists');

            return response()->json([
                'status' => true,
            ]);
        }



            $shipping = new ShippingCharge();
            $shipping->country_id = $request->country;
            $shipping->amount = $request->amount;
            $shipping->save();

            Session()->flash('success','Shipping Added Successfully');

            return response()->json([
                'status' => true,
                
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);

        }
    }

    public function edit($id){
        $shippingCharge = ShippingCharge::find($id);
        $countries = Country::get();


        $data['countries'] = $countries;
        $data['shippingCharge'] = $shippingCharge;

        return view('admin.shipping.edit',$data);
    }

    public function update($id, Request $request){
        $shipping = ShippingCharge::find($id);
        $validator = Validator::make($request->all(),[
            'country' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($validator->passes()) {

            if($shipping==null){
                Session()->flash('error','Shipping not Found');
    
                return response()->json([
                    'status' => true,
                    
                ]);
            }
            
            $shipping->country_id = $request->country;
            $shipping->amount = $request->amount;
            $shipping->save();

            Session()->flash('success','Shipping updated Successfully');

            return response()->json([
                'status' => true,
                
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);

        }
    }

    public function destroy($id){
        $shippingCharge = ShippingCharge::find($id);

        if($shippingCharge==null){
            Session()->flash('error','Shipping not Found');

            return response()->json([
                'status' => true,
                
            ]);
        }
        
        $shippingCharge->delete();

        Session()->flash('success','Shipping deleted Successfully');

            return response()->json([
                'status' => true,
                
            ]);
    }
}
