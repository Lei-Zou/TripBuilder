
@extends('layout/app')

@section('content')
    <h1 class="display-6 text-center mt-2 mb-4"> Trip Builder </h1>

    {!! Form::open(['url' => 'trip/search']) !!}

    <div class="container">
        <div class="row mb-3">
            <div class="col">
                {{ Form::radio('type', 'one-way', true, ['class'=>'form-check-input','id'=>'rd_wayType1' ])  }}
                {{ Form::label('One Way', '', ['class'=>'form-check-label' ]) }}
            </div>
            <div class="col">
                {{ Form::radio('type', 'round-trip', false, ['class'=>'form-check-input','id'=>'rd_wayType2'  ]) }}
                {{ Form::label('Round Trip', '', ['class'=>'form-check-label' ]) }}
            </div>
            <div class="col" style="display: none;">
                {{ Form::radio('type', 'multi-city',  false, ['class'=>'form-check-input' ]) }}
                {{ Form::label('multi-city', '', ['class'=>'form-check-label' ]) }}
            </div>
        </div>
        <div class="row mb-3">
            <div class='col-sm-3'>
                {{ Form::label('From') }}
                {{ Form::text('from', '', ["class"=>"form-control", "style"=>"text-transform: uppercase"]) }}
            </div>
            <div class='col-sm-3'>
                {{ Form::label('To') }}
                {{ Form::text('to','', ["class"=>"form-control", "style"=>"text-transform: uppercase"]) }}
            </div>
            <div class='col-sm-3'>
                {{ Form::label('Depart') }}
                {{ Form::date('departDate', \Carbon\Carbon::now(), ["class"=>"form-control"]) }}
            </div>

            <div class='col-sm-3 ' style="display: none;" id="div_returnDate">
                {{ Form::label('Return') }}
                {{ Form::date('returnDate', \Carbon\Carbon::now(), ["class"=>"form-control"]) }}
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-sm-3">
                {{--Form::checkbox('name', 'value');--}}
                {{ Form::radio('sort', 'priceASC', true, ['class'=>'form-check-input'])  }}
                {{ Form::label('Sort by price low->high', '', ['class'=>'form-check-label' ]) }}
            </div>
            <div class="col-sm-3">
                {{ Form::radio('sort', 'priceDESE', false, ['class'=>'form-check-input' ])  }}
                {{ Form::label('Sort by price high->low', '', ['class'=>'form-check-label' ]) }}
            </div>
            <div class="col-sm-3"  id="div_sortTime1">
                {{--Form::checkbox('name', 'value');--}}
                {{ Form::radio('sort', 'timeASC', false, ['class'=>'form-check-input'])  }}
                {{ Form::label('Sort by shortest time', '', ['class'=>'form-check-label' ]) }}
            </div>
            <div class="col-sm-3"  id="div_sortTime2">
                {{ Form::radio('sort', 'timeDESC', false, ['class'=>'form-check-input' ])  }}
                {{ Form::label('Sort by longest time', '', ['class'=>'form-check-label' ]) }}
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-sm-3">
                {{ Form::checkbox('airlineList[]', 'AC', true, ['class'=>'form-check-input'])  }}
                {{ Form::label('Air Canada', '', ['class'=>'form-check-label' ]) }}
            </div>
            <div class="col-sm-3">
                {{ Form::checkbox('airlineList[]', 'WS', true, ['class'=>'form-check-input' ])  }}
                {{ Form::label('Westjet', '', ['class'=>'form-check-label' ]) }}
            </div>
        </div>
        <div  class="row mt-3" >
            <div class='col' >
                {{ form::submit('Search', ['class'=> 'btn btn-primary']) }}
            </div>
        </div>
        <div class="row mt-5">
            <div class="col">
                <ul>
                    <li>
                        This web Service support one way and round trip search.
                    </li>
                    <li>
                        A trip must depart after creation time at the earliest or 365 days after creation time at the latest.
                        Depart date must later than current time.
                    </li>
                    <li>
                        You can input city code, airport code, city name to do the search.(Example, Monteal to Vancouver, Monteal has two airports)
                    </li>
                    <li>
                        Departure date must later than current time. All the flights departure date must later than current time.
                    </li>
                    <li>
                        Departure date and Return date is the local date of the airport
                    </li>
                    <li>
                        Result can be filtered by airline.
                    </li>
                    <li>
                        Result can be sort by total time for one trip.
                    </li>
                    <li>
                        Example:
                         From,TO can be : YUL,YVR,YMX,YMQ,Montreal,Vancouver
                    </li>
                </ul>

            </div>
        </div>
    </div>

    {!! Form::close() !!}


    @endsection


