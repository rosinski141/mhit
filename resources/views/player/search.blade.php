@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center"> 
        <h1> Stats </h1>
   
        @foreach($matchData as $match) 
            {{ dd($match[0]->info) }}
        @endforeach
    </div>
@endsection