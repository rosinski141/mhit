@extends('layouts.app')

@section('content')


    <div class="row" style="margin-bottom:10px">
        <div class="col-2">
        </div>
        <div class="col-1">
            <div style="width:120px;height:120px;" class="profile">
                <img style="width:120px;height:120px;"class="rounded" src="http://ddragon.leagueoflegends.com/cdn/{{$league_patch}}/img/profileicon/{{$profileIcon}}.png"> </img>
                <div class="level" style="height:20px;margin-top:-11px;text-align:center;">
                    <span style="display:inline-block;line-height:20px;padding: 0px 8px;font-size:15px;border-radius:5px;color:white;background-color:black;">{{$level}}</span>
                </div>
            </div>
        </div>
        <div class="col-sm" style="margin-left:1%">              
            <h2>{{$name}}</h2>
        </div>
    </div>

    <div class="row" style="margin-top:20px;margin-right:5%">
        <div class="col-2">
        </div>

        <div class="col-lg">
            <b>From the last {{$match_count}} games the following statistics were identified: </b> 
            <ul style="margin-top:1%" class="list-group">
                @foreach($stat_text as $text)
                    <li class="list-group-item"> {{$text}} </li>
                    <hl>
                @endforeach
            </ul>   
        </div>
    </div>


@endsection