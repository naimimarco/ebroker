<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session as FacadesSession;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('create', 'language')) {
            ResponseService::errorRedirectResponse(PERMISSION_ERROR_MSG);
        }
        $language_count = Language::count();

        if ($language_count == 0) {
            $lang = new Language();
            $lang->name = "English";
            $lang->code = "en";
            $lang->file_name = "en.json";
            $lang->status = 1;
            $lang->save();
        }
        return view('settings.language');
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

        if (!has_permissions('create', 'language')) {
            ResponseService::errorRedirectResponse(PERMISSION_ERROR_MSG);
        }
        $request->validate([
            'name'              => 'required',
            'code'              => 'required|regex:/^[a-z0-9-]+$/|unique:languages',
            'file'              => 'required',
            'file_for_web'      => 'required',
            'file_for_panel'    => 'required',
        ]);
        $language = new Language();
        $language->name = $request->name;
        $language->code = $request->code;
        $language->status = 0;
        $language->rtl = $request->rtl == "true" ? true : false;

        if($request->code == 'en'){
            $languageCode = 'en-new';
        }else{
            $languageCode = $request->code;
        }
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if (strtolower($file->getClientOriginalExtension()) != 'json') {
                return redirect()->back()->with('error', 'Invalid File');
            }
            $filename = $languageCode . '.' . strtolower($file->getClientOriginalExtension());
            $file->move(base_path('public/languages/'), $filename);
            $language->file_name = $filename;
        }
        if ($request->hasFile('file_for_web')) {
            $file = $request->file('file_for_web');
            if (strtolower($file->getClientOriginalExtension()) != 'json') {
                return redirect()->back()->with('error', 'Invalid File');
            }
            $filename = $languageCode . '.' . strtolower($file->getClientOriginalExtension());
            $file->move(base_path('public/web_languages/'), $filename);
            $language->file_name = $filename;
        }
        if ($request->hasFile('file_for_panel')) {
            $file = $request->file('file_for_panel');
            if (strtolower($file->getClientOriginalExtension()) != 'json') {
                return redirect()->back()->with('error', 'Invalid File');
            }
            $filename = $languageCode . '.' . strtolower($file->getClientOriginalExtension());
            $file->move(base_path('resources/lang/'), $filename);
            $language->file_name = $filename;
        }
        $language->save();
        ResponseService::successRedirectResponse('Data Added Successfully');
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

        $sql = Language::orderBy($sort, $order);
        // dd($sql->toArray());


        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('code', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%");
        }

        $total = $sql->count();

        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }


        $res = $sql->get();
        // return $res;
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;
        $operate = '';
        foreach ($res as $row) {
            $defaultLanguageCode = system_setting('default_language');
            $tempRow = $row->toArray();
            $tempRow['rtl'] = $row->rtl ? "Yes" : "No";
            $tempRow['file_for_admin'] = file_exists(base_path('resources/lang/'.$row->file_name)) ? url('resources/lang/'.$row->file_name) : false;
            $tempRow['file_for_app'] = file_exists(base_path('public/languages/'.$row->file_name)) ? url('public/languages/'.$row->file_name) : false;
            $tempRow['file_for_web'] = file_exists(base_path('public/web_languages/'.$row->file_name)) ? url('public/web_languages/'.$row->file_name) : false;
            $ids = isset($row->parameter_types) ? $row->parameter_types : '';
            $operate = BootstrapTableService::editButton('', true, null, null, $row->id, null);
            if(isset($defaultLanguageCode) && !empty($defaultLanguageCode)){
                if($defaultLanguageCode != $row->code){
                    $operate .= BootstrapTableService::deleteButton(route('language.destroy', $row->id), $row->id);
                }
            }
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
        if (!has_permissions('update', 'language')) {
            ResponseService::errorRedirectResponse(PERMISSION_ERROR_MSG);
        }
        $request->validate([
            'edit_language_name' => 'required',
            'edit_language_code' => 'required|regex:/^[a-z0-9-]+$/|unique:languages,code,'.$request->edit_id.',id',
        ]);
        $defaultLanguageCode = system_setting('default_language');

        $language = Language::find($request->edit_id);
        $language->name = $request->edit_language_name;
        $language->status = 0;
        $language->rtl = $request->edit_rtl == "on" ? true : false;
        if($request->edit_language_code == 'en'){
            $languageCode = 'en-new';
        }else{
            $languageCode = $request->edit_language_code;
        }
        if($defaultLanguageCode == $language->code){
            Setting::where('type','default_language')->update(['data' => $languageCode]);
        }
        $language->code = $languageCode;

        // Edit App JSON File
        if ($request->hasFile('edit_json_app')) {
            $file = $request->file('edit_json_app');
            $filename = $languageCode . '.' . strtolower($file->getClientOriginalExtension());
            if (strtolower($file->getClientOriginalExtension()) != 'json') {
                return back()->with('error', 'Invalid File Type');
            }
            if (file_exists(base_path('public/languages/'.$languageCode))) {
                File::delete(base_path('public/languages/'.$languageCode));
            }
            $file->move(base_path('public/languages/'), $filename);
            $language->file_name = $filename;
        }

        // Edit Admin JSON File
        if ($request->hasFile('edit_json_admin')) {
            $file = $request->file('edit_json_admin');
            $filename = $languageCode . '.' . strtolower($file->getClientOriginalExtension());
            if (strtolower($file->getClientOriginalExtension()) != 'json') {
                return redirect()->back()->with('success', 'Invalid File');
            }
            if (file_exists(base_path('resources/lang/'.$languageCode))) {
                File::delete(base_path('resources/lang/'.$languageCode));
            }
            $file->move(base_path('resources/lang/'), $filename);
            $language->file_name = $filename;
        }

        // Edit Web JSON File
        if ($request->hasFile('edit_json_web')) {
            $file = $request->file('edit_json_web');
            $filename = $languageCode . '.' . strtolower($file->getClientOriginalExtension());
            if (strtolower($file->getClientOriginalExtension()) != 'json') {
                return redirect()->back()->with('error', 'Invalid File Type');
            }
            if (file_exists(base_path('public/web_languages/'.$languageCode))) {
                File::delete(base_path('public/web_languages/'.$languageCode));
            }
            $file->move(base_path('public/web_languages/'), $filename);
            $language->file_name = $filename;
        }


        $language->save();
        ResponseService::successRedirectResponse('Data Updated Successfully');
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

        if (!has_permissions('delete', 'language')) {
            ResponseService::errorRedirectResponse(PERMISSION_ERROR_MSG);
        } else {
            $language = Language::find($id);
            $languageData = $language;
            if ($language->delete()) {
                if (file_exists(base_path('public/languages/'.$languageData->file_name))) {
                    unlink(base_path('public/languages/'.$languageData->file_name));
                }
                if (file_exists(base_path('public/web_languages/'.$languageData->file_name))) {
                    unlink(base_path('public/web_languages/'.$languageData->file_name));
                }
                if (file_exists(base_path('resources/lang/'.$languageData->file_name))) {
                    unlink(base_path('resources/lang/'.$languageData->file_name));
                }
                return redirect()->back()->with('success', trans('Data Deleted Successfully'));
            } else {
                return redirect()->back()->with('error', trans('Something Wrong'));
            }
        }
    }
    public function set_language(Request $request)
    {
        FacadesSession::put('locale', $request->lang);
        $language = Language::where('code',$request->lang)->first();
        FacadesSession::put('language', $language);
        FacadesSession::save();
        app()->setLocale($request->lang);
        Artisan::call('cache:clear');
        return redirect()->back();
    }
    public function downloadPanelFile()
    {

        $file = base_path("resources/lang/en.json");
        $filename = 'admin_panel_en.json';

        return Response::download($file, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
    public function downloadAppFile()
    {
        $file = public_path("languages/en.json");
        $filename = 'app_en.json';

        return Response::download($file, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
    public function downloadWebFile()
    {
        $file = public_path("web_languages/en.json");

        $filename = 'web_en.json';

        return Response::download($file, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}

