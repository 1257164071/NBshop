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
}
