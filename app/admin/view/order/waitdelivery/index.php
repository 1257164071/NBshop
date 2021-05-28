<div class="row content-nav">
    <div class="col-xs-12">
        <ul>
            <li><a href="javascript:;"><i></i>&nbsp;订单管理</a></li>
            <li><a href="javascript:;">发货列表</a></li>
        </ul>
    </div>
</div>

<div class="layui-fluid" id="search-box">
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form layui-form-pane" action="">

                <div class="layui-form-item">

                    <div class="layui-inline">
                        <label class="layui-form-label seller-inline-2">查找类型：</label>
                        <div class="layui-input-inline seller-inline-4">
                        <select name="type">
                            <option value="0">订单号</option>
                            <option value="1">用户名</option>
                        </select>
                        </div>
                    </div>

                    <div class="layui-inline">
                        <label class="layui-form-label seller-inline-2">关键字：</label>
                        <div class="layui-input-inline seller-inline-4">
                            <input type="text" name="title" placeholder="请输入关键字" autocomplete="off" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-inline">
                        <button type="button" id="search-btn" class="layui-btn layui-btn-sm layui-bg-light-blue"><i class="layui-icon layui-icon-search"></i> 搜索</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<section class="content clearfix">
    <div class="layui-list-box">
        <table class="layui-hide" id="list-box" lay-filter="list-box"></table>
    </div>
</section>

<script type="text/html" id="list-toolbar">
    <div class="layui-btn-container">
        <button lay-event="refresh" type="button" class="layui-btn layui-btn-sm layui-bg-red"><i class="layui-icon">&#xe9aa;</i> 刷新</button>
        <button lay-event="url" type="button" class="layui-btn layui-btn-sm layui-bg-light-blue" id="dayin"><i class="layui-icon">&#xe61f;</i> 打印</button>
        <button lay-event="url" type="button" class="layui-btn layui-btn-sm layui-bg-light-blue" id="fahuo"><i class="layui-icon">&#xe61f;</i> 发货</button>
    </div>
</script>

<script type="text/html" id="list-bar">
    <a class="layui-btn layui-btn-xs" lay-event="print">打印</a>
    <a class="layui-btn layui-btn-xs" lay-event="done">发货</a>
</script>

<script>
layui.use(['table','form'], function () {
    var table = layui.table;
    var form = layui.form;

    table.render({
        elem: '#list-box'
        , url: '{:createUrl("index")}'
        , toolbar: '#list-toolbar'
        , defaultToolbar: []
        , title: '数据表'
        , cols: [[
                  {type: 'checkbox'}
                , {field: 'order_no', title: '订单号',align:'center',width:180}
                // , {field: 'username', title: '用户名'}
                , {field: 'accept_name', title: '收货人'}
                , {field: 'mobile', title: '电话',width:120,align:'center'}
                , {field: 'real_amount', title: '订单金额',width:120,align:'center'}
                // , {field: 'title', title: '物流公司',width:120,align:'center'}
                // , {field: 'freight', title: '运费',width:120,align:'center'}
                , {field: 'create_time', title: '创建时间',width:180,align:'center'}
                , {fixed: 'right', align: 'center', title: '操作', toolbar: '#list-bar', width: 140}
            ]]
        , page: true
        , id: 'list-table'
        , height: 'full-255'
        // ,limit:30
    });

    $("#search-btn").on("click",function (){
        table.reload('list-table', {
          page: {
            curr: 1
          }
          ,where: {
            key: {
              type : $('[name="type"]').val(),
              title : $('[name="title"]').val()
            }
          }
        }, 'data');
    });


    $('#dayin').on('click', function () {
        var checkStatus = table.checkStatus('list-table');
        var ids = [];

        $(checkStatus.data).each(function (i, o) {
              ids.push(o.id);
        })
        if (ids.length < 1) {
            layer.msg('无选中项');
            return false;
        }
        ids = ids.join(",");
        layer.confirm('确定要对选中项打印吗？', function (index) {
          window.open('{:createUrl("detail")}?id='+ids,'面单打印','height=100, width=400');
            layer.close(index);
        });
    });
    $('#fahuo').on('click', function () {
        var checkStatus = table.checkStatus('list-table');
        var ids = [];

        $(checkStatus.data).each(function (i, o) {
              ids.push(o.id);
        })
        if (ids.length < 1) {
            layer.msg('无选中项');
            return false;
        }
        ids = ids.join(",");
        layer.confirm('确定要对选中项进行发货操作吗？', function (index) {
            $.get("{:createUrl('send_goods')}", {ids: ids}, function (res) {
                  layer.msg(res.msg, {time: 1000, icon: 1});
                window.location.reload();
            });
          // window.location.href='{:createUrl("detail")}?id='+ids
          // window.open('{:createUrl("detail")}?id='+ids,'面单打印','height=100, width=400');
            layer.close(index);
        });
    });
    table.on('toolbar(list-box)', function (obj) {
        switch (obj.event) {
            case 'refresh':
                window.location.reload();
                break;
        }
    });

    //监听行工具事件
    table.on('tool(list-box)', function (obj) {
        var data = obj.data;
        if (obj.event === 'edit') {
            window.location.href = '{:createUrl("detail")}?id='+data.id;
        }
        if (obj.event === 'print') {
          window.open('{:createUrl("detail")}?id='+data.id,'面单打印','height=800, width=400');
        }
        if (obj.event === 'done') {
          layer.confirm('确定要对选中项进行发货操作吗？', function (index) {
                $.get("{:createUrl('send_goods')}", {ids: data.id}, function (res) {
                      layer.msg(res.msg, {time: 1000, icon: 1});
                    window.location.reload();
                });
                layer.close(index);
            });
        }
    });

});
</script>





