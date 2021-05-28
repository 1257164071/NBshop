<?php
// +----------------------------------------------------------------------
// | 卫润商城
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://www.a3-mall.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: xzncit <158373108@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller\order;

use app\admin\controller\Auth;
use app\common\model\order\Delivery as  OrderDelivery;
use app\common\model\order\Goods;
use app\common\model\order\Order;
use mall\basic\Area;
use think\facade\Request;
use think\facade\Db;
use mall\utils\Date;
use mall\response\Response;
use think\facade\Session;
use think\facade\View;

class Waitdelivery extends Auth {

    public function index(){
        if(Request::isAjax()){
            $limit = Request::get("limit");
            $key = Request::get("key/a","","trim,strip_tags");

            $condition = [];
            $arr = ["lorder.order_no","users.username"];
            if((isset($key["type"]) && isset($arr[$key["type"]])) && !empty($key["title"])){
                $condition[] = [$arr[$key["type"]],"like",'%'.$key["title"].'%'];
            }

//            $orderDelivery = new OrderDelivery();
//            $list = $orderDelivery->getList($condition,$limit);
//            $count = $this->withJoin(["lorder","users"])->where($condition)->count();
//            $data = $this->withJoin(["lorder","users"])->where($condition)->order('delivery.id','DESC')->paginate($limit);

            $list['count'] = Order::where(['status'=>2,'pay_status'=>1,'distribution_status'=>0])->count("*");
            $list['data'] = Order::where(['status'=>2,'pay_status'=>1,'distribution_status'=>0])->paginate($limit)->items();
            if(empty($list["data"])){
                return Response::returnArray("当前还没有数据哦！",1);
            }

            return Response::returnArray("ok",0,$list["data"],$list['count']);
        }

        return View::fetch();
    }
    public function detail(){
        $id = Request::param('id');
        $ids = explode(",", $id);
        echo "<div style='height: 10px;line-height: 10px;display: block'></div>";

        $order = Order::whereIn('id',$ids)->select();
        $sender = array();
        $sender["Name"] = "山东传宝电商物流园 客服";
        $sender["Mobile"] = "13181240757";
        $sender["ProvinceName"] = "山东省";
        $sender["CityName"] = "临沂市";
        $sender["ExpAreaName"] = "沂水县";
        $sender["Address"] = "山东传宝电商物流园";
        $sender["PostCode"] = "276400";
        foreach ($order as $item){

            if ($item->miandan != ''){
                echo $item->miandan;
                continue;
            }

            $eorder = array();
            $eorder["ShipperCode"] = "YZPY";
            $eorder["OrderCode"] = $item->order_no;
            $eorder["PayType"] = 1;
            $eorder["ExpType"] = 1;


            $receiver = array();
            $address = Area::get_area([$item['province'], $item['city'], $item['area']]);

            if ($address == null){
                continue;
            }

            $receiver["Name"] = $item['accept_name'];
            $receiver["Mobile"] = $item['mobile'];
            $receiver["ProvinceName"] = $address[0];
            $receiver["CityName"] = $address[1];
            $receiver["ExpAreaName"] = $address[2];
            $receiver["Address"] = $item['address'];
            $receiver["PostCode"] = "276400";
            $commodityOne = array(
                'GoodsName' => '',
                'Goodsquantity' => 0,
                'GoodsDesc' => '',
                'GoodsWeight' => 0,
            );
            $goods_ids = [];
            Goods::where(['order_id'=>$item->id])->select()->each(function ($row)use (&$commodityOne,&$goods_ids){
                $goods_ids[] = $row->id;
                $json = json_decode($row['goods_array'],true);
                $commodityOne["GoodsName"] =  $commodityOne['GoodsName'].$json['title'];

                $commodityOne["Goodsquantity"] += intval($row['goods_nums']);
                $commodityOne["GoodsDesc"] = $commodityOne['GoodsDesc'].$row['title'];
                if (intval($row['goods_weight']) > 0){
                    $commodityOne["GoodsWeight"] += ($row['goods_weight'] * $commodityOne["Goodsquantity"]);
                }
            });

            $commodity = array();
            $commodity[] = $commodityOne;

            $eorder["Sender"] = $sender;
            $eorder["Receiver"] = $receiver;
            $eorder["Commodity"] = $commodity;
            $eorder["IsReturnPrintTemplate"] = "1";


            //调用电子面单
            $jsonParam = json_encode($eorder);
            $datas = array(
                'EBusinessID' => '1317536',
                'RequestType' => '1007',
                'RequestData' => urlencode($jsonParam),
                'DataType' => '2',
            );
            $datas['DataSign'] = urlencode(base64_encode(md5($jsonParam.'af26855f-71cb-4778-b1a8-5ebde6ac949e')));

//            $jsonResult=$this->sendPost('https://api.kdniao.com/api/EOrderService', $datas);

            $jsonResult=send_post('https://api.kdniao.com/api/EOrderService', $datas);
            $result = json_decode($jsonResult, true);
            if ($result['ResultCode'] != 100){
                echo <<<EOF
<div >
  订单生成错误
  <br>
  订单号: {$item->order_no}
  <br>
  错误代码: {$result['ResultCode']}
  <br>
  错误信息: {$result['Reason']}
  <hr>
</div>
EOF;
                continue;
            }

            $item->miandan =$result['PrintTemplate'];
            $item->save();
            $admin_id = Session::get("system_user_id");
            \mall\basic\Order::autoPrintGoods($item,$goods_ids,$result['Order']['LogisticCode'],16, $admin_id);

            echo $item->miandan;
        }
//        return View::fetch("",[
//            "data"=>$order
//        ]);
    }
    public function send_goods()
    {
        $id = Request::param('ids');
        $ids = explode(",", $id);
        Db::startTrans();

        $order = Order::whereIn('id',$ids)->select();
        foreach ($order as $item){
            if ($item->miandan == ''){
                Db::rollback();
                return Response::returnArray("面单未生成无法发货！",1);
            }
            $goods_ids = [];
            Goods::where(['order_id'=>$item->id])->select()->each(function ($row)use (&$commodityOne,&$goods_ids) {
                $goods_ids[] = $row->id;
            });
            $admin_id = Session::get("system_user_id");
            \mall\basic\Order::autoSendDistributionGoods($item,$goods_ids, $admin_id);
        }
        Db::commit();
        return Response::returnArray("成功！",1);

    }


    public function detail12(){
        $id = Request::param("id");
        $ids = explode(",",$id);

        die;
        $data = Db::name("order_delivery")->alias("c")
                    ->field('c.id as id,c.admin_id,o.order_no,c.order_id,d.title as pname,o.create_time as order_create_time,u.username,c.name,c.province,c.city,c.area,c.address,c.mobile,c.phone,c.zip,c.freight,c.distribution_code,c.create_time,c.note')
                    ->join("order o","c.order_id=o.id","LEFT")
                    ->join("users u","u.id=c.user_id","LEFT")
                    ->join("distribution d","c.distribution_id=d.id","LEFT")->where('c.id',$id)->find();

        if(empty($data)){
            $this->error("您要查找的内容不存在！");
        }

        $data["area_name"] = Area::get_area([$data['province'], $data['city'], $data['area']],",");
        $data["order_create_time"] = Date::format($data['order_create_time']);
        $data["create_time"] = Date::format($data['create_time']);
        $data["goods"] = Db::name("order_goods")->where(["order_id" => $data["order_id"]])->order("id DESC")->select()->toArray();

        if($data["admin_id"] == "-1"){
            $data['admin_name'] = 'system';
        }else{
            $data['admin_name'] = Db::name("system_users")->where(["id"=>$data["admin_id"]])->value("username");
        }

        foreach($data["goods"] as $key=>$item){
            $data["goods"][$key]["goods_array"] = "";
            if(!empty($item["goods_array"])){
                $data["goods"][$key]["goods_array"] = json_decode($item["goods_array"],true);
            }

            $data["goods"][$key]["order_price"] = number_format($item["goods_nums"]*$item["sell_price"],2);
        }

        return View::fetch("",[
            "data"=>$data
        ]);
    }


}
