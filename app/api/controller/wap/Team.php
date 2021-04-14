<?php

// +----------------------------------------------------------------------
// | A3Mall
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------
namespace app\api\controller\wap;
use dh2y\qrcode\QRcode;
use mall\basic\Users;
use mall\utils\Tool;
use think\facade\Db;
use think\facade\Request;

class Team extends Base
{
    public function poster()
    {
        $user = Users::get('id');
        $user = \app\common\model\users\Users::find($user);


        if ($user['poster'] == null){
            $code = new QRcode();
            $code_path =  $code->png(request()->domain().'/public/register?parent_id='.$user->id,false,7)        //生成二维码
                ->background(270,200,'static/images/bg.jpg')
                ->text("我是<".$user->nickname.">",40,['center',1020],'#999999')
                ->text('微信扫描二维码或长按识别',30,['center',1100],'#999999')
                ->getPath();
            $user->poster = $code_path;
            $user->save();
        }

        return $this->returnAjax('ok',1,['path' => request()->domain().$user->poster]);
    }


    public function share(){
        $page = Request::param("page","1","intval");
        $size = 10;
        $base_url = request()->domain();
        $parent_id = input('parent_id');
        if ($parent_id == ''){
            $parent_id = Users::get("id");
        }

        $count = Db::name("users")
            ->where("parent_id",$parent_id)
            ->count();

        $total = ceil($count/$size);
        if($total == $page -1){
            return $this->returnAjax("empty",-1,[
                "list"=>[],
                "page"=>$page,
                "total"=>$total,
                "size"=>$size,
                'num' => $count,
            ]);
        }

        $data = Db::name("users")
            ->where("parent_id",$parent_id)
            ->limit((($page - 1) * $size),$size)
            ->order('id DESC')->select()->toArray();
        $data = array_map(function ($rs) use ($base_url){
            $rs["avatar"] = substr($rs['avatar'],0,1)=='/'?$base_url.$rs['avatar']:$rs['avatar'];
            $rs["create_time"] = date("Y-m-d H:i:s",$rs["create_time"]);
            return $rs;
        },$data);

        $list = $data;

        return $this->returnAjax("ok",1,[
            "list"=>$list,
            "page"=>$page,
            "total"=>$total,
            "size"=>$size,
            'num' => $count,
        ]);
    }

}
