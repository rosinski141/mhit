<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp;
use File;
use Illuminate\Support\Facades\Http;
use App\Models\MatchHistory;
use App\Models\User;
use App\Models\Feedback;
use Auth;
use StdClass;


class AccountController extends Controller
{  
    private $user; 
    private $cleint;
    private $api_key;
    private $league_path;
    private $server_details;
    private $userdata; 

    public function __construct()
    {
        $this->middleware('auth');
    }

    private function init_variables() {
        $this->client = new GuzzleHttp\Client(['http_errors' => false]);
        $this->api_key = env("API_KEY");
        $this->league_patch = env("LEAGUE_PATCH");
        $this->user = Auth::user();
        $server_details = $this->get_platform($this->user->league_server);

        $stream = $this->client->get('https://' . $server_details->platform . '.api.riotgames.com/lol/summoner/v4/summoners/by-name/' . $this->user->league_username . '?api_key=' . $this->api_key);
        $this->userdata =  json_decode($stream->getBody()->getContents()); 

    }

    public function get_details() {
        $this->init_variables();
        if(!$this->user->league_username) {
            return redirect()->back()->with('error', 'No account has been claimed to this user');
        }

        $feedback = Feedback::where('user_id', '=', $this->user->id)->get();

        $profileIcon = $this->userdata->profileIconId;
        $name = $this->userdata->name;
        $level = $this->userdata->summonerLevel;
        $league_patch = $this->league_patch;

        $match_count = $feedback[0]->matches_analyzed;
       
        return view('player.account', compact("profileIcon", "name", "feedback", "level", "league_patch", "match_count"));
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

    

    public function update_feedback() {
        $this->init_variables();
        // Delete all previous records
        Feedback::where('user_id', '=', $this->user->id)->delete();

        $matchHist = MatchHistory::where('match.metadata.participants', '=', $this->userdata->puuid)
        ->orderBy('match.info.gameCreation', 'desc')
        ->get();
    
        $match_count = count($matchHist);

        $stats = new stdClass();
        $stats->cs = new stdClass();
        $stats->cs->name = "creep score";
        $stats->cs->value = 0;
        $stats->damage = new stdClass();
        $stats->damage->name = "damage dealt";
        $stats->damage->value = 0;
        $stats->vision = new stdClass();
        $stats->vision->name = "vision score";
        $stats->vision->value = 0;
        $stats->gold = new stdClass();
        $stats->gold->name = "gold earned";
        $stats->gold->value = 0;
        $stats->control = new stdClass();
        $stats->control->name = "control wards";
        $stats->control->value = 0;
        $stats->surrendered = new stdClass();
        $stats->surrendered->name = "surrendered";
        $stats->surrendered->value = 0;

        foreach ($matchHist as $match) {
            $player = "";
            $opponent = "";
            $data = json_decode(json_encode($match->match), FALSE);
            foreach ($data->info->participants as $participant) {
                if ($this->user->league_username == $participant->summonerName) {
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
                    $stats->surrendered->value += 1;
                } else {
                    $stats->surrendered->value -= 1;
                }
            }

            if ($player->goldEarned > $opponent->goldEarned) {
                $stats->gold->value += 1;
            } else {
                $stats->gold->value -= 1;
            }

            if (($player->neutralMinionsKilled + $player->totalMinionsKilled) > ($opponent->neutralMinionsKilled + $opponent->totalMinionsKilled)) {
                $stats->cs->value += 1;
            } else {
                $stats->cs->value -= 1;
            }

            if ($player->visionScore > $opponent->visionScore) {
                $stats->vision->value += 1;
            } else {
                $stats->vision->value -= 1;
            }

            if ($player->visionWardsBoughtInGame > $opponent->visionWardsBoughtInGame) {
                $stats->control->value += 1;
            } else {
                $stats->control->value -= 1;
            }

            if ($player->totalDamageDealtToChampions > $opponent->totalDamageDealtToChampions) {
                $stats->damage->value += 1;
            } else {
                $stats->damage->value -= 1;
            }
        }


        foreach($stats as $stat) {
            $feedback = new Feedback();
            $feedback->user_id = $this->user->id;
            $feedback->category = $stat->name; 
            $feedback->matches_analyzed = $match_count;
            if($stat->value >= 0) {
                if($stat->name == 'surrendered') {
                    $feedback->text = "You have surrendered " . $stat->value . " times more than your lane opponent. A match could never truely be over until the nexus explodes, the tides can always turn in your favour";
                    $feedback->video_link = "https://www.youtube.com/embed/8SioWlCeO64";
                }
                if($stat->name == 'gold earned') {
                    $feedback->text = "You have earned more gold than your lane opponent by over " . $stat->value . " times. Great Job!";
                } 
                if($stat->name == 'vision score') {
                    $feedback->text = "Throughout your games you have more vision score than your lane opponent by over " . $stat->value . " times. Your vision has given your teammates useful insight.";
                }
                if($stat->name == 'control wards') {
                    $feedback->text = $stat->value . " times you have purchased more control wards then your opponent. Good job!";;
                }
                if($stat->name == 'damage dealt') {
                    $feedback->text = "You have outdamaged your opponent in over " . $stat->value . " matches. Keep it coming!";
                }
                if($stat->name == 'creep score') {
                    $feedback->text = "You had more cs than your opponent in " . $stat->value . " matches. Great farming!";
                }

            } else {
                if($stat->name == 'surrendered') { 
                    $feedback->text = "You have surrendered " . abs($stat->value) . " times less than your lane opponent. Your resilience to never give up wins you games!";     
                }
                if($stat->name == 'gold earned') {
                    $feedback->text = "You have earned less gold than your lane opponent by over " . abs($stat->value) . " times. Make sure to participate in teamfights, get gold from tower plates and objectives.";
                    $feedback->video_link = "https://www.youtube.com/embed/XqX4XY6lQ7k";     
                } 
                if($stat->name == 'vision score') {
                    $feedback->text = "Throughout your games you have less vision score than your lane opponent by over " . abs($stat->value) . " times. Make sure to place wards and get deep vision.";
                    $feedback->video_link = "https://www.youtube.com/embed/RKrZPFRCpYU";
                }
                if($stat->name == 'control wards') {
                    $feedback->text = abs($stat->value) . " times you have purchased less control wards then your opponent. Securing high priority areas with control wards is important for objectives and to catch players off-gaurd.";
                    $feedback->video_link = "https://www.youtube.com/embed/6cXqzH2vMH8";
                }
                if($stat->name == 'damage dealt') {
                    $feedback->text = "You have dealt less damage than your opponent in over " . abs($stat->value) . " matches. Play more aggressively in order to secure more kills.";
                    $feedback->video_link = "https://www.youtube.com/embed/94j-1g5V-LQ";
                }
                if($stat->name == 'creep score') {
                    $feedback->text = "You had less cs than your opponent in " . abs($stat->value) . " matches. Practice last-hitting in practice tool to get better cs/m or optimise your jungle pathing.";
                    $feedback->video_link = "https://www.youtube.com/embed/jOSyf1NQspo";
                }
            }
          
            $feedback->save();
        }

        return $this->get_details();
    }


}
