<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Usertokens;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\PropertysInquiry;
use App\Services\ResponseService;

class PropertysInquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($status = null)
    {

        if (!has_permissions('read', 'property_inquiry')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {

            if ($status) {
                $status = $status;
            } else {
                $status = '';
            }
            $firebase_settings = array();
            $operate = '';
            $firebase_settings['apiKey'] = system_setting('apiKey');
            $firebase_settings['authDomain'] = system_setting('authDomain');
            $firebase_settings['projectId'] = system_setting('projectId');
            $firebase_settings['storageBucket'] = system_setting('storageBucket');
            $firebase_settings['messagingSenderId'] = system_setting('messagingSenderId');
            $firebase_settings['appId'] = system_setting('appId');
            $firebase_settings['measurementId'] = system_setting('measurementId');
            return view('property_inquiry.index', compact('status', 'firebase_settings'));
        }
    }

    public function show()
    {
        if (!has_permissions('read', 'property_inquiry')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        return view('property_inquiry.index');
    }


    public function getPropertyInquiryList()
    {
        if (!has_permissions('read', 'property_inquiry')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }

        $offset = 0;
        $limit = 10;
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

        $sql = PropertysInquiry::with('customer')->with('property.customer')->orderBy($sort, $order);


        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql =  $sql->where('id', 'LIKE', "%$search%")->orWhereHas('property', function ($query) use ($search) {
                $query->where('title', 'LIKE', "%$search%");
            })->orWhereHas('customer', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")->orWhere('mobile', 'LIKE', "%$search%");
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
            $operate = '';
            if ($row->property->added_by == 0) {
                $operate1 = '<a  id="' . $row->id . '"  class="btn icon btn-primary chatdata btn-sm rounded-pill"  data-bs-toggle="modal" data-bs-target="#chat_modal"  onclick="setallMessage(' . $row->propertys_id . ',' . $row->customers_id . ');"" title="Chat"><i class="bi bi-chat"></i></a>';
            } else {
                $operate1 = '';
            }

            $operate .=  '<a class="btn icon btn-info btn-sm rounded-pill view-property"  data-bs-toggle="modal" data-bs-target="#ViewPropertyModal"   title="View Property"><i class="bi bi-building"></i></a>&nbsp;&nbsp;';
            $tempRow['id'] = $row->id;
            $tempRow['title'] = $row->property->title;
            $tempRow['name'] = $row->customer->name;
            $tempRow['inquiry_by'] = $row->customer->id;
            $tempRow['property_id'] = $row->property->id;
            $tempRow['email'] = $row->customer->email;
            $tempRow['mobile'] = $row->customer->mobile;
            $tempRow['address'] = $row->customer->address;
            $tempRow['client_address'] = $row->property->client_address;
            $tempRow['price'] = $row->property->price;
            $tempRow['chat'] = $operate1;
            $tempRow['property_type'] =  ucfirst($row->propery_type);
            $tempRow['inquiry_created'] = $row->created_at->diffForHumans();
            if ($row->status == '0') {
                $tempRow['status'] = '<span class="badge bg-primary">Pending</span>';
            }
            if ($row->status == '1') {
                $tempRow['status'] = '<span class="badge bg-secondary">Accept</span>';
            }
            if ($row->status == '2') {
                $tempRow['status'] = '<span class="badge bg-success">Complete</span>';
            }
            if ($row->status == '3') {
                $tempRow['status'] = '<span class="badge bg-danger">Cancel</span>';
            }
            if ($row->property->added_by != 0) {
                $tempRow['property_owner'] = (count($row->property->customer) > 0) ? $row->property->customer[0]->name : 'Administrator';
                $tempRow['property_mobile'] = (count($row->property->customer) > 0) ? $row->property->customer[0]->mobile : '';
            } else {
                $mobile = Setting::where('type', 'company_tel1')->select('data')->first();
                $tempRow['property_owner'] = 'Administrator';
                $tempRow['property_mobile'] = $mobile->data;
            }
            $tempRow['location'] = ($row->property->latitude != '' && $row->property->longitude != '') ? '&nbsp;<button class="btn icon btn-secondary btn-sm rounded-pill mt-2 CopyLocation"  data-clipboard-text="https://maps.google.com/?q=' . $row->property->latitude . ',' . $row->property->longitude . '"><i class="bi bi-geo-alt-fill"></i></button>' : '';

            $tempRow['category'] = $row->property->category->category;
            $tempRow['state'] = $row->property->state;
            $tempRow['city'] = $row->property->city;
            $tempRow['country'] = $row->property->country;
            $tempRow['state'] = $row->property->state;
            $tempRow['latitude'] = $row->property->latitude;
            $tempRow['longitude'] = $row->property->longitude;
            $tempRow['description'] = $row->property->description;

            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }


    public function updateStatus(Request $request)
    {
        if (!has_permissions('update', 'property_inquiry')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $id = $request->id;
            $status = $request->status;
            $status_text = '';

            $PropertysInquiry = PropertysInquiry::with('customer')->find($id);
            $old_status = $PropertysInquiry->status;

            $PropertysInquiry->status = $status;
            $PropertysInquiry->update();

            if ($status == '0') {
                $status_text  = 'Pending';
            } else if ($status == '1') {
                $status_text  = 'Accept';
            } else if ($status == '2') {
                $status_text  = 'Complete';
            } else if ($status == '3') {
                $status_text  = 'Cancel';
            }
            $result = '';
            if ($status != $old_status) {

                if ($PropertysInquiry->customer->notification == 1) {
                    $user_token = Usertokens::where('customer_id', $PropertysInquiry->customer->id)->pluck('fcm_id')->toArray();

                    //START :: Send Notification To Customer
                    $fcm_ids = array();
                    $fcm_ids = $user_token;

                    if (!empty($fcm_ids)) {
                        $registrationIDs = $fcm_ids[0];
                        $fcmMsg = array(
                            'title' => 'Property Inquiry Updated',
                            'message' => 'Your Property Inquiry Updated To ' . $status_text,
                            'type' => 'property_inquiry',
                            'body' => 'Your Property Inquiry Updated To ' . $status_text,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default',
                            'id' => (int)$PropertysInquiry->id,
                        );
                        $result = send_push_notification($registrationIDs, $fcmMsg);
                    }
                    //END ::  Send Notification To Customer

                    Notifications::create([
                        'title' => 'Property Inquiry Updated',
                        'message' => 'Your Property Inquiry Updated To ' . $status_text,
                        'image' => '',
                        'type' => '1',
                        'send_type' => '0',
                        'customers_id' => $PropertysInquiry->customer->id,
                        'propertys_id' => $PropertysInquiry->id
                    ]);
                }
            }
            $response['error'] = false;
            $response['data'] = $result;
            return response()->json($response);
        }
    }
}
