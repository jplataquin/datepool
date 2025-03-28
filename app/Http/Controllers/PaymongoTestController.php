<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class PaymongoTestController extends Controller
{

    public function test(){

        $secret_key =  env('PAYMONGO_TEST_SECRET_KEY','');

        $response = Http::withBody(json_encode([
            'data' =>[
                'attributes'=>[
                    'cancel_url' => '',
                    'line_items' =>[
                        [
                            'amount'        => 1000,//10.00
                            'currency'      => 'PHP',
                            'named'         => 'Toys and Bagoong',
                            'quantity'      => 10
                        ],
                        [
                            'amount'        => 10050,//100.50
                            'currency'      => 'PHP',
                            'named'         => 'Guns and Pancakes',
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
            'Authorization' => 'Basic '.base64_encode( $this->secret_key.':' )
        ])->post();

        print_r($response);
    }
}