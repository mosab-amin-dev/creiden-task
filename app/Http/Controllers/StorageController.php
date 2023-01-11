<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorageRequest;
use App\Http\Resources\StorageResource;
use App\Models\Storage;
use App\Models\User;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function store(StorageRequest $request){
        $storage = $this->create($request->validated());
        if($storage)
        return $this->apiResponse(new StorageResource($storage), self::STATUS_CREATED, __('site.created_successfully'));
        return $this->apiResponse(null, self::STATUS_OK, __('site.already_has_storage'));
    }

    public function show($id){
        $storage = $this->view($id,[]);
        if($storage)
            return $this->apiResponse(new StorageResource($storage),self::STATUS_OK,__('site.get_successfully'));
        return $this->apiResponse(null, self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function update(StorageRequest $request,$storage_id){
        $storage = $this->edit($request->validated(),$storage_id );
        if($storage)
            return $this->apiResponse(new StorageResource($storage),self::STATUS_OK,__('site.updated_successfully'));
        return $this->apiResponse(null, self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function destroy($storage_id){
        $storage = $this->delete($storage_id);
        if($storage)
            return $this->apiResponse(true,self::STATUS_OK,__('site.deleted_successfully'));
        return $this->apiResponse(null, self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function index()
    {
        $storages = $this->paginate();
        if(count($storages)>0) {
            $paginateData = $this->formatPaginateData($storages);
            return $this->apiResponse(StorageResource::collection($storages), self::STATUS_OK, __('site.get_successfully'),$paginateData);
        }
        return $this->apiResponse([], self::STATUS_OK, __('site.there_is_no_data'));
    }

    private function create($validated) {
        $storage=Storage::with([])->where('user_id',$validated['user_id'])->first();
        if(!$storage)
            return Storage::create($validated);
        else
            return false;
    }

    private function view($id,$relations=[]) {
        return Storage::with($relations)->find($id);
    }

    private function edit($validated, $storage_id,$relations=[]) {
        $storage=$this->view($storage_id,$relations);
        $storage->fill($validated);
        $storage->save();
        return $storage;
    }

    private function delete($storage_id) {
        $storage=$this->view($storage_id);
        if($storage) {
            $storage->delete();
            return true;
        }
        return null;
    }

    private function paginate($relations=[],$per_page=10) {
        return Storage::with($relations)->paginate($per_page);
    }
}
