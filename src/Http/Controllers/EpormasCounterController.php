<?php

namespace Bantenprov\DashboardEpormas\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Bantenprov\DashboardEpormas\Models\EpormasCounter;
use Bantenprov\DashboardEpormas\Models\EpormasCategory;
use Bantenprov\DashboardEpormas\Models\EpormasCity;
use Validator, Image, Session, File, Response;

class EpormasCounterController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
        $result = EpormasCounter::whereNull('deleted_at')
                        ->with('getCity')
                        ->with('getCategory')
                        ->get();
        return response()->json([
            'result' => $result
        ]);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
        $city = EpormasCity::all();
        $category = EpormasCategory::all();
        return response()->json([
            'city' => $city,
            'category' => $category,
        ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request, $version='')
  {
        $path = \Request::path();
        $explode = explode('/', $path);

        $from = 'form';
        if(in_array('api',$explode)){
          $from = 'api';
        }

        $via = $from;
        if($version != '' && $version != 'store'){
          $via .= '-'.$version;
        }

      	$rules = [
            'count' => 'required|numeric',
            'city_id' => 'required|numeric',
            'category_id' => 'required|numeric',
            'tanggal' => 'required|date',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json([
              'title' => 'Error',
              'type'  => 'error',
              'message' => $validator->errors()->all()
          ]);
        }

        $format = date('Y-m-d', strtotime(str_replace(' ','-',$request->tanggal)));
        $resultcek = EpormasCounter::whereNull('deleted_at')
                             ->where('tanggal','like','%'.$format.'%')
                             ->where('category_id',$request->category_id)
                             ->where('city_id',$request->city_id)
                             ->groupBy('tahun','bulan','category_id','city_id')
                             ->orderBy('bulan')
                             ->count();
        if($resultcek > 0){
          return response()->json([
              'title' => 'Error',
              'type'  => 'error',
              'message' => 'Data has already been taken.'
          ]);
        }

        $date = explode("-",$format);
        $data = EpormasCounter::whereNull('deleted_at')
                       ->where('tahun', $date[0])
                       ->where('bulan', $date[1])
                       ->where('category_id',$request->category_id)
                       ->where('city_id',$request->city_id)
                       ->groupBy('tahun','bulan','category_id','city_id')
                       ->orderBy('bulan')
                       ->count();
        if($data > 0){
          return response()->json([
              'title' => 'Error',
              'type'  => 'error',
              'message' => 'Data has already been taken.'
          ]);
        }

        $result = EpormasCounter::create([
            'count' => $request->count,
            'category_id' => $request->category_id,
            'city_id' => $request->city_id,
            'tahun' => $date[0],
            'bulan' => $date[1],
            'tanggal' => $format,
            'user_id' => $request->user_id,
            'via' => $via
        ]);

        return response()->json([
            'title' => 'Success',
            'type'  => 'success',
            'message' => 'Data created successfully'
        ]);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($version='', $id)
  {
        $result = EpormasCounter::whereNull('deleted_at')
                        ->with('getCity')
                        ->with('getCategory')
                        ->find($id);
        return response()->json([
            'result' => $result
        ]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function edit($id)
  {
        $city = EpormasCity::all();
        $category = EpormasCategory::all();
        $result = EpormasCounter::whereNull('deleted_at')
                        ->with('getCity')
                        ->with('getCategory')
                        ->find($id);
        $format = date('Y-m-d', strtotime($result->tanggal));
        return response()->json([
            'result' => $result,
            'city' => $city,
            'tanggal' => $format,
            'category' => $category,
        ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function update(Request $request, $version='', $id)
  {
        $result = EpormasCounter::whereNull('deleted_at')
                        ->with('getCity')
                        ->with('getCategory')
                        ->find($id);

        $rules = [
            'count' => 'required|numeric',
            'city_id' => 'required|numeric',
            'category_id' => 'required|numeric',
            'tanggal' => 'required|date',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json([
              'title' => 'Error',
              'type'  => 'error',
              'message' => $validator->errors()->all()
          ]);
        }

        $format = date('Y-m-d', strtotime(str_replace(' ','-',$request->tanggal)));
        if($result->city_id != $request->city_id || $result->category_id != $request->category_id){
            $resultcek = EpormasCounter::whereNull('deleted_at')
                                 ->where('tanggal','like','%'.$format.'%')
            		                 ->where('category_id',$request->category_id)
            		                 ->where('city_id',$request->city_id)
                                 ->groupBy('tahun','bulan','category_id','city_id')
                                 ->orderBy('bulan')
                                 ->count();
            if($resultcek > 0){
              return response()->json([
                  'title' => 'Error',
                  'type'  => 'error',
                  'message' => 'Data has already been taken.'
              ]);
            }
        }

        $date = explode("-",$format);
        $dates = date('Y-m-d', strtotime($result->tanggal));
        if($dates != $format){
            $resultcek = EpormasCounter::whereNull('deleted_at')
            		                 ->where('category_id',$request->category_id)
            		                 ->where('city_id',$request->city_id)
                                 ->where('tanggal','like','%'.$format.'%')
                                 ->groupBy('tahun','bulan','category_id','city_id')
                                 ->orderBy('bulan')
                                 ->count();
            if($resultcek > 0){
              return response()->json([
                  'title' => 'Error',
                  'type'  => 'error',
                  'message' => 'Data has already been taken.'
              ]);
            }

            $data = EpormasCounter::whereNull('deleted_at')
                           ->where('tahun', $date[0])
                           ->where('bulan', $date[1])
            		           ->where('category_id',$request->category_id)
            		           ->where('city_id',$request->city_id)
                           ->groupBy('tahun','bulan','category_id','city_id')
                           ->orderBy('bulan')
                           ->count();
            if($data > 0){
              return response()->json([
                  'title' => 'Error',
                  'type'  => 'error',
                  'message' => 'Data has already been taken.'
              ]);
            }
        }

        $path = \Request::path();
        $explode = explode('/', $path);

        $from = $result->via;
        $user_id = $result->user_id;
        if(in_array('api',$explode)){
          $from = 'api';
          $user_id = $request->user_id;
        }

        $via = $from;
        if($version != '' && $version != 'update'){
          $via .= '-'.$version;
        }

        $result->update([
            'count' => $request->count,
            'category_id' => $request->category_id,
            'city_id' => $request->city_id,
            'tahun' => $date[0],
            'bulan' => $date[1],
            'tanggal' => $format,
            'user_id' => $user_id,
            'via' => $via
        ]);

        return response()->json([
            'title' => 'Success',
            'type'  => 'success',
            'message' => 'Data updated successfully'
        ]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {
        EpormasCounter::find($id)->delete();
        return response()->json([
            'message' => 'Data deleted successfully'
        ]);
  }

}

?>
