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

class AccountController extends Controller
{  
    public function get_details() {

        $user = Auth::user();
        $client = new GuzzleHttp\Client();
        $api_key = env("API_KEY");
        $league_patch = env("LEAGUE_PATCH");

        if(!$user->league_username) {
            return redirect()->back()->with('error', 'No account has been claimed to this user');
        }
    
        $server_details = $this->get_platform($user->league_server);

        $stream = $client->get('https://' . $server_details->platform . '.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $user->league_username . '?api_key=' . $api_key);
            
        $userdata = $stream->getBody()->getContents(); 
        $puuid = json_decode($userdata)->puuid;

        $matchHist = MatchHistory::where('match.metadata.participants', '=', $puuid)
        ->orderBy('match.info.gameCreation', 'desc')
        ->get();
    
        $match_count = count($matchHist);

        $stats = new stdClass();
        $stats->more_cs = 0;
        $stats->less_cs = 0;
        $stats->more_damage = 0;
        $stats->less_damage = 0;
        $stats->more_vision = 0;
        $stats->less_vision = 0;
        $stats->more_gold = 0;
        $stats->less_gold = 0;
        $stats->more_control = 0;
        $stats->less_control = 0;
        $stats->surrendered_more = 0;
        $stats->surrendered_less = 0;

        foreach ($matchHist as $match) {
            $player = "";
            $opponent = "";
            $data = json_decode(json_encode($match->match), FALSE);
            foreach ($data->info->participants as $participant) {
                if ($user->league_username == $participant->summonerName) {
                    $player = $participant;
                }    
            }

            foreach ($data->info->participants as $participant) {
                if ($player->puuid != $participant->puuid) {
                    if ($player->individualPosition == $participant->individualPosition) {
                        $opponent = $participant;
                    }
                }
            }

            if ($player->gameEndedInSurrender == true) { 
                if ($player->win == false) {
                    $stats->surrendered_more += 1;
                } else {
                    $stats->surrendered_less += 1;
                }
            }

            if ($player->goldEarned > $opponent->goldEarned) {
                $stats->more_gold += 1;
            } else {
                $stats->less_gold += 1;
            }

            if (($player->neutralMinionsKilled + $player->totalMinionsKilled) > ($opponent->neutralMinionsKilled + $opponent->totalMinionsKilled)) {
                $stats->more_cs += 1;
            } else {
                $stats->less_cs += 1;
            }

            if ($player->visionScore > $opponent->visionScore) {
                $stats->more_vision += 1;
            } else {
                $stats->less_vision += 1;
            }

            if ($player->visionWardsBoughtInGame > $opponent->visionWardsBoughtInGame) {
                $stats->more_control += 1;
            } else {
                $stats->less_control += 1;
            }

            if ($player->totalDamageDealtToChampions > $opponent->totalDamageDealtToChampions) {
                $stats->more_damage += 1;
            } else {
                $stats->less_damage += 1;
            }


        }
        $profileIcon = $player->profileIcon;
        $name = $player->summonerName;
        $level = $player->summonerLevel;

        $stat_text = $this->get_stat_text($stats);
       

        return view('player.account', compact("profileIcon", "name", "stat_text", "level", "league_patch", "match_count"));
    
    }

    private function get_platform($server) {
        $server_details = new StdClass();
        
        switch($server) {
            case "EUW":
                $server_details->platform = "euw1";
                break;
            case "NA":
                $server_details->platform = "na1";
                break;
            case "OCE":
                $server_details->platform = "oc1";
                break;
            case "BR":
                $server_details->platform = "br1";
                break;
            case "KR":
                $server_details->platform = "kr";
                break;
        }

        return $server_details; 
    }

    private function get_stat_text($stats) {
        $stat_text = new stdClass();
    

        if ($stats->surrendered_more > $stats->surrendered_less) {
            $stat_text->surrender = "You have surrendered " . ($stats->surrendered_more  - $stats->surrendered_less) . " times more than your lane opponent. A match could never truely be over until the nexus explodes, the tides can always turn in your favour.";
        } else {
            $stat_text->surrender = "You have surrendered " . ($stats->surrendered_less - $stats->surrendered_more) . " times less than your lane opponent. Your resilience to never give up wins you games!";
        }

        if ($stats->more_gold > $stats->less_gold) {
            $stat_text->gold = "You have earned more gold than your lane opponent by over " . ($stats->more_gold  - $stats->less_gold) . " times. Great Job!";
        } else {
            $stat_text->gold = "You have earned less gold than your lane opponent by over " . ($stats->less_gold  - $stats->more_gold) . " times. Make sure to participate in teamfights, get gold from tower plates and objectives.";
        }

        if ($stats->more_vision > $stats->less_vision) {
            $stat_text->vision = "Throughout your games you have more vision score than your lane opponent by over " . ($stats->more_vision  - $stats->less_vision) . " times. Your vision has given your teammates useful insight.";
        } else {
            $stat_text->vision = "Throughout your games you have less vision score than your lane opponent by over " . ($stats->less_vision  - $stats->more_vision) . " times. Make sure to place wards and get deep vision.";
        }

        if($stats->more_control > $stats->less_control) {
            $stat_text->control = ($stats->more_control  - $stats->less_control) . " times you have purchased more control wards then your opponent. Good job!";
        } else {
            $stat_text->control = ($stats->less_control  - $stats->more_control) . " times you have purchased less control wards then your opponent. Securing high priority areas with control wards is important for objectives and to catch players off-gaurd.";
        }

        if($stats->more_damage > $stats->less_damage) {
            $stat_text->damage = "You have outdamaged your opponent in over " . ($stats->more_damage  - $stats->less_damage) . " matches. Keep it coming!";
        } else {
            $stat_text->damage = "You have dealt less damage than your opponent in over " . ($stats->less_damage  - $stats->more_damage) . " matches. Play more aggressively in order to secure more kills.";
        }

        if($stats->more_cs > $stats->less_cs) {
            $stat_text->damage = "You had more cs than your opponent in " . ($stats->more_cs  - $stats->less_cs) . " matches. Great farming!";
        } else {
            $stat_text->damage = "You had less cs than your opponent in " . ($stats->less_cs  - $stats->more_cs) . " matches. Practice last-hitting in practice tool to get better cs/m or optimise your jungle pathing.";
        }
       
        return $stat_text;
    }
}