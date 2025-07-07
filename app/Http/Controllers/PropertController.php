<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Customer;


use App\Models\Property;
use App\Models\CityImage;
use App\Models\parameter;
use App\Models\Usertokens;
use Illuminate\Support\Str;
use App\Models\RejectReason;
use Illuminate\Http\Request;

use App\Models\Notifications;
use App\Models\PropertyImages;
use App\Services\HelperService;
use App\Models\AssignParameters;
use App\Models\OutdoorFacilities;
use App\Services\ResponseService;
use App\Models\PropertiesDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\BootstrapTableService;
use App\Models\AssignedOutdoorFacilities;
use Illuminate\Support\Facades\Validator;


class PropertController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $customerID = $_GET['customer'] ?? null;
            $category = Category::all();
            return view('property.index', compact('category','customerID'));
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!has_permissions('create', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $category = Category::where('status', '1')->get();
            $parameters = parameter::all();
            $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();
            $facility = OutdoorFacilities::all();
            $distanceValueDB = system_setting('distance_option');
            $distanceValue = isset($distanceValueDB) && !empty($distanceValueDB) ? $distanceValueDB : 'km';
            return view('property.create', compact('category', 'parameters', 'currency_symbol', 'facility', 'distanceValue'));
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
        $arr = [];
        if (!has_permissions('create', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'slug'              => 'nullable|regex:/^[a-z0-9-]+$/|unique:propertys,slug_id',
                'gallery_images.*'  => 'required|image|mimes:jpg,png,jpeg|max:2048',
                'documents.*'       => 'nullable|mimes:pdf,doc,docx,txt|max:5120',
                'title_image'       => 'required|image|mimes:jpg,png,jpeg|max:2048',
                'meta_image'        => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
                'price'             => 'required|numeric|min:1|max:9223372036854775807',
                'video_link' => ['nullable', 'url', function ($attribute, $value, $fail) {
                    // Regular expression to validate YouTube URLs
                    $youtubePattern = '/^(https?\:\/\/)?(www\.youtube\.com|youtu\.be)\/.+$/';

                    if (!preg_match($youtubePattern, $value)) {
                        return $fail("The Video Link must be a valid YouTube URL.");
                    }

                    // Transform youtu.be short URL to full YouTube URL for validation
                    if (strpos($value, 'youtu.be') !== false) {
                        $value = 'https://www.youtube.com/watch?v=' . substr(parse_url($value, PHP_URL_PATH), 1);
                    }

                    // Get the headers of the URL
                    $headers = @get_headers($value);

                    // Check if the URL is accessible
                    if (!$headers || strpos($headers[0], '200') === false) {
                        return $fail("The Video Link must be accessible.");
                    }
                }]
            ], [], [
                'documents.*' => 'document :position'
            ]);

            try {
                DB::beginTransaction();

                $saveProperty = new Property();
                $saveProperty->category_id = $request->category;
                $saveProperty->title = $request->title;
                $saveProperty->slug_id = $request->slug ?? generateUniqueSlug($request->title,1);
                $saveProperty->description = $request->description;
                $saveProperty->address = $request->address;
                $saveProperty->client_address = $request->client_address;
                $saveProperty->propery_type = $request->property_type;
                $saveProperty->price = $request->price;
                $saveProperty->request_status = "approved";
                $saveProperty->package_id = 0;
                $saveProperty->city = (isset($request->city)) ? $request->city : '';
                $saveProperty->country = (isset($request->country)) ? $request->country : '';
                $saveProperty->state = (isset($request->state)) ? $request->state : '';
                $saveProperty->latitude = (isset($request->latitude)) ? $request->latitude : '';
                $saveProperty->longitude = (isset($request->longitude)) ? $request->longitude : '';
                $saveProperty->video_link = (isset($request->video_link)) ? $request->video_link : '';
                $saveProperty->post_type = 0;
                $saveProperty->added_by = 0;
                $saveProperty->meta_title = isset($request->meta_title) ? $request->meta_title : $request->title;
                $saveProperty->meta_description = $request->meta_description;
                $saveProperty->meta_keywords = $request->keywords;
                $saveProperty->rentduration = $request->price_duration;
                $saveProperty->is_premium = $request->is_premium;

                if ($request->hasFile('title_image')) {
                    $saveProperty->title_image = store_image($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
                } else {
                    $saveProperty->title_image  = '';
                }

                if ($request->hasFile('3d_image')) {
                    $saveProperty->three_d_image = store_image($request->file('3d_image'), '3D_IMG_PATH');
                } else {
                    $saveProperty->three_d_image  = '';
                }

                if ($request->hasFile('meta_image')) {
                    $saveProperty->meta_image = store_image($request->file('meta_image'), 'PROPERTY_SEO_IMG_PATH');
                }

                $saveProperty->save();

                $facility = OutdoorFacilities::all();
                foreach ($facility as $key => $value) {
                    if ($request->has('facility' . $value->id) && $request->input('facility' . $value->id) != '') {
                        $facilities = new AssignedOutdoorFacilities();
                        $facilities->facility_id = $value->id;
                        $facilities->property_id = $saveProperty->id;
                        $facilities->distance = $request->input('facility' . $value->id);
                        $facilities->save();
                    }
                }
                $parameters = parameter::all();
                foreach ($parameters as $par) {
                    if ($request->has('par_' . $par->id)) {
                        $assign_parameter = new AssignParameters();
                        $assign_parameter->parameter_id = $par->id;
                        if (($request->hasFile('par_' . $par->id))) {
                            $destinationPath = public_path('images') . config('global.PARAMETER_IMG_PATH');
                            if (!is_dir($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $imageName = microtime(true) . "." . ($request->file('par_' . $par->id))->getClientOriginalExtension();
                            ($request->file('par_' . $par->id))->move($destinationPath, $imageName);
                            $assign_parameter->value = $imageName;
                        } else {
                            $assign_parameter->value = is_array($request->input('par_' . $par->id)) ? json_encode($request->input('par_' . $par->id), JSON_FORCE_OBJECT) : ($request->input('par_' . $par->id));
                        }
                        $assign_parameter->modal()->associate($saveProperty);
                        $assign_parameter->save();
                        $arr = $arr + [$par->id => $request->input('par_' . $par->id)];
                    }
                }

                /// START :: UPLOAD GALLERY IMAGE
                $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $saveProperty->id;
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                if ($request->hasfile('gallery_images')) {
                    foreach ($request->file('gallery_images') as $file) {
                        $name = microtime(true) . '.' . $file->extension();
                        $file->move($destinationPath, $name);
                        PropertyImages::create([
                            'image' => $name,
                            'propertys_id' => $saveProperty->id
                        ]);
                    }
                }
                /// END :: UPLOAD GALLERY IMAGE


                /// START :: UPLOAD DOCUMENT
                $destinationPath = public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . "/" . $saveProperty->id;
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                if ($request->hasFile('documents')) {
                    $documentsData = array();
                    foreach ($request->file('documents') as $file) {
                        $type = $file->extension();
                        $name = microtime(true). '.' . $type;
                        $file->move($destinationPath, $name);

                        $documentsData[] = array(
                            'property_id'   => $saveProperty->id,
                            'name'          => $name,
                            'type'          => $type
                        );
                    }

                    if(collect($documentsData)->isNotEmpty()){
                        PropertiesDocument::insert($documentsData);
                    }
                }
                /// END :: UPLOAD DOCUMENT

                // START :: ADD CITY DATA
                if(isset($request->city) && !empty($request->city)){
                    CityImage::updateOrCreate(array('city' => $request->city));
                }
                // END :: ADD CITY DATA

                DB::commit();
                ResponseService::successRedirectResponse('Data Created Successfully');
            } catch (Exception $e) {
                DB::rollBack();
                ResponseService::logErrorRedirectResponse($e,"Create Property Issue");
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!has_permissions('update', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $category = Category::all()->where('status', '1')->mapWithKeys(function ($item, $key) {
                return [$item['id'] => $item['category']];
            });
            $category = Category::where('status', '1')->get();
            $list = Property::with(['assignParameter' => function($q){
                $q->with('parameter:id,name,type_of_parameter,type_values,is_required,image')->select('id','modal_type','modal_id','property_id','parameter_id','value');
            }])->where('id', $id)->get()->first();

            $categoryData = Category::find($list->category_id);

            $categoryParameterTypeIds = explode(',', $categoryData['parameter_types']);

            $parameters = parameter::all();
            $edit_parameters = parameter::with(['assigned_parameter' => function ($q) use ($id) {
                $q->where('modal_id', $id)->select('id','modal_type','modal_id','property_id','parameter_id','value');
            }])->whereIn('id',$categoryParameterTypeIds)->get();

            // Sort the collection by the order of IDs in $categoryParameterTypeIds.
            $edit_parameters = $edit_parameters->sortBy(function ($parameter) use ($categoryParameterTypeIds) {
                return array_search($parameter->id, $categoryParameterTypeIds);
            });

            // Reset the keys on the sorted collection.
            $edit_parameters = $edit_parameters->values();




            $facility = OutdoorFacilities::with(['assign_facilities' => function ($q) use ($id) {
                $q->where('property_id', $id)->select('id','property_id','facility_id','distance');
            }])->get();

            $assignFacility = AssignedOutdoorFacilities::where('property_id', $id)->get();

            $arr = json_decode($list->carpet_area);
            $par_arr = [];
            $par_id = [];
            $type_arr = [];
            foreach ($list->assignParameter as  $par) {
                $par_arr = $par_arr + [$par->parameter->name => $par->value];
                $par_id = $par_id + [$par->parameter->name => $par->value];
            }
            $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();
            $distanceValueDB = system_setting('distance_option');
            $distanceValue = isset($distanceValueDB) && !empty($distanceValueDB) ? $distanceValueDB : 'km';
            return view('property.edit', compact('category', 'facility', 'assignFacility', 'edit_parameters', 'list', 'id', 'par_arr', 'parameters', 'par_id', 'currency_symbol','distanceValue'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!has_permissions('update', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'slug'              => 'nullable|regex:/^[a-z0-9-]+$/|unique:propertys,slug_id,'.$id.',id',
                'gallery_images.*'  => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
                'documents.*'       => 'nullable|mimes:pdf,doc,docx,txt|max:5120',
                'title_image'       => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
                'meta_image'        => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
                'price'             => 'required|numeric|min:1|max:9223372036854775807',
                'video_link' => ['nullable', 'url', function ($attribute, $value, $fail) {
                    // Regular expression to validate YouTube URLs
                    $youtubePattern = '/^(https?\:\/\/)?(www\.youtube\.com|youtu\.be)\/.+$/';

                    if (!preg_match($youtubePattern, $value)) {
                        return $fail("The Video Link must be a valid YouTube URL.");
                    }

                    // Transform youtu.be short URL to full YouTube URL for validation
                    if (strpos($value, 'youtu.be') !== false) {
                        $value = 'https://www.youtube.com/watch?v=' . substr(parse_url($value, PHP_URL_PATH), 1);
                    }

                    // Get the headers of the URL
                    $headers = @get_headers($value);

                    // Check if the URL is accessible
                    if (!$headers || strpos($headers[0], '200') === false) {
                        return $fail("The Video Link must be accessible.");
                    }
                }]
            ],[], [
                'documents.*' => 'document :position'
            ]);

            try{

                DB::beginTransaction();
                $UpdateProperty = Property::with('assignparameter.parameter')->find($id);
                $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $UpdateProperty->category_id = $request->category;
                $UpdateProperty->title = $request->title;
                $UpdateProperty->slug_id = $request->slug ?? generateUniqueSlug($request->title,1,null,$id);
                $UpdateProperty->description = $request->description;
                $UpdateProperty->address = $request->address;
                $UpdateProperty->client_address = $request->client_address;
                $UpdateProperty->propery_type = $request->property_type;
                $UpdateProperty->price = $request->price;
                $UpdateProperty->propery_type = $request->property_type;
                $UpdateProperty->price = $request->price;
                $UpdateProperty->state = (isset($request->state)) ? $request->state : '';
                $UpdateProperty->country = (isset($request->country)) ? $request->country : '';
                $UpdateProperty->city = (isset($request->city)) ? $request->city : '';
                $UpdateProperty->latitude = (isset($request->latitude)) ? $request->latitude : '';
                $UpdateProperty->longitude = (isset($request->longitude)) ? $request->longitude : '';
                $UpdateProperty->video_link = (isset($request->video_link)) ? $request->video_link : '';
                $UpdateProperty->is_premium = $request->is_premium;
                $UpdateProperty->meta_title = (isset($request->edit_meta_title)) ? $request->edit_meta_title : '';
                $UpdateProperty->meta_description = (isset($request->edit_meta_description)) ? $request->edit_meta_description : '';
                $UpdateProperty->meta_keywords = (isset($request->Keywords)) ? $request->Keywords : '';

                $UpdateProperty->rentduration = $request->price_duration;
                if ($request->hasFile('title_image')) {
                    \unlink_image($UpdateProperty->title_image);
                    $UpdateProperty->title_image = \store_image($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
                }

                if ($request->hasFile('3d_image')) {
                    \unlink_image($UpdateProperty->three_d_image);
                    $UpdateProperty->three_d_image = \store_image($request->file('3d_image'), '3D_IMG_PATH');
                }

                if ($request->hasFile('meta_image')) {
                    \unlink_image($UpdateProperty->meta_image);
                    $UpdateProperty->meta_image = \store_image($request->file('meta_image'), 'PROPERTY_SEO_IMG_PATH');
                }

                $UpdateProperty->update();
                AssignedOutdoorFacilities::where('property_id', $UpdateProperty->id)->delete();
                $facility = OutdoorFacilities::all();
                foreach ($facility as $key => $value) {
                    if ($request->has('facility' . $value->id) && $request->input('facility' . $value->id) != '') {
                        $facilities = new AssignedOutdoorFacilities();
                        $facilities->facility_id = $value->id;
                        $facilities->property_id = $UpdateProperty->id;
                        $facilities->distance = $request->input('facility' . $value->id);
                        $facilities->save();
                    }
                }
                $parameters = parameter::all();

                AssignParameters::where('modal_id', $id)->delete();
                foreach ($parameters as $par) {
                    if ($request->has('par_' . $par->id)) {
                        $update_parameter = new AssignParameters();
                        $update_parameter->parameter_id = $par->id;
                        if (($request->hasFile('par_' . $par->id))) {
                            $update_parameter->value = \store_image($request->file('par_' . $par->id), 'PARAMETER_IMG_PATH');
                        } else {
                            $update_parameter->value = is_array($request->input('par_' . $par->id)) || $request->input('par_' . $par->id) == null ? json_encode($request->input('par_' . $par->id), JSON_FORCE_OBJECT) : ($request->input('par_' . $par->id));
                        }
                        $update_parameter->modal()->associate($UpdateProperty);
                        $update_parameter->save();
                    }
                }

                /// START :: UPLOAD GALLERY IMAGE
                $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
                if (!is_dir($FolderPath)) {
                    mkdir($FolderPath, 0777, true);
                }

                $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $UpdateProperty->id;

                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                if ($request->hasfile('gallery_images')) {
                    foreach ($request->file('gallery_images') as $file) {
                        $name = microtime(true) . '.' . $file->extension();
                        $file->move($destinationPath, $name);

                        PropertyImages::create([
                            'image' => $name,
                            'propertys_id' => $UpdateProperty->id
                        ]);
                    }
                }
                /// END :: UPLOAD GALLERY IMAGE


                /// START :: UPLOAD DOCUMENT
                $destinationPath = public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . "/" . $UpdateProperty->id;
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                if ($request->hasFile('documents')) {
                    $documentsData = array();
                    foreach ($request->file('documents') as $file) {
                        $type = $file->extension();
                        $name = microtime(true). '.' . $type;
                        $file->move($destinationPath, $name);

                        $documentsData[] = array(
                            'property_id'   => $UpdateProperty->id,
                            'name'          => $name,
                            'type'          => $type
                        );
                    }

                    if(collect($documentsData)->isNotEmpty()){
                        PropertiesDocument::insert($documentsData);
                    }
                }
                /// END :: UPLOAD DOCUMENT

                // START :: ADD CITY DATA
                if(isset($request->city) && !empty($request->city)){
                    CityImage::updateOrCreate(array('city' => $request->city));
                }
                // END :: ADD CITY DATA

                DB::commit();
                ResponseService::successRedirectResponse('Data Updated Successfully');
            } catch (Exception $e) {
                DB::rollBack();
                ResponseService::logErrorRedirectResponse($e,"Update Property Issue");
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
        if (!has_permissions('delete', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            DB::beginTransaction();
            $property = Property::find($id);

            if ($property->delete()) {
                DB::commit();
                ResponseService::successRedirectResponse('Data Deleted Successfully');
            } else {
                DB::rollBack();
                ResponseService::errorRedirectResponse('Something Wrong');
            }
        }
    }



    public function getPropertyList(Request $request)
    {

        $offset = (int) $request->input('offset', 0); // Ensure integer for pagination
        $limit = (int) $request->input('limit', 10);   // Ensure integer for pagination
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $customerID = $request->input('customerID', null);

        $sql = Property::with('category')
            ->with('customer:id,name,mobile')
            ->with('assignParameter.parameter')
            ->with('interested_users')
            ->with('advertisement')
            ->orderBy($sort, $order);

        $searchQuery = null;
        $propertyType = null;
        $status = null;
        $categoryId = null;
        $propertyAddedBy = null;

        // Extract and validate filters
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchQuery = trim($_GET['search']);  // Trim whitespace
        }

        if (isset($_GET['property_type']) && $_GET['property_type'] !== "") {
            $propertyType = $_GET['property_type'];
        }

        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $status = $_GET['status'];
        }

        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $categoryId = (int) $_GET['category']; // Ensure integer for category ID
        }

        if (isset($_GET['property_added_by']) && $_GET['property_added_by'] !== '') {
            $propertyAddedBy = $_GET['property_added_by']; // Ensure integer for category ID
        }
        if (isset($_GET['property_accessibility']) && $_GET['property_accessibility'] !== '') {
            $propertyAccessibility = $_GET['property_accessibility']; // Ensure integer for category ID
        }

        // Apply filters with proper escaping for security
        if ($searchQuery !== null) {
            $sql = $sql->where(function ($query) use ($searchQuery) {
                $query->where('id', 'LIKE', "%$searchQuery%")->orwhere('title', 'LIKE', "%$searchQuery%")->orwhere('address', 'LIKE', "%$searchQuery%");
                $query->orWhereHas('category', function ($query) use ($searchQuery) {
                    $query->where('category', 'LIKE', "%$searchQuery%");
                })->orWhereHas('customer', function ($query) use ($searchQuery) {
                    $query->where('name', 'LIKE', "%$searchQuery%")->orwhere('email', 'LIKE', "%$searchQuery%");
                });
            });
        }

        if ($propertyType !== null) {
            $sql = $sql->where('propery_type', $propertyType);
        }

        if (!empty($customerID)) {
            $sql = $sql->where('added_by',$customerID);
        }

        if ($status !== null) {
            $sql = $sql->where('status', $status);
        }

        if ($categoryId !== null) {
            $sql = $sql->where('category_id', $categoryId);
        }

        if ($propertyAddedBy !== null) {
            if($propertyAddedBy == 0){
                $sql = $sql->where('added_by', 0);
            }else{
                $sql = $sql->whereNot('added_by', 0);
            }
        }
        if (isset($propertyAccessibility) && $propertyAccessibility !== null) {
            if($propertyAccessibility == 1){
                $sql = $sql->where('is_premium', 1);
            }else{
                $sql = $sql->where('is_premium', 0);
            }
        }

        $total = $sql->count();

        if (isset($limit)) {
            $sql = $sql->skip($offset)->take($limit);
        }

        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;

        $operate = '';
        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

        foreach ($res as $row) {
            $tempRow = $row->toArray();
            $tempRow['property_type_raw'] = $row->getRawOriginal('propery_type');

            if($row->added_by == 0){
                if (has_permissions('update', 'property')) {
                    $operate = BootstrapTableService::editButton(route('property.edit', $row->id), false);
                }
                if (has_permissions('delete', 'property')) {
                    $operate .= BootstrapTableService::deleteButton(route('property.destroy', $row->id));
                }
            }else{
                if (has_permissions('update', 'property')) {
                    $requestStatusButtonCustomClasses = ["btn","icon","btn-warning","btn-sm","rounded-pill","request-status-btn"];
                    $requestStatusButtonCustomAttributes = ["id" => $row->id, "title" => trans('Change Status'), "data-toggle" => "modal", "data-bs-target" => "#changeRequestStatusModal", "data-bs-toggle" => "modal"];
                    $operate = BootstrapTableService::button('fa fa-exclamation-circle', '',$requestStatusButtonCustomClasses,$requestStatusButtonCustomAttributes);
                }
                if (has_permissions('delete', 'property')) {
                    $operate .= BootstrapTableService::deleteButton(route('property.destroy', $row->id));
                }
            }
            $onlyDeleteOperate = BootstrapTableService::deleteButton(route('property.destroy', $row->id));

            $interested_users = array();
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    array_push($interested_users, $interested_user->customer_id);
                }
            }

            $price = null;
            if(!empty($row->propery_type) && $row->getRawOriginal('propery_type') == 1){
                $price = !empty($row->rentduration) ?  $currency_symbol . '' . $row->price . '/' . $row->rentduration : $row->price;
            }else{
                $price = $currency_symbol . '' .$row->price;
            }

            $tempRow['total_interested_users'] = count($interested_users);
            $tempRow['promoted'] = $row->is_promoted;
            if($row->added_by == 0 && $row->request_status == "approved"){
                $tempRow['edit_status'] = $row->status;
            }else{
                $tempRow['edit_status'] = null;
            }
            $tempRow['edit_status_url'] = $row->added_by == 0 && $row->request_status == "approved" ? 'updatepropertystatus' : null;
            $tempRow['price'] = $price;
            $featured = count($row->advertisement) ? '<div class="featured_tag"><div class="featured_lable">Featured</div></div>' : '';
            $tempRow['Property_name'] = '<div class="propetrty_name d-flex"><img class="property_image" alt="" src="' . $row->title_image . '"><div class="property_detail"><div class="property_title">' . $row->title . '</div>' . $featured . '</div></div></div>';

            if ($row->added_by != 0) {
                $tempRow['added_by'] = $row->customer->name;
                $tempRow['mobile'] = (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( $row->customer->mobile ) : '****************************' ) : ( $row->customer->mobile ));
            }
            if ($row->added_by == 0) {
                $mobile = Setting::where('type', 'company_tel1')->pluck('data');
                $tempRow['added_by'] = trans('Admin');
                $tempRow['mobile'] =   $mobile[0];
            }
            $tempRow['customer_ids'] = $interested_users;

            // Interested Users
            $count = "  " . count($interested_users);
            $interestedUserButton = BootstrapTableService::editButton('', true, null, 'text-secondary', $row->id, null, '', 'bi bi-eye-fill edit_icon', $count);
            $tempRow['interested_users'] = $interestedUserButton;
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    $tempRow['interested_users_details'] = Customer::Where('id', $interested_user->customer_id)->get()->toArray();
                }
            }

            // Gallery Images
            $galleryButtonCustomClasses = ["btn","icon","btn-primary","btn-sm","rounded-pill","gallery-image-btn"];
            $galleryButtonCustomAttributes = ["id" => $row->id, "title" => trans('Gallery Images'), "data-toggle" => "modal", "data-bs-target" => "#galleryImagesModal", "data-bs-toggle" => "modal"];
            $galleryImagesCount = count($row->gallery);
            $galleryImagesButton = BootstrapTableService::button('bi bi-eye-fill ml-2', '',$galleryButtonCustomClasses,$galleryButtonCustomAttributes,$galleryImagesCount);
            $tempRow['gallery-images-btn'] = $galleryImagesButton;


            // Documents
            $documentsButtonCustomClasses = ["btn","icon","btn-primary","btn-sm","rounded-pill","documents-btn"];
            $documentsButtonCustomAttributes = ["id" => $row->id, "title" => trans('Documents'), "data-toggle" => "modal", "data-bs-target" => "#documentsModal", "data-bs-toggle" => "modal"];
            $documentsCount = count($row->documents);
            $documentsButton = BootstrapTableService::button('bi bi-eye-fill', '',$documentsButtonCustomClasses,$documentsButtonCustomAttributes,$documentsCount);
            $tempRow['documents-btn'] = $documentsButton;


            $tempRow['operate'] = $operate;
            $tempRow['only_delete_operate'] = $onlyDeleteOperate;
            $rows[] = $tempRow;
            $count++;
        }
        // $cities =  json_decode(file_get_contents(public_path('json') . "/cities.json"), true);

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    public function updateStatus(Request $request)
    {
        if (!has_permissions('update', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            Property::where('id', $request->id)->update(['status' => $request->status]);
            $Property = Property::with('customer')->find($request->id);

            if (!empty($Property->customer)) {
                if ($Property->customer->isActive == 1 && $Property->customer->notification == 1) {

                    $fcm_ids = array();
                    $user_token = Usertokens::where('customer_id', $Property->customer->id)->pluck('fcm_id')->toArray();
                    $fcm_ids[] = !empty($user_token) ? $user_token : array();

                    $msg = "";
                    if (!empty($fcm_ids)) {
                        $msg = $Property->status == 1 ? 'Activated now by Administrator ' : 'Deactivated now by Administrator ';
                        $registrationIDs = $fcm_ids[0];

                        $fcmMsg = array(
                            'title' =>  $Property->name . 'Property Updated',
                            'message' => 'Your Property Post ' . $msg,
                            'type' => 'property_inquiry',
                            'body' => 'Your Property Post ' . $msg,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default',
                            'id' => (string)$Property->id,

                        );
                        send_push_notification($registrationIDs, $fcmMsg);
                    }
                    //END ::  Send Notification To Customer

                    Notifications::create([
                        'title' => $Property->name . 'Property Updated',
                        'message' => 'Your Property Post ' . $msg,
                        'image' => '',
                        'type' => '1',
                        'send_type' => '0',
                        'customers_id' => $Property->customer->id,
                        'propertys_id' => $Property->id
                    ]);
                }
            }
            $response['error'] = false;
            ResponseService::successResponse($request->status ? "Property Activated Successfully" : "Property Deactivated Successfully");
        }
    }


    public function removeGalleryImage(Request $request)
    {

        if (!has_permissions('delete', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $id = $request->id;

            $getImage = PropertyImages::where('id', $id)->first();


            $image = $getImage->image;
            $propertys_id =  $getImage->propertys_id;

            if (PropertyImages::where('id', $id)->delete()) {
                if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image)) {
                    unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image);
                }
                $response['error'] = false;
            } else {
                $response['error'] = true;
            }

            $countImage = PropertyImages::where('propertys_id', $propertys_id)->get();
            if ($countImage->count() == 0) {
                rmdir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id);
            }
            return response()->json($response);
        }
    }



    public function getFeaturedPropertyList()
    {

        $offset = 0;
        $limit = 4;
        $sort = 'id';
        $order = 'DESC';

        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }

        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }

        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }

        $sql = Property::with('category')->with('customer')->whereHas('advertisement')->orderBy($sort, $order);

        $sql->skip($offset)->take($limit);

        $res = $sql->get();

        $bulkData = array();

        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';

        foreach ($res as $row) {

            if (count($row->advertisement)) {
                if (has_permissions('update', 'property') && $row->added_by == 0) {
                    $operate = '<a  href="' . route('property.edit', $row->id) . '"  class="btn icon btn-primary btn-sm rounded-pill mt-2" id="edit_btn" title="Edit"><i class="fa fa-edit edit_icon"></i></a>';
                }else{
                    $operate = "-";
                }
                $tempRow = $row->toArray();
                $tempRow['type'] = ucfirst($row->propery_type);
                if($row->added_by == 0 && $row->request_status == "approved"){
                    $tempRow['status'] = $row->status;
                }else{
                    $tempRow['status'] = null;
                }
                $tempRow['edit_status_url'] = 'updatepropertystatus';
                $tempRow['promoted'] = $row->is_promoted;
                $tempRow['operate'] = $operate;
                $rows[] = $tempRow;
                $count++;
            }
        }
        $total = $sql->count();
        $bulkData['total'] = $total;
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }
    public function updateaccessability(Request $request)
    {
        if (!has_permissions('update', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            Property::where('id', $request->id)->update(['is_premium' => $request->status]);
            ResponseService::successResponse("Data Updated Successfully");
        }
    }

    public function generateAndCheckSlug(Request $request){
        // Validation
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }

        // Generate the slug or throw exception
        try {
            $title = $request->title;
            $id = $request->has('id') && !empty($request->id) ? $request->id : null;
            if($id){
                $slug = generateUniqueSlug($title,1,null,$id);
            }else{
                $slug = generateUniqueSlug($title,1);
            }
            ResponseService::successResponse("",$slug);
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e, "Property Slug Generation Error", "Something Went Wrong");
        }
    }



    public function removeDocument(Request $request)
    {

        if (!has_permissions('delete', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $id = $request->id;
            $getDocument = PropertiesDocument::where('id', $id)->first();
            if($getDocument){
                $file = $getDocument->getRawOriginal('name');
                $propertyId =  $getDocument->property_id;

                if (PropertiesDocument::where('id', $id)->delete()) {
                    if (file_exists(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $propertyId . "/" . $file)) {
                        unlink(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $propertyId . "/" . $file);
                    }
                    $response['error'] = false;
                } else {
                    $response['error'] = true;
                }

                $countImage = PropertiesDocument::where('property_id', $propertyId)->get();
                if ($countImage->count() == 0) {
                    rmdir(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $propertyId);
                }
                return response()->json($response);
            }
        }
    }


    public function removeThreeDImage($id,Request $request){
        if (!has_permissions('delete', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            try {
                $propertyData = Property::findOrFail($id);
                unlink_image($propertyData->three_d_image);
                $propertyData->three_d_image = null;
                $propertyData->save();
                ResponseService::successResponse("Data Deleted Successfully");
            } catch (Exception $e) {
                ResponseService::logErrorResponse($e, "Remove ThreeD Image Error", "Something Went Wrong");
            }
        }
    }

    public function updateRequestStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_status' => 'required|in:approved,rejected',
            'reject_reason' => 'required_if:request_status,rejected|max:300'
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            if (!has_permissions('update', 'property')) {
                ResponseService::errorResponse(PERMISSION_ERROR_MSG);
            } else {
                if($request->request_status == "rejected"){
                    RejectReason::create(array(
                        'property_id' => $request->id,
                        'reason' => $request->reject_reason
                    ));
                    $status = 0;
                }else{
                    $status = 1;
                }
                Property::where('id', $request->id)->update(['request_status' => $request->request_status, 'status' => $status]);
                DB::commit();

                // Send mail for property status
                try {
                    $propertyData = Property::where('id',$request->id)->select('id','title','request_status','added_by')->with('customer:id,name,email')->firstOrFail();
                    if(!empty($propertyData->customer->email)){
                        // Get Data of email type
                        $emailTypeData = HelperService::getEmailTemplatesTypes("property_status");

                        // Email Template
                        $propertyStatusTemplateData = system_setting($emailTypeData['type']);
                        $appName = env("APP_NAME") ?? "eBroker";
                        $variables = array(
                            'app_name' => $appName,
                            'user_name' => $propertyData->customer->name,
                            'property_name' => $propertyData->title,
                            'status' => $request->request_status,
                            'reject_reason' => $request->request_status == 'rejected' ? $request->reject_reason : null,
                            'email' => $propertyData->customer->email
                        );
                        if(empty($propertyStatusTemplateData)){
                            $propertyStatusTemplateData = "Property Status have been changed";
                        }
                        $propertyStatusTemplate = HelperService::replaceEmailVariables($propertyStatusTemplateData,$variables);

                        $data = array(
                            'email_template' => $propertyStatusTemplate,
                            'email' => $propertyData->customer->email,
                            'title' => $emailTypeData['title'],
                        );
                        HelperService::sendMail($data);
                    }
                } catch (Exception $e) {
                    Log::error("Something Went Wrong in Property Status Update Mail Sending");
                }



                // Send Notification
                $property = Property::with('customer:id,name,isActive,notification')->select('id','title','request_status','added_by')->find($request->id);
                $fcm_ids = array();
                if ($property->customer->isActive == 1 && $property->customer->notification == 1) {
                    $user_token = Usertokens::where('customer_id', $property->customer->id)->pluck('fcm_id')->toArray();
                }

                $fcm_ids[] = $user_token ?? array();

                $msg = "";
                if (!empty($fcm_ids)) {
                    $msg = $property->request_status == 'approved' ? 'Approved by Administrator ' : 'Rejected by Administrator ';
                    $registrationIDs = $fcm_ids[0];

                    $fcmMsg = array(
                        'title' =>  $property->title . 'Property Updated',
                        'message' => 'Your Property Post ' . $msg,
                        'type' => 'property_inquiry',
                        'body' => 'Your Property Post ' . $msg,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                        'id' => (string)$property->id,

                    );
                    send_push_notification($registrationIDs, $fcmMsg);
                }
                //END ::  Send Notification To Customer

                Notifications::create([
                    'title' => $property->name . 'Property Updated',
                    'message' => 'Your Property Post ' . $msg,
                    'image' => '',
                    'type' => '1',
                    'send_type' => '0',
                    'customers_id' => $property->customer->id,
                    'propertys_id' => $property->id
                ]);
                ResponseService::successResponse("Data Updated Successfully");
            }
        } catch (Exception $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Update Request Status in Property", "Something Went Wrong");
        }
    }
}
