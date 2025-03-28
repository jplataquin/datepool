<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class paymongoWebhook extends Command
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
        $this->secret_key           = '';
        $this->paymongo_baseurl     = 'https://api.paymongo.com/v1/webhooks/';
        

        if($mode == 'test'){
            $this->secret_key =  env('PAYMONGO_TEST_SECRET_KEY','');
        }else if($mode == 'live'){
            $this->secret_key =  env('PAYMONGO_LIVE_SECRET_KEY','');
        }else{
            $this->error('Mode parameter is required');
        }

        if(!$this->secret_key){
            $this->error('No secret key found in .env file.');
            return true;
        }

        $this->base64_key = base64_encode( $this->secret_key.':' );
        $this->line('Authorization: Basic '.$this->base64_key);
        $this->newLine();
        $this->line('Mode: '.$mode);
        $this->newLine();
        
        switch($action){
            case 'list':
                return $this->list($mode);
                break;
            case 'create':
                return $this->create($mode,$param1,$param2);
                break;
            case 'disable':
                return $this->disable($mode,$param1);
                break;
            case 'enable':
                return $this->enable($mode,$param1);
                break;
            default:
                $this->error('Invalid action ('.$action.')');
                return true;
        }
    }

    private function list($mode){
     
        try{

            $this->line('(GET) Paymongo API: '.$this->paymongo_baseurl);
            $this->newLine();

            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic '.$this->base64_key
            ])->get($this->paymongo_baseurl, [])
            ->throw()
            ->json();
            
            
            if(!isset($response['data'])){
                $this->error('Unrecogonized response structure from Paymongo. (data)');
                return true;
            }


            $data  = $response['data']; 
            

            foreach($data as $item){
                
                $type = 'test';
                $attr = $item['attributes'];

                if($attr['livemode']){
                    $type = 'live';
                }

                $this->line('============================================');

                $this->line('Type: '.$type);

                $this->line('Status: '.$attr['status']);
            
                $this->line('ID: '.$item['id']);

                $this->line('URL: '.$attr['url']);

                $this->line('Attributes: '.implode(', ',$attr['events']));

                $this->line('Created at: '.$attr['created_at']);

                $this->line('Updated at: '.$attr['updated_at']);
                $this->newLine();
            
            }

       

        }catch(\Exception $e){

          
            $this->error('Something went wrong');
            $this->line($e->getMessage());
        }
    }

    private function create($mode,$param1,$param2){
        
        if(!$param1){
            $this->error('Parameter for URI webhook is required');
            return false;
        }

        $events = [
            'source.chargeable',
            'payment.paid',
            'payment.failed'
        ];

        if($param2){
        
            $events = explode(',',$param2);
        }

        $webhook_url =  url($param1);


        try{

            $this->line('(POST) Paymongo API: '.$this->paymongo_baseurl);
            $this->newLine();


            $response = Http::withBody(json_encode([
                'data'=>[
                    'attributes'=>[
                        'url'   => $webhook_url,
                        'events'=> $events
                    ]
                ]
             ]))->withHeaders([
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic '.$this->base64_key
            ])->post($this->paymongo_baseurl)
            ->throw()
            ->json();
            
            
            $this->line(json_encode($response));
            $this->newLine();

            if(!isset($response['data'])){
                $this->error('Unrecogonized response structure from Paymongo. (data)');

                $this->line($response);

                return true;
            }

            $this->newLine();
            $this->line('Mode: '.strtoupper($mode));
            $this->newLine();
            $this->line('Webhook created uri: '.$webhook_url.' with events ['.implode(',',$events).']');
            $this->newLine();

        }catch(\Exception $e){

          
            $this->error('Something went wrong');
            $this->newLine();
            $this->line('uri: '.$webhook_url);
            $this->newLine();
            $this->line($e->getMessage());
        }
    }

    private function disable($mode,$param1){

        if(!$param1){
            $this->error('Parameter for webook_id is required');
            return false;
        }

        $api_endpoint = $this->paymongo_baseurl.$param1.'/disable';

        try{

            $this->line('(POST) Paymongo API: '.$api_endpoint);
            $this->newLine();

            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic '.$this->base64_key
            ])->post($api_endpoint)
            ->throw()
            ->json();
            
            if(!isset($response['data'])){
                $this->error('Unrecognized Paymongo resoponse (data)');
                return false;
            }

            $data = $response['data'];
            $attr = $data['attributes'];

            $this->line('===Webhook Disabled===');
            $this->line('ID: '.$param1);
            $this->line('URL: '.$attr['url']);
            $this->newLine();

        }catch(\Exception $e){

          
            $this->error('Something went wrong');
            $this->newLine();
            $this->line($e->getMessage());
        }

    }

    private function enable($mode,$param1){

        if(!$param1){
            $this->error('Parameter for webook_id is required');
            return false;
        }

        $api_endpoint = $this->paymongo_baseurl.$param1.'/enable';

        try{

            $this->line('(POST) Paymongo API: '.$api_endpoint);
            $this->newLine();

            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic '.$this->base64_key
            ])->post($api_endpoint)
            ->throw()
            ->json();
            
            if(!isset($response['data'])){
                $this->error('Unrecognized Paymongo resoponse (data)');
                return false;
            }

            $data = $response['data'];
            $attr = $data['attributes'];

            $this->line('===Webhook Enabled===');
            $this->line('ID: '.$param1);
            $this->line('URL: '.$attr['url']);
            $this->newLine();

        }catch(\Exception $e){

          
            $this->error('Something went wrong');
            $this->newLine();
            $this->line($e->getMessage());
        }

    }
}
