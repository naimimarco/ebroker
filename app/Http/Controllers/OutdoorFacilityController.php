<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OutdoorFacilities;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Auth;
use App\Services\BootstrapTableService;
use App\Models\AssignedOutdoorFacilities;

class OutdoorFacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'near_by_places')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('OutdoorFacilities.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!has_permissions('create', 'near_by_places')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }

        $request->validate([
            'image' => 'required|image|mimes:svg|max:2048', // Adjust max size as needed
        ], [
            'image.required' => 'The image field is required.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a SVG file.',
            'image.max' => 'The image size should not exceed 2MB.', // Adjust as needed
        ]);


        $destinationPath = public_path('images') . config('global.FACILITY_IMAGE_PATH');
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $facility = new OutdoorFacilities();
        $facility->name = $request->facility;
        if ($request->hasFile('image')) {
            $facility->image = \store_image($request->file('image'), 'FACILITY_IMAGE_PATH');
        } else {
            $facility->image  = '';
        }
        $facility->save();
        ResponseService::successRedirectResponse('Near by Place Added Successfully ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if (!has_permissions('read', 'near_by_places')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');

        $sql = OutdoorFacilities::orderBy($sort, $order);

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
            if (has_permissions('update', 'near_by_places')) {

                $operate = BootstrapTableService::editButton('', true, null, null, $row->id, null);
                $operate .= BootstrapTableService::deleteButton(route('outdoor_facilities.destroy', $row->id));
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
        if (!has_permissions('update', 'near_by_places')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {

            $request->validate([
                'image' => 'nullable|image|mimes:svg|max:2048', // Adjust max size as needed
            ], [
                'image.required' => 'The image field is required.',
                'image.image' => 'The uploaded file must be an image.',
                'image.mimes' => 'The image must be a SVG file.',
                'image.max' => 'The image size should not exceed 2MB.', // Adjust as needed
            ]);

            $id =  $request->edit_id;
            $facility = OutdoorFacilities::find($id);
            $facility->name = ($request->edit_name) ? $request->edit_name : '';

            if ($request->hasFile('image')) {


                if ($facility->image != '') {
                    unlink_image($facility->image);
                }
                $facility->image = \store_image($request->file('image'), 'FACILITY_IMAGE_PATH');
            }

            $facility->update();

            ResponseService::successRedirectResponse('Near by Place Updated Successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        if (env('DEMO_MODE') && Auth::user()->email != "superadmin@gmail.com") {
            return redirect()->back()->with('error', 'This is not allowed in the Demo Version');
        }

        if (!has_permissions('delete', 'near_by_places')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $facility = OutdoorFacilities::find($id);

            AssignedOutdoorFacilities::where('facility_id')->delete();
            if ($facility->delete()) {

                if ($facility->image != '') {
                    \unlink_image($facility->image);
                }
                ResponseService::successRedirectResponse('Facility Deleted Successfully');
            } else {
                ResponseService::errorRedirectResponse('Something Wrong');
            }
        }
    }
}
