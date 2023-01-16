@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/search.css') }}" rel="stylesheet">
    <div class="row">
        <img style="width:170px;height:150px;margin-left:5%" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/profileicon/{{$user->profileIconId}}.png"> 
        <h3 style="margin-left:5%;float:right">{{$user->name}}</h3>
        <div style="margin-top:5%;margin-left:2%" class="col-2">
            
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
        <div style="margin-right:20%;margin-top:5%" class="col-lg">
            <div class="justify-content-center"> 
                @foreach($match_data as $match) 
                    @foreach($match->participants as $participant)
                        @if($participant->summonerName == $user->name)
                            @if($participant->win == "true")
                                <div style="background-color:lightblue" class="card">
                            @else
                                <div style="background-color:#F08080" class="card">
                            @endif
                                <div class="row">
                                    <div class="col-sm">
                                        <img style="width:70px;height:70px" class="rounded-circle" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                        <h3>KDA: {{$participant->kills}}/{{$participant->deaths}}/{{$participant->assists}}</h3>
                                    </div>
                                    <div class="col-sm">
                                        @foreach($participant->items as $item)
                                            @if($item == 0) 
                                                <span class="placeholder item rounded"></span>
                                            @else 
                                                <img class="item" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/item/{{$item}}.png"> 
                                            @endif
                                        @endforeach
                                    </div> 
                                        <div class="col-lg">
                                        @foreach($match->participants as $participant)
                                            <div class="row">
                                                @if($participant->teamId == 100)
                                                    <img class="players" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                                    <link href="search?accountServer=EUW&username=LordComets" style="font-size:10px">{{$participant->summonerName}} </link>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="col-lg">
                                        @foreach($match->participants as $participant)
                                            <div class="row">
                                                @if($participant->teamId == 200)
                                                    <img class="players" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/champion/{{$participant->championName}}.png">
                                                    <link href="search?accountServer=EUW&username=LordComets" style="font-size:10px">{{$participant->summonerName}} </link>
                                                @endif
                                            </div>
                                        @endforeach
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
    </div>
@endsection