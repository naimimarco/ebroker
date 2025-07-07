<?php

namespace App\Http\Controllers;

use App\Models\BlockedChatUser;
use Exception;
use Carbon\Carbon;
use App\Models\Chats;
use App\Models\Customer;
use App\Models\Property;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!has_permissions('create', 'chat')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }

        $validator = Validator::make($request->all(), [
            'attachment' => 'nullable|mimes:png,jpg,jpeg,pdf,doc,docx|max:2024',
            'aud' => 'nullable|mimes:audio/mpeg'
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }

        $senderBlockedReciever = BlockedChatUser::where(['by_admin' => 1, 'user_id' => $request->receiver_id])->count();
        if($senderBlockedReciever){
            ResponseService::errorResponse("You have blocked user");
        }
        $recieverBlockedSender = BlockedChatUser::where(['by_user_id' => $request->receiver_id, 'user_id' => $request->sender_id])->count();
        if($recieverBlockedSender){
            ResponseService::errorResponse("You are blocked by user");
        }

        $chat = new Chats();
        $chat->sender_id = $request->sender_by;
        $chat->receiver_id = $request->receiver_id;
        $chat->message = $request->message ? $request->message : null;
        $chat->property_id = $request->property_id;

        if ($request->receiver_id == '' || !isset($request->receiver_id)) {
            $response['error'] = true;
            return response()->json($response);
        }

        $audio_data = $request->aud;
        if ($audio_data) {

            // Decode the data URL and extract the raw audio data
            $audio_data = str_replace('data:audio/mp3; codecs=opus;base64,', '', $audio_data);
            $audio_data = base64_decode($audio_data);

            // Save the audio data to a file
            $filename = uniqid() . '.mp3';
            $audiodestinationPath = public_path('images/chat_audio/') . $filename;
            if (!is_dir(dirname($audiodestinationPath))) {
                mkdir(dirname($audiodestinationPath), 0777, true);
            }

            file_put_contents($audiodestinationPath, $audio_data);


            $chat->audio = $filename;
        }
        $destinationPath = public_path('images') . config('global.CHAT_FILE');

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $imageName = microtime(true) . "." . $attachment->getClientOriginalExtension();
            $attachment->move($destinationPath, $imageName);
            $chat->file = $imageName;
        } else {
            $chat->file = '';
        }

        $chat->save();
        if($chat->sender_id == 0){
            $senderUserProfile = !empty(Auth::user()->getRawOriginal('profile')) ? Auth::user()->profile : url('assets/images/faces/2.jpg');
        }else{
            $senderUserProfile = $chat->sender()->profile ?? null;
        }

        $fcm_id = [];
        $customer = Customer::select('id', 'name')->with(['usertokens' => function ($q) {
            $q->select('fcm_id', 'id', 'customer_id');
        }])->find($request->receiver_id);
        // dd($customer->usertokens);
        if ($customer && !empty($customer->usertokens)) {
            foreach ($customer->usertokens as $usertokens) {

                array_push($fcm_id, $usertokens->fcm_id);
            }

            $username = $customer->name;
        } else {
            $fcm_id = [];
        }


        $Property = Property::find($request->property_id);



        $chat_message_type = "";

        if (!empty($request->aud)) {
            $chat_message_type = "audio";
        } else if (!empty($request->file('attachment')) && $request->message == "") {
            $chat_message_type = "file";
        } else if (!empty($request->file('attachment')) && $request->message != "") {
            $chat_message_type = "file_and_text";
        } else if (empty($request->file('attachment')) && $request->message != "" && empty($request->aud)) {
            $chat_message_type = "text";
        }

        $fcmMsg = array(
            'title' => 'Message',
            'message' => $request->message,
            'type' => 'chat',
            'body' => $request->message,
            'sender_id' => (string)$request->sender_by,
            'receiver_id' => (string)$request->receiver_id,
            'username' => $username,
            'file' => $chat->file != '' ? $chat->file : '',
            'audio' => $chat->audio,
            'date' => $chat->created_at,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'sound' => 'default',
            'property_id' => (string)$Property->id,
            'property_title_image' => $Property->title_image,
            'title' => $Property->title,
            'chat_message_type' => $chat_message_type,
            'user_profile' => $senderUserProfile
        );

        send_push_notification($fcm_id, $fcmMsg);

        $response['error'] = false;
        $response['message'] = trans('Message Sent Successfully');
        return response()->json($response);
    }

    public function getChats()
    {
        if (!has_permissions('read', 'chat')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }

        $userListQuery = Chats::with(['sender:id,name,profile', 'receiver:id,name,profile', 'property:id,title,title_image'])
            ->select('id', 'sender_id', 'receiver_id', 'property_id', 'message', 'created_at',
                DB::raw('COUNT(CASE WHEN is_read = 0 AND receiver_id = 0 THEN 1 END) AS unread_count')
            )
            ->where('sender_id', 0)
            ->orWhere('receiver_id', 0)
            ->groupBy(DB::raw('IF(sender_id < receiver_id, CONCAT(sender_id, "-", receiver_id), CONCAT(receiver_id, "-", sender_id))'), 'property_id')
            ->orderBy('id', 'desc');

        // User's List with Blocked Status
        $user_list = $userListQuery->clone()->get()->map(function ($user){
            if($user->sender_id){
                $userId = $user->sender_id;
            }else{
                $userId = $user->reciever_id;
            }
            // Check if blocked
            $isBlockedByMe = BlockedChatUser::where('by_admin', 1)
                ->where('user_id', $userId)
                ->exists();

            $isBlockedByUser = BlockedChatUser::where('admin', 1)
                ->where('by_user_id', $userId)
                ->exists();

            $user->is_blocked_by_me = $isBlockedByMe ? 1 : 0;
            $user->is_blocked_by_user = $isBlockedByUser ? 1 : 0;
            $user->unread_count = $user->receiver_id == 0 ? $user->unread_count : 0;
            return $user;
        });

        // Property ID from Chat
        $propertiesId = $userListQuery->clone()->pluck('property_id')->toArray();

        // Sender ID from Chat
        $sendersId = $userListQuery->clone()->pluck('sender_id')->toArray();

        // Receiver ID from Chat
        $receiversId = $userListQuery->clone()->pluck('receiver_id')->toArray();

        // Merge UserIds
        $user_array = array_merge($sendersId, $receiversId);

        $otherUsers = Property::select('id', 'added_by', 'title', 'title_image')->with('customer', function ($q) use ($user_array) {
            $q->whereNotIn('id', $user_array)->select('id', 'name', 'profile');
        })->orWhereNotIn('id', $propertiesId)->groupBy('added_by')->get();

        $tempRow = array();
        foreach ($otherUsers as $key => $row) {
            if ($row->customer) {
                $tempRow[$key]['proeperty_id'] = $row->id;
                $tempRow[$key]['title_image'] = $row->title_image;
                $tempRow[$key]['title'] = $row->title;
                $tempRow[$key]['customer_id'] = $row->customer->id;
                $tempRow[$key]['profile'] = $row->customer->profile;
                $tempRow[$key]['name'] = $row->customer->name;

                // Check if blocked
                $isBlockedByMe = BlockedChatUser::where('by_admin', 1)
                    ->where('user_id', $row->customer->id)
                    ->exists();

                $isBlockedByUser = BlockedChatUser::where('admin', 1)
                    ->where('by_user_id', $row->customer->id)
                    ->exists();

                $tempRow[$key]['is_blocked_by_me'] = $isBlockedByMe ? 1 : 0;
                $tempRow[$key]['is_blocked_by_user'] = $isBlockedByUser ? 1 : 0;
            }
        }

        $firebase_settings = array();

        $firebase_settings['apiKey'] = system_setting('apiKey');
        $firebase_settings['authDomain'] = system_setting('authDomain');
        $firebase_settings['projectId'] = system_setting('projectId');
        $firebase_settings['storageBucket'] = system_setting('storageBucket');
        $firebase_settings['messagingSenderId'] = system_setting('messagingSenderId');
        $firebase_settings['appId'] = system_setting('appId');
        $firebase_settings['measurementId'] = system_setting('measurementId');

        return view('chat.index', [
            'user_list' => $user_list,
            'firebase_settings' => $firebase_settings,
            'otherUsers' => $tempRow
        ]);
    }

    public function getAllMessage(Request $request)
    {
        if (!has_permissions('read', 'chat')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }

        // Update is_read to true for Admin
        Chats::where(['property_id' => $request->property_id, 'receiver_id' => 0, 'is_read' => false])->update(['is_read' => true]);


        $chat = Chats::with(['sender:id,name,profile', 'receiver:id,name,profile', 'property:id,title,title_image'])->select('id', 'sender_id', 'receiver_id', 'message', 'audio', 'property_id', 'file', 'created_at')->where('property_id', $request->property_id)
            ->where(function ($query) use ($request) {
                $query->where('sender_id', $request->client_id)
                    ->orWhere('receiver_id', $request->client_id);
            })->orderBy('id', 'DESC')->get();


        $rows = array();
        $tempRow = array();
        $count = 1;
        foreach ($chat as $row) {
            if ($row->sender_id  == 0 || $row->receiver_id == 0) {
                $tempRow['message'] = $row->message;
                $tempRow['time_ago'] = $row->created_at->diffForHumans(now(), CarbonInterface::DIFF_RELATIVE_AUTO, true);
                $tempRow['attachment'] = $row->file;
                $tempRow['audio'] = !empty($row->audio) ? $row->audio : '';


                if ($row->receiver_id == 0) {
                    $customer = Customer::find($row->sender_id);
                    if ($customer) {
                        $name = $customer->name;
                        $profile = $customer->profile;
                    } else {
                        $name = "Admin";
                        $profile = '';
                    }
                    $tempRow['sendeprofile'] = $profile;

                    $tempRow['sender_type'] = 1;

                    $tempRow['sendername'] = $name;
                }
                if ($row->sender_id  == 0) {



                    // $user = User::find($row->sender_id);

                    $customer = Customer::find($row->receiver_id);
                    if ($row->property->added_by != 0) {

                        $name = $customer->name;
                        $profile = $customer->profile;
                    }
                    if ($row->property->added_by == 0) {

                        $name = "Admin";
                        $profile = '';
                    }
                    // $tempRow['attachment'] = $row->file;

                    $tempRow['ssendeprofile'] = $profile;
                    $tempRow['ssendername'] = $name;

                    $tempRow['sender_type'] = 0;
                }

                $rows[] = $tempRow;
                $count++;
            }
        }

        $bulkData['rows'] = $rows;
        return response()->json($rows);
    }

    public function blockUser($userId){
        $validator = Validator::make(['userId' => $userId], [
            'userId' => 'required|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        try {
            $data = array(
                "by_admin" => 1,
                "user_id" => $userId
            );
            BlockedChatUser::create($data);
            ResponseService::successResponse("User Blocked Successfully");
        } catch (Exception $e) {
            ResponseService::errorResponse("Something Went Wrong");
        }
    }

    public function unBlockUser($userId){
        $validator = Validator::make(['userId' => $userId], [
            'userId' => 'required|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        try {
            $getBlockedUserQuery = BlockedChatUser::where(["by_admin" => 1, "user_id" => $userId]);
            if($getBlockedUserQuery->clone()->count()){
                $getBlockedUserQuery->delete();


                $isBlockedByUser = BlockedChatUser::where('admin', 1)
                    ->where('by_user_id', $userId)
                    ->exists();

                $data = array('is_blocked_by_user' => $isBlockedByUser);
                ResponseService::successResponse("User Unblocked Successfully",$data);
            }else{
                ResponseService::errorResponse("User Already Unblocked");
            }
        } catch (Exception $e) {
            ResponseService::errorResponse("Something Went Wrong");
        }
    }

}
