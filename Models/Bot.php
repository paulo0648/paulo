<?php

namespace Modules\Flowiseai\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasConfig;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bot extends Model
{
    use HasConfig;
    use SoftDeletes;

    protected $modelName="Modules\Flowiseai\Models\Bot";

    protected $table = 'flowisebots';
    public $guarded = [];
}
