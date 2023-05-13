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
            <a class="btn btn-primary" href="{{ route('update_feedback')}}">Update</a>
        </div>
    </div>

    <div class="row" style="margin-top:20px;margin-right:5%">
        <div class="col-2">
        </div>

        <div class="col-lg">
            <h2>From the last {{$match_count}} games the following statistics were identified: </h2> 
            <ul style="margin-top:1%;text-align:center" class="list-group">
          
                @foreach($feedback as $current_feedback)
                    @if($current_feedback->video_link)
                        <li style="background-color:#FFCCCB" class="list-group-item"> 
                    @else
                        <li style="background-color:lightgreen" class="list-group-item"> 
                    @endif
                            <b style="text-transform: capitalize;">{{$current_feedback->text}}</b>
                            @if($current_feedback->video_link)
                                <div class="video" style="margin:3%">
                                    <iframe width="560" height="315" src="{{$current_feedback->video_link}}"  frameborder="1" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                </div>
                            @endif
                        </li>
                        <hl>
                @endforeach
            </ul>   
        </div>
    </div>


@endsection