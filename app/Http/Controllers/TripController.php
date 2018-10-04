<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\Curls;
use View;

class TripController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('UTC');
    }

    private $sampleDate = '{
"airlines": [
{
"code": "AC",
"name": "Air Canada"
},
{
"code":"WS",
"name":"Westjet"
}
],
"airports": [
{
"code": "YUL",
"city_code": "YMQ",
"name": "Pierre Elliott Trudeau International",
"city": "Montreal",
"country_code": "CA",
"region_code": "QC",
"latitude": 45.457714,
"longitude": -73.749908,
"timezone": "America/Montreal"
},
{
"code": "YMX",
"city_code": "YMQ",
"name": "Mirabel",
"city": "Montreal",
"country_code": "CA",
"region_code": "QC",
"latitude": 45.457714,
"longitude": -73.749908,
"timezone": "America/Montreal"
},
{
"code": "YVR",
"city_code": "YVR",
"name": "Vancouver International",
"city": "Vancouver",
"country_code": "CA",
"region_code": "BC",
"latitude": 49.194698,
"longitude": -123.179192,
"timezone": "America/Vancouver"
}
],
"flights": [
{
"airline": "AC",
"number": "301",
"departure_airport": "YUL",
"departure_time": "07:35",
"arrival_airport": "YVR",
"arrival_time": "10:05",
"price": "273.23"
},
{
"airline": "AC",
"number": "401",
"departure_airport": "YUL",
"departure_time": "08:00",
"arrival_airport": "YVR",
"arrival_time": "11:55",
"price": "300.00"
},
{
"airline": "AC",
"number": "302",
"departure_airport": "YVR",
"departure_time": "11:30",
"arrival_airport": "YUL",
"arrival_time": "19:11",
"price": "220.63"
},
{
"airline": "AC",
"number": "402",
"departure_airport": "YVR",
"departure_time": "16:30",
"arrival_airport": "YUL",
"arrival_time": "23:11",
"price": "350.00"
},
{
"airline": "WS",
"number": "101",
"departure_airport": "YMX",
"departure_time": "14:00",
"arrival_airport": "YVR",
"arrival_time": "17:00",
"price": "350.00"
}
]
}';
    //
    private $invalidQueryParameter = array(
        "errors" => array(
            "status" => 400,
            "code" => 401,
            "title" => "INVALID FORMAT",
            "detail" => "Invalid query parameter format."
        )
    );
    private $unexpectedError = array(
        "errors" => array(
            "status" => 600,
            "code" => 601,
            "title" => "SYSTEM ERROR HAS OCCURRED",
            "detail" => "Unexpected Error."
        )
    );

    private $notFound = array(
        "errors" => array(
            "status" => 500,
            "code" => 501,
            "title" => "NOT FOUND",
            "detail" => "No response found for this query parameter."
        )
    );

    public function search(Request $request)
    {
        // validate
        $messages = [
            'from.required' => 'Please enter From.',
            'to.required' => 'Please enter To.',
            'departDate.required' => 'Please enter Depart.'
        ];


        $this->validate($request, [
            'from' => 'required',
            'to' => 'required',
            'departDate' => 'required'
        ], $messages);

        //validate time
        $departureDate = $request->post('departDate');
        $returnDate = $request->post('returnDate');
        $dateLimit = strtotime($departureDate . " +365 day");

        //validate airport
        $from_input = trim(strtoupper($request->post('from')));
        $to_input = trim(strtoupper($request->post('to')));

        if (!isset($departureDate) || !isset($from_input) || !isset($to_input)) {
            $this->invalidQueryParameter['errors']['detail'] .= ' <=> From, To, Departure date can\'t be empty.';
            $returnValue = $this->invalidQueryParameter;
//            return $returnValue;
            return View::make('trip')
                ->with('tripJson', $returnValue);
        }


        $records = $this->getAllTripInfo();
        if (count($records) > 0) {
            //$airlines = $records['airlines'];
            $airports = $records['airports'];
            $from_airports = $this->inputAirport($from_input, $airports);
            $to_airports = $this->inputAirport($to_input, $airports);

            if (!isset($from_airports)) {
                $this->invalidQueryParameter['errors']['detail'] .= ' <=> Departure airport doesn\'t exist.';
                $returnValue = $this->invalidQueryParameter;
            } elseif (!isset($to_airports)) {
                $this->invalidQueryParameter['errors']['detail'] .= ' <=> Arrive airport doesn\'t exist.';
                $returnValue = $this->invalidQueryParameter;
            } else {
                $currentDate = $this->timeConvert('UTC', $from_airports[0]['timezone'], date('Y-m-d', time()));
//                var_dump($currentDate);
                $currentDate = strtotime($currentDate);
                $input_departureDate = strtotime($this->timeConvert($from_airports[0]['timezone'], 'UTC', $departureDate));
                $tripType = $request->post('type');

                //validate round-trip return date rule
                if ($tripType == 'round-trip') {
                    if (strtotime($returnDate) < strtotime($departureDate)) {
                        $this->invalidQueryParameter['errors']['detail'] .= ' <=> Return date must later than departure date.';
//                        return $this->invalidQueryParameter;
                        $returnValue = $this->invalidQueryParameter;
                        return View::make('trip')
                            ->with('tripJson', $returnValue);
                    } elseif ($dateLimit < strtotime($returnDate)) {
                        $this->invalidQueryParameter['errors']['detail'] .= ' <=> Return date must within 365 days.';
//                        return $this->invalidQueryParameter;
                        $returnValue = $this->invalidQueryParameter;
                        return View::make('trip')
                            ->with('tripJson', $returnValue);
                    }
                }
                if ($input_departureDate < $currentDate) {
                    $this->invalidQueryParameter['errors']['detail'] .= ' <=> Departure date must later than current time.';
                    $returnValue = $this->invalidQueryParameter;
                } else {
                    //validation end here

                    //filter by airlines
                    $airlineList = $request->post('airlineList');
                    $flights = $this->filterByAirline($records['flights'], $airlineList);
                    //filter end

                    if ($tripType == 'one-way') {
                        if (isset($departureDate) && $departureDate != '') {
                            $trip = $this->getOneWayTrip($flights, $from_airports, $to_airports, $departureDate);
                        } else {
                            $returnValue = $this->invalidQueryParameter;
                        }
                    } else if ($tripType == 'round-trip') {
                        if (isset($departureDate) && ($departureDate != '') &&
                            isset($returnDate) && ($returnDate != '')) {
                            $trip = $this->getRoundTrip($flights, $from_airports, $to_airports, $departureDate, $returnDate);

                        } else {
                            $returnValue = $this->invalidQueryParameter;
                        }
                    }
//                    else if ($tripType == 'multi-city') {
                        //$trip = $this->getmulticityTrip($flights, $from, $to);
//                    }
                    else {
                        $returnValue = $this->unexpectedError;
                    }
                }
            }
        }

        if (!isset($returnValue)) {
            if (isset($trip) && count($trip) > 0) {
                $sortType = $request->post('sort');
                $tripSortByPrice = $this->sort($trip, $sortType);
                $returnValue = array('data' => $tripSortByPrice);
            } else {
                $returnValue = $this->notFound;
            }
        }

//        return $returnValue;
        return View::make('trip')
            ->with('tripJson', $returnValue);
    }
    private function getAllTripInfo(){
        return json_decode($this->sampleDate, true);
    }
    private function getOneWayTrip($flights, $fromAirports, $toAirports, $departureDate)
    {
        //for multi airport in one city
        $from = array();
        $to = array();
        $departureTimeZone = $fromAirports[0]['timezone'];
        $arriveTimeZone = $toAirports[0]['timezone'];

        foreach ($fromAirports as $item){
            $from[] = $item['code'];
        }
        foreach ($toAirports as $item)
        {
            $to[] = $item['code'];
        }

        //
        $trip = array();
        foreach ($flights as $flight) {
            if (in_array($flight['departure_airport'], $from) && in_array($flight['arrival_airport'], $to)) {
                $d_T = $departureDate . ' ' . $flight['departure_time'];
                if ($this->isDepartureTimeValid($departureTimeZone, $d_T)) {
                    //calculate flight time
                    $a_T = $this->calculateArriveDate($departureDate, $flight['departure_time'], $flight['arrival_time'],
                        $departureTimeZone, $arriveTimeZone);
                    $d_T_UTC = $this->timeConvert($departureTimeZone, 'UTC', $d_T);
                    $a_T_UTC = $this->timeConvert($arriveTimeZone, 'UTC', $a_T);
                    $total = strtotime($a_T_UTC) - strtotime($d_T_UTC);

                    $now = new \DateTime($a_T_UTC);
                    $then = new \DateTime($d_T_UTC);
                    $diff = $now->diff($then);
                    $totalTime=  $diff->format('%Hh %im');
                    //end calculate

                    $trip[] = array(
                        "itinerary" => array(array(
                            'departure_airport' => $flight['departure_airport'],
                            'arrival_airport' => $flight['arrival_airport'],
                            'flightNumber' => $flight["airline"] . ' ' . $flight["number"],
                            "departure_time" => $d_T,
                            'arrival_time' => $a_T)),
                        "price" => $flight['price'],
                        "totalTime" => $total,
                        "totalTimeFormat" => $totalTime);
                }
            }
        }
        return $trip;
    }
    private function getRoundTrip($flights, $fromAirports, $toAirports, $departureDate, $returnDate)
    {
        //for multi airport in one city
        $from = array();
        $to = array();
        $departureTimeZone = $fromAirports[0]['timezone'];
        $arriveTimeZone = $toAirports[0]['timezone'];

        foreach ($fromAirports as $item) {
            $from[] = $item['code'];
        }
        foreach ($toAirports as $item) {
            $to[] = $item['code'];
        }

        //
        $trip = array();
        $departs = array();
        $returns = array();
        foreach ($flights as $flight) {
            if (in_array($flight['departure_airport'], $from) && in_array($flight['arrival_airport'], $to)) {
                if ($this->isDepartureTimeValid($departureTimeZone, $departureDate . ' ' . $flight['departure_time'])) {
                    $departs[] = $flight;
                }
            } else if (in_array($flight['departure_airport'], $to) && in_array($flight['arrival_airport'], $from)) {
                $returns[] = $flight;
            }
        }

        foreach ($departs as $depart) {
            foreach ($returns as $return) {
                //
                $time1 = strtotime($departureDate . ' ' . $depart['arrival_time']);
                $time2 = strtotime($returnDate . ' ' . $return['departure_time']);

                // compare: departure time for return flight need to later than arrive time for departure flight
                if ($time2 > $time1) {

                    $a_T = $this->calculateArriveDate($departureDate, $depart['departure_time'], $depart['arrival_time'],
                        $departureTimeZone, $arriveTimeZone);
                    $a_T2 = $this->calculateArriveDate($returnDate, $return['departure_time'], $return['arrival_time'],
                        $arriveTimeZone, $departureTimeZone);


                    $trip[] = array(
                        "itinerary" => array(
                            array('departure_airport' => $depart['departure_airport'],
                                'arrival_airport' => $depart['arrival_airport'],
                                'flightNumber' => $depart["airline"] . ' ' . $depart["number"],
                                "departure_time" => $departureDate . ' ' . $depart['departure_time'],
                                'arrival_time' => $a_T),
                            array('departure_airport' => $return['departure_airport'],
                                'arrival_airport' => $return['arrival_airport'],
                                'flightNumber' => $return["airline"] . ' ' . $return["number"],
                                "departure_time" => $returnDate . ' ' . $return['departure_time'],
                                'arrival_time' => $a_T2)),
                        "price" => number_format(floatval($depart['price']) + floatval($return['price']), 2),
                        "totalTime" => 0
                    );
                }
            }
        }
        return $trip;
    }
    private function inputAirport($input, $airports)
    {
        $result = null;
        foreach ($airports as $airport) {
            if (($airport['code'] == $input) ||
                ($airport['city_code'] == $input) ||
                (strtoupper($airport['city']) == $input)) {
                $result[] = $airport;
            }
        }
        return $result;
    }
    private function isDepartureTimeValid($timezone, $time)
    {
        $currentTime = time();
//        var_dump('vancover flight time :'.$time);
//        var_dump('current utc time'.date('Y-m-d H:i:s', $currentTime));
//        var_dump('vancouver flight utc time'.$this->timeConvert($timezone, 'UTC',$time));
//        var_dump($timezone);
        $time = strtotime($this->timeConvert($timezone, 'UTC',$time));
        if ($time > $currentTime)
            return true;
        else
            return false;
    }
    private function timeConvert($timeZone_In, $timeZone_out, $time)
    {
//        var_dump('timeConvert--1--'.$timeZone_In);
//        var_dump('timeConvert--2--'.$timeZone_out);
//        var_dump('timeConvert--3--'.$time);
        $dt = new \DateTime($time, new \DateTimeZone($timeZone_In));
//        var_dump('timeConvert--4--'.$dt->format('Y-m-d H:i:s'));
        $dt->setTimeZone(new \DateTimeZone($timeZone_out));
//        var_dump('timeConvert--5--'.$dt->format('Y-m-d H:i:s'));
        return $dt->format('Y-m-d H:i:s');
    }

    private function sort($tripList, $sortType)
    {
        if (!isset($sortType)) {
            $sortType = 'priceASC';
        }
//        var_dump($sortType);
        switch ($sortType) {
            case 'priceDESC':
                $list = $this->sortByArg($tripList, 'price',SORT_DESC);
                break;
            case 'priceASC':
                $list = $this->sortByArg($tripList, 'price',SORT_ASC);
                break;
            case 'timeDESC':
                $list = $this->sortByArg($tripList, 'totalTime', SORT_DESC);
                break;
            case 'timeASC':
                $list = $this->sortByArg($tripList, 'totalTime', SORT_ASC);
                break;
            default:
                $list = $this->sortByArg($tripList, 'totalTime', SORT_ASC);
        }
        return $list;
    }

    private function sortByArg($tripList, $field, $type)
    {
        $price = array();
        foreach ($tripList as $key => $row) {
            $price[$key] = $row[$field];
        }
        array_multisort($price, $type, $tripList);
        return $tripList;
    }

    private function filterByAirline($flights, $filterList){
        if(!isset($filterList) || count($filterList) == 0) {
            return $flights;
        }
        $dataRecords = array();
        foreach ($flights as $flight){
            if(in_array($flight['airline'], $filterList)){
                $dataRecords[] = $flight;
            }
        }
        return $dataRecords;
    }

    private function calculateArriveDate($departureDate, $departure_time, $arrival_time,$departureTimeZone, $arriveTimeZone)
    {
        $d_T = $departureDate . ' ' . $departure_time;
        //calculate flight time
        $a_T = $departureDate . ' ' . $arrival_time;
        $d_T_UTC = $this->timeConvert($departureTimeZone, 'UTC', $d_T);
        $a_T_UTC = $this->timeConvert($arriveTimeZone, 'UTC', $a_T);
        if (strtotime($a_T_UTC) < strtotime($d_T_UTC)) {
            $a_T = date('Y-m-d H:i', strtotime($a_T . "+1 days"));
        }
        return $a_T;
    }
}
