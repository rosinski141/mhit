<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp;
use File;
use Illuminate\Support\Facades\Http;
use App\Models\MatchHistory;
use App\Models\User;
use Auth;


class NavigationController extends Controller
{
    public function search(Request $request)
    {
        $client = new GuzzleHttp\Client();
        $api_key = env("API_KEY");
        $league_patch = env("LEAGUE_PATCH");
        // Intializing necessary variables within one line to save line space
        $solo_tier = $stream = $flex_tier = $emblem_path =  $flex_emblem_path =  $flex_rank =  $solo_rank =  "";
        $start_count = 0;
        $region =  $platform = $primary_rune =  $secondary_rune =  $runes = "";
        
        $runesReforgedJson = $client->get('http://ddragon.leagueoflegends.com/cdn/' . $league_patch . '/data/en_US/runesReforged.json');
        $runesDecoded = json_decode($runesReforgedJson->getBody()->getContents());

        $summonerJson = $client->get('http://ddragon.leagueoflegends.com/cdn/' . $league_patch . '/data/en_US/summoner.json');
        $summonersDecoded =  json_decode($summonerJson->getBody()->getContents());

       

        $account_server = $request->accountServer;
    
        switch($account_server) {
            case "EUW":
                $platform = "euw1";
                $region = "europe";
                break;
            case "NA":
                $platform = "na1";
                $region = "americas";
                break;
            case "OCE":
                $platform = "oc1";
                $region = "sea";
                break;
            case "BR":
                $platform = "br1";
                $region = "americas";
                break;
            case "KR":
                $platform = "kr";
                $region = "asia";
                break;
        }
    
        if($platform && $region) {
            $stream = $client->get('https://' . $platform . '.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $request->username . '?api_key=' . $api_key);
        
            
            $userdata = $stream->getBody()->getContents(); 
            
            $userid = json_decode($userdata)->id;
            $puuid = json_decode($userdata)->puuid;
            $user = json_decode($userdata);
            $stream = $client->get('https://' . $platform . '.api.riotgames.com/lol/league/v4/entries/by-summoner/' . $userid . '?api_key=' . $api_key);
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
            
            
            // Fetching from MongoDB
            $matchHist = MatchHistory::where('match.metadata.participants', '=', $puuid)
                ->orderBy('match.info.gameCreation', 'desc')
                ->get();
            
    
            $match_data = [];

            foreach($matchHist as $match) {
                $max_damage_dealt = 0;
                $max_damage_taken = 0;
                $max_damage_mitigated = 0;
    
                $users_damage_dealt = 0;
                $users_damage_taken = 0;
                $users_damage_mitigated = 0;

                $user_time_spent_alive = 0;
                $user_cc_time = 0;

                $longest_time_spent_alive = 0;
                $highest_cc_time = 0;

                $total_pings = 0;
                $users_pings = 0;

                // Converting to object instead of nested arrays
                $data = json_decode(json_encode($match->match), FALSE);
            
              
             
                foreach($data->info->participants as $participant) {
                    // Damage Dealt
                    if($participant->totalDamageDealtToChampions > $max_damage_dealt) {
                        $max_damage_dealt = $participant->totalDamageDealtToChampions;
                    }

                    // Damage mitigated
                    if($participant->damageSelfMitigated > $max_damage_mitigated) {
                        $max_damage_mitigated = $participant->damageSelfMitigated;
                    }
                    // Damage Taken
                    if($participant->totalDamageTaken > $max_damage_taken) {
                        $max_damage_taken = $participant->totalDamageTaken;
                    }
                    // CC time
                    if($participant->timeCCingOthers > $highest_cc_time) {
                        $highest_cc_time = $participant->timeCCingOthers;
                    }

                    // Longest time alive 
                    if($participant->longestTimeSpentLiving > $longest_time_spent_alive) {
                        $longest_time_spent_alive = $participant->longestTimeSpentLiving;
                    }



                    $total_pings += $participant->allInPings + $participant->assistMePings + $participant->baitPings + $participant->basicPings + $participant->commandPings + $participant->dangerPings + 
                    $participant->enemyMissingPings +  $participant->enemyVisionPings +  $participant->getBackPings +  $participant->holdPings + $participant->needVisionPings + $participant->onMyWayPings + 
                    $participant->pushPings + $participant->visionClearedPings;

                   
                    if($user->name == $participant->summonerName) {
                        $runes = $participant->perks;
                        $primary_rune_id = $runes->styles[0]->selections[0]->perk;
                      
                        foreach($runesDecoded as $rune) {
                            if($runes->styles[0]->style == $rune->id) { 
                                foreach($rune->slots[0]->runes as $major_rune) {
                                    if($major_rune->id == $primary_rune_id) {
                                        $primary_rune = $major_rune;
                                    }
                                }
                            } 
                            if($runes->styles[1]->style == $rune->id) {  
                                $secondary_rune = $rune;
                            }
                        }
                        $data->info->primary_rune = $primary_rune;
                        $data->info->secondary_rune = $secondary_rune;

                        foreach($summonersDecoded->data as $summoner) {
                            if($summoner->key == $participant->summoner1Id) {
                                $data->info->primary_summoner = $summoner->image->full;
                            }
                            if($summoner->key == $participant->summoner2Id) {
                                $data->info->secondary_summoner = $summoner->image->full;
                            }
                        }

                        $users_damage_dealt =  $participant->totalDamageDealtToChampions;
                        $users_damage_taken = $participant->totalDamageTaken;
                        $users_damage_mitigated =  $participant->damageSelfMitigated;
                        $user_time_spent_alive = $participant->longestTimeSpentLiving;
                        $user_cc_time = $participant->timeCCingOthers;
                        $user_role = $participant->individualPosition;
                        // Pings 
                        $users_pings = $participant->allInPings + $participant->assistMePings + $participant->baitPings + $participant->basicPings + $participant->commandPings + $participant->dangerPings + 
                        $participant->enemyMissingPings +  $participant->enemyVisionPings +  $participant->getBackPings +  $participant->holdPings + $participant->needVisionPings + $participant->onMyWayPings + 
                        $participant->pushPings + $participant->visionClearedPings;

                        $minions_killed = $participant->neutralMinionsKilled + $participant->totalMinionsKilled;
                        
                    }

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

                // Calculating precentage decrease for each instance of damage dealt, taken and mitigated
                $data->info->damage_dealt_precentage = 100 - (($max_damage_dealt - $users_damage_dealt) / $max_damage_dealt * 100);
                $data->info->damage_mitigated_precentage = 100 - (($max_damage_mitigated - $users_damage_mitigated) /  $max_damage_mitigated * 100);
                $data->info->damage_taken_precentage = 100 - (($max_damage_taken - $users_damage_taken) / $max_damage_taken * 100);
                $data->info->minions_per_min =  round($minions_killed / ($data->info->gameDuration / 60),1);
                $data->info->game_length = gmdate("i:s",$data->info->gameDuration);

                if ($users_pings < $total_pings / 10) {
                    $data->info->feedback[] = 'Low Communication';
                } else {
                    $data->info->feedback[] = 'Good Communication';
                }
           
                if($user_role != "JUNGLE" && $user_role != "UTILITY") {
                    if ($data->info->minions_per_min < 5.5) {
                        $data->info->feedback[] = 'Low CS Per Min';
                    } elseif ($data->info->minions_per_min > 7) {
                        $data->info->feedback[] = 'Great CS per Min';
                    }
                }
               

                if($user_cc_time == $highest_cc_time) {
                    $data->info->feedback[] = 'Crowd Control King';
                }
            
                if($user_time_spent_alive == $longest_time_spent_alive) {
                    $data->info->feedback[] = 'Unkillable!';
                }

                // Checking if game is less then 5 min 
                if($data->info->gameDuration < 300) {
                    $data->info->remake = true;
                } else {
                    $data->info->remake = false;
                }
                $match_data[] = $data->info; 
            
            }
        }
        return view('player.search', compact("match_data", "ranks", "emblem_path", "user", "solo_rank", "flex_rank", "flex_emblem_path", "league_patch", "account_server", "runes"));
    }

    public function update(Request $request) {

        $client = new GuzzleHttp\Client();
        $api_key = env("API_KEY");
        $start_count = 0;
        $platform = "";
        $reqion = "";
        $account_server = $request->accountServer;
    
        switch($account_server) {
            case "EUW":
                $platform = "euw1";
                $region = "europe";
                break;
            case "NA":
                $platform = "na1";
                $region = "americas";
                break;
            case "OCE":
                $platform = "oc1";
                $region = "sea";
                break;
            case "BR":
                $platform = "br1";
                $region = "americas";
                break;
            case "KR":
                $platform = "kr";
                $region = "asia";
                break;
        }
    

        $stream = $client->get('https://' . $platform . '.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $request->username . '?api_key=' . $api_key);
        
            
        $userdata = $stream->getBody()->getContents(); 
        $puuid = json_decode($userdata)->puuid;

        $stream = $client->get('https://' . $region . '.api.riotgames.com/lol/match/v5/matches/by-puuid/'  . $puuid . '/ids?start='.  $start_count . '&count=10&api_key=' . $api_key);
        $matches = json_decode($data = $stream->getBody()->getContents());
      
        foreach($matches as $match) {
            $stream =  $client->get('https://' . $region . '.api.riotgames.com/lol/match/v5/matches/'  . $match . '?api_key=' . $api_key);
            $data = json_decode($stream->getBody()->getContents());     
            if(! MatchHistory::where('_id', '=', $data->metadata->matchId)->exists()) {
                $match_history = new MatchHistory();
                $match_history->_id = $data->metadata->matchId;
                $match_history->match = $data;
                $match_history->save();
            }
           
        }
        return $this->search($request);

    }

    public function link_account(Request $request) {
        $user = Auth::user();
        $user->league_username = $request->username; 
        $user->update();

        return redirect()->back()->with('success', 'Succesfully claimed the account!');   
    }
    

}
