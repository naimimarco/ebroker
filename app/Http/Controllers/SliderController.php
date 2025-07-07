<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Slider;
use Spatie\Image\Image;
use App\Models\Category;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'slider')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $slider = Slider::select('id', 'image', 'sequence')->orderBy('sequence', 'ASC')->get();
            $categories = Category::select('id', 'category')->where('status', 1)->orderBy('id','DESC')->get();
            $properties = Property::select('id','title', 'category_id')->where('status',1)->orderBy('id','DESC')->get();
            return view('slider.index', compact('slider', 'categories', 'properties'));
        }
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!has_permissions('create', 'slider')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'type'      => 'required|in:1,2,3,4',
                'image'         => 'required|mimes:jpg,png,jpeg|max:2048',
                'web_image'     => 'required|mimes:jpg,png,jpeg|max:2048',
                'category'  => 'nullable|required_if:type,2',
                'property'  => 'nullable|required_if:type,3',
                'link'      => 'nullable|required_if:type,4'
            ]);

            $destinationPath = public_path('images') . config('global.SLIDER_IMG_PATH');

            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $appImageName = null;
            if ($request->hasFile('image')) {
                $appImageName = store_image($request->file('image'), 'SLIDER_IMG_PATH');
            }
            $webImageName = null;
            if ($request->hasFile('web_image')) {
                $webImageName = store_image($request->file('web_image'), 'SLIDER_IMG_PATH');
            }
            Slider::create([
                'type'                  => $request->type,
                'image'                 => $appImageName,
                'web_image'             => $webImageName,
                'category_id'           => (isset($request->category)) ? $request->category : null,
                'propertys_id'          => (isset($request->property)) ? $request->property : null,
                'show_property_details' => (isset($request->show_property_details) && $request->show_property_details) ? 1 : 0,
                'link'                  => (isset($request->link)) ? $request->link : null,
            ]);
            ResponseService::successRedirectResponse('Data Created Successfully');
        }
    }




    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        if (!has_permissions('update', 'slider')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $validator = Validator::make($request->all(), [
                'edit_image' => 'nullable|mimes:jpg,png,jpeg|max:2048',
            ]);
            if ($validator->fails()) {
                ResponseService::validationError($validator->errors()->first());
            }
            try {
                // Get Slider Data
                $sliderData = Slider::where('id', $id)->first();

                // Update App Image
                if($request->has('edit_image')){

                    // Get Image Raw Name
                    $image = $sliderData->getRawOriginal('image');

                    if(!empty($image)){
                        // Check the file exists and delete if exists
                        if (file_exists(public_path('images') . config('global.SLIDER_IMG_PATH') . $image)) {
                            unlink(public_path('images') . config('global.SLIDER_IMG_PATH') . $image);
                        }
                    }

                    // Upload new file and save the name in database
                    $editAppImageName = store_image($request->file('edit_image'), 'SLIDER_IMG_PATH');
                    Slider::where('id',$id)->update(array('image' => $editAppImageName));
                }

                // Update Web Image
                if($request->has('edit_web_image')){

                    // Get Image Raw Name
                    $image = $sliderData->getRawOriginal('web_image');

                    if(!empty($image)){
                        // Check the file exists and delete if exists
                        if (file_exists(public_path('images') . config('global.SLIDER_IMG_PATH') . $image)) {
                            unlink(public_path('images') . config('global.SLIDER_IMG_PATH') . $image);
                        }
                    }

                    // Upload new file and save the name in database
                    $editWebImageName = store_image($request->file('edit_web_image'), 'SLIDER_IMG_PATH');
                    Slider::where('id',$id)->update(array('web_image' => $editWebImageName));
                }

                ResponseService::successResponse(trans('Data Updated Successfully'));
            } catch (Exception $e) {
                ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
            }
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
        if (!has_permissions('delete', 'slider')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            try {
                DB::beginTransaction();
                $slider = Slider::find($id);
                if ($slider) {
                    $slider->delete();
                }
                DB::commit();
                ResponseService::successRedirectResponse('Data Deleted Successfully');
            } catch (Exception $th) {
                DB::rollback();
                ResponseService::errorRedirectResponse(null, 'something is wrong !!!');
            }
        }
    }

    public function sliderList(Request $request)
    {
        if (!has_permissions('read', 'slider')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');

        $sql = Slider::with('category:id,category','property:id,title,title_image')->orderBy($sort, $order);
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql = $sql->where('id', 'LIKE', "%$search%")->orWhere('link', 'LIKE', "%$search%")->orWhereHas('category', function ($query) use ($search) {
                $query->where('category', 'LIKE', "%$search%");
            })->orWhereHas('property', function ($query) use ($search) {
                $query->where('title', 'LIKE', "%$search%");
            });
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

        foreach ($res as $row) {
            $operate = BootstrapTableService::editButton(route('slider.update',$row->id),true,null,null,$row->id);
            if($row->default_data == 0){
                $operate .= BootstrapTableService::deleteButton(route('slider.destroy', $row->id));
            }
            $tempRow = $row->toArray();
            $tempRow['type'] = trans($row->type);
            $tempRow['image_exists'] = !empty($row->getRawOriginal('image')) ? (file_exists(public_path('images') . config('global.SLIDER_IMG_PATH') . $row->getRawOriginal('image'))) : false;
            $tempRow['web_image_exists'] = !empty($row->getRawOriginal('web_image')) ? file_exists(public_path('images') . config('global.SLIDER_IMG_PATH') . $row->getRawOriginal('web_image')) : false;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
}
