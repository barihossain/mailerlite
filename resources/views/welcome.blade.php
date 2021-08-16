@extends('layout')

@section('page-title')
    {!! $title !!}
@endsection

@section('body')
    @if (session()->has('message'))
    <div class="alert alert-{{ session()->get('message')['type'] }}">
        {!! session()->get('message')['body'] !!}
    </div>
    @endif
    @if(count($errors))
        @foreach($errors->all() as $error)
        <div class="alert alert-danger">
            {{$error}}
        </div>
        @endforeach
    @endif
    <h4>{{ (is_null($api_key)) ? 'Please add' : 'Update' }} your MailerLite API key</h4>
    <form id="api-key-form" method="post" action="/">
        @if(!is_null($api_key))
        <input name="_method" type="hidden" value="PUT">
        @endif
        <div class="mb-3">
            <input type="text" class="form-control" value="{{ (count($errors)) ? old('api_key') : $api_key }}" id="api_key" name="api_key" placeholder="Enter your MailerLite API key">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection
@if(!empty($api_key))
    @section('extra-buttons')
        <a class="btn btn-outline-primary ms-auto" href={{ route('subscribers.list') }}>Subscribers</a>
    @endsection
@endif
