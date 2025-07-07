<?php

namespace App\Http\Controllers;

use App\Models\SeoSettings;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class SeoSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = [
            'homepage',
            'all-categories',
            'about-us',
            'articles',
            'chat',
            'contact-us',
            'featured-properties',
            'properties-on-map',
            'most-viewed-properties',
            'most-favorite-properties',
            'privacy-policy',
            'all-properties',
            'properties-nearby-city',
            'search',
            'subscription-plan',
            'terms-and-condition',
            'profile',
            'user-register',
            'all-agents',
            'agent-details',
            'faqs'
        ];
        $seo_pages = SeoSettings::pluck('page');

        return \view('seo_settings.index', compact('seo_pages', 'pages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!has_permissions('create', 'seo_setting')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'meta_title' => 'required',
                'meta_description' => 'required',
                'keywords' => 'required',
                'image' => 'required|image|mimes:jpg,png,jpeg,svg|max:2048',

            ]);
            $seo_setting = new SeoSettings();
            $seo_setting->page = $request->page;
            $seo_setting->title = $request->meta_title;
            $seo_setting->description = $request->meta_description;
            $seo_setting->keywords = $request->keywords;

            $destinationPath = public_path('images') . config('global.SEO_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            // image upload


            if ($request->hasFile('image')) {
                $profile = $request->file('image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPath, $imageName);
                $seo_setting->image = $imageName;
            } else {
                $seo_setting->image  = '';
            }

            $seo_setting->save();
            ResponseService::successRedirectResponse('Data Created Successfully');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $sql = SeoSettings::orderBy($sort, $order);

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('page', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%")->orwhere('description', 'LIKE', "%$search%");
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
        $tempRow['type'] = '';
        $parameter_name_arr = [];
        foreach ($res as $row) {
            $tempRow = $row->toArray();


            $operate = BootstrapTableService::editButton('', true, null, null, $row->id);
            $operate .= BootstrapTableService::deleteButton(route('seo_settings.destroy', $row->id), $row->id);

            $tempRow['operate'] = $operate;

            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        if (!has_permissions('create', 'seo_setting')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {

            $seo_setting = SeoSettings::find($request->edit_id);
            $seo_setting->page = $request->edit_page;
            $seo_setting->title = $request->edit_meta_title;
            $seo_setting->description = $request->edit_meta_description;
            $seo_setting->keywords = $request->edit_keywords;

            $destinationPath = public_path('images') . config('global.SEO_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            // image upload

            if ($request->hasFile('edit_image')) {
                $url = $seo_setting->image;
                $relativePath = parse_url($url, PHP_URL_PATH);

                if (file_exists(public_path()  . $relativePath)) {
                    unlink(public_path()  . $relativePath);
                }
                $profile = $request->file('edit_image');

                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPath, $imageName);
                $seo_setting->image = $imageName;
            }

            $seo_setting->save();
            ResponseService::successRedirectResponse('Data Updated Successfully');
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

        if (!has_permissions('delete', 'seo_setting')) {
            return redirect()->back()->with('error', env('PERMISSION_ERROR_MSG'));
        } else {
            $seo_setting = SeoSettings::find($id);
            if ($seo_setting->delete()) {


                if ($seo_setting->image != '') {

                    $url = $seo_setting->image;
                    $relativePath = parse_url($url, PHP_URL_PATH);

                    if (file_exists(public_path()  . $relativePath)) {
                        unlink(public_path()  . $relativePath);
                    }
                }

                ResponseService::successRedirectResponse('Data Deleted Successfully');
            } else {
                ResponseService::errorRedirectResponse(\null, 'Something Wrong');
            }
        }
    }
}
