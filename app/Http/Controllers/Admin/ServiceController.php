<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Slot;
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
        
        $service->description = $request->description;
        $service->save();

        // Handle date range
        $dateRange = explode(' - ', $request->input('date_range'));
        $start_date = $dateRange[0];
        $end_date = $dateRange[1];

        // Create slots
        $this->createSlotsForService($service, $start_date, $end_date,$request);

        return response()->json(['status' => '200']);
    }

    function createSlotsForService($service, $start_date, $end_date,$request)
    {
        
        $interval = new DateInterval('P1D'); // Assuming 30 minutes interval, adjust as needed
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
       
        while ($start <= $end) {
            $slot = new Slot();
            $slot->start_date = $start;
            $slot->end_date = $start;
            $slot->service_id = $service->id;
            $slot->max_people = $request->max_people;
            $slot->save();
            $start = $start->add($interval);
        }
    }

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

    public function indexs(Request $request)
    {
        if($request->ajax()) {
             $datas = Slot::whereDate('start_date', '>=', $request->start)
                       ->whereDate('end_date',   '<=', $request->end)
                       ->get(['id', 'start_date', 'end_date','max_people']);
            foreach ($datas as $data) {
                $events[] = [
                    'title' => "( 0/".$data->max_people.")",
                    'start' => $data->start_date,
                    'end' => $data->end_date,
                    'id' => $data->id,
                    'max_people' => $data->max_people,
                ];
            }          
             return response()->json($events);
        }
        $rows = Service::paginate(15);
        return view('admin.services.availability',compact('rows'));
    }
 
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function ajax(Request $request)
    {
        switch ($request->type) {
           case 'add':
              $event = Slot::create([
                  'title' => $request->title,
                  'start' => $request->start,
                  'end' => $request->end,
              ]);
 
              return response()->json($event);
             break;
  
           case 'update':
              $event = Slot::find($request->id)->update([
                  'max_people' => $request->max_people,
              ]);
 
              return response()->json($event);
             break;
  
           case 'delete':
              $event = Slot::find($request->id)->delete();
  
              return response()->json($event);
             break;
             
           default:
             # code...
             break;
        }
    }


}
