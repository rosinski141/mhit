<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp;
use File;
use Illuminate\Support\Facades\Http;
use App\Models\MatchHistory;
use App\Models\User;
use Auth;
use StdClass;


class NavigationController extends Controller
{
 
    private $client;
    private $env;
    private $league_patch;
    private $runes_decoded;
    private $summoners_decoded;
    private $queues;

    private function initiate_variables() {
        $this->client = new GuzzleHttp\Client(['http_errors' => false]);
        $this->api_key = env("API_KEY");
        $this->league_patch = env("LEAGUE_PATCH");

        // Get data from json files
        $this->runes_decoded =  json_decode($this->client->get('http://ddragon.leagueoflegends.com/cdn/' . $this->league_patch . '/data/en_US/runesReforged.json')->getBody()->getContents());
        $this->summoners_decoded = json_decode($this->client->get('http://ddragon.leagueoflegends.com/cdn/' . $this->league_patch . '/data/en_US/summoner.json')->getBody()->getContents());
    }
    

    public function search(Request $request)
    {
        $this->initiate_variables();
      
        // Intializing necessary variables within one line to save line space
        $solo_tier = $stream = $flex_tier =  $flex_rank =  $solo_rank =  "";
        $start_count = 0;
        $primary_rune =  $secondary_rune =  $runes = "";
        

        $account_server = $request->accountServer;
    
        $server_details = $this->get_platform_and_region($account_server);
     
    
        if($server_details->platform && $server_details->region) {
            
            $stream = $this->client->get('https://' . $server_details->platform . '.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $request->username . '?api_key=' . $this->api_key);

            // Returning back if user can't be found
            if($stream->getStatusCode() == 404 ) {
                return redirect()->back()->with('error', 'Could not find an account with that username in that region!');
            }
           
            
            $userdata = json_decode($stream->getBody()->getContents()); 
            $user = $userdata;
         
            $stream = $this->client->get('https://' . $server_details->platform . '.api.riotgames.com/lol/league/v4/entries/by-summoner/' . $user->id . '?api_key=' . $this->api_key);
            $ranks = json_decode($data = $stream->getBody()->getContents());
        
        
            $emblems = $this->get_emblems($ranks);
            
            // Fetching from MongoDB
            $matchHist = MatchHistory::where('match.metadata.participants', '=', $user->puuid)
                ->orderBy('match.info.gameCreation', 'desc')
                ->get();
            
    
            $match_data = [];

            $champions =  array();


            foreach($matchHist as $match) {

                // Initiating variables for per match statistics
                $max_damage_dealt = 0;
                $max_damage_taken = 0;
                $max_damage_mitigated = 0;
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

                    
                    // Total number of pings
                    $total_pings += $participant->allInPings + $participant->assistMePings + $participant->baitPings + $participant->basicPings + $participant->commandPings + $participant->dangerPings + 
                    $participant->enemyMissingPings +  $participant->enemyVisionPings +  $participant->getBackPings +  $participant->holdPings + $participant->needVisionPings + $participant->onMyWayPings + 
                    $participant->pushPings + $participant->visionClearedPings;

                
                    if($user->name == $participant->summonerName) {
                        $player = $participant;

                        // Get runes
                        $data->info->runes = $this->get_runes($participant->perks);

                        // Get summoner spells
                        $data->info->summoners = $this->get_summoners($participant->summoner1Id, $participant->summoner2Id);

                        // Get champions played
                        $champions = $this->get_champions($champions, $participant->championName, $participant->win);
                       
                        
                        // Get users total pings
                        $users_pings = $participant->allInPings + $participant->assistMePings + $participant->baitPings + $participant->basicPings + $participant->commandPings + $participant->dangerPings + 
                        $participant->enemyMissingPings +  $participant->enemyVisionPings +  $participant->getBackPings +  $participant->holdPings + $participant->needVisionPings + $participant->onMyWayPings + 
                        $participant->pushPings + $participant->visionClearedPings;

                        // Get minions killed
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
                $data->info->damage_dealt_precentage = 100 - (($max_damage_dealt - $player->totalDamageDealtToChampions) / $max_damage_dealt * 100);
                $data->info->damage_mitigated_precentage = 100 - (($max_damage_mitigated - $player->damageSelfMitigated) /  $max_damage_mitigated * 100);
                $data->info->damage_taken_precentage = 100 - (($max_damage_taken - $player->totalDamageTaken) / $max_damage_taken * 100);
                
                $data->info->minions_per_min =  round($minions_killed / ($data->info->gameDuration / 60),1);
                $data->info->game_length = gmdate("i:s",$data->info->gameDuration);

                // Checking if user averages higher pings than the average 
                if ($users_pings < $total_pings / 10) {
                    $data->info->feedback[] = 'Low Communication';
                } else {
                    $data->info->feedback[] = 'Good Communication';
                }
                
                // Checking if user is a jungler or support as they don't recieve equal cs to laners
                if($player->individualPosition != "JUNGLE" && $player->individualPosition != "UTILITY") {
                    if ($data->info->minions_per_min < 5.5) {
                        $data->info->feedback[] = 'Low CS Per Min';
                    } elseif ($data->info->minions_per_min > 7) {
                        $data->info->feedback[] = 'Great CS per Min';
                    }
                }
                // Checking if user has highest crowd control score 
                if($player->timeCCingOthers == $highest_cc_time) {
                    $data->info->feedback[] = 'Crowd Control King';
                }
                // Checking if user died the least in the game
                if($player->longestTimeSpentLiving == $longest_time_spent_alive) {
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
        $league_patch = $this->league_patch;
    

        $key_values = array_column($champions, 'games_played'); 
        array_multisort($key_values, SORT_DESC, $champions);
       
        return view('player.search', compact("match_data", "ranks", "emblems", "user", "league_patch", "account_server", "runes", "champions"));
    }

    public function update(Request $request) {
        $this->initiate_variables();
        $start_count = 0;
        $account_server = $request->accountServer;
        // Region and Platform
        $server_details = $this->get_platform_and_region($account_server);
        // Fetch User details from API
        $stream = $this->client->get('https://' . $server_details->platform . '.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $request->username . '?api_key=' . $this->api_key);
            
        $user = json_decode($stream->getBody()->getContents()); 

        // Get lastest 10 Matches ID's from API
        $stream = $this->client->get('https://' . $server_details->region . '.api.riotgames.com/lol/match/v5/matches/by-puuid/'  . $user->puuid . '/ids?start='.  $start_count . '&count=10&api_key=' . $this->api_key);
        $matches = json_decode($data = $stream->getBody()->getContents());
      
        foreach($matches as $match) {
            // Only update New Records     
            if(! MatchHistory::where('_id', '=', $match)->exists()) {
                // fetch full match details 
                $stream =  $this->client->get('https://' . $server_details->region . '.api.riotgames.com/lol/match/v5/matches/'  . $match . '?api_key=' . $this->api_key);
                $data = json_decode($stream->getBody()->getContents());
                
                // Push to database
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
        $user->league_server = $request->accountServer;
        $user->update();

        return redirect()->back()->with('success', 'Succesfully claimed the account!');   
    }

    private function get_platform_and_region($server) {
        $server_details = new StdClass();
        
        switch($server) {
            case "EUW":
                $server_details->platform = "euw1";
                $server_details->region = "europe";
                break;
            case "NA":
                $server_details->platform = "na1";
                $server_details->region = "americas";
                break;
            case "OCE":
                $server_details->platform = "oc1";
                $server_details->region = "sea";
                break;
            case "BR":
                $server_details->platform = "br1";
                $server_details->region = "americas";
                break;
            case "KR":
                $server_details->platform = "kr";
                $server_details->region = "asia";
                break;
        }

        return $server_details; 
    }

    private function get_runes($participants_runes) {
        
        $runes = new StdClass();
        $primary_rune_id = $participants_runes->styles[0]->selections[0]->perk;

        foreach($this->runes_decoded as $rune) {
            if($participants_runes->styles[0]->style == $rune->id) { 
                foreach($rune->slots[0]->runes as $major_rune) {
                    if($major_rune->id == $primary_rune_id) {
                        $runes->primary_rune = $major_rune;
                    }
                }
            } 
            if($participants_runes->styles[1]->style == $rune->id) {  
                $runes->secondary_rune = $rune;
            }
        }
        
        return $runes;
    }

    private function get_summoners($summoner1Id, $summoner2Id) {

        $summoners = new StdClass();
        // Get summoner spell image from summoner json 
        foreach($this->summoners_decoded->data as $summoner) {
            if($summoner->key == $summoner1Id) {
                $summoners->primary_summoner = $summoner->image->full;
            }
            if($summoner->key == $summoner2Id) {
               $summoners->secondary_summoner = $summoner->image->full;
            }
        }

        return $summoners;
    }

    private function get_champions($champions, $champion_name, $win) {

        $champ_exits_flag = false;
        // Adding wins and losses to each champion
        foreach($champions as $champ) {
            if($champ['name'] == $champion_name) {

                $array_id  = array_search($champ, $champions);
            
                if($win == true) {
                    $champ['wins'] += 1;
                } else {
                    $champ['losses'] += 1;
                }
                $champ['games_played'] += 1;
                $champions[$array_id] = $champ;
                $champ_exits_flag = true;
            }                          
        }
        // If champion dosen't exist add them to champion array
        if($champ_exits_flag == false) {
            $current_champ = [];
            $current_champ['name'] = $champion_name;
            if($win == true) {
                $current_champ['wins'] = 1;
                $current_champ['losses'] = 0;
            } else {
                $current_champ['wins'] = 0;
                $current_champ['losses'] = 1;
            }
            $current_champ['games_played'] = 1;
            $champions[] = $current_champ;
        }

        return $champions;
    }

    private function get_emblems($ranks){
        
        $emblems = new StdClass();
        $emblems_array = File::files(public_path() . "/ranked-emblems/");
        $emblems->flex_tier = "";
        $emblems->solo_tier = "";
        $emblems->solo_rank = "";
        $emblems->flex_rank = "";
        $emblems->flex_emblem_path = "";
        $emblems->emblem_path = "";
        foreach($ranks as $rank) {
            if($rank->queueType == "RANKED_SOLO_5x5") {
                $emblems->solo_tier = strtolower($rank->tier);
                $emblems->solo_rank = $rank;
            } else {
                $emblems->flex_tier = strtolower($rank->tier);
                $emblems->flex_rank = $rank;
            }
        }
    
        foreach($emblems_array as $emblem) {
            $current_filename = strtolower($emblem->getFilename());
            $stripped_filename = substr($current_filename, strpos($current_filename, "_") + 1);
            $stripped_filename = str_replace(".png", "", $stripped_filename);
            if($emblems->solo_tier == $stripped_filename) {
                $emblems->emblem_path = $emblem->getRealPath();
            }
            if($emblems->flex_tier == $stripped_filename) {
                $emblems->flex_emblem_path = $emblem->getRealPath();
            }
        }

        $emblems->emblem_path = "." . str_replace(public_path(), "", $emblems->emblem_path);
        $emblems->flex_emblem_path = "." . str_replace(public_path(), "", $emblems->flex_emblem_path);    
        
        return $emblems;
    }

}
