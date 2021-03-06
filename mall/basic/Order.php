<?php
// +----------------------------------------------------------------------
// | A3Mall
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------
namespace mall\basic;

use app\common\model\system\UsersLog;
use mall\utils\CString;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use \app\common\model\users\Users as UserModel;

class Order {

    public static function create($data = []) {
        Db::startTrans();
        try{
            Db::name("order")->insert([
                "activity_id"=>isset($data["activity_id"]) ? $data["activity_id"] : 0,
                "type"=>$data['type'],
                "user_id"=>Users::get("id"),
                "order_no"=>self::orderNo(),
                "pay_type"=>$data['payment']["id"],
                "distribution_id"=>0,
                "accept_name"=>$data["address"]["accept_name"],
                "zip"=>$data["address"]["zip"],
                "mobile"=>$data["address"]["mobile"],
                "phone"=>$data["address"]["phone"],
                "province"=>$data["address"]["province"],
                "city"=>$data["address"]["city"],
                "area"=>$data["address"]["area"],
                "address"=>$data["address"]["address"],
                "message"=>$data["remarks"],
                "promotions"=>isset($data["promotions"]) ? $data["promotions"] : 0,
                "discount"=>isset($data["discount"]) ? $data["discount"] : 0,
                "real_freight"=>$data["real_freight"],
                "payable_freight"=>$data["payable_freight"],
                "real_amount"=>$data["real_amount"],
                "real_point"=>isset($data["real_point"]) ? $data["real_point"] : 0,
                "order_amount"=>$data["order_amount"],
                "payable_amount"=>$data["payable_amount"],
                "exp"=>$data["exp"],
                "point"=>$data["point"],
                "source"=>$data["source"],
                "create_time"=>time()
            ]);

            $order_id = Db::name("order")->getLastInsID();
            foreach($data["item"] as $val){
                $val["order_id"] = $order_id;
                $val["thumb_image"] = str_replace(Request::domain(),"",$val["thumb_image"]);
                $val["goods_array"] = json_encode([
                    "title"=>$val["title"],
                    "spec"=>!empty($val["goods_array"]) ? implode(", ",array_map(function ($res){
                        return $res["name"] . '???' . $res['value'];
                    },$val["goods_array"])) : ""
                ],JSON_UNESCAPED_UNICODE);
                Db::name("order_goods")->strict(false)->insert($val);
            }

            Db::commit();
        }catch(\Exception $ex){
            Db::rollback();
            throw new \Exception("????????????????????????????????????",0);
        }

        return $order_id;
    }
    public static function fx_first_exec($order_no){
        Db::startTrans();
        if(($order = Db::name("order")->where(["order_no"=>$order_no])->find()) == false){
            throw new \Exception("?????????????????????????????????",0);
        }
//        if($order["pay_status"] == 1){
//            throw new \Exception("??????????????????????????????",0);
//        }
        $fx_setting = Db::name('fx_set')->where(['type'=>0])->order("id","asc")->select();
        $userModel = UserModel::find($order['user_id']);
        if ($order['fx_type'] != 1){
            return false;
        }

        $userModel->is_consumption=1;
        $users_all = array();
        $user_logs = array();
        $level = 0;
        $parent_money = 0;
        $parentModel = $userModel;
        foreach ($userModel->ancestors as $key => $item){

            if ($item->is_consumption==0){
                continue;
            }
//            if ($item->consumption_num < $fx_setting[$level++]['condition']){
//                continue;
//            }
            if ($item->consumption_num >= 7){
                $rate = 0.5;
            } else {
                $rate = 0.3;
            }
            if ($parent_money == 0) {
                $item->consumption_num = Db::raw('consumption_num+1');
                $money = round($order['order_amount']*0.3,2);
                $parent_money = $money;
            }else{
                $money = round($parent_money * $rate,2);
                $parent_money = $money;
            }
            if ($money < 0.05){
                continue;
            }
            $item->consumption_order_num = Db::raw('consumption_order_num+1');
            $item->amount += $money;
            $item->shouru += $money;

            Db::name("users_log")->insert([
                "user_id"=>$item->id,
                "action"=>4,
                "operation"=>0,
                "point"=>0,
                "exp"=>0,
                "description"=>"????????????({$parentModel->nickname})???????????????????????? ???????????? {$money} ???",
                "amount"=> $money,
                "order_no" => $order_no,
                "create_time"=>time(),
                "pid" => $parentModel->id,
                "type"=>'????????????',
            ]);


            $givemoney = bcmul($money,0.05,2);
            $count = UserModel::where(['parent_id'=>$item->id,'is_consumption'=>1])->where('shouru<99')->count();
            if ($count > 0){
                $givemoney = bcdiv($givemoney,$count,2);
                if ($givemoney>= 0.01){
                    $users = UserModel::where(['parent_id' => $item->id,'is_consumption'=>1])->where('shouru < 99')->select();
                    foreach ($users as $item2){
                        $users_all[] = array(
                            'id' => $item2['id'],
                            'amount' => Db::raw("amount+".$givemoney),
                            'shouru' => Db::raw("shouru+".$givemoney)
                        );
                        $user_logs[] = array(
                            "user_id"=>$item2['id'],
                            "action"=>4,
                            "operation"=>0,
                            "point"=>0,
                            "exp"=>0,
                            "description"=>"????????????????????????{$givemoney}???",
                            "amount"=> $givemoney,
                            "order_no" => $order_no,
                            "create_time"=>time(),
                            "pid" => $item->id,
                            "type"=>'????????????',
                        );
                    }
                }
            }
            $parentModel = $item;
            $item->save();
        }
        $userModel->saveAll($users_all);
        Db::name('users_log')->insertAll($user_logs);
        $userModel->save();
        Db::name("order")->where(["order_no"=>$order_no])->update(['fx_flag'=>1]);
        Db::commit();
        return true;
    }

    public static function fx_exec($order_no)
    {
        if(($order = Db::name("order")->where(["order_no"=>$order_no])->find()) == false){
            throw new \Exception("?????????????????????????????????",0);
        }
        $fx_setting = Db::name('fx_set')->where(['type'=>1])->order("id","asc")->select();
        $userModel = UserModel::find($order['user_id']);
        if ($order['fx_type'] != 2){
            return false;
        }
        $parent_money = 0;
        $parentModel = $userModel;

        foreach ($userModel->ancestors as $key => $item){
            if ($item->is_consumption==0){
                continue;
            }
            if ($parent_money == 0) {
                $item->consumption_num = Db::raw('consumption_num+1');
                $money = round($order['order_amount'] * 0.15, 2);
                $parent_money = $money;
            }else{
                $money = round($parent_money * 0.15,2);
                $parent_money = $money;
            }
            if ($money < 0.05){
                continue;
            }
            $item->consumption_order_num = Db::raw('consumption_order_num+1');
            $item->amount += $money;

            $item->shouru += $money;

            Db::name("users_log")->insert([
                "user_id"=>$item->id,
                "action"=>4,
                "operation"=>0,
                "point"=>0,
                "exp"=>0,
                "description"=>"????????????({$parentModel->nickname})???????????????????????????????????????{$money}",
                "amount"=> $money,
                "order_no" => $order_no,
                "create_time"=>time(),
                "pid" => $parentModel->id,
                "type"=>'????????????',
            ]);
            $parentModel = $item;
            $item->save();
        }
        Db::name("order")->where(["order_no"=>$order_no])->update(['fx_flag'=>1]);
        return true;
    }

    /**
     * ?????????????????????????????????
     */
    public static function payment($order_no,$admin_id=0,$note="",$trade_no=""){
        if(($order = Db::name("order")->where(["order_no"=>$order_no])->find()) == false){
            throw new \Exception("?????????????????????????????????",0);
        }

        if($order["pay_status"] == 1){
            throw new \Exception("??????????????????????????????",0);
        }
        $userModel = UserModel::find($order['user_id']);
        $type = 1;
        if ($userModel->is_consumption != 0){
            $type = 2;
        }
        $userModel->is_consumption=1;
        if(Db::name("order")->where(["order_no"=>$order_no])->update([
                "status" => ($order['status'] == 5) ? 5 : 2,
                "pay_time" => time(),
                "pay_status" => 1,
                "note" => $note,
                "trade_no"=>$trade_no,
                "admin_id"=>$admin_id,
            ]) == false){
            throw new \Exception("?????????????????????????????????",0);
        }

        //???????????????
        Db::name('order_collection')->insert([
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'amount' => $order['order_amount'],
            'create_time' => time(),
            'payment_id' => $order['pay_type'],
            'pay_status' => 1,
            'is_delete' => 0,
            'note' => $note,
            'admin_id' => $admin_id ? $admin_id : 0,
        ]);

        //??????????????????????????????
        if ($order['pay_type'] != 0) {
            //???????????????
            $orderGoodsList = Db::name("order_goods")->where(['order_id' => $order['id']])->select()->toArray();
            foreach ($orderGoodsList as $val) {
                self::updateStock([
                    "goods_id" => $val["goods_id"],
                    "product_id" => $val["product_id"],
                    "goods_nums"=>$val["goods_nums"]
                ], "-");
                Db::name("goods")->where('id',$val["goods_id"])->update([
                    "sale"=>Db::raw("sale+1"),
                ]);
            }
        }
        Db::name("order")->where(["order_no"=>$order_no])->update(['fx_type'=>$type]);
        $userModel->save();

        return true;
    }

    /**
     * ????????????
     */
    public static function complete($order_no,$admin_id=0){
        if(($order = Db::name("order")->where(["order_no"=>$order_no])->find()) == false){
            throw new \Exception("???????????????????????????????????????????????????",0);
        }

        if(($users = Db::name("users")->where(array("id"=>$order["user_id"]))->find()) != false){

            if($order['exp'] > 0){
                $log = '???????????????????????????' . $order['order_no'] . '????????????,????????????' . $order['exp'];
                Db::name("users")->where(["id"=>$order["user_id"]])->inc("exp",$order['exp'])->update();
                Db::name("users_log")->insert([
                    "order_no"=>$order_no,
                    "user_id"=>$order["user_id"],
                    "admin_id"=> $admin_id ? $admin_id : 0,
                    "action"=>2,
                    "operation"=>0,
                    "exp"=>$order['exp'],
                    "description"=>$log,
                    "create_time"=>time()
                ]);
            }

            if($order['point'] > 0){
                $log = '???????????????????????????' . $order['order_no'] . '????????????,????????????' . $order['point'];
                Db::name("users")->where(["id"=>$order["user_id"]])->inc("point",$order['point'])->update();
                Db::name("users_log")->insert([
                    "order_no"=>$order_no,
                    "user_id"=>$order["user_id"],
                    "admin_id"=> $admin_id ? $admin_id : 0,
                    "action"=>1,
                    "operation"=>0,
                    "point"=>$order['point'],
                    "description"=>$log,
                    "create_time"=>time()
                ]);
            }

        }

        //?????????????????????????????????
        $orderList = Db::name("order_goods")->where(['order_id'=>$order["id"]])->group('goods_id')->select()->toArray();

        //?????????????????????????????????
        foreach ($orderList as $val) {
            if (Db::name("goods")->where(['id'=>$val['goods_id']])->find()) {
                Db::name("users_comment")->insert([
                    'goods_id' => $val['goods_id'],
                    'order_no' => $order['order_no'],
                    'user_id' => $order['user_id'],
                    'create_time' => time()
                ]);
            }
        }

        return true;
    }

    public static function sendDistributionGoods($order_id, $order_goods_id, $admin_id){
        if ($order_id <= 0) {
            throw new \Exception("???????????????", 0);
        }

        if (empty($order_goods_id)) {
            throw new \Exception("???????????????????????????", 0);
        }

        $distribution_code = Request::post('distribution_code',"","trim,strip_tags");

        $freight_id = Request::post('freight_id',0,"intval");
        if (empty($distribution_code)) {
            throw new \Exception("?????????????????????", 0);
        }

        if (empty($freight_id)) {
            throw new \Exception("?????????????????????", 0);
        }

        $refund = Db::name('order_refundment')->where([
            "order_id"=>$order_id,"pay_status"=>0,"is_delete"=>0
        ])->find();
        if(!empty($refund)){
            throw new \Exception("????????????????????????????????????",0);
        }

        $order = Db::name("order")->where(["id"=>$order_id])->find();

        if(empty($order)){
            throw new \Exception("?????????????????????",0);
        }

        $data = [
            'order_id' => $order_id,
            'user_id' => $order["user_id"],
            'name' => Request::post('accept_name','','trim,strip_tags'),
            'zip' => Request::post('zip','','intval'),
            'phone' => Request::post('phone','','intval'),
            'province' => Request::post('province','','intval'),
            'city' => Request::post('city','','intval'),
            'area' => Request::post('area','','intval'),
            'address' => Request::post('address','','trim,strip_tags'),
            'mobile' => Request::post('mobile','','trim,strip_tags'),
            'freight' => $order["real_freight"],
            'distribution_code' => $distribution_code,
            'distribution_id' => $order["distribution_id"],
            'note' => Request::post('remarks','','trim,strip_tags'),
            'create_time' => time(),
            'freight_id' => $freight_id
        ];

        $data['admin_id'] = $admin_id;

        $delivery_id = Db::name('order_delivery')->insert($data);

        $admin = Db::name("system_users")->where(["id"=>$admin_id])->find();

        if ($order['pay_type'] == 0) {
            //???????????????
            $orderGoodsList = Db::name("order_goods")->where("id","in",$order_goods_id)->select()->toArray();
            foreach ($orderGoodsList as $val) {
                self::updateStock([
                    "goods_id" => $val["goods_id"],
                    "product_id" => $val["product_id"],
                    "goods_nums"=>$val["goods_nums"]
                ], "-");
            }
        }

        //??????????????????
        $orderGoods = Db::name("order_goods")->field('count(*) as num')->where([
            "is_send"=>0,"order_id"=>$order_id
        ])->find();

        $sendStatus = 2; //????????????
        if (count($order_goods_id) >= $orderGoods['num']) {
            $sendStatus = 1; //????????????
        }

        foreach ($order_goods_id as $val) {
            Db::name("order_goods")->where(["id"=>$val])->update([
                "is_send" => 1,
                "distribution_id" => $delivery_id
            ]);
        }

        //??????????????????
        Db::name('order')->where(['id'=>$order_id])->update([
            'distribution_status' => $sendStatus,
            'send_time' =>time()
        ]);

        Db::name("order_log")->insert([
            'order_id' => $order_id,
            'username' => $admin["username"],
            'action' => '??????',
            'result' => '??????',
            'note' => '?????????' . $order["order_no"] . '?????????????????????' . $admin["username"] . '??????',
            'create_time' => time()
        ]);

        try {
            Sms::send(
                ["mobile"=>$order["mobile"],"order_no"=>$order["order_no"]],
                "deliver_goods"
            );
        }catch (\Exception $e){}

        return true;
    }
    public static function autoPrintGoods($order, $order_goods_id,$distribution_code,$freight_id, $admin_id){
        $order_id = $order->id;
        if ($order_id <= 0) {
            throw new \Exception("???????????????", 0);
        }

        if (empty($order_goods_id)) {
            throw new \Exception("???????????????????????????", 0);
        }


        if (empty($distribution_code)) {
            throw new \Exception("?????????????????????", 0);
        }

        if (empty($freight_id)) {
            throw new \Exception("?????????????????????", 0);
        }

        $refund = Db::name('order_refundment')->where([
            "order_id"=>$order_id,"pay_status"=>0,"is_delete"=>0
        ])->find();
        if(!empty($refund)){
            throw new \Exception("????????????????????????????????????",0);
        }

//        $order = Db::name("order")->where(["id"=>$order_id])->find();

        if(empty($order)){
            throw new \Exception("?????????????????????",0);
        }
//        $address = Area::get_area([$order['province'], $order['city'], $order['area']]);

        $data = [
            'order_id' => $order_id,
            'user_id' => $order["user_id"],
            'name' => $order->accept_name,
            'zip' => 276400,
            'phone' => $order->mobile,
            'province' => $order['province'],
            'city' => $order['city'],
            'area' => $order['area'],
            'address' => $order->address,
            'mobile' => $order->mobile,
            'freight' => $order["real_freight"],
            'distribution_code' => $distribution_code,
            'distribution_id' => $order["distribution_id"],
            'note' => '????????????',
            'create_time' => time(),
            'freight_id' => $freight_id
        ];

        $data['admin_id'] = $admin_id;

        $delivery_id = Db::name('order_delivery')->insert($data);

        return true;
    }
    public static function autoSendDistributionGoods($order, $order_goods_id, $admin_id){
        $order_id = $order->id;
        if ($order_id <= 0) {
            throw new \Exception("???????????????", 0);
        }

        if (empty($order_goods_id)) {
            throw new \Exception("???????????????????????????", 0);
        }

        $refund = Db::name('order_refundment')->where([
            "order_id"=>$order_id,"pay_status"=>0,"is_delete"=>0
        ])->find();
        if(!empty($refund)){
            throw new \Exception("????????????????????????????????????",0);
        }

        $order = Db::name("order")->where(["id"=>$order_id])->find();

        if(empty($order)){
            throw new \Exception("?????????????????????",0);
        }

        $delivery_id = Db::name('order_delivery')->where(['order_id'=>$order_id])->value('id');
        $admin = Db::name("system_users")->where(["id"=>$admin_id])->find();
        if ($order['pay_type'] == 0) {
            //???????????????
            $orderGoodsList = Db::name("order_goods")->where("id","in",$order_goods_id)->select()->toArray();
            foreach ($orderGoodsList as $val) {
                self::updateStock([
                    "goods_id" => $val["goods_id"],
                    "product_id" => $val["product_id"],
                    "goods_nums"=>$val["goods_nums"]
                ], "-");
            }
        }

        //??????????????????
        $orderGoods = Db::name("order_goods")->field('count(*) as num')->where([
            "is_send"=>0,"order_id"=>$order_id
        ])->find();

//        $sendStatus = 2; //????????????
//        if (count($order_goods_id) >= $orderGoods['num']) {
//            $sendStatus = 1; //????????????
//        }
        $sendStatus = 1; //

        foreach ($order_goods_id as $val) {
            Db::name("order_goods")->where(["id"=>$val])->update([
                "is_send" => 1,
                "distribution_id" => $delivery_id
            ]);
        }

        //??????????????????
        Db::name('order')->where(['id'=>$order_id])->update([
            'distribution_status' => $sendStatus,
            'send_time' =>time()
        ]);

        Db::name("order_log")->insert([
            'order_id' => $order_id,
            'username' => $admin["username"],
            'action' => '??????',
            'result' => '??????',
            'note' => '?????????' . $order["order_no"] . '?????????????????????' . $admin["username"] . '??????',
            'create_time' => time()
        ]);

//        try {
//            Sms::send(
//                ["mobile"=>$order["mobile"],"order_no"=>$order["order_no"]],
//                "deliver_goods"
//            );
//        }catch (\Exception $e){}

        return true;
    }

    public static function refund($refunds_id,$admin_id=0){
        $refunds = Db::name("order_refundment")->where(["id"=>$refunds_id])->find();

        $orderGoodsList = Db::name("order_goods")->where("id","in",$refunds['order_goods_id'])->where("is_send","<>","2")->select()->toArray();
        if (!$orderGoodsList) {
            throw new \Exception("?????????????????????????????????????????????",0);
        }

        //???????????????????????????
        $autoMount = 0;
        $orderRow = [
            'exp' => 0,
            'point' => 0,
            'order_no' => $refunds['order_no']
        ];

        foreach ($orderGoodsList as $val) {
            $autoMount += $val['goods_nums'] * $val['real_price'];

            //????????????
            self::updateStock(["goods_id" => $val["goods_id"], "product_id" => $val["product_id"], "goods_nums"=>$val["goods_nums"]], '+');

            //??????????????????
            Db::name("order_goods")->where('id',$val['id'])->update(['is_send' => 2]);

            //????????????,??????
            $goodsRow = Db::name("goods")->where('id',$val['goods_id'])->find();
            $orderRow['exp'] += $goodsRow['exp'] * $val['goods_nums'];
            $orderRow['point'] += $goodsRow['point'] * $val['goods_nums'];
        }

        //????????????????????????????????????????????????????????????????????????????????????
        $amount = $refunds['amount'] > 0 ? $refunds['amount'] : $autoMount;

        //??????order?????????,?????????????????????????????????????????????????????????????????????????????????????????????????????????
        $isSendData = Db::name("order_goods")->where('order_id',$refunds['order_id'])->where('is_send','<>','2')->find();
        $orderStatus = 6; //????????????
        if ($isSendData) {
            $orderStatus = 7; //????????????
        }

        Db::name("order")->where(["id"=>$refunds['order_id']])->update(['status' => $orderStatus]);

        $order = Db::name("order")->where('id',$refunds['order_id'])->find();
        if ($orderStatus == 6) {
            //?????????????????????????????????????????????????????????????????????
            $isDeliveryData = Db::name("order_goods")->where('order_id',$refunds['order_id'])->where('distribution_id','>','0')->find();
            if (!$isDeliveryData) {
                $amount += $order['real_freight'] + $order['insured'] + $order['taxes'];
            }
        }

        //???????????????
        Db::name("order_refundment")->where(["id"=>$refunds_id])->update([
            'amount' => $amount,
            'pay_status' => 2,
            'dispose_time' =>time()
        ]);

        $admin = Db::name("system_users")->where(["id"=>$refunds["admin_id"]])->find();

        if($refunds["type"] == 0){
            Db::name("users")->where(["id"=>$order["user_id"]])->inc("amount",$amount)->update();
            Db::name("users_log")->insert([
                "order_no"=>$order["order_no"],
                "user_id"=>$order["user_id"],
                "admin_id"=>Session::get("system_user_id"),
                "action"=>3,
                "operation"=>1,
                "amount"=>$amount,
                "description"=>'??????????????????' . $refunds['order_no'] . '????????????,???????????? -???' . $amount,
                "create_time"=>time()
            ]);
        }

        if($orderRow['exp'] > 0){
            //?????????????????????
            $users = Db::name("users")->where(["id"=>$refunds["user_id"]])->find();

            $exp = $users['exp'] - $orderRow['exp'];
            if($exp > 0) {
                Db::name("users")->where(["id" => $refunds["user_id"]])->update([
                    'exp' => $exp <= 0 ? 0 : $exp
                ]);
            }

            $log = '??????????????????' . $refunds['order_no'] . '????????????,???????????? -' . $orderRow['exp'];
            Db::name("users_log")->insert([
                "order_no"=>$order["order_no"],
                "user_id"=>$refunds["user_id"],
                "admin_id"=>$admin_id ? $admin_id : "-1",
                "action"=>2,
                "operation"=>1,
                "point"=>$orderRow['exp'],
                "description"=>$log,
                "create_time"=>time()
            ]);
        }

        if($orderRow['point'] > 0){
            $log = '??????????????????' . $refunds['order_no'] . '????????????,???????????? -' . $orderRow['point'];
            Db::name("users")->where(["id"=>$order["user_id"]])->dec("point",$orderRow['point'])->update();
            Db::name("users_log")->insert([
                "order_no"=>$order["order_no"],
                "user_id"=>$refunds["user_id"],
                "admin_id"=>$admin_id ? $admin_id : "-1",
                "action"=>1,
                "operation"=>0,
                "point"=>$orderRow['point'],
                "description"=>$log,
                "create_time"=>time()
            ]);

        }

        Db::name("order_log")->insert([
            'order_id' => $refunds["order_id"],
            'username' => $admin["username"],
            'action' => '??????',
            'result' => '??????',
            'note' => '?????????' . $refunds["order_no"] . '???????????????????????????' . $amount,
            'create_time' => time()
        ]);

        return true;
    }

    /**
     *  ?????????????????????
     * @param array $data
     * @return float|int
     */
    public static function getRefundAmount($data){
        $list = Db::name("order_refundment")->where([
            "order_id"=>$data["id"],"pay_status"=>2
        ])->select()->toArray();
        $refundFee = 0.00;
        foreach ($list as $val) {
            $refundFee += $val['amount'];
        }

        return number_format($refundFee,2);
    }

    /**
     * ????????????????????????
     * @param $order
     * @param $message
     * @return bool
     */
    public static function refundmentApply($order, $message) {
        if(!in_array($order["status"],[2,7])){
            throw new \Exception("????????????????????????????????????",0);
        }

        $orderGoods = Db::name("order_goods")->where([
            "order_id"=>$order["id"]
        ])->select()->toArray();

        $arr = [];
        foreach ($orderGoods as $val) {
            if ($val['is_send'] == 2) {
                throw new \Exception("??????????????????????????????????????????", 0);
            }

            if (Db::name("order_refundment")
                ->where("is_delete",0)->where("pay_status",0)
                ->where('FIND_IN_SET(' . $val["id"] . ',order_goods_id)')->count()) {
                throw new \Exception("????????????????????????????????????????????????????????????", 0);
            }
            $arr[] = $val["id"];
        }

        Db::startTrans();
        try{
            Db::name("order_refundment")->insert([
                "order_no"=>$order["order_no"],
                "order_id"=>$order["id"],
                "user_id"=>$order["user_id"],
                "pay_status"=>0,
                "content"=>$message,
                "amount"=>$order['order_amount'],
                "order_goods_id"=>implode(',',$arr),
                "create_time"=>time(),
            ]);

            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception("?????????????????????????????????????????????",0);
            //throw new \Exception($e->getMessage(),0);
        }

        return true;
    }

    public static function getOrderType($type=""){
        $arr = ["point"=>1,"regiment"=>2,"second"=>3,"special"=>4,"group"=>5,"buy"=>0,"cart"=>0];
        return isset($arr[$type]) ? $arr[$type] : 0;
    }

    public static function getOrderTypeText($type,$length=-1) {
        switch ($type) {
            case "1":
                $string = '????????????';
                break;
            case "2":
                $string = '????????????';
                break;
            case "3":
                $string = "????????????";
                break;
            case "5":
                $string = "????????????";
                break;
            case '4':
            default:
                $string = '????????????';
        }

        return $length == -1 ? $string : CString::msubstr($string,$length,false);
    }

    public static function getRefundmentText($code) {
        $result = ['0' => '????????????', '1' => '????????????', '2' => '????????????'];
        return isset($result[$code]) ? $result[$code] : '';
    }

    public static function getSendStatus($code) {
        $data = [0 => '????????????', 1 => '?????????', 2=>"????????????", 3 => '?????????'];
        return isset($data[$code]) ? $data[$code] : '?????????';
    }

    public static function getPaymentStatusText($status){
        return $status == 0 ? "?????????" : "?????????";
    }

    public static function getDeliveryStatus($status){
        return $status == 0 ? "?????????" : "?????????";
    }

    public static function getEvaluateStatus($status){
        switch ($status){
            case 0:
                return '?????????';
            case 1:
                return '?????????';
            case 2:
                return '????????????';
            default:
                return '';
        }
    }

    public static function getOrderActive($order){
        if($order["pay_status"] == 0){
            return 0;
        }

        if($order["status"] == 2 && $order["distribution_status"] == 0){
            return 1;
        }else if($order["status"] == 2 && $order["distribution_status"]){
            return 2;
        }

        if($order["status"] == 5 && in_array($order["evaluate_status"],[0,2])){
            return 3;
        }

        if($order["status"] == 5 && $order["evaluate_status"] == 1){
            return 4;
        }

        return -1;
    }

    public static function getStatusText($code) {
        $result = [
            0=>'??????',1=>'????????????',2=>'????????????',3=>'?????????',4 => '????????????',
            5=>'?????????',6=>'?????????',7=>'?????????',8=>'??????',9=>'?????????',
            10=>'????????????',11=>'????????????'
        ];

        return isset($result[$code]) ? $result[$code] : '';
    }

    public static function getStatus($order){
        if(empty($order)){
            return 0;
        }

        if($order["status"] == 1 && $order["pay_status"] == 0){ // ?????????
            return 1;
        }else if($order["status"] == 2){
            // ?????????????????????
            if(Db::name('order_refundment')->where([
                "order_id"=>$order['id'],"is_delete"=>0,"pay_status"=>0
            ])->count()){
                return 11;
            }

            if($order["distribution_status"] == 0){ // ?????????
                return 2;
            }else if($order["distribution_status"] == 1){ // ?????????
                return 3;
            }else if($order["distribution_status"] == 2){ // ????????????
                return 4;
            }
        }

        if($order["status"] == 5){
            if(in_array($order["evaluate_status"],[0,2])){ // ?????????
                return 5;
            }else if($order["evaluate_status"] == 1){ // ?????????
                return 6;
            }
        }else if ($order['status'] == 3 || $order['status'] == 4) { //3,????????????????????????
            return 7;
        }else if ($order['status'] == 6) { // 5,??????
            return 8;
        }else if ($order['status'] == 7) { // 6,????????????
            if ($order['distribution_status'] == 1) { // ??????
                return 10;
            } else { // ?????????
                return 9;
            }
        }

        return 0;
    }

    public static function orderNo($number = '', $date = 'YmdHis') {
        $arr = explode(" ", microtime());
        $usec = substr(str_replace('0.', '', $arr[0]), 0, 2) . rand(100, 999);
        return $number . date($date) . $usec;
    }

    /**
     * ????????????
     * @return boolean
     */
    public static function updateStock($data, $type = "-") {
        if ($data["product_id"] > 0) {
            $product = Db::name("goods_item")->where([
                "goods_id"=>$data["goods_id"],"id"=>$data["product_id"]
            ])->find();
        }

        $product_store = 0;
        $goods = Db::name("goods")->where(["id"=>$data["goods_id"]])->find();
        switch ($type) {
            case "-":
                if (!empty($product)) {
                    $product_store = $product["store_nums"] - $data["goods_nums"];
                }
                $goods_store = $goods["store_nums"] - $data["goods_nums"];
                break;
            case "+":
                if (!empty($product)) {
                    $product_store = $product["store_nums"] + $data["goods_nums"];
                }
                $goods_store = $goods["store_nums"] + $data["goods_nums"];
                break;
        }

        if ($data["product_id"] > 0) {
            Db::name("goods_item")->where([
                "goods_id"=>$data["goods_id"],"id"=>$data["product_id"]
            ])->update(["store_nums" => $product_store]);
        }

        Db::name("goods")->where([ "id"=>$data["goods_id"]])->update(["store_nums" => $goods_store]);

        return true;
    }

}
