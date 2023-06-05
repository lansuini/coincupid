<?php

namespace App\Http\Controllers\Analysis;

use App\Models\AccountInfo;
use App\Models\Manager\Analysis\UserIntention;
use App\Models\Manager\Analysis\UserTag;
use App\Models\Manager\Analysis\UserTagAttr;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;


class ConfigSetController extends AnalysisController
{
    public function configtagView(Request $request)
    {
        return view('Analysis/ConfigSet/configTagView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    /**
     * 标签列表
     * @param Request $request
     * @return array
     */
    public function configTagList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'tag_id');
        $order = $request->query->get('order', 'desc');
        $tagName = $request->query->get('tag_name');
        $attrId = $request->query->get('attr_id');

        $attrInfoModel=new UserTagAttr();
        $attrInfo=$attrInfoModel->get()->toArray();

        $model = new UserTag();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $model = $model->where('deleted_at', null);

        $tagName && $model = $model->where('tag_name', 'like', '%' . $tagName . '%');
        $attrId && $model = $model->where('attr_id', $attrId);

        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();



        return [
            'result' => ['attrTag'=>$attrInfo],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }

    //添加标签
    public function tagAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_name' => ['required', 'string', 'max:20'],
            'attr_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }
        $res=DB::table("user_tag")->insert([
            'tag_name'=>$request['tag_name'],
            'tag_name_name'=>$request['tag_name_name'],
            'attr_id'=>$request['attr_id'],
        ]);


        if($res===false){
            return ['success' => 0, 'result' => __('ts.create error')];
        }

        return ['success' => 1, 'result' => __('ts.create success')];
    }

    //标签详情
    public function tagDetail(Request $request, $id)
    {
        $data = UserTag::select(
            'tag_id',
            'tag_name',
            'attr_id'
        )
            ->where('tag_id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }

    //编辑标签
    public function tagEdit(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'tag_name' => ['required', 'string', 'max:20'],
            'attr_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }
        $before = UserTag::select(
            'tag_name',
            'attr_id',
            'uid'
        )->where('tag_id', $id)->first();
        if ($before->uid!=null || $before->uid!='') {
            return ['success' => 0, 'result' => __('ts.uid error')];
        }

        $data = $request->only(
            'tag_name',
            'tag_name_en',
            'attr_id'
        );
        DB::table("user_tag")->where('tag_id', $id)->update($data);

        return ['success' => 1, 'result' => __('ts.update success')];
    }

    //删除标签（假删除）
    public function tagDel(Request $request, $id)
    {
        $before = UserTag::select(
            'tag_name',
            'attr_id',
            'uid'
        )->where('tag_id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        if ($before->uid!=null || $before->uid!='') {
            return ['success' => 0, 'result' => __('ts.uid error')];
        }

        DB::table("user_tag")->where('tag_id', $id)->update(['deleted_at'=>date('Y-m-d H:i:s')]);

        return ['success' => 1, 'result' => __('ts.delete success')];
    }


    public function configAttrTagView(Request $request)
    {
        return view('Analysis/ConfigSet/configAttrTagView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    public function configAttrTagList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'attr_id');
        $order = $request->query->get('order', 'desc');
        $tagName = $request->query->get('attr_name');

        $model = new UserTagAttr();
        !empty($sort) && $model = $model->orderBy($sort, $order);
//        $model = $model->where('deleted_at', null);

        $tagName && $model = $model->where('attr_name', 'like', '%' . $tagName . '%');

        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }
    //添加标签类目
    public function attrTagAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attr_name' => ['required', 'string', 'max:20'],
            'display' => ['integer', Rule::in([0,1])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }
        $res=DB::table("user_tag_attr")->insert([
            'attr_name'=>$request['attr_name'],
            'attr_name_en'=>$request['attr_name_en'],
            'display'=>$request['display'],
        ]);


        if($res===false){
            return ['success' => 0, 'result' => __('ts.create error')];
        }

        return ['success' => 1, 'result' => __('ts.create success')];
    }

    //标签类目详情
    public function attrTagDetail(Request $request, $id)
    {
        $data = UserTagAttr::select(
            'attr_id',
            'attr_name',
            'attr_name_en',
            'display'
        )->where('attr_id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }

    //编辑标签类目
    public function attrTagEdit(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'attr_name' => ['required', 'string', 'max:20'],
            'display' => ['integer', Rule::in([0,1])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }
        $before = UserTag::select(
            'uid'
        )->where('attr_id', $id)->first();
        if ($before->uid!=null || $before->uid!='') {
            return ['success' => 0, 'result' => __('ts.uid error')];
        }

        $data = $request->only(
            'attr_name',
            'attr_name_en',
            'display',
        );
        DB::table("user_tag_attr")->where('attr_id', $id)->update($data);

        return ['success' => 1, 'result' => __('ts.update success')];
    }

    //删除标签类目（假删除）
    public function attrTagDel(Request $request, $id)
    {
        $before = UserTag::select(
            'uid'
        )->where('attr_id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        if ($before->uid!=null || $before->uid!='') {
            return ['success' => 0, 'result' => __('ts.uid error')];
        }

        DB::table("user_tag_attr")->where('attr_id', $id)->update(['deleted_at'=>date('Y-m-d H:i:s')]);

        return ['success' => 1, 'result' => __('ts.delete success')];
    }


    //注册目的view
    public function configIntentionView(Request $request)
    {
        return view('Analysis/ConfigSet/configIntentionView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    public function configIntentionList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $tagName = $request->query->get('name');

        $model = new UserIntention();
        !empty($sort) && $model = $model->orderBy($sort, $order);
//        $model = $model->where('deleted_at', null);

        $tagName && $model = $model->where('name', 'like', '%' . $tagName . '%');

        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }
    public function intentionDetail(Request $request, $id)
    {
        $data = UserIntention::select(
            'id',
            'name',
            'name_en',
            'display'
        )->where('id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }
    public function intentionAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:20'],
            'display' => ['integer', Rule::in([0,1])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }
        $res=DB::table("user_intention")->insert([
            'name'=>$request['name'],
            'name_en'=>$request['name_en'],
            'display'=>$request['display'],
        ]);


        if($res===false){
            return ['success' => 0, 'result' => __('ts.create error')];
        }

        return ['success' => 1, 'result' => __('ts.create success')];
    }
    public function intentionEdit(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:20'],
            'display' => ['integer', Rule::in([0,1])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }
        $before = AccountInfo::select(
            'uid','intention'
        )->whereRaw(\DB::raw('FIND_IN_SET('.$id.',intention)'))->count();
        if ($before!=0 ) {
            return ['success' => 0, 'result' => __('ts.uid error')];
        }

        $data = $request->only(
            'name',
            'name_en',
            'display',
        );
        DB::table("user_intention")->where('id', $id)->update($data);

        return ['success' => 1, 'result' => __('ts.update success')];
    }

}
