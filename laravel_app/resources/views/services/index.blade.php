@php $title = 'Bang dich vu' @endphp
@php $subtitle = 'Theo doi va quan tri cac dich vu co so du lieu da duoc ket noi' @endphp
@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="toolbar">
            <div>
                <h2 class="no-top-margin">Danh muc dich vu</h2>
                <p class="page-note">Moi dich vu duoc map den dung ket noi va tai nguyen, phuc vu giao dien va API tuong thich.</p>
            </div>
            <div class="inline-actions">
                <a class="btn btn-secondary" href="{{ route('dashboard') }}">Ve bang dieu khien</a>
            </div>
        </div>
    </section>

    <section class="console-grid">
        @foreach ($services as $serviceName => $service)
            <article class="panel console-card">
                <span class="eyebrow">{{ $serviceName }}</span>
                <h3 class="no-top-margin">{{ $serviceName }}</h3>
                <div class="page-note">Ket noi: <strong>{{ $service['connection'] }}</strong></div>
                <div class="chip-list top-gap-lg">
                    @foreach ($service['resources'] as $resource)
                        <a class="chip-link" href="{{ route('services.show', ['service' => $serviceName, 'resource' => $resource]) }}">{{ $resource }}</a>
                    @endforeach
                </div>
            </article>
        @endforeach
    </section>
@endsection