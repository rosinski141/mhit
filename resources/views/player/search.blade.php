@extends('layouts.app')

@push('other-scripts')
    <link href="{{ asset('css/search.css') }}" rel="stylesheet">
    <script>

        function hideNonSolo() {
            var flex = document.getElementsByClassName("440");

            for(var i = 0; i < flex.length; i++) {
                flex[i].style.display = "none";
                flex[i].style.marginBottom = "0px";
            }

            var solo = document.getElementsByClassName("420");

            for(var i = 0; i < solo.length; i++) {
                solo[i].style.display = "block";
                solo[i].style.marginBottom = "25px";
            }


            var aram = document.getElementsByClassName("450");

            for(var i = 0; i < aram.length; i++) {
                aram[i].style.display = "none";
                aram[i].style.marginBottom = "0";
            }
        }

        function hideNonFlex() {

            var flex = document.getElementsByClassName("440");

            for(var i = 0; i < flex.length; i++) {
                flex[i].style.display = "block";
                flex[i].style.marginBottom = "25px";
            }

            var solo = document.getElementsByClassName("420");

            for(var i = 0; i < solo.length; i++) {
                solo[i].style.display = "none";
                solo[i].style.marginBottom = "0";
            }

            var aram = document.getElementsByClassName("450");

            for(var i = 0; i < aram.length; i++) {
                aram[i].style.display = "none";
                aram[i].style.marginBottom = "0";
            }
        }

        function showAll() {

            var card = document.getElementsByClassName("match");

            for(var i = 0; i < card.length; i++) {
                card[i].style.display = "block";
                card[i].style.marginBottom = "25px";
            }

        }
        
    </script>
@endpush

@section('content')
      

    <div class="row" style="margin-bottom:10px">
        <div class="col-2">
        </div>
        <div class="col-1">
            <img style="width:120px;height:120px"class="rounded" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/profileicon/{{$user->profileIconId}}.png"> </img>
        </div>
        <div class="col-lg">
            @auth('web')
                <a href="{{ route('link_account', array('accountServer' => $account_server, 'username' => $user->name))}}">
                    Claim Account <img style="width:25px;height:25px;display:inline-block"src="link.png"></img>
                </a>
            @endauth
            <h2>{{$user->name}}</h2>
            <a class="btn btn-primary" href="{{ route('update', array('accountServer' => $account_server, 'username' => $user->name))}}">Update</a>
        </div>

    </div>
   
    <div class="row">
        <div class="col-1">
        </div>
        <div class="col-2 championRanks">
                <div class="card border"> 
                    <h2 class="card-header"> Solo Duo </h2>
                    @if($emblems->solo_rank != "")
                        <div class="row">
                            <hr>
                            <div class="col-4">
                                <img class="emblems" src="{{$emblems->emblem_path}}">
                            </div>
                            <div class="col-sm">
                                <b> {{$emblems->solo_rank->tier}} {{$emblems->solo_rank->rank}} </b> <br>
                                <b> {{$emblems->solo_rank->leaguePoints}} LP</b> <br>
                                <b> Wins: {{$emblems->solo_rank->wins}} </b> <br>
                                <b> Losses: {{$emblems->solo_rank->losses}} </b> <br>
                                <b> Win rate: {{ round((intval($emblems->solo_rank->wins) / (intval($emblems->solo_rank->wins) + intval($emblems->solo_rank->losses))) * 100, 0)}}%</b>
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            <b> Unranked </b>
                        </div>
                    @endif
                </div>
                <br>
                <div class="card border"> 
                    <h2 class="card-header"> Ranked Flex </h2>
                    @if($emblems->flex_rank != "")
                        <div class="row">
                            <hr>
                            <div class="col-4">
                                <img class="emblems" src="{{$emblems->flex_emblem_path}}">
                            </div>
                            <div class="col-sm">
                                <b> {{$emblems->flex_rank->tier}} {{$emblems->flex_rank->rank}} </b> <br>
                                <b> {{$emblems->flex_rank->leaguePoints}} LP</b> <br>
                                <b> Wins: {{$emblems->flex_rank->wins}} </b> <br>
                                <b> Losses: {{$emblems->flex_rank->losses}} </b> <br>
                                <b> Win rate: {{ round((intval($emblems->flex_rank->wins) / (intval($emblems->flex_rank->wins) + intval($emblems->flex_rank->losses))) * 100, 0)}}%</b>
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            <b> Unranked </b>
                        </div>
                    @endif
                </div>
                <br>
                <div class="card border"> 
                    <h2 class="card-header"> Champions Played </h2>
                    <div class="row">
                        @foreach($champions as $champion)
                            <hr>
                            <img style="width:20%;height:14%;padding:0px;display:inline-block;margin-left:5%" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$champion['name']}}.png">
                            <div style="display:inline-block;width:fit-content;height:fit-content">
                                <b> Win rate: {{ round((intval($champion['wins']) / (intval($champion['wins']) + intval($champion['losses']))) * 100, 0)}}%</b><br>
                                <b> Games Played: {{$champion['games_played']}}</b>
                            </div>
                        @endforeach
                    </div>
                </div>
        
        </div>
        <div class="col-md">
            <div class="justify-content-center"> 
            <div class="filters" style="margin-bottom:15px"> 
                <btn id='solo' onclick="hideNonSolo()" class="btn btn-secondary" style='margin-right:5px'> Ranked Solo/Duo </btn>
                <btn id='flex' onclick="hideNonFlex()" class="btn btn-secondary" style='margin-right:5px'> Ranked Flex </btn>
                <btn id='flex' onclick="showAll()" class="btn btn-secondary"> Show All </btn>
            </div>
                @foreach($match_data as $match) 
                    @foreach($match->participants as $participant)
                        <!-- Getting the current user from each match-->
                        @if($participant->summonerName == $user->name)

                            <!-- Setting each model colour depending on outcome of match -->
                            @if($match->remake == "true") 
                                <div style="background-color:lightgray;max-width:100%;margin-bottom:25px" class="card match {{$match->queueId}}">
                            @elseif($participant->win == "true")
                                <div style="background-color:lightblue;max-width:100%;margin-bottom:25px" class="card match {{$match->queueId}}">
                            @else
                                <div style="background-color:#FFCCCB;max-width:100%;margin-bottom:25px" class="card match {{$match->queueId}}">
                            @endif
                            <div class="card-header">
                                <!-- Game Mode Title-->
                                @if($match->queueId == '440')
                                    <b class="card-title">Ranked Flex</b>
                                @elseif($match->queueId == '420')
                                    <b class="card-title">Ranked Solo/Duo</b>
                                @else
                                    <b class="card-title">{{$match->gameMode}}</b>
                                @endif
                                
                            </div>
                                <div class="row">
                                    <div class="col-sm-2">
                                        <img style="width:38%;height:45%;max-width:120px;max-height:120px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                        <b class="kda">{{$participant->kills}} / <b style="color:red">{{$participant->deaths}} </b> / {{$participant->assists}}</b>
                                        <div class="description"> 
                                            <hr style="height:2%;border:none;color:#333;background-color:#333;">
                                            <b style="margin-left:5%">Game Length: {{$match->game_length}}</b><br>
                                            <b style="margin-left:5%">Cs/min: {{$match->minions_per_min}}</b>
                                        </div>

                                    </div>
                                    <div class="col-sm-1">
                                        <img style="width:50%;height:25%;margin-bottom:5%;max-width:60px;max-height:60px" class="rounded" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/spell/{{$match->summoners->primary_summoner}}">
                                        <img style="width:50%;height:25%;margin-bottom:5%;max-width:60px;max-height:60px" class="rounded" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/spell/{{$match->summoners->secondary_summoner}}"> <br>
                                        <img style="width:90%;height:50%;max-width:120px;max-height:120px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/img/{{$match->runes->primary_rune->icon}}">
                                        <img style="width:40%;height:20%;max-width:40px;max-height:40px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/img/{{$match->runes->secondary_rune->icon}}">
                                    </div>
                                    <div class="col-sm" style="margin:none">
                                        @foreach($participant->items as $item)
                                            @if($item == 0) 
                                                <span class="placeholder item rounded"></span>
                                            @else 
                                                <img class="item" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/item/{{$item}}.png"> 
                                            @endif
                                        @endforeach
                                        <br>
                                        <div class="vision" >
                                            Vision Score: {{$participant->visionScore}} <br>
                                            Control Wards: {{$participant->visionWardsBoughtInGame}} <br>
                                            Wards Killed: {{$participant->wardsKilled}} 
                                        </div>
                                    </div> 
                                        <div class="col-2" style="margin:none">
                                        @foreach($match->participants as $participant)
                                            <div class="row-sm participant">
                                                @if($participant->teamId == 100)
                                                    <div style="display:inline-block">
                                                        <a class="participant" href="{{route('search', array('accountServer' => $account_server, 'username' => $participant->summonerName))}}">
                                                            <img class="players" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                                            {{$participant->summonerName}}  
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="col-2">
                                        @foreach($match->participants as $participant)
                                            <div class="row-sm participant">
                                                @if($participant->teamId == 200)
                                                    <div style="display:inline-block">
                                                      
                                                        <a class="participant" href="{{route('search', array('accountServer' => $account_server, 'username' => $participant->summonerName))}}">
                                                            <img class="players" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                                            {{$participant->summonerName}} 
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                        @endforeach
                                    </div>
                                    <div class="col-sm-2" style="margin-right:10px">

                                        <b> Damage Dealt </b>
                                        <div class="progress">
                                            <div class="fill" style="width:{{$match->damage_dealt_precentage}}%;"></div>
                                        </div> 

                                        <b> Damage Taken </b>
                                        <div class="progress">
                                            <div class="fill" style="width:{{$match->damage_taken_precentage}}%;"></div>
                                        </div>

                                        <b> Damage Mitigated </b>
                                        <div class="progress">
                                            <div class="fill" style="width:{{$match->damage_mitigated_precentage}}%;"></div>
                                        </div> 
                                    </div>
                                    @if($match->remake != true) 
                                    <div style="margin-left:1%;margin-top:1%;margin-bottom:1%;" class="row">
                                        @foreach($match->feedback as $feedback) 
                                            <div class="feedback">
                                                {{$feedback}}
                                            </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                
                            </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
        <div class="col-1">
        </div>
    </div>


@endsection