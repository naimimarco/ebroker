<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Customer;
use App\Models\Usertokens;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\InterestedUser;
use App\Services\HelperService;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'customer')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('customer.index');
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
        if (!has_permissions('update', 'customer')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            Customer::where('id', $request->id)->update(['isActive' => $request->status]);

            // Send mail for user status
            try {
                $customerData = Customer::where('id',$request->id)->select('id','name','email','isActive')->first();

                if($customerData->email){
                    // Get Data of email type
                    $emailTypeData = HelperService::getEmailTemplatesTypes("user_status");

                    // Email Template
                    $propertyFeatureStatusTemplateData = system_setting($emailTypeData['type']);
                    $appName = env("APP_NAME") ?? "eBroker";
                    $variables = array(
                        'app_name' => $appName,
                        'user_name' => $customerData->name,
                        'status' => $customerData->isActive == 1 ? 'Activated' : 'Deactivated' ,
                        'email' => $customerData->email
                    );
                    if(empty($propertyFeatureStatusTemplateData)){
                        $propertyFeatureStatusTemplateData = "Your Property :- ".$variables['propertyName']."'s feature status ".$variables['status'];
                    }
                    $propertyFeatureStatusTemplate = HelperService::replaceEmailVariables($propertyFeatureStatusTemplateData,$variables);

                    $data = array(
                        'email_template' => $propertyFeatureStatusTemplate,
                        'email' =>$customerData->email,
                        'title' => $emailTypeData['title'],
                    );
                    HelperService::sendMail($data);
                }
            } catch (Exception $e) {
                Log::error("Something Went Wrong in Customer Status Update Mail Sending");
            }

            /** Notification */
            $fcm_ids = array();

            $customer_id = Customer::where(['id' => $request->id,'notification' => 1])->count();
            if ($customer_id) {
                $user_token = Usertokens::where('customer_id', $request->id)->pluck('fcm_id')->toArray();
                $fcm_ids[] = $user_token;
            }


            $msg = "";
            if (!empty($fcm_ids)) {
                $msg = $request->status == 1 ? 'Activate by Adminstrator ' : 'Deactive by Adminstrator ';
                $type = $request->status == 1 ? 'account_activated' : 'account_deactivated';
                $full_msg = $request->status == 1 ? 'Your Account' . $msg : 'Please Contact to Administrator';
                $registrationIDs = $fcm_ids[0];

                $fcmMsg = array(
                    'title' =>  'Your Account' . $msg,
                    'message' => $full_msg,
                    'type' => $type,
                    'body' => 'Your Account'  . $msg,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',

                );
                send_push_notification($registrationIDs, $fcmMsg);
            }

            ResponseService::successResponse($request->status ? "Customer Activated Successfully" : "Customer Deactivated Successfully");
        }
    }




    public function customerList(Request $request)
    {
        if (!has_permissions('read', 'customer')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');


        if (isset($_GET['property_id'])) {
            $interested_users =  InterestedUser::select('customer_id')->where('property_id', $_GET['property_id'])->pluck('customer_id');

            $sql = Customer::whereIn('id', $interested_users)->orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where(function($query) use($search){
                    $query->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
                });
            }
        } else {

            $sql = Customer::orderBy($sort, $order);
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
            }
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

            // Mask Details in Demo Mode
            $tempRow['mobile'] = (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( $row->mobile ) : '****************************' ) : ( $row->mobile ));
            $tempRow['email'] = (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( $row->email ) : '****************************' ) : ( $row->email ));
            $tempRow['address'] = (env('DEMO_MODE') ? ( env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com' ? ( $row->address ) : '****************************' ) : ( $row->address ));
            $tempRow['logintype'] = $row->logintype;

            $tempRow['edit_status_url'] = 'customerstatus';
            $tempRow['total_properties'] =  $row->total_properties;
            $tempRow['total_projects'] =  $row->total_projects;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    public function resetPasswordIndex(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect(route('home'))->with('error',$validator->errors()->first())->send();
        }
        try {
            $token = $request->token;
            $email = HelperService::verifyToken($token);
            if($email){
                return view('customer.reset-password',compact('token'));
            }else{
                ResponseService::errorRedirectResponse("",trans('Invalid Token'));
            }
        } catch (Exception $e) {
            ResponseService::errorRedirectResponse("",trans('Something Went Wrong'));
        }
    }

    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|min:6',
            're_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            $email = HelperService::verifyToken($request->token);
            if($email){
                $customerQuery = Customer::where(['email' => $email, 'logintype' => 3]);
                $customerCheck = $customerQuery->clone()->count();
                if(!$customerCheck){
                    ResponseService::errorResponse("No User Found");
                }
                $password = Hash::make($request->password);
                $customerQuery->clone()->update(['password' => $password]);
                HelperService::expireToken($email);
                ResponseService::successResponse("Password Changed Successfully");
            }else{
                ResponseService::errorResponse("Token Expired");
            }
        } catch (Exception $e) {
            ResponseService::errorRedirectResponse("",trans('Something Went Wrong'));
        }
    }
}
