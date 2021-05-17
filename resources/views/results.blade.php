@extends('layout')
@section('content')

    <div class="row">
        @foreach ($results as $userID => $value)
            <div class="col-sm-6">
                <div class="card ">
                    <div class="card-body">
                        <h5 class="card-title">Utilisateur #{{ $userID }}</h5>
                        <p class="card-text">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">PERIODE</th>
                                    <th scope="col">POINTS</th>
                                    <th scope="col">EUROS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($value as $periodKey => $value)

                                    <tr>
                                        <th scope="row" rel="tooltip" @if (isset($timeslots[$periodKey])) title="Période de {{ $timeslots[$periodKey]['start']->format($dateFormat) }} a {{ $timeslots[$periodKey]['end']->format($dateFormat) }}" @endif>{{ $periodKey }}</th>
                                        <td>{{ $value['TOTAL']['POINTS'] }}</td>
                                        <td>{{ $value['TOTAL']['EUROS'] }} €</td>
                                    </tr>
                                @endforeach
                        </table>
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-sm-6">
            <div class="card text-white bg-info text-center">
                <div class="card-body">
                    @foreach ($timeslots as $periodKey => $value)
                        <p><strong>{{ $periodKey }}</strong>: {{ $value['start']->format($dateFormat) }} -
                            {{ $value['end']->format($dateFormat) }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <style>
        .card {
            margin: 10px;
        }

    </style>
@endsection
