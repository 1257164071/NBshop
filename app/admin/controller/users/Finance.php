<?php
// +----------------------------------------------------------------------
// | 卫润商城
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller\users;

use app\admin\controller\Auth;
use app\common\model\users\Log as UsersLog;
use app\common\model\users\Users;
use app\common\model\users\WithdrawLog as UsersWithdrawLog;
use mall\library\wechat\chat\payment\Redpack;
use mall\response\Response;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Finance extends Auth {

    private $type = ["1"=>"银行卡","2"=>"支付宝","3"=>"微信"];
    private $status = ["0"=>"审核中","1"=>"已提现","2"=>"未通过"];

    public function index(){
        if(Request::isAjax()){
            $limit = Request::get("limit");
            $condition = ["log.action"=>4];

            $usersLog = new UsersLog();
            $list = $usersLog->getList($condition,$limit);

            if(empty($list['data'])){
                return Response::returnArray("当前还没有数据哦！",1);
            }

            return Response::returnArray("ok",0,$list['data'],$list['count']);
        }

        return View::fetch();
    }

    public function fund(){
        if(Request::isAjax()){
            $limit = Request::get("limit");
//            $condition = ["log.action"=>0];
            $condition = [];

            $usersLog = new UsersLog();
            $list = $usersLog->getList($condition,$limit);

            if(empty($list['data'])){
                return Response::returnArray("当前还没有数据哦！",1);
            }

            return Response::returnArray("ok",0,$list['data'],$list['count']);
        }

        return View::fetch();
    }

    public function refund(){
        if(Request::isAjax()){
            $limit = Request::get("limit");
            $condition = ["log.action"=>3];

            $usersLog = new UsersLog();
            $list = $usersLog->getList($condition,$limit);

            if(empty($list['data'])){
                return Response::returnArray("当前还没有数据哦！",1);
            }

            return Response::returnArray("ok",0,$list['data'],$list['count']);
        }

        return View::fetch();
    }

    public function point(){
        if(Request::isAjax()){
            $limit = Request::get("limit");
            $condition = ["log.action"=>1];

            $usersLog = new UsersLog();
            $list = $usersLog->getList($condition,$limit);

            if(empty($list['data'])){
                return Response::returnArray("当前还没有数据哦！",1);
            }

            return Response::returnArray("ok",0,$list['data'],$list['count']);
        }

        return View::fetch();
    }

    public function exp(){
        if(Request::isAjax()){
            $limit = Request::get("limit");
            $condition = ["log.action"=>2];

            $usersLog = new UsersLog();
            $list = $usersLog->getList($condition,$limit);

            if(empty($list['data'])){
                return Response::returnArray("当前还没有数据哦！",1);
            }

            return Response::returnArray("ok",0,$list['data'],$list['count']);
        }

        return View::fetch();
    }

    public function apply(){
        if(Request::isAjax()){
            $limit = Request::get("limit");
            $condition = [];

            $usersWithdrawLog = new UsersWithdrawLog();
            $list = $usersWithdrawLog->get_list($condition,$limit);
            if(empty($list['data'])){
                return Response::returnArray("当前还没有数据哦！",1);
            }

            foreach($list['data'] as $key=>$item){
                $list['data'][$key]["type_name"] = $this->type[$item["type"]];
                $list['data'][$key]["status_name"] = $this->status[$item["status"]];
                // 提现方式
                $str = '';

                if($item["type"] == 1){
                    $str .= "<p>卡号：" . $item["code"] . '</p>';
                    $str .= "<p>开户地址：" . $item["address"] . '</p>';
                    $str .= "<p>银行：" . $item["bank_name"] . '</p>';
                }else if($item["type"] == 2){
                    $str .= "<p>用户名：" . $item["username"] . '</p>';
                    $str .= "<p>支付宝：" . $item["account"] . '</p>';
                }else if($item["type"] == 3){
                    $str .= "<p>用户名：" . $item['users']["nickname"] . '</p>';
                    $str .= "<p>手机号：" . $item['users']["mobile"] . '</p>';
                }

                $list['data'][$key]['string'] = $str;
            }

            return Response::returnArray("ok",0,$list['data'],$list['count']);
        }

        return View::fetch();
    }

    public function handle(){




        $id = Request::param("id");
        if(($row = Db::name("users_withdraw_log")->where(["id"=>$id])->find()) == false){
            if(Request::isAjax()) {
                return Response::returnArray("您要查找的内容不存在！", 0);
            }else{
                $this->error("您要查找的内容不存在！");
            }

        }
        $str = '&nbsp;&nbsp;';
        $item2 = Db::name('users')->where(['id' => $row['user_id']])->find();
        $row['nickname'] = $item2['nickname'];
        $row['mobile'] = $item2['mobile'];
        $row['username'] = $item2['username'];

        if($row["type"] == 1){
            $str .= "<span>卡号：" . $row["code"] . '</span>&nbsp;&nbsp;';
            $str .= "<span>开户地址：" . $row["address"] . '</span>&nbsp;&nbsp;';
            $str .= "<span>银行：" . $row["bank_name"] . '</span>&nbsp;&nbsp;';
        }else if($row["type"] == 2){
            $str .= "<span>用户名：" . $row["nickname"] . '</span>&nbsp;&nbsp;';
            $str .= "<span>支付宝：" . $row["username"] . '</span>&nbsp;&nbsp;';
        }else if($row["type"] == 3){
            $str .= "<span>用户名：" . $row["nickname"] . '</span>&nbsp;&nbsp;';
            $str .= "<span>微信：" . $row["username"] . '</span>&nbsp;&nbsp;';
            $str .= "<span>手机号：" . $row["mobile"] . '</span>&nbsp;&nbsp;';
        }
        $row["string"] = $str;
        if(($user = Db::name("users")->where(["id"=>$row["user_id"]])->find()) == false){
            if(Request::isAjax()) {
                return Response::returnArray("您操作的会员不存在！", 0);
            }else{
                $this->error("您操作的会员不存在！");
            }
        }


        if(Request::isAjax()){
            $data = Request::post();

            $u = Db::name("users")->where(["id"=>$row["user_id"]])->find();
//            if($u["amount"] < $row["price"]){
//                return Response::returnArray("操作失败，余额不足！",0);
//            }
            Db::startTrans();
            try {
                $status = input('status');
                $order_no = 0;
                $log = Db::name("users_withdraw_log")->where(["id"=>$id])->find();
                $yidakuan = 0;
                if ($status == 3){
                    $yidakuan = 1;
                    $status = 1;
                    $data['status'] = 1;
                }
                if ($status == 1&&$log['type'] == 3&&$yidakuan==0){
                    $redpack = new Redpack;
                    $openid = Db::name('wechat_users')->where(['user_id'=>$user['id']])->value('openid');


                    $order_no = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                    $request = [
                        "nonce_str" => date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
                        're_openid' => $openid,
                        'wishing'   => "感谢您在本商城进行的消费,祝您天天开心",
                        "mch_billno" => $order_no,
                        "send_name" =>  "新零售商城",
                        "total_amount"  => bcmul($row["price"],100),
                        "act_name"  =>  '商城购物红包活动',
                        "total_num" => 1,
                        "client_ip" =>  Request::ip(),
                        "remark"    =>  '发放时间'.date('Y-m-d H:i:s'),
                        "scene_id"  =>  'PRODUCT_1',
                    ];
                    $result = $redpack->create($request);
                    if ($result["result_code"] == "SUCCESS"){
                        $status = 1;
                    } else {
//                        dump($row['price']);
//                        dump($result);die;
                        $status = 2;
                        return Response::returnArray($result['return_msg'],0);
                    }
                }
                Db::name("users_withdraw_log")->where(["id"=>$id])->update([
                    "msg"=>$data["msg"],
                    "status"=>$data["status"],
                    "order_no" => $order_no,
                    "update_time"=>time()
                ]);

                if($data["status"] == 2&&$yidakuan==0){
                    Db::name("users")
                        ->where(["id"=>$row["user_id"]])
                        ->inc("amount",$row["price"])->update();
                }

                Db::commit();

            }catch (\Exception $e){
                Db::rollback();
                return Response::returnArray("微信打款失败请手动联系该用户！",0);
            }

            return Response::returnArray("操作成功！");
        }

        return View::fetch("",[
            "id"=>$id,"user"=>$user,"row"=>$row
        ]);
    }

}
