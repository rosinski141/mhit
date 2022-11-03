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
        $api_key = 'RGAPI-a776d72a-0905-4678-8dac-36830dd40f2e';
        
        if($request->accountServer == "EUW") {
            $stream = $client->get('https://euw1.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $request->username . '?api_key=' . $api_key);
        }

        $userdata = $stream->getBody()->getContents(); 
        $userid = json_decode($userdata)->id;
        $puuid = json_decode($userdata)->puuid;
      
        $stream = $client->get('https://euw1.api.riotgames.com/lol/league/v4/entries/by-summoner/' . $userid . '?api_key=' . $api_key);
        $ranks = json_decode($data = $stream->getBody()->getContents());

        $stream = $client->get('https://europe.api.riotgames.com/lol/match/v5/matches/by-puuid/'  . $puuid . '/ids?start=0&count=2&api_key=' . $api_key);
        $matches = json_decode($data = $stream->getBody()->getContents());
        
        $matchData = [];
        foreach($matches as $match) {
            $stream =  $client->get('https://europe.api.riotgames.com/lol/match/v5/matches/'  . $match . '?api_key=' . $api_key);

            $matchData[] = [
                json_decode($data = $stream->getBody()->getContents())
            ];
        }
    
        return view('player.search', compact("matchData"));
    }
}
