<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCreateItemRequest;
use App\Http\Requests\AdminUpdateItemRequest;
use App\Http\Requests\UserCreateItemRequest;
use App\Http\Requests\UserUpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ItemController extends Controller
{
    public function store(AdminCreateItemRequest $request){
        $result = $this->create($request->validated());
        if($result)
            return $this->apiResponse(['laravel'=>new ItemResource($result['item']),'WP'=>$result['response']], self::STATUS_CREATED, __('site.created_successfully'));
        return $this->apiResponse(null, self::STATUS_NOT_FOUND, __('site.there_is_no_data'));
    }

    public function show($item_id){
        $result = $this->view($item_id,[]);
        if($result)
            return $this->apiResponse(['laravel'=>new ItemResource($result['item']),'WP'=>$result['response']],self::STATUS_OK,__('site.get_successfully'));
        return $this->apiResponse([], self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function update(AdminUpdateItemRequest $request,$item_id){
        $result = $this->edit($request->validated(),$item_id );
        if($result)
            return $this->apiResponse(['laravel'=>new ItemResource($result['item']),'WP'=>$result['response']],self::STATUS_OK,__('site.updated_successfully'));
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

    public function user_show($item_id) {
        $result = $this->view($item_id,['storage']);
        if($result)
            if($result['item']->storage->user_id==auth()->user()->id)
            return $this->apiResponse(['laravel'=>new ItemResource($result['item']),'WP'=>$result['response']],self::STATUS_OK,__('site.get_successfully'));
        return $this->apiResponse([], self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function user_store(UserCreateItemRequest $request) {
        if (auth()->user()->storage) {
            $data=$request->validated();
            $data['storage_id']=auth()->user()->storage->id;
            $result = $this->create($data);
            if ($result)
                return $this->apiResponse(['laravel' => new ItemResource($result['item']), 'WP' => $result['response']], self::STATUS_CREATED, __('site.created_successfully'));
            return $this->apiResponse(null, self::STATUS_NOT_FOUND, __('site.there_is_no_data'));
        }
        return $this->apiResponse(null, self::STATUS_NOT_FOUND, __('site.you_do_not_have_storage'));
    }

    public function user_index() {
        $items = $this->user_paginate([],15);
        if(count($items)>0) {
            $paginateData = $this->formatPaginateData($items);
            return $this->apiResponse(ItemResource::collection($items), self::STATUS_OK, __('site.get_successfully'),$paginateData);
        }
        return $this->apiResponse([], self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function user_update(UserUpdateItemRequest $request,$item_id) {
        $result = $this->user_edit($request->validated(),$item_id );
        if($result)
            return $this->apiResponse(['laravel'=>new ItemResource($result['item']),'WP'=>$result['response']],self::STATUS_OK,__('site.updated_successfully'));
        return $this->apiResponse(null, self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function user_destroy($item_id) {
        $item = $this->user_delete($item_id);
        if($item)
            return $this->apiResponse(true,self::STATUS_OK,__('site.deleted_successfully'));
        return $this->apiResponse(null, self::STATUS_OK, __('site.there_is_no_data'));
    }

    public function find_item($item_id) {
        return Item::find($item_id);
    }
    private function create($validated) {
        $storage_id=$validated['storage_id'];
        unset($validated['storage_id']);
        $response=Http::post(env('WORDPRESS_URL').'test/items',$validated)->json();
        if($response) {
            return ['item'=>Item::create(['storage_id'=>$storage_id,'wp_id'=>$response['data']['ID']]),
                    'response'=>$response
            ];
        }
        return false;
    }

    private function view($item_id,$relations=[]) {
        $item=$this->find_item($item_id);
        if($item) {
            $response = Http::get(env('WORDPRESS_URL') . 'test/items/' . $item->wp_id)->json();
            return [
                    'item' => $item,
                    'response' => $response
            ];
        }
        return false;
    }

    private function user_edit($validated, $item_id,$relations=[]) {
        $item=$this->find_item($item_id);
        if($item) {
            $response = Http::put(env('WORDPRESS_URL') . 'test/items/' . $item->wp_id, $validated)->json();
            return [
                    'item' => $item,
                    'response' => $response
            ];
        }
        return false;
    }

    private function edit($validated, $item_id,$relations=[]) {
        $item=$this->find_item($item_id);
        if(isset($validated['storage_id'])) {
            $item->fill($validated);
            $item->save();
            unset($validated['storage_id']);
        }
        $response=Http::put(env('WORDPRESS_URL').'test/items/'.$item->wp_id,$validated)->json();
        return [
                'item'=>$item,
                'response'=>$response
        ];
    }

    private function delete($item_id) {
        $item=$this->find_item($item_id);
        if($item){
            $response=Http::delete(env('WORDPRESS_URL').'test/items/'.$item->wp_id)->json();
            $item->delete();
            return true;
        }
        return null;
    }
    private function user_delete($item_id) {
        $item=$this->find_item($item_id);
        if($item){
            if($item->storage_id==auth()->user()->storage)
            $response=Http::delete(env('WORDPRESS_URL').'test/items/'.$item->wp_id)->json();
            $item->delete();
            return true;
        }
        return null;
    }

    private function paginate($relations=null,$per_page=10) {
        return Item::with($relations)->paginate($per_page);
    }
    private function user_paginate($relations=null,$per_page=10) {
        return Item::with($relations)->where('storage_id',auth()->user()->storage->id)->paginate($per_page);
    }
}
