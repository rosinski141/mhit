@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/search.css') }}" rel="stylesheet">
    <div class="row">
        <div style ="width:200px" class="col-2">
        </div>
        <div style ="width:200px" class="col-1">
            <img style="width:170px;height:170px" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/profileicon/{{$user->profileIconId}}.png"> </img>
            <h2>{{$user->name}}</h2>
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
                            @if($participant->win == "true")
                                <div style="background-color:lightblue;max-width:1200px" class="card">
                            @else
                                <div style="background-color:#F08080;max-width:1200px" class="card">
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
                                        <img style="width:70px;height:70px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                        <h3>KDA: {{$participant->kills}}/{{$participant->deaths}}/{{$participant->assists}}</h3>
                                      
                                    </div>
                                    <div class="col-sm-1">
                                        <img style="width:70px;height:70px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/img/{{$match->primary_rune->icon}}">
                                        <img style="width:30px;height:30px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/img/{{$match->secondary_rune->icon}}">
                                    </div>
                                    <div class="col-sm">
                                        @foreach($participant->items as $item)
                                            @if($item == 0) 
                                                <span class="placeholder item rounded"></span>
                                            @else 
                                                <img class="item" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/item/{{$item}}.png"> 
                                            @endif
                                        @endforeach
                                        <br>
                                        Vision Score: {{$participant->visionScore}} <br>
                                        Control Wards Purchased: {{$participant->visionWardsBoughtInGame}} <br>
                                        Wards Killed: {{$participant->wardsKilled}} 
                                    </div> 
                                        <div class="col-md">
                                        @foreach($match->participants as $participant)
                                            <div class="row">
                                            
                                                @if($participant->teamId == 100)
                                                    <div style="display:inline-block">
                                                            <img class="players" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                                            <a href="{{route('search', array('accountServer' => $account_server, 'username' => $participant->summonerName))}}" style="wrap:flex;text-decoration:none;color:black;font-size:15px;">{{$participant->summonerName}} </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="col-md">
                                        @foreach($match->participants as $participant)
                                            <div class="row">
                                                @if($participant->teamId == 200)
                                                    <div style="display:inline-block">
                                                        <img class="players" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                                        <a href="{{route('search', array('accountServer' => $account_server, 'username' => $participant->summonerName))}}" style="wrap:flex;text-decoration:none;color:black;font-size:15px;">{{$participant->summonerName}} </a>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                        @endforeach
                                    </div>
                                    <div class="col-sm-2">

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
                                </div>
                            </div>
                        </br>
                        @endif
                    @endforeach
                @endforeach
                <btn class="btn btn-primary" style="margin-left:45%">Load More</btn>
            </div>
        </div>
        <div class="col-1">
        </div>
    </div>

@endsection