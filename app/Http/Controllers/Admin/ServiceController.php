<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceDate;
use App\Models\User;
use Illuminate\Http\Request;
use DataTables;
use DateInterval;
use DateTime;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $services = Service::select(['id', 'name', 'price']);

        return DataTables::of($services)
            ->addColumn('action', function ($service) {
                return '<a href="' . route('services.edit', $service->id) . '" class="btn btn-sm btn-primary">Edit</a>';
            })
            ->make(true);
        }

        return view('admin.services.index');
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric',
            'max_people' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $service = new Service();
        $service->name = $request->name;
        $service->price = $request->price;
        $service->max_people = $request->max_people;
        $service->description = $request->description;
        $service->save();

       
        // $dateRange = explode(' - ', $request->input('date_range'));
        // $start_date = $dateRange[0];
        // $end_date = $dateRange[1];

      
        // $this->createSlotsForService($service, $start_date, $end_date,$request);

        return response()->json(['status' => '200']);
    }

    // function createSlotsForService($service, $start_date, $end_date,$request)
    // {
        
    //     $interval = new DateInterval('P1D'); // Assuming 30 minutes interval, adjust as needed
    //     $start = new DateTime($start_date);
    //     $end = new DateTime($end_date);
       
    //     while ($start <= $end) {
    //         $slot = new Slot();
    //         $slot->start_date = $start;
    //         $slot->end_date = $start;
    //         $slot->service_id = $service->id;
    //         $slot->max_people = $request->max_people;
    //         $slot->save();
    //         $start = $start->add($interval);
    //     }
    // }

    public function edit($id)
    {
        $service = Service::find($id);
        return view('admin.services.edit',compact('service'));
    }


    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric',
            'max_people' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $service = Service::find($id);

        if(!$service){
            return response()->json(['status' => '400']);
        }
        $service->name = $request->name;
        $service->price = $request->price;
        $service->max_people = $request->max_people;
        $service->description = $request->description;
        $service->save();

        return response()->json(['status' => '200']);
    }


    public function availability(Request $request)
    {
        $rows = Service::paginate(15);
        return view('admin.services.availability',compact('rows'));
    }

    
    public function loadDates(Request $request)
    {
        $service = Service::find($request->query('id'));
        $query = ServiceDate::query();
        $query->where('service_id', $request->query('id'));
        $query->where('start_date', '>=', date('Y-m-d H:i:s', strtotime($request->query('start'))));
        $query->where('end_date', '<=', date('Y-m-d H:i:s', strtotime($request->query('end'))));
        $rows = $query->take(50)->get();
        $allDates = [];
        $period = $this->periodDate($request->input('start'),$request->input('end'));
        foreach ($period as $dt){
            $i = $dt->getTimestamp();
            $date = [
                'id'           => rand(0, 999),
                'active'       => 0,
                'price'        =>  $service->price,
            ];
           
            $date['max_people'] = $service->max_people;
            $date['title'] = $date['event'] = "( 0/".$service->max_people.")";
            $date['start'] = $date['end'] = date('Y-m-d', $i);
            $date['active'] = 1;
            $date['textColor'] = '#fff';
            if (empty(!$service->max_people) and $service->max_people < 1) {
                $date['active'] = 0;
            }
            $allDates[date('Y-m-d', $i)] = $date;
        }
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $row->start = date('Y-m-d', strtotime($row->start_date));
                $row->end = date('Y-m-d', strtotime($row->start_date));
                $row->textColor = '#fff';
                $row->title = $row->event  = "( 0/".$row->max_people.")";
                if (empty(!$row->max_guests) and $row->max_guests < 1) {
                    $row->active = 0;
                }
                $row->classNames = ['active-event'];
                if (!$row->active) {
                    $row->backgroundColor = 'rgba(255, 0, 0, 0.6)';
                    $row->classNames = ['blocked-event'];
                    $row->textColor = '#fff';
                    $row->active = 0;
                } else {
                    $row->classNames = ['active-event'];
                    $row->active = 1;
                }
                $allDates[date('Y-m-d', strtotime($row->start_date))] = $row->toArray();
            }
        }
        
        $data = array_values($allDates);
        return response()->json($data);
    }

    function periodDate($startDate,$endDate,$day = true,$interval='1 day'){
        $begin = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        if($day){
            $end = $end->modify('+1 day');
        }
        $interval = \DateInterval::createFromDateString($interval);
        $period = new \DatePeriod($begin, $interval, $end);
        return $period;
    }

   
    public function storeavailability(Request $request)
    {
        $date = ServiceDate::where('start_date', $request->start_date)->where('service_id', $request->service_id)->first();
        if (empty($date)) {
            $date = new ServiceDate();
            $date->service_id = $request->service_id;
        }
        $postData['start_date'] = $request->start_date;
        $postData['end_date'] = $request->start_date;
        $postData['max_people'] = $request->max_people;
        $postData['active'] = $request->active;
        $date->fillByAttr([
            'start_date',
            'end_date',
            'max_people',
            'active',
        ], $postData);
        $date->save();
        return response()->json($date);
    }


}
