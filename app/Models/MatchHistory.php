<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class MatchHistory extends Model
{
    protected $connection = 'mongo';
    protected $collection = 'match_history';
    
}
