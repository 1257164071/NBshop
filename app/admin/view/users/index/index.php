<div class="row content-nav">
    <div class="col-xs-12">
        <ul>
            <li><a href="javascript:;"><i></i>&nbsp;会员管理</a></li>
            <li><a href="javascript:;">会员列表</a></li>
        </ul>
    </div>
</div>

<div class="layui-fluid" id="search-box">
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form layui-form-pane" action="">

                <div class="layui-form-item">

                    <div class="layui-inline">
                        <label class="layui-form-label seller-inline-2">选择分组：</label>
                        <div class="layui-input-inline seller-inline-4">
                            <select name="cat_id">
                                <option value="-1">全部</option>
                                {if !empty($cat)}
                                {volist name="cat" id="value"}
                                <option value="{$value.id}">{$value.name}</option>
                                {/volist}
                                {/if}
                            </select>
                        </div>
                    </div>

                    <div class="layui-inline">
                        <label class="layui-form-label seller-inline-2">用户名：</label>
                        <div class="layui-input-inline seller-inline-4">
                            <input type="text" name="title" placeholder="请输入用户名" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label seller-inline-2">昵称：</label>
                        <div class="layui-input-inline seller-inline-4">
                            <input type="text" name="nickname" placeholder="请输入用户名" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label seller-inline-2">手机号：</label>
                        <div class="layui-input-inline seller-inline-4">
                            <input type="text" name="mobile" placeholder="请输入手机号" autocomplete="off" class="layui-input">
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
        <button lay-event="url" type="button" class="layui-btn layui-btn-sm layui-bg-light-blue"><i class="layui-icon">&#xe61f;</i> 添加</button>
        <button lay-event="refresh" type="button" class="layui-btn layui-btn-sm layui-bg-red"><i class="layui-icon">&#xe9aa;</i> 刷新</button>
    </div>
</script>

<script type="text/html" id="list-bar">
    <a class="layui-btn layui-btn-xs layui-bg-orange" lay-event="finance">财务</a>
    <a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="log">日志</a>
    <a class="layui-btn layui-btn-xs layui-bg-cyan" lay-event="tags">标签</a>
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
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
                , {field: 'group_name', title: '所属分组',width:120}
                , {field: 'username', title: '用户名'}
                , {field: 'nickname', title: '昵称'}
                , {field: 'mobile', title: '手机号'}
                , {field: 'tags', title: '标签',width:120}
                , {field: 'point', title: '积分',width:100,align:"center"}
                , {field: 'amount', title: '余额',width:120,align:"center"}
                , {field:'status', title:'状态', width:60,align:"center",templet: function(res){
                        switch(res.status){
                            case 0:
                                return "<span style='color:green;'>正常</span>";
                            case 1:
                                return "<span style='color:#FFB800;'>审核</span>";
                            case 2:
                                return "<span style='color:#01AAED;'>锁定</span>";
                            case 3:
                                return "<span style='color:#FF5722;'>删除</span>";
                        }
                    }}
                , {field: 'time', title: '注册时间', width: 180, align: "center"}
                , {fixed: 'right', align: 'center', title: '操作', toolbar: '#list-bar', width: 280}
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
                        cat_id : $('[name="cat_id"]').val(),
                        title : $('[name="title"]').val(),
                        mobile : $('[name="mobile"]').val(),
                        nickname : $('[name="nickname"]').val(),
                    }
                }
            }, 'data');
        });

        //头工具栏事件
        table.on('toolbar(list-box)', function (obj) {
            switch (obj.event) {
                case 'url':
                    window.location.href = "{:createUrl('editor')}";
                    break;
                case 'refresh':
                    window.location.reload();
                    break;
            }
        });

        //监听行工具事件
        table.on('tool(list-box)', function (obj) {
            var data = obj.data;
            if (obj.event === 'del') {
                layer.confirm('你确定要删除吗？', function (index) {
                    $.get('{:createUrl("delete")}',{
                        id : data.id
                    },function(result){
                        layer.close(index);
                        if(result.code){
                            obj.del();
                        }else{
                            layer.msg(result.msg,{ icon : 2 });
                        }
                    },"json");
                });
            } else if (obj.event === 'edit') {
                window.location.href = '{:createUrl("editor")}?id='+data.id;
            }else if(obj.event == 'finance'){
                layer.open({
                    type: 2,
                    title: '金额管理',
                    shadeClose: true,
                    shade: 0.3,
                    area: ['60%', '58%'],
                    content: '{:createUrl("finance")}?id='+data.id
                });
            }else if(obj.event == 'log'){
                window.location.href = '{:createUrl("log")}?id='+data.id;
            }else if(obj.event == 'tags'){
                var string = '<form style="padding: 20px 20px 30px 20px;" class="layui-form" action="">';
                {volist name="tags" id="vo"}
                string += '<input type="checkbox" name="id[]" value="{$vo.id}" title="{$vo.name}" lay-skin="primary">';
                {/volist}
                    string += '<div style="padding-top: 20px;" class="layui-form-item">\n' +
                        '    <div class="layui-input-block">\n' +
                        '      <button class="layui-btn" lay-submit lay-filter="*">立即提交</button>\n' +
                        '      <button type="reset" class="layui-btn layui-btn-primary">重置</button>\n' +
                        '    </div>\n' +
                        '  </div>';
                    string += '</form>';
                    layer.open({
                        type: 1,
                        skin: 'layui-layer-rim',
                        area: ['420px', '240px'],
                        content: string,
                        success: function(layero, index){
                            form.render("checkbox");
                        }
                    });

                    form.on('submit(*)', function(res){
                        res.field.user_id = data.id;
                        $.post('{:createUrl("tags")}',res.field,function (res) {
                            console.log(res)
                        });
                        window.location.reload();
                        return false;
                    });
                }
            });


    });
</script>





