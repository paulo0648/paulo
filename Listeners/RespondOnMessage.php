<?php

namespace Modules\Flowiseai\Listeners;

use App\Models\Config;
use Modules\Flowiseai\Models\Bot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use PDO;

class RespondOnMessage
{

    public function handleMessageByContact($event){
        try {
            $contact=$event->message->contact;
            $message=$event->message;
            if($contact->enabled_ai_bot&&!$message->bot_has_replied){
            
                //Based on the contact company, find this company firs active AI Bot
                $company_id= $contact->company_id;
    
                $aibot=Config::where('model_type','Modules\Flowiseai\Models\Bot')->where('key',$company_id.'_activ')->where('value','true')->first();
    
    
    
                if($aibot){
                    //Get the AI Bot
                    $bot=Bot::findOrFail($aibot->model_id);
    
                    //Get the specific configs, by default contains the zep sessionId
                    $overrideConfig=[
                        'sessionId'=>"chat_".$contact->phone
                    ];
                    $configs=Config::where('model_type','Modules\Flowiseai\Models\Bot')->where('key','like',$company_id.'_%')->where('model_id',$aibot->model_id)->get()->toArray();
                    foreach ($configs as $keyc => $valueConfig) {   
                        $nameOfConfig=str_replace($company_id."_","",$valueConfig['key']);
                        if($nameOfConfig!="activ"){
                            $overrideConfig[$nameOfConfig]=$valueConfig['value'];
                        } 
                    }

                    
    
                    //Send question to bot
                    $messageFromBot=$this->makePredictionRequest($bot,$message,$contact,$overrideConfig);
    
                    if($messageFromBot){
                        
                        

                        if(isset($messageFromBot['text'])){
                            $chunks=$this->chunkIt($messageFromBot['text']);
                            foreach ($chunks as $key => $chunk) {
                                $contact->sendMessage($chunk,false);
                               
                            }
                        }else{
                            //This can be the error
                            $contact->sendMessage($messageFromBot,false);
                           
                        }

                        try {
                            if(isset($messageFromBot['sourceDocuments'])){
                                $link=$messageFromBot['sourceDocuments'][0]['metadata']['source'];
                                if($link!="blob"){
                                    $contact->sendMessage($link,false);
                                }
                                
                               
                            }
                        } catch (\Throwable $th) {
                            //throw $th;
                        }

                        try {
                            if(isset($messageFromBot['sourceDocuments'])){
                                $link=$messageFromBot['sourceDocuments'][0]['metadata']['url'];
                                if($link!="blob"){
                                    $contact->sendMessage($link,false);
                                }
                               
                            }
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        

                        
                        $message->ai_bot_has_replied=true;
                        $message->update();
                    
                    }
    
    
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
       
        


    }

    public function chunkIt($message){
        $maxChunkSize = 3800;

        // Split the text into an array of words
        $words = preg_split('/\s+/', $message);

        // Initialize an array to store the chunks
        $chunks = [];
        $currentChunk = '';

        // Iterate through the words and add them to chunks
        foreach ($words as $word) {
            // Check if adding the current word to the current chunk would exceed the maximum size
            if (strlen($currentChunk) + strlen($word) + 1 <= $maxChunkSize) {
                if (!empty($currentChunk)) {
                    $currentChunk .= ' '; // Add a space between words
                }
                $currentChunk .= $word;
            } else {
                // Start a new chunk with the current word
                $chunks[] = $currentChunk;
                $currentChunk = $word;
            }
        }

        // Add the last chunk
        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        // Now $chunks contains an array of text chunks, each with a maximum size of 3800 characters
        return $chunks;
    }

    public function makePredictionRequest(Bot $bot,$message,$contact,$overrideConfig) {
        $url = $bot->url;
        $data = [
            'question' => $message->value,
            'overrideConfig'=>$overrideConfig
        ];
        $headers = [
            'Content-Type' => 'application/json'
        ];
        

        
        $response = Http::post($url, $data, $headers);
        //dd($response->body());
        //$contact->sendMessage("AI Chat Response:: ".$response->body(),false);
        //return false;
        //$this->sendMessage("Test",false);

        // Handle the response here
        if ($response->successful()) {
            $responseData = $response->json();
            return $responseData;
        } else {
            // Handle error response
            $errorCode = $response->status();
            return null;
            // Handle the error based on $errorCode
        }
    }

    public function subscribe($events)
    {
        $events->listen(
            'Modules\Wpbox\Events\ContactReplies',
            [RespondOnMessage::class, 'handleMessageByContact']
        );
    }

}
