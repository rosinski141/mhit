@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center"> 
        <h2> Match History and Improvement Tool </h2>
    </div>
    <div class="d-flex justify-content-center"> 
        <form class="d-flex" method="get" action="{{url('search')}}" >
                <select id="accountServer" name="accountServer" class="form-select me-2" style="width:30%;" aria-label="Default select example" required="">
                    <option selected value="EUW">EUW</option>
                    <option value="NA">NA</option>
                    <option value="OCE">OCE</option>
                    <option value="KR">KR</option>
                </select>
                <input name="username" class="form-control me-2" type="search" placeholder="Summoner Name" aria-label="Search" required="">
                <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
    </div>
@endsection