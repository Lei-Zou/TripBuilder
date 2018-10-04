
@extends('layout/app')

@section('content')

    <div class="container">
        <input type="button" value="Back" onclick="location.href ='{{ url()->previous() }}'" class="btn btn-primary m-3" />

@if(isset($tripJson) && $tripJson !='')
    @if(isset($tripJson['errors']))
            <div class="row m-3">
                {{$tripJson['errors']['detail']}}
            </div>
    @elseif(isset($tripJson['data']))
            <div class="row m-3">
                <div class="col-sm-2 text-center">
                    Departure Airport
                </div>
                <div class="col-sm-1 text-center">

                </div>
                <div class="col-sm-2 text-center">
                    Arrival Airport
                </div>
                <div class="col-sm-2 text-center">
                    Flight Number
                </div>
                <div class="col-sm-2 text-center">
                    Departure Time
                </div>
                <div class="col-sm-2 text-center">
                    Arrival Time
                </div>
                <div class="col-sm-1 text-center">
                </div>
            </div>
        @foreach($tripJson['data'] as $item)
            <div class="border m-3">
                <div class="row md-3">
                @foreach($item['itinerary'] as $itinerary)
                    <div class="col-sm-2 text-center">
                        {{$itinerary['departure_airport']}}
                    </div>
                    <div class="col-sm-1 text-center">
                        --->
                    </div>
                    <div class="col-sm-2 text-center">
                        {{$itinerary['arrival_airport']}}
                    </div>
                    <div class="col-sm-2 text-center" >
                        {{$itinerary['flightNumber']}}
                    </div>
                    <div class="col-sm-2 text-center">
                        {{$itinerary['departure_time']}}
                    </div>
                    <div class="col-sm-2 text-center">
                        {{$itinerary['arrival_time']}}
                    </div>
                    <div class="col-sm-1 text-center">
                    </div>
                @endforeach

            </div>
                <div class="row md-3">
                    <div class="col-sm-2">

                    </div>
                    <div class="col-sm-2">

                    </div>
                    <div class="col-sm-2">

                    </div>
                    <div class="col-sm-2">
                        {{ isset($item['totalTimeFormat'])? 'Total time: '.$item['totalTimeFormat']:''}}
                    </div>
                    <div class="col-sm-2">

                    </div>
                    <div class="col-sm-2">
                        price: {{ $item['price'] }}
                    </div>

                </div>
            </div>
        @endforeach
    @endif
@endif

</div>
@endsection