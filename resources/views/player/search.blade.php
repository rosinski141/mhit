@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/search.css') }}" rel="stylesheet">

    @if (\Session::has('success'))
    <div id="update_alert" class="alert alert-success">
        <b>{!! \Session::get('success') !!}</b>
        <a class="btn" style="position:absolute;top:0;right:0;" onclick="this.parentNode.style.display='none'" id="alert_close_btn">x</a>
    </div>
    @endif

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
        <div class="col-2">
                <div class="card border"> 
                    <h2 class="card-header"> Solo Duo </h2>
                    @if($solo_rank != "")
                        <div class="row">
                            <hr>
                            <div class="col-4">
                                <img style="width:100px;height:130px;display:inline-block" src="{{$emblem_path}}">
                            </div>
                            <div class="col-sm">
                                <b> {{$solo_rank->tier}} {{$solo_rank->rank}} </b> <br>
                                <b> {{$solo_rank->leaguePoints}} LP</b> <br>
                                <b> Wins: {{$solo_rank->wins}} </b> <br>
                                <b> Losses: {{$solo_rank->losses}} </b> <br>
                                <b> Win rate: {{ round((intval($solo_rank->wins) / (intval($solo_rank->wins) + intval($solo_rank->losses))) * 100, 0)}} %</b>
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
                    @if($flex_rank != "")
                        <div class="row">
                            <hr>
                            <div class="col-4">
                                <img style="width:100px;height:130px;display:inline-block"src="{{$flex_emblem_path}}">
                            </div>
                            <div class="col-sm">
                                <b> {{$flex_rank->tier}} {{$flex_rank->rank}} </b> <br>
                                <b> {{$flex_rank->leaguePoints}} LP</b> <br>
                                <b> Wins: {{$flex_rank->wins}} </b> <br>
                                <b> Losses: {{$flex_rank->losses}} </b> <br>
                                <b> Win rate: {{ round((intval($flex_rank->wins) / (intval($flex_rank->wins) + intval($flex_rank->losses))) * 100, 0)}} %</b>
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            <b> Unranked </b>
                        </div>
                    @endif
                </div>
        
        </div>
        <div class="col-md">
  
            <div class="justify-content-center"> 
                @foreach($match_data as $match) 
            
                    @foreach($match->participants as $participant)
                        @if($participant->summonerName == $user->name)
                            @if($match->remake == "true") 
                                <div style="background-color:lightgray;max-width:1200px" class="card">
                            @elseif($participant->win == "true")
                                <div style="background-color:lightblue;max-width:1200px" class="card">
                            @else
                                <div style="background-color:#FFCCCB;max-width:1200px" class="card">
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
                                        <img style="width:60px;height:60px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                        <b style="margin-left:2%;font-size:22px">{{$participant->kills}} / <b style="color:red">{{$participant->deaths}} </b> / {{$participant->assists}}</b>
                                        <hr style="height:2px;border:none;color:#333;background-color:#333;">
                                        <b style="margin-left:5%">Game Length: {{$match->game_length}}</b><br>
                                        <b style="margin-left:5%">Cs/min: {{$match->minions_per_min}}</b>

                                    </div>
                                    <div class="col-sm-1">
                                        <img style="width:35px;height:35px;margin-bottom:5%" class="rounded" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/spell/{{$match->primary_summoner}}">
                                        <img style="width:35px;height:35px;margin-bottom:5%" class="rounded" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/spell/{{$match->secondary_summoner}}"> <br>
                                        <img style="width:70px;height:70px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/img/{{$match->primary_rune->icon}}">
                                        <img style="width:25px;height:25px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/img/{{$match->secondary_rune->icon}}">
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
                                        <b>
                                            Vision Score: {{$participant->visionScore}} <br>
                                            Control Wards: {{$participant->visionWardsBoughtInGame}} <br>
                                            Wards Killed: {{$participant->wardsKilled}} 
                                        </b>
                                    </div> 
                                        <div class="col-2" style="margin:none">
                                        @foreach($match->participants as $participant)
                                            <div class="row-sm participant">
                                                @if($participant->teamId == 100)
                                                    <div style="display:inline-block">
                                                            <img class="players" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                                            <a class="participant" href="{{route('search', array('accountServer' => $account_server, 'username' => $participant->summonerName))}}">{{$participant->summonerName}} </a>
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
                                                        <img class="players" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                                        <a class="participant" href="{{route('search', array('accountServer' => $account_server, 'username' => $participant->summonerName))}}">{{$participant->summonerName}} </a>
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
                        </br>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
        <div class="col-1">
        </div>
    </div>


@endsection