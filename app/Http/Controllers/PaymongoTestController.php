<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Arr;

class PaymongoTestController extends Controller
{

    public function test(){

        $secret_key =  env('PAYMONGO_TEST_SECRET_KEY','');

        $response = Http::withBody(json_encode([
            'data' =>[
                'attributes'=>[
                    'cancel_url' => url('/'),
                    'line_items' =>[
                        [
                            'amount'        => 1000,//10.00
                            'currency'      => 'PHP',
                            'name'         => 'Toys and Bagoong',
                            'quantity'      => 10
                        ],
                        [
                            'amount'        => 10050,//100.50
                            'currency'      => 'PHP',
                            'name'         => 'Guns and Pancakes',
                            'quantity'      => 2
                        ]
                        ],
                        'payment_method_types' =>[
                            'card',
                            'gcash',
                            'paymaya',
                            'grab_pay',
                            'brankas_bdo',
                            'brankas_metrobank'
                        ],
                        'reference_number' => 'POP10',
                        'statement_descriptor' => 'Beam Gifts',
                        'success_url' => url('/checkout/success')
                ]
            ]
        ]))->withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic '.base64_encode( $secret_key.':' )
        ])->post('https://api.paymongo.com/v1/checkout_sessions')
        ->throw()
        ->json();
        
        echo json_encode([
            'data' =>[
                'attributes'=>[
                    'cancel_url' => url('/'),
                    'line_items' =>[
                        [
                            'amount'        => 1000,//10.00
                            'currency'      => 'PHP',
                            'name'         => 'Toys and Bagoong',
                            'quantity'      => 10
                        ],
                        [
                            'amount'        => 10050,//100.50
                            'currency'      => 'PHP',
                            'name'         => 'Guns and Pancakes',
                            'quantity'      => 2
                        ]
                        ],
                        'payment_method_types' =>[
                            'card',
                            'gcash',
                            'paymaya',
                            'grab_pay',
                            'brankas_bdo',
                            'brankas_metrobank'
                        ],
                        'reference_number' => 'POP10',
                        'statement_descriptor' => 'Beam Gifts',
                        'success_url' => url('/checkout/success')
                ]
            ]
        ]);
        echo "<br/><br/>";
        dd($response);

        if(!isset($response['data'])){
            echo 'No Data';
            return false;
        }

        $data = $response['data'];

        if(!isset($data['attributes'])){
            echo 'No Attributes';
            return false;
        }

        $attr = $data['attributes'];

        if(!isset($attr['checkout_url'])){
            echo 'No Checkout URL';
            return false;
        }

        $checkout_url = $attr['checkout_url'];

        echo $checkout_url;
    }


    public function callback(Request $request){
       
        try{
            Log::channel('paymongo')->info( 'Call back' );

            $data = $request->input('data');

            Log::channel('paymongo')->info( $data );
            if(!$data){

                return false;
            }

            //data. attributes . type
            $type = Arr::get($data,'attributes.type',null);

            $save = [];

            if($type != 'checkout_session.payment.paid'){

                return false;
            }

            $save['type'] = $type;

            //data . attributes . livemode
            $live = Arr::get($data,'attributes.livemode',null);

            $save['live'] = $live;

            //data. attributes.data.attributes.reference_number 
            $reference_number = Arr::get($data,'attributes.data.attributes.reference_number',null);

            //data. attributes . data. attributes. payments [0] . attributes.
            //currency
            //fee
            //net_amount
            //amount
            //status
            //source.type = gcash
            

            $payment_arr = Arr::get($data,'attributes.data.attributes.payment');

            if(!isset($payment_arr[0])){
                Log::channel('paymongo')->info( json_encode($save) );

                return false;
            }

            $payment = $payment_arr[0];

            $currency = Arr::get($payment,'currency',null);
            $fee      = Arr::get($payment,'fee',null);
            $amount   = Arr::get($payment,'amount',null);
            $status   = Arr::get($payment,'status',null);
            $source   = Arr::get($payment,'source',null);

            $save['currency']   = $currency;
            $save['fee']        = $fee;
            $save['amount']     = $amount;
            $save['status']     = $status;
            $save['source']     = $source;

            Log::channel('paymongo')->info( json_encode($save) );
 
        }catch(\Exception $e){
            Log::channel('paymongo')->info( $e->getMessage() );
        }
    }
}