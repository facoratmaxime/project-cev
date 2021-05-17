@extends('layout')
@section('content')

    <div class="row">
        @foreach ($results as $userID => $value)
            <div class="col-sm-6">
                <div class="card">
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
                                        <th scope="row">{{ $periodKey }}</th>
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
    </div>
@endsection
