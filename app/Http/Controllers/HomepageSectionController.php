<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\HomepageSection;
use App\Services\ResponseService;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class HomepageSectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if(!has_permissions('read', 'homepage-sections')) {
                return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
            }
        $sectionTypes = [
            config('constants.HOMEPAGE_SECTION_TYPES.AGENTS_LIST_SECTION.TYPE')             => config('constants.HOMEPAGE_SECTION_TYPES.AGENTS_LIST_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.ARTICLES_SECTION.TYPE')                => config('constants.HOMEPAGE_SECTION_TYPES.ARTICLES_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.CATEGORIES_SECTION.TYPE')              => config('constants.HOMEPAGE_SECTION_TYPES.CATEGORIES_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.FAQS_SECTION.TYPE')                    => config('constants.HOMEPAGE_SECTION_TYPES.FAQS_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.FEATURED_PROPERTIES_SECTION.TYPE')     => config('constants.HOMEPAGE_SECTION_TYPES.FEATURED_PROPERTIES_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.FEATURED_PROJECTS_SECTION.TYPE')       => config('constants.HOMEPAGE_SECTION_TYPES.FEATURED_PROJECTS_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.MOST_LIKED_PROPERTIES_SECTION.TYPE')     => config('constants.HOMEPAGE_SECTION_TYPES.MOST_LIKED_PROPERTIES_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.MOST_VIEWED_PROPERTIES_SECTION.TYPE')  => config('constants.HOMEPAGE_SECTION_TYPES.MOST_VIEWED_PROPERTIES_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.NEARBY_PROPERTIES_SECTION.TYPE')       => config('constants.HOMEPAGE_SECTION_TYPES.NEARBY_PROPERTIES_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.PROJECTS_SECTION.TYPE')                => config('constants.HOMEPAGE_SECTION_TYPES.PROJECTS_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.PREMIUM_PROPERTIES_SECTION.TYPE')      => config('constants.HOMEPAGE_SECTION_TYPES.PREMIUM_PROPERTIES_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.USER_RECOMMENDATIONS_SECTION.TYPE')    => config('constants.HOMEPAGE_SECTION_TYPES.USER_RECOMMENDATIONS_SECTION.TITLE'),
            config('constants.HOMEPAGE_SECTION_TYPES.PROPERTIES_BY_CITIES_SECTION.TYPE')    => config('constants.HOMEPAGE_SECTION_TYPES.PROPERTIES_BY_CITIES_SECTION.TITLE'),
            ];
            return view('homepage-sections.index', compact('sectionTypes'));
        } catch (Exception $e) {
            ResponseService::logErrorRedirectResponse($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            if(!has_permissions('create', 'homepage-sections')) {
                return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
            }
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'section_type' => 'required',
            ]);
            if ($validator->fails()) {
                ResponseService::validationError($validator->errors()->first());
            }
            // Check if section already exists
            $section = HomepageSection::where('section_type', $request->section_type)->first();
            if($section){
                ResponseService::errorResponse(trans('Section already exists'));
            }
            // Create new section
            $data = $request->except('_token');
            HomepageSection::create($data);
            ResponseService::successResponse(trans('Data Updated Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            if(!has_permissions('read', 'homepage-sections')) {
                return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
            }
            $offset = request('offset', 0);
            $limit = request('limit', 10);
            $sort = request('sort', 'id');
            $order = request('order', 'DESC');
            $search = request('search');

            $sql = HomepageSection::when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('id', 'LIKE', "%$search%")
                            ->orWhere('title', 'LIKE', "%$search%")
                            ->orWhere('section_type', 'LIKE', "%$search%");
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
                $operate .= BootstrapTableService::deleteAjaxButton(route('homepage-sections.destroy', $row->id));

                $tempRow = $row->toArray();
                if(has_permissions('update', 'homepage-sections')){
                    $tempRow['edit_status_url'] = route('homepage-sections.status-update');
                }
                $tempRow['operate'] = $operate;
                $rows[] = $tempRow;
            }

            $bulkData['rows'] = $rows;
            return response()->json($bulkData);
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            if(!has_permissions('update', 'homepage-sections')) {
                return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
            }
            // Check if section already exists
            $section = HomepageSection::where('section_type', $request->section_type)->where('id', '!=', $id)->first();
            if($section){
                ResponseService::errorResponse(trans('Section already exists'));
            }
            $data = $request->except('_token','_method','edit_id');
            $section = HomepageSection::find($id);
            $section->update($data);
            ResponseService::successResponse(trans('Data Updated Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            if(!has_permissions('delete', 'homepage-sections')) {
                return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
            }
            HomepageSection::where('id', $id)->delete();
            ResponseService::successResponse(trans('Data Deleted Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e);
        }
    }

    /**
     * Update the status of the specified resource in storage.
     */
    public function statusUpdate(Request $request)
    {
        try {
            if(!has_permissions('update', 'homepage-sections')) {
                return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
            }
            $section = HomepageSection::find($request->id);
            $section->is_active = $request->status == 1 ? 1 : 0;
            $section->save();
            ResponseService::successResponse(trans('Data Updated Successfully'));
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e);
        }
    }

    /**
     * Update the order of homepage sections.
     */
    public function updateOrder(Request $request)
    {
        try {
            if(!has_permissions('update', 'homepage-sections')) {
                ResponseService::errorResponse(PERMISSION_ERROR_MSG);
            }

            // Validate request data
            $validator = Validator::make($request->all(), [
                'sections' => 'required|array',
                'sections.*.id' => 'required|exists:homepage_sections,id',
                'sections.*.sort_order' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                ResponseService::validationError($validator->errors()->first());
            }

            DB::beginTransaction();

            // Update each section's sort_order
            foreach ($request->sections as $section) {
                HomepageSection::where('id', $section['id'])
                    ->update(['sort_order' => $section['sort_order']]);
            }

            DB::commit();

            ResponseService::successResponse(trans('Order Updated Successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e);
        }
    }
}
