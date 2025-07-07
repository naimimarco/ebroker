<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Setting;
use App\Models\Usertokens;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\VerifyCustomer;
use App\Services\HelperService;
use App\Services\ResponseService;
use App\Models\VerifyCustomerForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\VerifyCustomerFormValue;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Validator;

class VerifyCustomerFormController extends Controller
{
    public function verifyCustomerFormIndex(){
        if (!has_permissions('read', 'verify_customer_form')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('verify-customer-form.verify_customer_form');
    }

    public function verifyCustomerFormStore(Request $request){
        if (!has_permissions('create', 'verify_customer_form')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'field_type' => 'required|in:text,number,radio,checkbox,textarea,file,dropdown',
            'option_data.*' => 'required_if:field_type,radio|required_if:field_type,checkbox|required_if:field_type,dropdown',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            // Get the data from Request
            $name = $request->name;
            $fieldType = $request->field_type;

            // Store name and field type in verify customer form
            $verifyCustomerForm = VerifyCustomerForm::create(['name' => $name, 'field_type' => $fieldType]);

            // Check if option data is available or not
            if($request->has('option_data') && !empty($request->option_data)){
                $verifyCustomerFormValueData = array();
                // Loop through
                foreach ($request->option_data as $option) {
                    if(!empty($option['option'])){
                        $verifyCustomerFormValueData[] = array(
                            'verify_customer_form_id'   => $verifyCustomerForm->id,
                            'value'                     => $option['option'],
                            'created_at'                => now(),
                            'updated_at'                => now()
                        );
                    }
                }
                if(!empty($verifyCustomerFormValueData)){
                    VerifyCustomerFormValue::insert($verifyCustomerFormValueData);
                }
            }
            DB::commit();
            ResponseService::successResponse(trans('Data Created Successfully'));
        } catch (Exception $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function verifyCustomerFormShow()
    {
        if (!has_permissions('read', 'verify_customer_form')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');

        $sql = VerifyCustomerForm::with('form_fields_values')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orWhere('name', 'LIKE', "%$search%")
                        ->orWhere('field_type', 'LIKE', "%$search%")
                        ->orWhereHas('form_fields_values',function($query) use($search){
                            $query->where('value','LIKE',"%$search%");
                        });
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

            $operate = BootstrapTableService::editButton('', true, null, null, $row->id, null);
            $operate .= BootstrapTableService::deleteAjaxButton(route('verify-customer-form.delete', $row->id));

            $tempRow = $row->toArray();
            $tempRow['edit_status_url'] = route('verify-customer-form.status');
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function verifyCustomerFormStatus(Request $request){
        if (!has_permissions('update', 'verify_customer_form')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            if($request->status == '1'){
                $status = 'active';
            }else{
                $status = 'inactive';
            }
            VerifyCustomerForm::where('id', $request->id)->update(['status' => $status]);
            ResponseService::successResponse($request->status ? "Field Activated Successfully" : "Field Deactivated Successfully");
        }
    }

    public function verifyCustomerFormUpdate(Request $request){
        if (!has_permissions('update', 'verify_customer_form')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            VerifyCustomerForm::where('id',$request->id)->update(['name' => $request->name]);
            ResponseService::successResponse(trans('Data Updated Successfully'));
        } catch (Exception $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }


    public function verifyCustomerFormDestroy($id){
        if (!has_permissions('delete', 'verify_customer_form')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        try {
            VerifyCustomerForm::where('id', $id)->delete();
            ResponseService::successResponse(trans('Data Deleted Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }


    public function agentVerificationListIndex(){
        if (!has_permissions('read', 'approve_agent_verification')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }

        return view('verify-customer-form.agent_verification_list');
    }
    public function agentVerificationList(){
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');

        $sql = VerifyCustomer::with(['user' => function($query){
            $query->select('id', 'name', 'profile')->withCount(['property', 'projects']);
        }])->with(['verify_customer_values' => function($query){
            $query->with('verify_form:id,name,field_type','verify_form.form_fields_values:id,verify_customer_form_id,value')->select('id','verify_customer_id','verify_customer_form_id','value');
        }]);

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql = $sql->where('id', 'LIKE', "%$search%")->orWhere('status', 'LIKE', "%$search%")
                            ->orWhereHas('user', function ($query) use ($search) {
                                $query->where('id', 'LIKE', "%$search%")->orWhere('name', 'LIKE', "%$search%");
                            });
        }


        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            // Check that is there any Values of forms by Customer
            if(collect($row->verify_customer_values)->isEmpty()){
                $row->update(['status' => 'failed']);
            }

            $row = (object)$row;
            $tempRow = $row->toArray();

            $operate = null;
            $operate = BootstrapTableService::editButton('', true, null, null, $row->id, null);
            $tempRow['operate'] = $operate;

            $viewFormClasses = ["btn","icon","btn-primary","btn-sm","rounded-pill","view-form-btn"];
            $viewFormAttributes = ["id" => $row->id, "title" => trans('Submitted Form Values')];
            $viewFormButton = BootstrapTableService::button('bi bi-eye-fill ml-2', route('agent-verification.show-form',$row->id),$viewFormClasses,$viewFormAttributes);
            $tempRow['view-form-btn'] = $viewFormButton;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }


    public function getAgentSubmittedForm($id){
        // Validate the ID
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:verify_customers,id'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        if (!has_permissions('read', 'verify_customer_form')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }

        $customerVerification = VerifyCustomer::where('id',$id)->with(['user' => function($query){
            $query->select('id', 'name', 'profile');
        }])->with(['verify_customer_values' => function($query){
            $query->with('verify_form:id,name,field_type','verify_form.form_fields_values:id,verify_customer_form_id,value')->select('id','verify_customer_id','verify_customer_form_id','value');
        }])->first();

        // Process file type based on value
        foreach ($customerVerification->verify_customer_values as &$value) {
            if($value->verify_form->field_type == 'file'){
                $value->file_type = $this->getFileType($value->value);
            }else{
                $value->file_type = "other";
            }
        }

        return view('verify-customer-form.view-form-details',compact('customerVerification'));
    }

    public function updateVerificationStatus(Request $request){
        if (!has_permissions('update', 'approve_agent_verification')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $validator = Validator::make($request->all(), [
            'edit_id'       => 'required',
            'edit_status'   => 'required|in:success,failed',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            $verifyCustomerQuery = VerifyCustomer::where('id', $request->edit_id);
            $verifyCustomerQuery->clone()->update(['status' => $request->edit_status]);
            $verifyCustomerData = $verifyCustomerQuery->clone()->with('user:id,name,notification,email')->first();

            if ($request->edit_status == 'success') {
                $statusText  = 'Approved';
            } else {
                $statusText  = 'Failed';
            }

            if ($verifyCustomerData->user->notification == 1) {
                $user_token = Usertokens::where('customer_id', $verifyCustomerData->user->id)->pluck('fcm_id')->toArray();
                //START :: Send Notification To Customer
                $fcm_ids = array();
                $fcm_ids = $user_token;
                if (!empty($fcm_ids)) {
                    $registrationIDs = $fcm_ids;
                    $fcmMsg = array(
                        'title' => 'Agent Verification Request',
                        'message' => 'Agent Verification Request Is ' . $statusText,
                        'type' => 'agent_verification',
                        'body' => 'Agent Verification Request Is ' . $statusText,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                        'id' => (string)$verifyCustomerData->id,
                    );
                    send_push_notification($registrationIDs, $fcmMsg);
                }
                //END ::  Send Notification To Customer

                Notifications::create([
                    'title' => 'Agent Verification Request Updated',
                    'message' => 'Your Agent Verification Request is ' . $statusText,
                    'image' => '',
                    'type' => '1',
                    'send_type' => '0',
                    'customers_id' => $verifyCustomerData->user_id
                ]);
            }


            // Send mail for property status
            try {
                // $verifyCustomerData
                if($verifyCustomerData->user->email){
                    // Get Data of email type
                    $emailTypeData = HelperService::getEmailTemplatesTypes("agent_verification_status");

                    // Email Template
                    $agentVerificationTemplateData = system_setting($emailTypeData['type']);
                    $appName = env("APP_NAME") ?? "eBroker";
                    $variables = array(
                        'app_name' => $appName,
                        'user_name' => $verifyCustomerData->user->name,
                        'status' => $statusText,
                        'email' => $verifyCustomerData->user->email
                    );
                    if(empty($agentVerificationTemplateData)){
                        $agentVerificationTemplateData = "Your Agent Verification Status is ".$variables['status'];
                    }
                    $agentVerificationTemplate = HelperService::replaceEmailVariables($agentVerificationTemplateData,$variables);

                    $data = array(
                        'email_template' => $agentVerificationTemplate,
                        'email' =>$verifyCustomerData->user->email,
                        'title' => $emailTypeData['title'],
                    );
                    HelperService::sendMail($data);
                }
            } catch (Exception $e) {
                Log::error("Something Went Wrong in Agent Verification Status Mail Sending");
            }

            ResponseService::successResponse(trans('Data Updated Successfully'));
        } catch (Exception $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }

    public function autoApproveSettings(Request $request){
        if (!has_permissions('update', 'verify_customer_form')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        try {
            Setting::updateOrCreate(['type' => 'auto_approve'], ['data' => $request->auto_approve]);
            ResponseService::successResponse(trans('Data Updated Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }

    public function verificationRequiredForUserSettings(Request $request){
        if (!has_permissions('update', 'verify_customer_form')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        try {
            Setting::updateOrCreate(['type' => 'verification_required_for_user'], ['data' => $request->verification_required_for_user]);
            ResponseService::successResponse(trans('Data Updated Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }

    private function getFileType($filePath) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $imageExtensions = ['jpg', 'jpeg', 'png'];
        $pdfExtensions = ['pdf'];
        $docExtensions = ['doc', 'docx'];
        $textExtensions = ['txt'];

        if (in_array($extension, $imageExtensions)) {
            return 'image';
        } elseif (in_array($extension, $pdfExtensions)) {
            return 'pdf';
        } elseif (in_array($extension, $docExtensions)) {
            return 'doc';
        } elseif (in_array($extension, $textExtensions)) {
            return 'txt';
        }
        return 'other';
    }
}
