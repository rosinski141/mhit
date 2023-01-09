@extends('layouts.app')

@section('content')
    <img style="width:200px;height:250px"src="{{$emblem_path}}">
    <h2 style="display:inline-block">{{$username}}</h2>
    <div class="justify-content-center"> 
        
        @foreach($match_data as $match) 
           
            @if($match->win == "true")
                <div style="background-color:lightblue" class="card">
              
                
           
            @else
                <div style="background-color:#F08080" class="card">
             
            @endif

            <h3>KDA: {{$match->kills}}/{{$match->deaths}}/{{$match->deaths}}</h3>
            </div>
        @endforeach
    </div>
@endsection