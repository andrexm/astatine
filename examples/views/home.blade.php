@extends('layout')

@section('title'):
    Welcome to my Home page!
@endsection

@section('content'):

    {{-- This is a comment --}}
    {{ "<b>asdfasd</b>" }} <hr>

    @if (1 < 2):
        <b>1 < 2</b>
    @endif

    <hr>

    @if(true && (2 > 0)):
        @if(true):
            Lorem Ipsum!<hr>
        @endif
    @endif

    @php:
        echo "3324243s";
    @endphp

    @include('subview')

    <hr>

    {{ (true && false) || (true && (true)) }}

@endsection
