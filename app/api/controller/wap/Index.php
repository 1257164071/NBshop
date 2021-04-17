<?php
// +----------------------------------------------------------------------
// | A3Mall
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------
namespace app\api\controller\wap;

use app\common\model\custom\Pages;
use dh2y\qrcode\QRcode;
use http\Client\Curl\User;
use mall\basic\Setting;
use mall\basic\Users;
use mall\response\Response;
use mall\utils\Tool;
use think\facade\Db;
use think\facade\Request;

class Index extends Base {

    public function index(){


//        $code = new QRcode();
//        $code_path =  $code->png('http://lifeng.dichangshangmao.com/public/login?parent_i=2121',false,7)        //生成二维码
////            ->logo('https://thirdwx.qlogo.cn/mmopen/vi_32/ib10rznyxX8QoMZBng3Aa3LQIQehvjoBUpq34OyVyx0ibUCxpewPm5FicSOCthiau5iaS92OxBiavLHfMU2WHx8WP61g/132')
//            ->background(240,200,'static/images/bg.jpg')
//            ->text('我是<哲生>',40,['center',1020],'#999999')
//            ->text('微信扫描或二维码或长按识别',30,['center',1100],'#999999')
//            ->getPath();
//
//        dump($code_path);
//        die;

        /*
         * 首次消费
         * 购买99元给上级7级返利
         * 7级若满足对应会员等级 即可获得拿钱条件
         * 1级 无条件获得
         * 2级3级  需要分享2单
         * 4级5级  需要分享4单
         * 6级7级  需要分享7单
         *
         * 数据结构
         * 不同级别能否获得钱数 取决于直推用户第一次购买的有多少
         * 获得多少钱数取决于 直推用户级别
         * 根据USER表 is_consumption 确定是否已第一次购买
         *
         *
         *
         */
//        $group_id = Db::name("users_group")->order('minexp','ASC')->value("id");
//
//        $data = [
//            "group_id"=>$group_id,
//            "username"=>'ffdxxxfx',
//            "mobile"=>'18716251981',
//            "password"=>md5(123456),
//            "status"=>0,
//            "create_ip"=>Request::ip(),
//            "last_ip"=>Request::ip(),
//            "create_time"=>time(),
//            "last_login"=>time(),
//            "parent_id"=>17,
//        ];
//
//        $user = new \app\common\model\users\Users;
//        $res = $user->save($data);
//dump($res);
//        die;
        $banner = Db::name("data")->where("sign","banner")->find();
        $slider = array_map(function($res){
            return Tool::thumb($res["photo"],"",true);
        },Db::name("data_item")->where("pid",$banner["id"])->order("sort","ASC")->select()->toArray());

        $category = Db::name("data")->where("sign","category")->find();
        $nav = array_map(function($res){
            return [
                "url"=>$res["url"],
                "name"=>$res["name"],
                "image"=>Tool::thumb($res["photo"],"",true)
            ];
        },Db::name("data_item")->where("pid",$category["id"])->order("sort","ASC")->select()->toArray());

        $adOne = Db::name("data")->where("sign","home_ad_one")->find();
        $adItemOne = array_map(function($res){
            return [
                "url"=>$res["url"],
                "name"=>$res["name"],
                "image"=>Tool::thumb($res["photo"],"",true)
            ];
        },Db::name("data_item")->where("pid",$adOne["id"])->order("sort","ASC")->select()->toArray());

        $adTwo = Db::name("data")->where("sign","home_ad_two")->find();
        $adItemTwo = array_map(function($res){
            return [
                "url"=>$res["url"],
                "name"=>$res["name"],
                "image"=>Tool::thumb($res["photo"],"",true)
            ];
        },Db::name("data_item")->where("pid",$adTwo["id"])->order("sort","ASC")->select()->toArray());

        $hot = array_map(function ($res){
                return [
                    "id"=> $res["id"],
                    "url"=>'/goods/view/'.$res["id"],
                    "name"=>$res["title"],
                    "image"=>Tool::thumb($res["photo"],"",true),
                    "price"=>$res["sell_price"]
                ];
            },
            Db::name("goods_extends")
            ->alias("e")->field("g.*")->join("goods g","e.goods_id=g.id","LEFT")
            ->where('g.status',0)->where("e.attribute","hot")
            ->order("e.id","DESC")->limit(3)->select()->toArray()
        );

        $recommend = array_map(function ($res){
            return [
                "id"=> $res["id"],
                "url"=>'/goods/view/'.$res["id"],
                "name"=>$res["title"],
                "image"=>Tool::thumb($res["photo"],"",true),
                "price"=>$res["sell_price"]
            ];
        },
            Db::name("goods_extends")
                ->alias("e")->field("g.*")->join("goods g","e.goods_id=g.id","LEFT")
                ->where('g.status',0)->where("e.attribute","recommend")
                ->order("e.id","DESC")->limit(5)->select()->toArray()
        );

        $notice = Db::name("archives")->field('id,title')->where("status",0)->where('pid',71)->find();

        return $this->returnAjax("ok",1,[
            "banner"=>$slider,
            "nav"=>$nav,
            "img_1"=>isset($adItemOne[0]) ? $adItemOne[0] : [],
            "img_2"=>$adItemTwo,
            "hot"=>$hot,
            "recommend"=>$recommend,
            "notice"=>isset($notice) ? $notice : []
        ]);
    }

    public function get_list(){
        $page = Request::param("page","1","intval");

        $size = 10;
        $count = Db::name("goods")
            ->where('status',0)->count();

        $total = ceil($count / $size);
        if($total == $page -1){
            return $this->returnAjax("empty",-1,[
                "list"=>[],
                "page"=>$page,
                "total"=>$total,
                "size"=>$size
            ]);
        }
        $user_id = Db::name("users_token")->where("token",Request::header('Auth-Token'))->value('user_id');

        if (!\app\common\model\users\Users::where(['id' => $user_id])->value('is_consumption')){
            $result = Db::name("goods")
                ->field("id,title,photo,first_price as price,sale")
                ->where('status',0)
                ->order('id','desc')->limit((($page - 1) * $size),$size)->select()->toArray();
        }else{
            $result = Db::name("goods")
                ->field("id,title,photo,sell_price as price,sale")
                ->where('status',0)
                ->order('id','desc')->limit((($page - 1) * $size),$size)->select()->toArray();
        }

        $data = array_map(function ($rs){
            $rs["photo"] = Tool::thumb($rs["photo"],"medium",true);
            return $rs;
        }, $result);

        return $this->returnAjax("ok",1, [
            "list"=>$data,
            "page"=>$page,
            "total"=>$total,
            "size"=>$size
        ]);
    }

}
