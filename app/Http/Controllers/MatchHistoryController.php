<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MatchHistory;


class MatchHistoryController extends Controller
{
   public function show($match_id)
   {
    try {
        $mc = MatchHistory::latest()->paginate( 10 );
        dd($mc);

    } catch (Exception $e) {
        echo $e->getMessage();
    }
   }
}
