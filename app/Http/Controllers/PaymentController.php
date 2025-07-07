<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Package;
use App\Models\Usertokens;
use App\Models\UserPackage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\PackageFeature;
use App\Models\BankReceiptFile;
use App\Services\HelperService;
use App\Models\UserPackageLimit;
use App\Services\ResponseService;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Validator;
use App\Services\PDF\PaymentReceiptService;

class PaymentController extends Controller
{
    public function index()
    {
        if (!has_permissions('read', 'payment')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        return view('payments.index');
    }

    public function paymentList(Request $request)
    {
        if (!has_permissions('read', 'payment')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $search = $request->input('search');

        $sql = PaymentTransaction::with('package:id,name','customer:id,name','bank_receipt_files')
            ->when($request->has('search') && !empty($search),function($query) use($search){
                $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('transaction_id', 'LIKE', "%$search%")
                    ->orWhere('payment_gateway', 'LIKE', "%$search%")
                    ->orWhere('amount', 'LIKE', "%$search%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })->orWhereHas('package', function ($q1) use ($search) {
                        $q1->where('name', 'LIKE', "%$search%");
                    });
            });

        $total = $sql->count();
        $res = $sql->orderBy($sort, $order)->skip($offset)->take($limit)->get()->map(function($item){
            $item->bank_receipt_files = $item->bank_receipt_files->map(function($file){
                $file->file_name = $file->getRawOriginal('file');
                return $file;
            });
            return $item;
        });

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;
        $priceSymbol = HelperService::getSettingData('currency_symbol') ?? '$';
        foreach ($res as $row) {
            $tempRow = $row->toArray();
            $tempRow['created_at'] = $row->created_at->format('d-m-Y H:i:s');
            $tempRow['payment_type'] = trans(Str::title($row->payment_type));
            $tempRow['updated_at'] = $row->updated_at->format('d-m-Y H:i:s');

            // Add action buttons for review status
            $tempRow['operate'] = null;
            if ($row->payment_status === 'review') {
                if (has_permissions('update', 'payment')) {
                    $operate = null;

                    // Success button
                    $successButtonClasses = ["btn", "icon", "btn-success", "btn-sm", "rounded-pill", "accept-payment","payment-status-btn"];
                    $successButtonAttributes = [
                        "id" => $row->id,
                        "title" => trans('Accept Payment'),
                        "data-id" => $row->id,
                        "data-url" => route('payment.status'),
                        "data-status" => 'success'
                    ];
                    $operate .= BootstrapTableService::button('bi bi-check-circle', '', $successButtonClasses, $successButtonAttributes);

                    // Reject button
                    $rejectButtonClasses = ["btn", "icon", "btn-warning", "btn-sm", "rounded-pill", "reject-payment","payment-status-btn"];
                    $rejectButtonAttributes = [
                        "id" => $row->id,
                        "title" => trans('Reject Payment'),
                        "data-id" => $row->id,
                        "data-url" => route('payment.status'),
                        "data-status" => 'rejected'
                    ];
                    $operate .= ' ' . BootstrapTableService::button('bi bi-x-circle', '', $rejectButtonClasses, $rejectButtonAttributes);

                    // Cancel button
                    $cancelButtonClasses = ["btn", "icon", "btn-danger", "btn-sm", "rounded-pill", "cancel-payment","payment-status-btn"];
                    $cancelButtonAttributes = [
                        "id" => $row->id,
                        "title" => trans('Cancel Payment'),
                        "data-id" => $row->id,
                        "data-url" => route('payment.status'),
                        "data-status" => 'failed'
                    ];
                    $operate .= ' ' . BootstrapTableService::button('bi bi-slash-circle', '', $cancelButtonClasses, $cancelButtonAttributes);

                    $tempRow['operate'] = $operate;
                }

                // Add view files button if files exist
                if ($row->bank_receipt_files->count() > 0) {
                    $viewFilesButtonClasses = ["btn", "icon", "btn-info", "btn-sm", "rounded-pill", "view-files"];
                    $viewFilesButtonCustomAttributes = ["id" => $row->id, "title" => trans('View Files'), "data-toggle" => "modal", "data-bs-target" => "#viewFilesModal", "data-bs-toggle" => "modal"];
                    $tempRow['operate'] .= ' ' . BootstrapTableService::button('bi bi-file-earmark-text', '', $viewFilesButtonClasses, $viewFilesButtonCustomAttributes);
                }
            } elseif ($row->payment_status === 'success') {
                // Add View receipt button for successful payments
                if (has_permissions('read', 'payment')) {
                    $receiptButtonClasses = ["btn", "icon", "btn-primary", "btn-sm", "rounded-pill"];
                    $receiptButtonAttributes = [
                        "id" => $row->id,
                        "title" => trans('View Receipt'),
                        "onclick" => "window.open('" . route('payment.receipt.view', $row->id) . "', '_blank')"
                    ];
                    $tempRow['operate'] = BootstrapTableService::button('bi bi-receipt', '', $receiptButtonClasses, $receiptButtonAttributes);
                }
            }
            $tempRow['price_symbol'] = $priceSymbol;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function updateStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'status' => 'required|in:success,rejected,failed',
                'reject_reason' => 'required_if:status,rejected,failed|string|max:255',
            ]);

            if ($validator->fails()) {
                return ResponseService::errorResponse($validator->errors()->first());
            }

            DB::beginTransaction();
            $payment = PaymentTransaction::find($request->id);
            if(!$payment){
                return ResponseService::errorResponse(trans('Payment not found'));
            }else if($payment->payment_status != 'review'){
                return ResponseService::errorResponse(trans('Payment is not in review status'));
            }
            $payment->payment_status = $request->status;
            $payment->transaction_id = Str::uuid();
            $payment->reject_reason = $request->status == 'rejected' || $request->status == 'failed' ? $request->reject_reason : null;
            $payment->save();

            // Assign package to user
            $packageId = $payment->package_id;
            $userId = $payment->user_id;
            $package = Package::find($packageId);
            if($request->status == 'success'){
                $userPackage = UserPackage::create([
                    'package_id'  => $packageId,
                    'user_id'     => $userId,
                    'start_date'  => Carbon::now(),
                    'end_date'    => $package->package_type == "unlimited" ? null : Carbon::now()->addHours($package->duration),
                ]);
                $packageFeatures = PackageFeature::where(['package_id' => $packageId, 'limit_type' => 'limited'])->get();
                if(collect($packageFeatures)->isNotEmpty()){
                    $userPackageLimitData = array();
                    foreach ($packageFeatures as $key => $feature) {
                        $userPackageLimitData[] = array(
                            'user_package_id' => $userPackage->id,
                            'package_feature_id' => $feature->id,
                            'total_limit' => $feature->limit,
                            'used_limit' => 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        );
                    }

                    if(!empty($userPackageLimitData)){
                        UserPackageLimit::insert($userPackageLimitData);
                    }
                }
            }

            $statusText = $request->status == 'success' ? 'Accepted' : ($request->status == 'rejected' ? 'Rejected' : 'Failed');

            //Send Notification To Customer
            $user_token = Usertokens::where('customer_id', $payment->user_id)->pluck('fcm_id')->toArray();
            $fcm_ids = array();
            $fcm_ids = $user_token;
            if (!empty($fcm_ids)) {
                $registrationIDs = $fcm_ids;
                $title = "Payment Status";
                $body = 'Payment Status Is ' . $statusText. ' amount is ' . $payment->amount;
                $fcmMsg = array(
                    'title' => $title,
                    'message' => $body,
                    'type' => 'payment',
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',
                    'id' => (string)$payment->id,
                );
                send_push_notification($registrationIDs, $fcmMsg);
            }
            //Send Notification To Customer

            Notifications::create([
                'title' => 'Payment Status',
                'message' => 'Payment Status Is ' . $statusText,
                'image' => '',
                'type' => '1',
                'send_type' => '0',
                'customers_id' => $payment->customer_id
            ]);
            DB::commit();
            return ResponseService::successResponse(trans('Payment status updated successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseService::errorResponse(trans('Payment status update failed'));
        }
    }

    /**
     * View a payment receipt in PDF format
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function viewReceipt($id)
    {
        if (!has_permissions('read', 'payment')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }

        try {
            $payment = PaymentTransaction::with('package', 'customer')->findOrFail($id);

            // Only allow viewing receipts for successful payments
            if ($payment->payment_status !== 'success') {
                return redirect()->back()->with('error', trans('Receipt is only available for successful payments'));
            }

            $receiptService = new PaymentReceiptService();
            return $receiptService->streamPDF($payment);
        } catch (Exception $e) {
            return redirect()->back()->with('error', trans('Error generating receipt: ') . $e->getMessage());
        }
    }
}
