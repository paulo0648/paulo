<?php

namespace Modules\Flowiseai\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Flowiseai\Models\Bot;

class AdminbotsController extends Controller
{
    /**
     * Provide class.
     */
    private $provider = Bot::class;

    /**
     * Web RoutePath for the name of the routes.
     */
    private $webroute_path = 'flowisebots.';

    /**
     * View path.
     */
    private $view_path = 'flowiseai::';

    /**
     * Parameter name.
     */
    private $parameter_name = 'bot';

    /**
     * Title of this crud.
     */
    private $title = 'bot';

    /**
     * Title of this crud in plural.
     */
    private $titlePlural = 'bots';

    private function getFields($class='col-md-4')
    {
        $fields=[];
        
        //Add name field
        $fields[0]=['class'=>$class, 'ftype'=>'input', 'name'=>'Name', 'id'=>'name', 'placeholder'=>'Enter name', 'required'=>true];
        
        //Add description field
        $fields[1]=['class'=>$class, 'ftype'=>'textarea', 'name'=>'Descripion', 'id'=>'description', 'placeholder'=>'Enter descriiption', 'required'=>false];
        
        //Add url field
        $fields[2]=['class'=>$class, 'ftype'=>'input', 'name'=>'URL', 'id'=>'url', 'placeholder'=>'Enter bot URL', 'required'=>true];

         //Add url field
         $fields[3]=['class'=>$class, 'ftype'=>'input', 'name'=>'Documentation url', 'id'=>'documentation_url', 'placeholder'=>'Enter documentation or video url', 'required'=>true];


        //Add company config
        $fields[4]=['class'=>$class, 'ftype'=>'input', 'name'=>'Configuration on the bot', 'id'=>'company_configs', 'placeholder'=>'Ex chatOpenAI_0.openAIApiKey, chatOpenAI_0.modelName', 'required'=>false,'additionalInfo'=>"Comma sepparated list of values, that need to be overwritten"];
        
        //Add url field
        //$fields[5]=['class'=>$class, 'ftype'=>'input', 'name'=>'Chat Access Token', 'id'=>'token', 'placeholder'=>'Enter the chat access token', 'required'=>true];


         

        //Return fields
        return $fields;
    }


    private function getFilterFields(){
        $fields=$this->getFields('col-md-3');
        $fields[0]['required']=true;
        return [$fields[0]];
    }

    /**
     * Auth checker functin for the crud.
     */
    private function authChecker()
    {
        $this->adminOnly();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->hasRole(['staff','owner'])){
            return redirect(route('flowisebots.indexcompany'));
        }
        $this->authChecker();

        $items=$this->provider::whereNull('company_id')->orderBy('id', 'desc');
        if(isset($_GET['name'])&&strlen($_GET['name'])>1){
            $items=$items->where('name',  'like', '%'.$_GET['name'].'%');
        }
        $items=$items->paginate(config('settings.paginate'));

        return view($this->view_path.'index', ['setup' => [
            //'usefilter'=>true,
            'title'=>__('crud.item_managment', ['item'=>__($this->titlePlural)]),
            'action_link'=>route($this->webroute_path.'create'),
            'action_name'=>__('crud.add_new_item', ['item'=>__($this->title)]),
            'items'=>$items,
            'item_names'=>$this->titlePlural,
            'webroute_path'=>$this->webroute_path,
            'fields'=>$this->getFields(),
            //'filterFields'=>$this->getFilterFields(),
            'custom_table'=>true,
            'parameter_name'=>$this->parameter_name,
            'parameters'=>count($_GET) != 0,
        ]]);
    }


    public function indexforcompanyes(){

        $this->ownerAndStaffOnly();
        $companyID=$this->getCompany()->id;
        $items=$this->provider::whereNull('company_id')
        ->orWhere('company_id',  $companyID)->orderBy('id', 'desc');
        $items=$items->paginate(config('settings.paginate'));
        return view($this->view_path.'indexcompany', [
            'companyID'=> $companyID,
            'setup' => [
            'action_link'=>route($this->webroute_path.'create'),
            'action_name'=>__('Add your own custom bot'),
            'title'=>__('Available chatbots'),
            'items'=>$items,
            'item_names'=>$this->titlePlural,
            'webroute_path'=>$this->webroute_path,
            'fields'=>$this->getFields(),
            //'filterFields'=>$this->getFilterFields(),
            'custom_table'=>true,
            'parameter_name'=>$this->parameter_name,
            'parameters'=>count($_GET) != 0,
        ]]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->hasRole(['admin','staff','owner'])){
            abort(403, 'Unauthorized action.');
        }
      


        return view('general.form', ['setup' => [
            'title'=>__('crud.new_item', ['item'=>__($this->title)]),
            'action_link'=>route($this->webroute_path.'index'),
            'action_name'=>__('crud.back'),
            'iscontent'=>true,
            'action'=>route($this->webroute_path.'store'),
            'breadcrumbs' => [
                [__('Aibots'), route('flowisebots.index')]
            ],
        ],
        'fields'=>$this->getFields() ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasRole(['admin','staff','owner'])){
            abort(403, 'Unauthorized action.');
        }
        
        //Create new item
        $item = $this->provider::create($request->all());
        $item->save();

        if(auth()->user()->hasRole(['staff','owner'])){
            $item->company_id=$this->getCompany()->id;
            $item->update();
        }

        return redirect()->route($this->webroute_path.'index')->withStatus(__('crud.item_has_been_added', ['item'=>__($this->title)]));
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Contact  $contacts
     * @return \Illuminate\Http\Response
     */
    public function edit(Bot $bot)
    {
        if(auth()->user()->hasRole(['staff','owner'])){
            if($bot->company_id!=$this->getCompany()->id){
                abort(403, 'Unauthorized action.');
            }
        }else{
            $this->authChecker();
        }
        

        $fields = $this->getFields();
        $fields[0]['value'] = $bot->name;
        $fields[1]['value'] = $bot->description;
        $fields[2]['value'] = $bot->url;
        $fields[3]['value'] = $bot->documentation_url;
        $fields[4]['value'] = $bot->company_configs;
        //$fields[5]['value'] = $bot->token;

      

        $parameter = [];
        $parameter[$this->parameter_name] = $bot->id;
        return view($this->view_path.'edit', [
           
            'setup' => [
                
            'title'=>__('crud.edit_item_name', ['item'=>__($this->title), 'name'=>$bot->name]),
            'action_link'=>route($this->webroute_path.'index'),
            'action_name'=>__('crud.back'),
            'iscontent'=>true,
            'isupdate'=>true,
            'action'=>route($this->webroute_path.'update', $parameter),
        ],
        'fields'=>$fields, ]);
    }

    public function config(Bot $bot)
    {
        $this->ownerAndStaffOnly();
        $companyID=$this->getCompany()->id;
       

        $fields = [
            ['class'=>'col-md-4', 'ftype'=>'bool', 'name'=>'Bot activated', 'id'=> $companyID."_activ",  'required'=>true,"value"=>$bot->getConfig($companyID."_activ",null)]
        ];
        if($bot->company_configs&&strlen($bot->company_configs)>0){

            foreach (explode(",",$bot->company_configs) as $key => $config) {
                $value=$bot->getConfig($companyID."_".$config,"");
                array_push($fields,['class'=>'col-md-4', 'ftype'=>'input', 'name'=>$config, 'id'=> $companyID."_".$config,  'required'=>true, 'placeholder'=>"","value"=>$value]
            );
            }  
        }
         
      

        $parameter = [];
        $parameter[$this->parameter_name] = $bot->id;

        return view($this->view_path.'editcompany', [
            'active'=> $bot->getConfig($companyID."_activ","false"),
            'bot'=> $bot,
            'setup' => [
            'title'=>__('crud.edit_item_name', ['item'=>__($this->title), 'name'=>$bot->name]),
            'action_link'=>route($this->webroute_path.'indexcompany'),
            'action_name'=>__('crud.back'),
            'action_link5'=>$bot->documentation_url,
            'action_name5'=>__('Bot documentation'),
            'iscontent'=>true,
            'isupdate'=>true,
            'action'=>route($this->webroute_path.'updateconfig', $parameter),
        ],
        'fields'=>$fields, ]);
    }

    public function updateconfig(Request $request, Bot $bot)
    {
        $this->ownerAndStaffOnly();
        $companyID=$this->getCompany()->id;
        $receivedData=$request->all();
        $bot->setConfig($companyID."_activ",$receivedData[$companyID."_activ"]);
        if($bot->company_configs&&strlen($bot->company_configs)>0){
            foreach (explode(",",$bot->company_configs) as $key => $config) {
                $bot->setConfig($companyID."_".$config,$receivedData[$companyID."_".str_replace('.','_',$config)]);
            }
        }
        return redirect()->route($this->webroute_path.'indexcompany',$bot->id)->withStatus(__('crud.item_has_been_updated', ['item'=>__($this->title)]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Contact  $contacts
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $item = $this->provider::findOrFail($id);
        if(auth()->user()->hasRole(['staff','owner'])){
            if($item ->company_id!=$this->getCompany()->id){
                abort(403, 'Unauthorized action.');
            }
        }else{
            $this->authChecker();
        }
       
        $item->name = $request->name;
        $item->url = $request->url;
        $item->documentation_url = $request->documentation_url;
        $item->description = $request->description;
        $item->company_configs = $request->company_configs;
        //$item->token = $request->token;

        $item->update();

        return redirect()->route($this->webroute_path.'index')->withStatus(__('crud.item_has_been_updated', ['item'=>__($this->title)]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Contact  $contacts
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        $item = $this->provider::findOrFail($id);
        if(auth()->user()->hasRole(['staff','owner'])){
            if($item ->company_id!=$this->getCompany()->id){
                abort(403, 'Unauthorized action.');
            }
        }else{
            $this->authChecker();
        }
        //DELETE ALL THE CONFIGS FIRST
        $item->deleteAllConfigs();
        $item->delete();
        return redirect()->route($this->webroute_path.'index')->withStatus(__('crud.item_has_been_removed', ['item'=>__($this->title)]));
    }
    

}
