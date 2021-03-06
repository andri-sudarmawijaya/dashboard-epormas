<?php

namespace Bantenprov\DashboardEpormas\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Bantenprov\DashboardEpormas\Models\EpormasCategory;
use Validator, Image, Session, File, Response;

class EpormasCategoryController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
        $result = EpormasCategory::all();
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
            'name' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json([
              'title' => 'Error',
              'type'  => 'error',
              'message' => $validator->errors()->all()
          ]);
        }

        $data = EpormasCategory::whereNull('deleted_at')
                        ->where('name', $request->name)
                        ->count();
        if($data > 0){
          return response()->json([
              'title' => 'Error',
              'type'  => 'error',
              'message' => 'Data has already been taken.'
          ]);
        }

        $result = EpormasCategory::create([
            'name' => $request->name
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
        $result = EpormasCategory::find($id);
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
        $result = EpormasCategory::find($id);
        return response()->json([
            'result' => $result
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
        $result = EpormasCategory::find($id);

        $rules = [
            'name' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json([
              'title' => 'Error',
              'type'  => 'error',
              'message' => $validator->errors()->all()
          ]);
        }

        if($request->name != $result->name){
          $data = EpormasCategory::whereNull('deleted_at')
                          ->where('name', $request->name)
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

        $from = 'form';
        if(in_array('api',$explode)){
          $from = 'api';
        }

        $via = $from;
        if($version != '' && $version != 'update'){
          $via .= '-'.$version;
        }

        $result->update([
            'name' => $request->name
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
        EpormasCategory::find($id)->delete();
        return response()->json([
            'message' => 'Data deleted successfully'
        ]);
  }

}

?>
