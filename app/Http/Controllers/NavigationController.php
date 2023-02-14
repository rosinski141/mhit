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
        $api_key = env("API_KEY");
        $league_patch = env("LEAGUE_PATCH");
        $solo_tier = "";
        $flex_tier = "";
        $emblem_path = "";
        $flex_emblem_path = "";
        $flex_rank = "";
        $solo_rank = "";
        $start_count = 0;

        if($request->accountServer == "EUW") {
            $stream = $client->get('https://euw1.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $request->username . '?api_key=' . $api_key);
        
            
            $userdata = $stream->getBody()->getContents(); 
            
            $userid = json_decode($userdata)->id;
            $puuid = json_decode($userdata)->puuid;
            $user = json_decode($userdata);
            $stream = $client->get('https://euw1.api.riotgames.com/lol/league/v4/entries/by-summoner/' . $userid . '?api_key=' . $api_key);
            $ranks = json_decode($data = $stream->getBody()->getContents());
        
            $emblems_array = File::files(public_path() . "/ranked-emblems/");
            foreach($ranks as $rank) {
                if($rank->queueType == "RANKED_SOLO_5x5") {
                    $solo_tier = strtolower($rank->tier);
                    $solo_rank = $rank;
                } else {
                    $flex_tier = strtolower($rank->tier);
                    $flex_rank = $rank;
                }
            }
        
            foreach($emblems_array as $emblem) {
                $current_filename = strtolower($emblem->getFilename());
                $stripped_filename = substr($current_filename, strpos($current_filename, "_") + 1);
                $stripped_filename = str_replace(".png", "", $stripped_filename);
                if($solo_tier == $stripped_filename) {
                    $emblem_path = $emblem->getRealPath();
                }
                if($flex_tier == $stripped_filename) {
                    $flex_emblem_path = $emblem->getRealPath();
                }
            }
        
            $emblem_path = "." . str_replace(public_path(), "", $emblem_path);
            $flex_emblem_path = "." . str_replace(public_path(), "", $flex_emblem_path);

            $stream = $client->get('https://europe.api.riotgames.com/lol/match/v5/matches/by-puuid/'  . $puuid . '/ids?start='.  $start_count . '&count=10&api_key=' . $api_key);
            $matches = json_decode($data = $stream->getBody()->getContents());
            
            $match_data = [];
            foreach($matches as $match) {
                $stream =  $client->get('https://europe.api.riotgames.com/lol/match/v5/matches/'  . $match . '?api_key=' . $api_key);

                
                $data = json_decode($stream->getBody()->getContents());     
            
                foreach($data->info->participants as $participant) {
                    // Fixing champion name so they appear on data dragon link
                    if($participant->championName == "FiddleSticks") {
                        $participant->championName = "Fiddlesticks";
                    }
                    $items[] = $participant->item0;
                    $items[] = $participant->item1;
                    $items[] = $participant->item2;
                    $items[] = $participant->item3;
                    $items[] = $participant->item4;
                    $items[] = $participant->item5;
                    $items[] = $participant->item6;
                    
                    $participant->items = $items;
                    $items = [];
                }      

                $match_data[] = $data->info; 
            
            }
        }
        return view('player.search', compact("match_data", "ranks", "emblem_path", "user", "solo_rank", "flex_rank", "flex_emblem_path", "league_patch", "start_count"));
    }

    public function load_more() {
        //set start to the variable start_count in query for find all matches
        // fix up code so its all atomic
        //return information to page 
    }
}
