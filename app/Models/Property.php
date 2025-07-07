<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAppTimezone;
class Property extends Model
{
    use HasFactory, HasAppTimezone;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'propertys';

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'address',
        'client_address',
        'propery_type',
        'price',
        'title_image',
        'state',
        'country',
        'state',
        'status',
        'request_status',
        'total_click',
        'latitude',
        'longitude',
        'three_d_image',
        'is_premium'
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    protected $appends = [
        'gallery',
        'documents',
        'is_favourite'
    ];

    protected static function boot() {
        parent::boot();
        static::deleting(static function ($property) {
            if(collect($property)->isNotEmpty()){
                // before delete() method call this

                // Delete Title Image
                if ($property->getRawOriginal('title_image') != '') {
                    $url = $property->title_image;
                    $relativePath = parse_url($url, PHP_URL_PATH);
                    if (file_exists(public_path()  . $relativePath)) {
                        unlink(public_path()  . $relativePath);
                    }
                }

                // Delete 3D image
                if ($property->getRawOriginal('three_d_image') != '') {
                    $url = $property->three_d_image;
                    $relativePath = parse_url($url, PHP_URL_PATH);
                    if (file_exists(public_path()  . $relativePath)) {
                        unlink(public_path()  . $relativePath);
                    }
                }

                // Delete Gallery Image
                if(isset($property->gallery) && collect($property->gallery)->isNotEmpty()){
                    $galleryImagePath = url('') . config('global.IMG_PATH') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id;
                    foreach ($property->gallery as $row) {
                        if (PropertyImages::where('id', $row->id)->delete()) {
                            if ($row->image != '') {
                                $url = $galleryImagePath. "/" .$row->image;
                                $relativePath = parse_url($url, PHP_URL_PATH);
                                $relativePath = parse_url($url, PHP_URL_PATH);

                                if (file_exists(public_path()  . $relativePath)) {
                                    unlink(public_path()  . $relativePath);
                                }
                            }
                        }
                    }
                    if(is_dir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id)){
                        rmdir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id);
                    }
                }

                // Delete Documents
                if(isset($property->documents) && collect($property->documents)->isNotEmpty()){
                    foreach ($property->documents as $row) {
                        if (PropertiesDocument::where('id', $row->id)->delete()) {
                            if ($row->getRawOriginal('name') != '') {
                                if (file_exists(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $property->id . "/" . $row->getRawOriginal('name'))) {
                                    unlink(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $property->id . "/" . $row->getRawOriginal('name'));
                                }
                            }
                        }
                    }
                    if(is_dir(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $property->id)){
                        rmdir(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $property->id);
                    }
                }
                /** Delete the properties associated data */
                // Delete Directly without modal boot events
                Advertisement::where('property_id', $property->id)->delete();
                AssignedOutdoorFacilities::where('property_id', $property->id)->delete();
                Favourite::where('property_id', $property->id)->delete();
                AssignParameters::where('property_id', $property->id)->delete();
                InterestedUser::where('property_id', $property->id)->delete();
                PropertysInquiry::where('propertys_id', $property->id)->delete();
                user_reports::where('property_id', $property->id)->delete();
                InterestedUser::where('property_id', $property->id)->delete();

                // Delete The Data with modal boot events
                $chats = Chats::where('property_id', $property->id)->get();
                if(collect($chats)->isNotEmpty()){
                    foreach ($chats as $chat) {
                        if(collect($chat)->isNotEmpty()){
                            $chat->delete(); // This will trigger the deleting and deleted events in modal
                        }
                    }
                }
                $sliders = Slider::where('propertys_id', $property->id)->get();
                if(collect($sliders)->isNotEmpty()){
                    foreach ($sliders as $slider) {
                        if(collect($slider)->isNotEmpty()){
                            $slider->delete(); // This will trigger the deleting and deleted events in modal
                        }
                    }
                }
                $notifications = Notifications::where('propertys_id', $property->id)->get();
                if(collect($notifications)->isNotEmpty()){
                    foreach ($notifications as $notification) {
                        if(collect($notification)->isNotEmpty()){
                            $notification->delete(); // This will trigger the deleting and deleted events in modal
                        }
                    }
                }
            }
        });
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->select('id', 'category', 'parameter_types', 'image');
    }
    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'added_by', 'fcm_id', 'notification');
    }
    public function user()
    {
        return $this->hasMany(User::class, 'id', 'added_by', 'fcm_id', 'notification');
    }

    public function assignParameter()
    {
        return  $this->morphMany(AssignParameters::class, 'modal');
    }

    public function parameters()
    {
        return $this->belongsToMany(parameter::class, 'assign_parameters', 'modal_id', 'parameter_id')->withPivot('value');
    }
    public function assignfacilities()
    {
        return $this->hasMany(AssignedOutdoorFacilities::class, 'property_id', 'id');
    }

    public function favourite()
    {
        return $this->hasMany(Favourite::class,'property_id','id');
    }
    public function interested_users()
    {
        return $this->hasMany(InterestedUser::class,'property_id');
    }
    // public function assign_parameter()
    // {
    //     return $this->hasMany(AssignParameters::class);
    // }
    public function advertisement()
    {
        return $this->hasMany(Advertisement::class)->where('for', 'property');
    }

    public function reject_reason(){
        return $this->hasMany(RejectReason::class,'property_id');
    }

    public function getGalleryAttribute()
    {
        $data = PropertyImages::select('id', 'image')->where('propertys_id', $this->id)->get();


        foreach ($data as $item) {
            if ($item['image'] != '') {
                $item['image'] = $item['image'];
                $item['image_url'] = ($item['image'] != '') ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_GALLERY_IMG_PATH') . $this->id . "/" . $item['image'] : '';
            }
        }
        return $data;
    }
    public function getTitleImageAttribute($image)
    {

        return $image != '' ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_TITLE_IMG_PATH') . $image : '';
    }


    public function getMetaImageAttribute($image)
    {

        return $image != '' ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_SEO_IMG_PATH') . $image : '';
    }
    public function getThreeDImageAttribute($image)
    {
        return $image != '' ? url('') . config('global.IMG_PATH') . config('global.3D_IMG_PATH') . $image : '';
    }

    public function getProperyTypeAttribute($value){
        if ($value == 0) {
            return "sell";
        } elseif ($value == 1) {
            return "rent";
        } elseif ($value == 2) {
            return "sold";
        } elseif ($value == 3) {
            return "rented";
        }
    }


    public function getIsPromotedAttribute() {
        $id = $this->id;
        return $this->whereHas('advertisement',function($query) use($id){
            $query->where(['property_id' => $id, 'status' => 0, 'is_enable' => 1]);
        })->count() ? true : false;
    }

    public function getHomePromotedAttribute() {
        $id = $this->id;
        return $this->whereHas('advertisement',function($query) use($id){
            $query->where(['property_id' => $id,'type' => 'HomeScreen', 'status' => 0, 'is_enable' => 1]);
        })->count() ? true : false;
    }

    public function getListPromotedAttribute() {
        $id = $this->id;
        return $this->whereHas('advertisement',function($query) use($id){
            $query->where(['property_id' => $id,'type' => 'ProductListing', 'status' => 0, 'is_enable' => 1]);
        })->count() ? true : false;
    }

    public function getIsFavouriteAttribute() {
        $propertyId = $this->id;
        $auth = Auth::guard('sanctum');
        if($auth->check()){
            $userId = $auth->user()->id;
            return $this->whereHas('favourite',function($query) use($userId,$propertyId){
                $query->where(['user_id' => $userId, 'property_id' => $propertyId]);
            })->count() >= 1 ? 1 : 0;
        }
        return 0;
    }

    public function getParametersAttribute(){

        $parameterQueryData = $this->parameters()->get();
        if(isset($parameterQueryData) && !empty($parameterQueryData)){
            $parameters = [];
            foreach ($parameterQueryData as $res) {
                    $res = (object)$res;
                    if (is_string($res['pivot']['value']) && is_array(json_decode($res['pivot']['value'], true))) {
                        $value = json_decode($res['pivot']['value'], true);
                    } else {
                        if ($res['type_of_parameter'] == "file") {
                            if ($res['pivot']['value'] == "null") {
                                $value = "";
                            } else {
                                $value = url('') . config('global.IMG_PATH') . config('global.PARAMETER_IMG_PATH') . '/' .  $res['pivot']['value'];
                            }
                        } else {
                            if ($res['pivot']['value'] == "null") {
                                $value = "";
                            } else {
                                $value = $res['pivot']['value'];
                            }
                        }
                    }

                    if(collect($value)->isNotEmpty()){
                        $parameters[] = [
                            'id' => $res->id,
                            'name' => $res->name,
                            'image' => $res->image,
                            'is_required' => $res->is_required,
                            'type_of_parameter' => $res->type_of_parameter,
                            'type_values' => $res->type_values,
                            'value' => $value,
                        ];
                    }
                }
            }
        return $parameters ?? null;
    }
    public function getAssignFacilitiesAttribute(){
        $assignFacilitiesQuery = $this->assignfacilities()->with('outdoorfacilities:id,name,image')->get();
        if(collect($assignFacilitiesQuery)->isNotEmpty()){
            $assignFacilitiesData = [];
            foreach ($assignFacilitiesQuery as $facility) {
                if(collect($facility->outdoorfacilities)->isNotEmpty()){
                    $assignFacilitiesData[] = [
                        'id' => $facility->id,
                        'property_id' => $facility->property_id,
                        'facility_id' => $facility->facility_id,
                        'distance' => $facility->distance,
                        'created_at' => $facility->created_at,
                        'updated_at' => $facility->updated_at,
                        'name' => $facility->outdoorfacilities->name,
                        'image' => $facility->outdoorfacilities->image,
                    ];
                }
            }
        }
        return !empty($assignFacilitiesData) ? $assignFacilitiesData :  array();
    }


    public function getDocumentsAttribute()
    {
        return PropertiesDocument::select('id', 'property_id', 'name', 'type')->where('property_id', $this->id)->get()->map(function($document){
            $document->id = $document->id;
            $document->file_name = $document->getRawOriginal('name');
            $document->file = $document->name;
            unset($document->name);
            return $document;
        });
    }

    public function getIsUserVerifiedAttribute(){
        return $this->whereHas('customer.verify_customer',function($query){
            $query->where(['user_id' => $this->added_by, 'status' => 'success']);
        })->count() ? true : false;
    }

    public function getIsFeatureAvailableAttribute()
    {
        $id = $this->id;

        // Check if the property type is 0 or 1
        $isPropertyTypeValid = $this->where('id', $this->id)
            ->whereIn('propery_type', [0, 1])->where(['status' => 1, 'request_status' => 'approved'])
            ->exists();

        // Check if there is no advertisement or if the advertisement has expired
        $hasExpiredAdvertisement = !$this->advertisement()->exists() ||
            $this->whereHas('advertisement', function ($query) use ($id) {
                $query->where('property_id', $id)->where('status', 3);
            })->exists();

        return $isPropertyTypeValid && $hasExpiredAdvertisement;
    }


    protected $casts = [
        'category_id' => 'integer',
        'status' => 'integer'
    ];
}
