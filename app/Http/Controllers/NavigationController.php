<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp;


class NavigationController extends Controller
{
    public function search(Request $request)
    {
        $client = new GuzzleHttp\Client();
        $stream = "";
        $api_key = 'RGAPI-f042a29b-321e-4d3d-9142-e0116d770f34';
        
        if($request->accountServer == "EUW") {
            $stream = $client->get('https://euw1.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $request->username . '?api_key=' . $api_key);
        }

        $userdata = $stream->getBody()->getContents(); 
        $userid = json_decode($userdata)->id;
        $puuid = json_decode($userdata)->puuid;
      
        $stream = $client->get('https://euw1.api.riotgames.com/lol/league/v4/entries/by-summoner/' . $userid . '?api_key=' . $api_key);
        $ranks = json_decode($data = $stream->getBody()->getContents());

        $stream = $client->get('https://europe.api.riotgames.com/lol/match/v5/matches/by-puuid/'  . $puuid . '/ids?start=0&count=100&api_key=' . $api_key);
        dd(json_decode($data = $stream->getBody()->getContents()));
    }
}
