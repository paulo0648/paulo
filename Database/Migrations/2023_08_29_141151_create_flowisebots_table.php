<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;
use Modules\Flowiseai\Models\Bot;

class CreateFlowisebotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flowisebots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('icon')->default("")->nullable();
            $table->string('url')->default("");
            $table->string('token')->default("");
            $table->string('documentation_url')->default("");
            $table->text('company_configs')->default("")->nullable();
            $table->text('description')->default("")->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('company_id')->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
        });
        Model::unguard();

        $webBot=Bot::create([
            "name"=>"Full web chat with memory",
            "description"=>"Ask this bot anything. He is so smart. Also, has a great memory.",
            "url"=>"https://flowiseai-railway-production-fe83.up.railway.app/api/v1/prediction/a111fa86-54c4-4202-a671-1bc609d65ce1",
            "documentation_url"=>"https://mobidonia.notion.site/Ask-any-question-search-web-with-long-time-chat-memory-4ded488aac994e3382a637993843cd2e?pvs=4",
        ]);
        //$webBot->setConfig('1_activ',"true");

        $translateBot=Bot::create([
            "name"=>"Translate bot",
            "url"=>"https://flowiseai-railway-production-fe83.up.railway.app/api/v1/prediction/e0d4d962-b5ea-4797-87f1-547df55cad3e",
            "description"=>"Translate from one language to another",
            "company_configs"=>"promptValues",
            "documentation_url"=>"https://mobidonia.notion.site/Translator-translate-any-to-any-809ada6398264203a892656142fa66f5?pvs=4",
        ]);
        $translateBot->setConfig('1_promptValues',' {   "input_language": "English",   "output_language": "Italian",   "text": "{{question}}" }');



       

        $supportBot=Bot::create([
            "name"=>"Support bot",
            "url"=>"https://flowiseai-railway-production-fe83.up.railway.app/api/v1/prediction/c84f2687-d45f-44d7-843b-da36afeb1b60",
            "description"=>"Support bot trained from Notion docs",
            "company_configs"=>"pageId,notionIntegrationToken",
            "documentation_url"=>"https://mobidonia.notion.site/Notion-Support-Bot-848e0788693c4c239e6f4351c1158ce7?pvs=4",
        ]);


        Model::reguard();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flowisebots');
    }
}
