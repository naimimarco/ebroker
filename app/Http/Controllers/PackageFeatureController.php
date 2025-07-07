<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Feature;
use Illuminate\Http\Request;
use App\Models\PackageFeature;
use App\Services\ResponseService;
use App\Services\BootstrapTableService;

class PackageFeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!has_permissions('read', 'package-feature')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('packages.features.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!has_permissions('create', 'package-feature')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        try {
            Feature::create($request->only('name'));
            ResponseService::successResponse(trans("Data Created Successfully"));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,'Error in Package Feature Store Controller',trans("Something Went Wrong"));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');

        $sql = Feature::when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orWhere('name', 'LIKE', "%$search%");
                });
            });


        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            $row = (object)$row;

            $operate = BootstrapTableService::editButton('', true, null, null, null, null);
            $operate .= BootstrapTableService::deleteAjaxButton(route('package-features.destroy', $row->id));

            $tempRow = $row->toArray();
            $tempRow['edit_status_url'] = route('package-features.status-update');
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!has_permissions('update', 'package-feature')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        try {
            Feature::where('id',$id)->update($request->only('name'));
            ResponseService::successResponse(trans("Data Updated Successfully"));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,'Error in Package Feature Update Controller',trans("Something Went Wrong"));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!has_permissions('delete', 'package-feature')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        try {
            Feature::where('id',$id)->delete();
            ResponseService::successResponse(trans("Data Deleted Successfully"));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,'Error in Package Feature Delete Controller',trans("Something Went Wrong"));
        }
    }

    /**
     * Update status of specified resource.
     */
    public function updateStatus(Request $request)
    {
        if (!has_permissions('update', 'package-feature')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        try {
            Feature::where('id',$request->id)->update(['status' => $request->status == 1 ? true : false]);
            ResponseService::successResponse(trans("Status Updated Successfully"));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,'Error in Package Feature Update Controller',trans("Something Went Wrong"));
        }
    }
}
