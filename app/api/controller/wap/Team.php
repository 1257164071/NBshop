<?php

// +----------------------------------------------------------------------
// | A3Mall
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------
namespace app\api\controller\wap;
use app\common\model\users\Log;
use dh2y\qrcode\QRcode;
use mall\basic\Users;
use mall\library\wechat\chat\module\User;
use mall\utils\Tool;
use think\facade\Db;
use think\facade\Request;

class Team extends Base
{
    public function posteraaa()
    {
        $user = Users::get('id');
        $user = \app\common\model\users\Users::find($user);



        if ($user['poster'] == null){
        // if (true){
            $code = new QRcode();
            $code_path =  $code->png(request()->domain().'/public/register?parent_id='.$user->id,false,2.5)        //生成二维码
                ->background(515,880,'static/images/bg2.jpg')
                ->text($user->nickname,30,[100,253], '#bd0f0f66')
//                ->text('微信扫描二维码或长按识别',30,['center',1100],'#fffffff')
                ->getPath();
            $code_path = substr($code_path,1);
            if ($user['avatar'] != ''){
//                   $avatar = substr($user['avatar'],0,1)=='/'?substr($user['avatar'],1):$user['avatar'];
//                 $dst = imagecreatefromstring(file_get_contents($avatar);
// dump($dst);die;
                        //创建图片的实例
                $dst = imagecreatefromstring(file_get_contents($code_path));
                $avatar = substr($user['avatar'],0,1)=='/'?substr($user['avatar'],1):$user['avatar'];

                $src = imagecreatefromstring(file_get_contents($avatar));

                //获取覆盖图图片的宽高
                list($src_w, $src_h) = getimagesize($avatar);

                //将覆盖图复制到目标图片上，最后个参数100是设置透明度（100是不透明），这里实现不透明效果
                imagecopymerge($dst, $src, 110, 298, 0, 0, $src_w, $src_h, 100);

                $outfile = 'uploads/qrcode/'.time().'.png';



                imagepng($dst, $outfile);//根据需要生成相应的图片
                imagedestroy($dst);
                $code_path = '/'.$outfile;
            }
            $user->poster = $code_path;
            $user->save();
        }
        return $this->returnAjax('ok',1,['path' => request()->domain().$user->poster]);
    }

    public function transform_image($image_path, $to_ext = 'png', $save_path = null)
    {
        if (!in_array($to_ext, ['png', 'gif', 'jpeg', 'wbmp', 'webp', 'xbm'])) {
            throw new \Exception('unsupport transform image to ' . $to_ext);
        }
        switch (exif_imagetype($image_path)) {
            case IMAGETYPE_GIF :
                $img = imagecreatefromgif($image_path);
                break;
            case IMAGETYPE_JPEG :
            case IMAGETYPE_JPEG2000:
                $img = imagecreatefromjpeg($image_path);
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($image_path);
                break;
            case IMAGETYPE_BMP:
            case IMAGETYPE_WBMP:
                $img = imagecreatefromwbmp($image_path);
                break;
            case IMAGETYPE_XBM:
                $img = imagecreatefromxbm($image_path);
                break;
            case IMAGETYPE_WEBP: //(从 PHP 7.1.0 开始支持)
                $img = imagecreatefromwebp($image_path);
                break;
            default :
                throw new \Exception('Invalid image type');
        }
        $function = 'image' . $to_ext;
        if ($save_path) {
            return $function($img, $save_path);
        } else {
            $tmp = 'uploads/' . uniqid() . '.' . $to_ext;
            if ($function($img, $tmp)) {
                $content = file_get_contents($tmp);
                unlink($tmp);
                return $content;
            } else {
                unlink($tmp);
                throw new \Exception('the file ' . $tmp . ' can not write');
            }
        }
    }

    public function poster()
    {

        $user = Users::get('id');

        $user = \app\common\model\users\Users::find($user);


        if ($user['poster'] == null){
        // if (true){
            $code = new QRcode();
            $code_path =  $code->png(request()->domain().'/public/register?parent_id='.$user->id,false,2.5)        //生成二维码
                ->background(515,880,'static/images/bg2.jpg')
                ->text($user->nickname,30,[100,253], '#bd0f0f66')
//                ->text('微信扫描二维码或长按识别',30,['center',1100],'#fffffff')
                ->getPath();
            $code_path = substr($code_path,1);
            if ($user['avatar'] != ''){
                $base_url = request()->domain();
                $avatar = substr($user['avatar'],0,1)=='/'?substr($user['avatar'],1):$user['avatar'];

                $src = imagecreatefromstring(file_get_contents($avatar));

                        //创建图片的实例
                $dst = imagecreatefromstring(file_get_contents($code_path));
                if (substr($user['avatar'],0,1) == '/'){
                    $src = imagecreatefromstring($this->transform_image($avatar,'png'));
                }else{
                    $src = imagecreatefromstring(file_get_contents($user['avatar']));
                }
                //获取覆盖图图片的宽高
                list($src_w, $src_h) = getimagesize($avatar);
                //将覆盖图复制到目标图片上，最后个参数100是设置透明度（100是不透明），这里实现不透明效果
                imagecopymerge($dst, $src, 110, 298, 0, 0, $src_w, $src_h, 100);
                $outfile = 'uploads/qrcode/'.time().'.png';
                imagepng($dst, $outfile);//根据需要生成相应的图片
                imagedestroy($dst);
                $code_path = '/'.$outfile;
            }
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

    public function mysort(){
        Db::query("set @rowNum=0;");
        $ranking = Db::query("select  * from (select id,(@rowNum:=@rowNum+1)  as rowNo  FROM users ORDER BY shouru desc) as c where id <> 1 and id <> 71 and id <> 72 and id=:id",['id'=>Users::get("id")])[0]['rowNo'];
        return $this->returnAjax("ok",1,[
            "ranking"=>$ranking,
        ]);
    }
    public function sort()
    {
        $page = Request::param("page","1","intval");
        $size = 10;
        Db::query("set @rowNum=0;");

        $page1 = ($page - 1) * $size;
        $page2 = $size;
        if ($page2 > 100){
            $page2 = 100;
        }


        $count = Db::query("select count(*) as total from (select (@rowNum:=@rowNum+1)  as rowNo  FROM users  ORDER BY num desc) as c ")[0]['total'];
        $data = Db::query("select * from (select id,avatar,create_time,nickname,shouru,(@rowNum:=@rowNum+1)  as rowNo  FROM users ORDER BY shouru desc) as c where id <> 10 and id <> 71 and id <> 72 limit {$page1}, {$page2}");

        $total = ceil($count/$size);
        if($total == $page -1 || $page > 10){
            return $this->returnAjax("empty",-1,[
                "list"=>[],
                "page"=>$page,
                "total"=>$total,
                "size"=>$size,
                'num' => $count,
            ]);
        }
        $base_url = request()->domain();
        $data = array_map(function ($rs) use ($base_url){
            $rs["avatar"] = substr($rs['avatar'],0,1)=='/'?$base_url.$rs['avatar']:$rs['avatar'];
            $rs["create_time"] = date("Y-m-d H:i:s",$rs["create_time"]);
            return $rs;
        },$data);

        return $this->returnAjax("ok",1,[
            "list"=>$data,
            "page"=>$page,
            "total"=>$total,
            "size"=>$size,
        ]);



//        Db::table('user')->field('b')->where(function ($query){
//            $query->table('user')->order('num asc')->
//        })->select();
    }
    public function copy_order_num()
    {
        function tree($array, $pid)
        {
            $tree = array();
            foreach ($array as $key => $value) {
                if ($value['parent_id'] == $pid) {
                    $value['child'] = tree($array, $value['id']);
                    if (!$value['child']) {
                        unset($value['child']);
                    }
                    $tree[] = $value;
                }
            }
            return $tree;
        }
        function get_all_child($array,$id){
            $arr = array();
            foreach($array as $v){
                if($v['parent_id'] == $id){
                    $arr[] = $v['id'];
                    $arr = array_merge($arr,get_all_child($array,$v['id']));
                };
            };
            return $arr;
        }
        $users = Db::name('users')->where(['is_consumption' => 1])->select();
        $count = 0;
        foreach ( $users as $key => $val)
        {
           $ids = get_all_child(\app\common\model\users\Users::field('id,parent_id,nickname')->select()->toArray(),$val['id']);
           $user = [
               'id' => $val['id'],
               'consumption_order_num' => Db::name('order')->where('user_id','in',$ids)->where(['pay_status'=>1,'fx_flag'=>1])->count(),
           ];
           Db::name('users')->update($user);
           $count ++ ;
        }
        return $count;
    }

    public function teammain()
    {
        $user = Users::get('id');
        $user = \app\common\model\users\Users::find($user);
        $arr= [];
        $parent = \app\common\model\users\Users::where(['id'=>$user->parent_id])->find();
        $arr[0] = Log::where(['user_id' => $user->id,'action' => 4])->sum('amount');
        $arr[1] = Log::where(['user_id' => $user->id, 'action' => 4])->whereDay('create_time')->sum('amount');
        $arr[2] = $user->amount;
        $arr[3] = $parent['nickname'];
        $arr[4] = $parent['mobile'];
        $level = '普通会员';
        if($user['is_consumption'] == 1){
            if($user['consumption_num'] >= 7){
                $level = '消费商';
            } else {
                $level = '消费者';
            }
        }

        return $this->returnAjax("ok",1,[
            0  =>  $arr[0],
            1  =>  $arr[1],
            2  =>  $arr[2],
            3  =>  $arr[3],
            4  =>  $arr[4],
            5  =>  $level,
            6  =>  $user['nickname'],
            7  =>  $user['consumption_num'],
            8  =>  $user['consumption_order_num'],
        ]);

    }

}
