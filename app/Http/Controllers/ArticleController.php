<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Auth;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'article')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }

        $articles = Article::all();
        return view('article.index', ['articles' => $articles]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!has_permissions('create', 'article')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        $category = Category::where('status', 1)->get();
        $recent_articles = Article::with('category:id,category')->orderBy('id', 'DESC')->limit(5)->get();
        return view('article.create', ['category' => $category, 'recent_articles' => $recent_articles]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!has_permissions('create', 'article')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'slug' => 'nullable|regex:/^[a-z0-9-]+$/|unique:articles,slug_id',
                'image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            ]);

            try {
                $destinationPath = public_path('images') . config('global.ARTICLE_IMG_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $article = new Article();
                $article->title = $request->title;
                $article->slug_id = $request->slug ?? generateUniqueSlug($request->title,2);
                $article->description = $request->description;
                $article->category_id = isset($request->category) ? $request->category : '';

                if ($request->hasFile('image')) {
                    $profile = $request->file('image');
                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                    $profile->move($destinationPath, $imageName);
                    $article->image = $imageName;
                } else {
                    $article->image  = '';
                }

                $article->meta_title = $request->meta_title;
                $article->meta_description = $request->meta_description;
                $article->meta_keywords = $request->meta_keywords;
                $article->save();
                return back()->with('success', 'Successfully Added');
            } catch (Exception $e) {
                return back()->with('error', 'Something Went Wrong');
            }
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
        if (!has_permissions('read', 'article')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        }
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');

        $sql = Article::with('category')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orWhere('title', 'LIKE', "%$search%")
                        ->orWhereHas('category',function($query) use($search){
                            $query->where('category','LIKE', "%$search%");
                        });
                    if (Str::contains(Str::lower($search), 'general')) {
                        $query->orWhere('category_id', 0);
                    }
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

            $operate = BootstrapTableService::editButton(route('article.edit',$row->id), false, null, null, null, null);
            $operate .= BootstrapTableService::deleteAjaxButton(route('article.destroy', $row->id));

            $tempRow = $row->toArray();
            $tempRow['category_title'] = $row->category_id == 0 ? 'General' : $row->category->category;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
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
        if (!has_permissions('update', 'article')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        $list = Article::where('id', $id)->first();
        $category = Category::all();
        $recent_articles = Article::with('category:id,category')->orderBy('id', 'DESC')->limit(6)->get();
        return view('article.edit', compact('list', 'category', 'id', 'recent_articles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!has_permissions('update', 'article')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        }
        $request->validate([
            'slug' => 'nullable|regex:/^[a-z0-9-]+$/|unique:articles,slug_id,'.$id.',id',
            'image' => 'image|mimes:jpg,png,jpeg|max:2048',
        ]);
        try {
            $destinationPath = public_path('images') . config('global.ARTICLE_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $updateArticle = Article::find($id);
            if ($request->hasFile('image')) {
                \unlink_image($updateArticle->image);
                $updateArticle->image = \store_image($request->file('image'), 'ARTICLE_IMG_PATH');
            }
            $updateArticle->title = $request->title;
            $updateArticle->slug_id = $request->slug ?? generateUniqueSlug($request->title,2,null,$id);
            $updateArticle->meta_title = $request->edit_meta_title;
            $updateArticle->meta_description = $request->edit_meta_description;
            $updateArticle->meta_keywords = $request->meta_keywords;
            $updateArticle->description = $request->description;
            $updateArticle->category_id = isset($request->category) ? $request->category : '';
            $updateArticle->update();
            return back()->with('success', 'Successfully Update');
        } catch (Exception $e) {
            return back()->with('error', 'Something Went Wrong');
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
        if (env('DEMO_MODE') && Auth::user()->email != "superadmin@gmail.com") {
            return redirect()->back()->with('error', 'This is not allowed in the Demo Version');
        }

        if (!has_permissions('delete', 'article')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $article = Article::find($id);
            if ($article->delete()) {


                if ($article->image != '') {

                    $url = $article->image;
                    $relativePath = parse_url($url, PHP_URL_PATH);

                    if (file_exists(public_path()  . $relativePath)) {
                        unlink(public_path()  . $relativePath);
                    }
                }

                // Notifications::where('articles_id', $id)->delete();
//                return back()->with('success', 'Article Deleted Successfully');
                ResponseService::successResponse("Article Deleted Successfully");
            } else {
                ResponseService::errorResponse("Something Wrong");
            }
        }
    }

    public function generateAndCheckSlug(Request $request){
        // Validation
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }

        // Generate the slug or throw exception
        try {
            $title = $request->title;
            $id = $request->has('id') && !empty($request->id) ? $request->id : null;
            if($id){
                $slug = generateUniqueSlug($title,2,null,$id);
            }else{
                $slug = generateUniqueSlug($title,2);
            }
            ResponseService::successResponse("",$slug);
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e, "Article Slug Generation Error", "Something Went Wrong");
        }
    }
}
