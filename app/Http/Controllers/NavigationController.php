<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp;
use File;


class NavigationController extends Controller
{
    public function search(Request $request)
    {
        $client = new GuzzleHttp\Client();
        $stream = "";
        $api_key = 'RGAPI-9b0828c8-571a-402c-817b-96e5f09df4d1';
        
        if($request->accountServer == "EUW") {
            $stream = $client->get('https://euw1.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $request->username . '?api_key=' . $api_key);
        }

        $userdata = $stream->getBody()->getContents(); 
        $userid = json_decode($userdata)->id;
        $puuid = json_decode($userdata)->puuid;
        $username = json_decode($userdata)->name;
        $stream = $client->get('https://euw1.api.riotgames.com/lol/league/v4/entries/by-summoner/' . $userid . '?api_key=' . $api_key);
        $ranks = json_decode($data = $stream->getBody()->getContents());
        $emblems_array = File::files(public_path() . "/ranked-emblems/");
        $tier = strtolower($ranks[1]->tier);
        //check if rank is solo duo or felx.
        $emblem_path = "";
        foreach($emblems_array as $emblem) {
            $current_filename = strtolower($emblem->getFilename());
            $stripped_filename = substr($current_filename, strpos($current_filename, "_") + 1);
            $stripped_filename = str_replace(".png", "", $stripped_filename);
            if($tier == $stripped_filename) {
                $emblem_path = $emblem->getRealPath();
            }
        }
        $emblem_path = "." . str_replace(public_path(), "", $emblem_path);
        $stream = $client->get('https://europe.api.riotgames.com/lol/match/v5/matches/by-puuid/'  . $puuid . '/ids?start=0&count=10&api_key=' . $api_key);
        $matches = json_decode($data = $stream->getBody()->getContents());
        
        $match_data = [];
        foreach($matches as $match) {
            $stream =  $client->get('https://europe.api.riotgames.com/lol/match/v5/matches/'  . $match . '?api_key=' . $api_key);

            
            $data = json_decode($stream->getBody()->getContents());
            foreach($data->info->participants as $participant) {
                if($participant->summonerName == $username) {
                    $match_data[] = $participant;
                }
            }
        
        }
       
        return view('player.search', compact("match_data", "ranks", "emblem_path", "username"));
    }
}
