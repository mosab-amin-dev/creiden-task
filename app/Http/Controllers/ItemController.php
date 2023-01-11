<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCreateItemRequest;
use App\Http\Requests\AdminUpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function store(AdminCreateItemRequest $request){
        $item = $this->create($request->validated());
        return $this->apiResponse(new ItemResource($item), self::STATUS_CREATED, __('site.created_successfully'));
    }

    public function show($id){
        $item = $this->view($id,[]);
        if($item)
            return $this->apiResponse(new ItemResource($item),self::STATUS_OK,__('site.get_successfully'));
        return $this->apiResponse(null, self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function update(AdminUpdateItemRequest $request,$item_id){
        $item = $this->edit($request->validated(),$item_id );
        if($item)
            return $this->apiResponse(new ItemResource($item),self::STATUS_OK,__('site.updated_successfully'));
        return $this->apiResponse(null, self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function destroy($item_id){
        $item = $this->delete($item_id);
        if($item)
            return $this->apiResponse(true,self::STATUS_OK,__('site.deleted_successfully'));
        return $this->apiResponse(null, self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function index()
    {
        $items = $this->paginate([],15);
        if(count($items)>0) {
            $paginateData = $this->formatPaginateData($items);
            return $this->apiResponse(ItemResource::collection($items), self::STATUS_OK, __('site.get_successfully'),$paginateData);
        }
        return $this->apiResponse([], self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function user_index() {

    }

    public function user_show() {

    }

    public function user_store($request) {

    }

    public function user_update($request,$item_id) {

    }

    public function user_destroy($id) {

    }

    private function create($validated) {
        return Item::create($validated);
    }

    private function view($id,$relations=null) {
        return Item::with($relations)->find($id);
    }

    private function edit($validated, $item_id,$relations=null) {
        $item=$this->view($item_id,$relations);
        $item->fill($validated);
        $item->save();
        return $item;
    }

    private function delete($item_id) {
        $item=$this->view($item_id);
        if($item) {
            $item->delete();
            return true;
        }
        return null;
    }

    private function paginate($relations=null,$per_page=10) {
        return Item::with($relations)->paginate($per_page);
    }
}
