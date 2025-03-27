<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class paymongoListWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paymongo:webhook {mode} {action=list} {param1?} {param2?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered webhooks from your Paymongo account';

    /**
     * Execute the console command.
     */
    public function handle()
    {   

        $mode       = $this->argument('mode');
        $action     = $this->argument('action');
        $param1     = $this->argument('param1');
        $param2     = $this->argument('param2');
        $secret_key = '';
        $url        = '';
        

        if($mode == 'test'){
            $secret_key =  env('PAYMONGO_TEST_SECRET_KEY','');
            $url = 'https://api.paymongo.com/v1/webhooks';
        }else if($mode == 'live'){
            $secret_key =  env('PAYMONGO_LIVE_SECRET_KEY','');
        }

        if(!$secret_key){
            $this->error('No secret key found in .env file. (PAYMONGO_SECRET_KEY)');
            return true;
        }

        switch($action){
            case 'list':
             
                return $this->list($mode,$secret_key,$url);
                break;
            case 'create':
                return $this->create($mode,$secret_key,$url,$param1,$param2);
                break;
        }
    }

    private function list($mode,$secret_key,$url){
     
        try{

      
            $this->line('Fetching...');

            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic '.base64_encode( $secret_key.':' )
            ])->get($url, [])
            ->throw()
            ->json();
            
            
            if(!isset($response['data'])){
                $this->error('Unrecogonized response structure from Paymongo. (data)');
                return true;
            }


            foreach($response as $k => $i){
                $this->line($k .':');
                $this->line($i);
                $this->newLine();
            }

            $items = $response['data'];

            $arr = [];

            foreach($items as $key => $item){
         
                $arr = [$item];
            }

            $this->newLine();
            $this->line('Mode: '.strtoupper($mode));
            $this->newLine();
            $this->table(

                ['Webhooks'],
            
                $arr
            
            );

       

        }catch(\Exception $e){

          
            $this->error('Something went wrong');
            $this->line($e->getMessage());
        }
    }

    private function create($mode,$secret_key,$url,$param1,$param2){
        

        if(!$param1){
            $this->error('Parameter for URI webhook is required');
        }

        $events = [
            'source.chargeable',
            'payment.paid',
            'payment.failed'
        ];

        if(!$param2){
        
            $events = explode(',',$param2);
        }

        $url =  url($param1);


        try{



            $this->line('Fetching...');

            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic '.base64_encode( $secret_key.':' )
            ])->post($url, [
                'data'=>[
                    'attributes'=>[
                        'url' => $url,
                        'events'=>$events
                    ]
                ]
            ])
            ->throw()
            ->json();

            if(!isset($response['data'])){
                $this->error('Unrecogonized response structure from Paymongo. (data)');

                $this->line($response);

                return true;
            }

            $this->newLine();
            $this->line('Mode: '.strtoupper($mode));
            $this->newLine();
            $this->line('Webhook created uri: '.$url.' with events ['.implode(',',$events).']');

        }catch(\Exception $e){

          
            $this->error('Something went wrong');
            $this->newLine();
            $this->line('uri: '.$url);
            $this->newLine();
            $this->line($e->getMessage());
        }
    }
}
