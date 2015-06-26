@extends('emails.template')

@section('content')
    Hello {{ Auth::user()->name }},<br/>
    Thanks for signing up for Squeegy. We hope you enjoy your car wash!

@endsection


