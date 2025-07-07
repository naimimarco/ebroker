<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Faq;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!has_permissions('read', 'faqs')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            return view('faqs.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!has_permissions('create', 'faqs')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $validator = Validator::make($request->all(), [
            'question' => 'required',
            'answer' => 'required',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            // Add Faq data to database
            Faq::create(array('question' => $request->question,'answer' => $request->answer));
            ResponseService::successResponse(trans('Data Created Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');

        $sql = Faq::when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orWhere('question', 'LIKE', "%$search%")
                        ->orWhere('answer', 'LIKE', "%$search%");
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

            $operate = BootstrapTableService::editButton('', true, null, null, null, null);
            $operate .= BootstrapTableService::deleteAjaxButton(route('faqs.destroy', $row->id));

            $tempRow = $row->toArray();
            $tempRow['edit_status_url'] = route('faqs.status-update');
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!has_permissions('update', 'faqs')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $validator = Validator::make($request->all(), [
            'edit_question' => 'required',
            'edit_answer' => 'required',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            Faq::where('id',$id)->update(array('question' => $request->edit_question,'answer' => $request->edit_answer));
            ResponseService::successResponse(trans('Data Updated Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!has_permissions('delete', 'faqs')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        try {
            Faq::where('id', $id)->delete();
            ResponseService::successResponse(trans('Data Deleted Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }


    public function statusUpdate(Request $request){
        if (!has_permissions('update', 'faqs')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $validator = Validator::make($request->all(), [
            'id'        => 'required',
            'status'    => 'required|in:0,1',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            Faq::where('id', $request->id)->update(array('status' => $request->status));
            ResponseService::successResponse(trans('Data Updated Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e,trans('Something Went Wrong'));
        }
    }
}
