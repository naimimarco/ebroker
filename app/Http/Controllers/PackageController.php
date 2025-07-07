<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Package;
use App\Models\Setting;
use App\Models\OldUserPurchasedPackage;
use App\Models\PackageFeature;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use App\Services\HelperService;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'package')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();
        $featuresList = HelperService::getFeatureList();

        $featureMapData = array();
        foreach ($featuresList as $key => $feature) {
            $featureMapData[$feature->name] = $feature->id;
        }

        return view('packages.index', compact('currency_symbol','featuresList','featureMapData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!has_permissions('create', 'package')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $validator = Validator::make($request->all(), [
                'name'                      => 'required|string|max:30',
                'ios_product_id'            => 'nullable|string|max:255|unique:packages,ios_product_id',
                'duration'                  => 'required|integer|min:1',
                'package_type'              => 'required|in:free,paid',
                'price'                     => 'nullable|required_if:package_type,paid|numeric|between:0,99999999.99',
                'feature_data'              => 'required|array',
                'feature_data.*.feature_id' => 'required',
                'feature_data.*.type'       => 'required',
                'feature_data.*.limit'      => 'nullable|required_if:feature_data.*.type,limited'
            ]);
            if ($validator->fails()) {
                ResponseService::validationError($validator->errors()->first());
            }
            try {
                DB::beginTransaction();

                // Create Package Data
                $packageData = $request->only('name','ios_product_id','duration','package_type');
                $packageData['price'] = $request->has('price') && !empty($request->price) ? round($request->price,2) : null;
                $packageData['duration'] = $request->duration * 24;
                $package = Package::create($packageData);

                // Assign Features to Package
                $packageFeatureData = array();
                foreach ($request->feature_data as $featureDataArray) {
                    $featureData = (object)$featureDataArray;
                    $packageFeatureData[] = array(
                        'package_id' => $package->id,
                        'feature_id' => (int)$featureData->feature_id,
                        'limit_type' => $featureData->type,
                        'limit' => $featureData->limit ?? null
                    );
                }

                PackageFeature::upsert($packageFeatureData,['package_id','feature_id'],['limit_type','limit']);
                DB::commit();
                ResponseService::successResponse(trans("Data Created Successfully"));
            } catch (Exception $e) {
                DB::rollback();
                ResponseService::logErrorResponse($e,'Package Controller -> store method',trans("Something Went Wrong"));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if (!has_permissions('read', 'package')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');

        $sql = Package::with('package_features');

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('duration', 'LIKE', "%$search%");
        }

        $total = $sql->count();
        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }

        $res = $sql->orderBy($sort, $order)->skip($offset)->take($limit)->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;

        $priceSymbol = HelperService::getSettingData('currency_symbol') ?? '$';
        foreach ($res as $row) {
            $tempRow = $row->toArray();

            $operate = BootstrapTableService::editButton('', true, null, null, $row->id);
            $operate .= BootstrapTableService::deleteAjaxButton(route('package.destroy',$row->id));

            $tempRow['operate'] = $operate;
            $tempRow['duration'] = $row->duration / 24;
            $tempRow['price_symbol'] = $priceSymbol;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update($id, Request $request)
    {

        if (!has_permissions('update', 'package')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $validator = Validator::make($request->all(), [
                'edit_id'           => 'required|exists:packages,id',
                'name'              => 'required|string|max:30',
                'ios_product_id'    => 'nullable|unique:packages,ios_product_id,'.$request->edit_id.'id',
                'duration'          => 'required|integer|min:1',
                'price'             => 'nullable',
            ]);
            if ($validator->fails()) {
                ResponseService::validationError($validator->errors()->first());
            }
            try{
                $requestData = $request->only('name','ios_product_id');
                $data = $requestData;
                $data['duration'] = $request->duration * 24;
                if($request->has('price') && !empty($request->price)){
                    $data = array_merge($data,array('price' => round($request->price,2)));
                }
                Package::where('id',$id)->update($data);
                ResponseService::successResponse('Data Updated Successfully');

            } catch (Exception $e) {
                ResponseService::logErrorResponse($e,'Package Controller -> Update method',trans("Something Went Wrong"));
            }
        }
    }

    public function destroy($id){
        try {
            Package::where('id',$id)->delete();
            ResponseService::successResponse("Data Deleted Successfully");
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,'Package Controller -> Destroy method',trans("Something Went Wrong"));
        }
    }

    public function updateStatus(Request $request)
    {
        if (!has_permissions('update', 'package')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            Package::where('id', $request->id)->update(['status' => $request->status]);
            $response['error'] = false;
            return response()->json($response);
        }
    }

    public function userPackageIndex(){
        if (!has_permissions('read', 'user_package')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('packages.user_packages');
    }
    public function getUserPackageList(Request $request)
    {
        if (!has_permissions('read', 'user_package')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $search = $request->input('search');

        $sql = UserPackage::with('package','customer:id,name')
            ->when($request->has('search') && !empty($search),function($query) use($search){
                $query->where('id', 'LIKE', "%$search%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })->orWhereHas('package', function ($q1) use ($search) {
                        $q1->where('name', 'LIKE', "%$search%");
                    });
            });

        $total = $sql->count();
        $res = $sql->orderBy($sort, $order)->skip($offset)->take($limit)->get();
        $res = $sql->orderBy($sort, $order)->skip($offset)->take($limit)->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;
        foreach ($res as $row) {
            $tempRow = $row->toArray();
            $tempRow['subscription_status'] = $row->end_date >= now() ? 1 : 0;
            $tempRow['start_date'] = $row->start_date->format('d-m-Y H:i:s');
            $tempRow['end_date'] = $row->end_date->format('d-m-Y H:i:s');
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
}
