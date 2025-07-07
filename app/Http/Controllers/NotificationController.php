<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Customer;
use App\Models\Property;
use App\Models\Usertokens;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'notification')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $property_list = Property::where('status',1)->get();
            return view('notification.index', compact('property_list'));
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
        if (!has_permissions('create', 'notification')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $firebaseProjectId = system_setting('firebase_project_id');
            $firebaseServiceJsonFile = system_setting('firebase_service_json_file');
            if(empty($firebaseProjectId)){
                ResponseService::errorRedirectResponse(route('notification.index'),'Firebase Project ID is Missing');
            }else if(empty($firebaseServiceJsonFile)){
                ResponseService::errorRedirectResponse(route('notification.index'),'Firebase Service File is Missing');
            }else{
                $request->validate([
                    'file'      => 'image|mimes:jpeg,png,jpg',
                    'type'      => 'required',
                    'send_type' => 'required',
                    'user_id'   => 'required_if:send_type,==,0',
                    'title'     => 'required',
                    'message'   => 'required',
                ],
                [
                    'user_id.*' => trans('Select User From Table'),
                ]);

                $imageName = '';
                if ($request->hasFile('file')) {
                    $imageName = store_image($request->file('file'), 'NOTIFICATION_IMG_PATH');
                }

                // Get Customer ids who is active and has notification activated
                $customer_ids = Customer::where(['isActive' => '1','notification' => 1 ])->pluck('id');
                // Start Query for user token according to customer ids
                $userTokenQuery = Usertokens::whereIn('customer_id', $customer_ids);
                if ($request->send_type == 1) {
                    $user_id = '';
                    $fcm_ids = $userTokenQuery->clone()->pluck('fcm_id');
                } else {
                    $user_id = $request->user_id;
                    $fcm_ids = $userTokenQuery->clone()->where("customer_id", $user_id)->pluck('fcm_id');
                }
                $type = 0;
                if (isset($request->property)) {
                    $type = 2;
                    $propertys_id = $request->property;
                } else {
                    $type = $request->type;
                }
                Notifications::create([
                    'title' => $request->title,
                    'message' => $request->message,
                    'image' => $imageName,
                    'type' => $type,
                    'send_type' => $request->send_type,
                    'customers_id' => $user_id,
                    'propertys_id' => isset($propertys_id) ? $propertys_id : 0
                ]);

                $img = ($imageName != '') ? url('') . config('global.IMG_PATH') . config('global.NOTIFICATION_IMG_PATH') . $imageName : "";


                //START :: Send Notification To Customer
                if (collect($fcm_ids)->isNotEmpty()) {

                    $registrationIDs = array_filter($fcm_ids->toArray());

                    $fcmMsg = array(
                        'title' => $request->title,
                        'message' => $request->message,
                        "image" => $img,
                        'type' => 'default',
                        'body' => $request->message,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',

                    );
                    send_push_notification($registrationIDs, $fcmMsg);
                    //END ::  Send Notification To Customer
                }
                ResponseService::successRedirectResponse('Message Send Successfully');
            }
        }
    }
    public function destroy(Request $request)
    {
        if (env('DEMO_MODE') && Auth::user()->email != "superadmin@gmail.com") {
            return redirect()->back()->with('error', 'This is not allowed in the Demo Version');
        }
        if (has_permissions('delete', 'notifications')) {
            $id = $request->id;
            $image = $request->image;
            $destinationPath = public_path('images') . config('global.NOTIFICATION_IMG_PATH');
            if (Notifications::where('id', $id)->delete()) {
                if ($image != '') {
                    if (file_exists($destinationPath . $image)) {
                        unlink($destinationPath . $image);
                    }
                }
                ResponseService::successResponse("Data Deleted Successfully");
            } else {
                ResponseService::errorResponse('Something Went Wrong');
            }
        } else {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
    }
    public function notificationList(Request $request)
    {
        if (!has_permissions('read', 'notification')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $sql = Notifications::where('id', '!=', 0);

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql = $sql->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%")->orwhere('message', 'LIKE', "%$search%");
        }

        $total = $sql->count();

        $sql = $sql->orderBy($sort, $order)->skip($offset)->take($limit);

        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;
        $operate = '';
        foreach ($res as $row) {
            $tempRow = $row->toArray();

            if (has_permissions('delete', 'notification')) {
                $operate = '<a data-id=' . $row->id . ' data-image="' . $row->image . '" class="btn icon btn-danger btn-sm rounded-pill mt-2 delete-data" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" title="Delete"><i class="bi bi-trash"></i></a>';
            }
            $type = '';
            if ($row->type == 0) {
                $type = 'General Notification';
            }
            if ($row->type == 1) {
                $type = 'Inquiry Notification';
            }
            if ($row->type == 2) {
                $type = 'Property Notification';
            }
            $tempRow['count'] = $count;

            $tempRow['type'] = $type;
            $tempRow['send_type'] = ($row->send_type == 0) ? 'Selected' : 'All';

            $tempRow['created_at'] = $row->created_at->diffForHumans();

            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function multiple_delete(Request $request)
    {
        if (env('DEMO_MODE') && Auth::user()->email != "superadmin@gmail.com") {
            return redirect()->back()->with('error', 'This is not allowed in the Demo Version');
        }
        if (has_permissions('delete', 'notification')) {
            $id = $request->id;
            $res = Notifications::whereIn('id', explode(',', $id))->get();
            $destinationPath = public_path('images') . config('global.NOTIFICATION_IMG_PATH');
            foreach ($res as $row) {
                if ($row->image != '') {
                    if (file_exists($destinationPath . $row->image)) {
                        unlink($destinationPath . $row->image);
                    }
                }
            }
            if (Notifications::whereIn('id', explode(',', $id))->delete()) {
                ResponseService::successResponse("Data Deleted Successfully");
            } else {
                ResponseService::errorResponse('Something Went Wrong');
            }
        } else {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
    }
}
