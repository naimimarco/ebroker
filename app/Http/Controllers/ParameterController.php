<?php

namespace App\Http\Controllers;

use App\Models\parameter;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;

class ParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'facility')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('parameter.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!has_permissions('create', 'facility')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }

        $request->validate([
            'parameter' => 'required',
            'options'   => 'required',
            'image'     => 'required|mimes:svg|max:2048',
        ]);
        try {
            $opt_value = null;

            // Convert The option data to json encode
            if(isset($request->opt)){
                $opt_value = json_encode($request->opt, JSON_FORCE_OBJECT);
            }

            // Get and create if not there destination path of images to be stored
            $destinationPath = public_path('images') . config('global.PARAMETER_IMAGE_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Add Data to Database
            $parameter = new parameter();
            $parameter->name = $request->parameter;
            $parameter->type_of_parameter = $request->options;
            $parameter->is_required = $request->is_required ?? 0;
            $parameter->type_values = $opt_value;

            // Add Image if exists
            if ($request->hasFile('image')) {
                $parameter->image = store_image($request->file('image'), 'PARAMETER_IMAGE_PATH');
            }

            // Save data
            $parameter->save();
            ResponseService::successResponse('Parameter Successfully Added');
        } catch (Exception $e) {
            ResponseService::errorResponse("Something Went Wrong");
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
        if (!has_permissions('read', 'facility')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);

        $order = $request->input('order', 'ASC');
        $sort = 'id';

        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'type') {
                $sort = 'type_of_parameter';
            }
            if ($_GET['sort'] == 'value') {
                $sort = 'type_values';
            }
        }



        $sql = parameter::orderBy($sort, $order);


        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%");
        }
        $total = $sql->count();

        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }


        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';
        foreach ($res as $row) {

            $tempRow = $row->toArray();

            $tempRow['value'] = is_array($row->type_values) ? implode(',', $row->type_values) : $row->type_values;

            $tempRow['svg_clr'] = !empty(system_setting('svg_clr')) ? system_setting('svg_clr') : 0;

            if (has_permissions('update', 'facility')) {
                $operate = BootstrapTableService::editButton('', true);
                $tempRow['operate'] = $operate;
            }
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $request->validate([
            'edit_name' => 'required',
            'image' => 'mimes:svg'
        ]);

        if (!has_permissions('update', 'facility')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {


            // $opt_value = isset($request->edit_opt) && !empty($request->edit_opt) ? json_encode($request->edit_opt, JSON_FORCE_OBJECT) : NULL;


            $id =  $request->edit_id;

            $parameter = parameter::find($id);
            $parameter->name = ($request->edit_name) ? $request->edit_name : '';
            $parameter->is_required = (isset($request->edit_is_required) && !empty($request->edit_is_required)) ? 1 : 0;
            // $parameter->type_of_parameter = (isset($request->edit_options)) ? $request->edit_options : '';
            // $parameter->type_values = $opt_value;
            if ($request->hasFile('image')) {


                unlink_image($parameter->image);
                $parameter->image = store_image($request->file('image'), 'PARAMETER_IMAGE_PATH');
            }

            $parameter->update();

            ResponseService::successRedirectResponse('Parameter Updated Successfully');
        }
    }
}
