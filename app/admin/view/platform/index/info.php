<div class="row content-nav">
    <div class="col-xs-12">
        <ul>
            <li><a href="javascript:;"><i></i>&nbsp;系统信息</a></li>
            <li><a href="javascript:;">详细信息</a></li>
        </ul>
    </div>
</div>

<section class="content clearfix">
    <div class="layui-list-box">


        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">服务器信息</div>
                <div class="layui-card-body">
                    <table class="layui-table">
                        <tbody>
                        <tr>
                            <td style="width:50%;">操作系统：<?php echo PHP_OS; ?></td>
                            <td style="width:50%;">网站IP：{$ip}</td>
                        </tr>
                        <tr>
                            <td>PHP运行方式：<?php echo php_sapi_name(); ?></td>
                            <td>MySQL版本：{$version}</td>
                        </tr>
                        <tr>
                            <td>网站域名：<?php echo $_SERVER['SERVER_NAME']; ?></td>
                            <td>运行PHP版本：{$Think.PHP_VERSION}</td>
                        </tr>

                        <tr>
                            <td>执行时间限制：<?php echo ini_get('max_execution_time'); ?>秒</td>
                            <td>运行环境：<?php echo $_SERVER["SERVER_SOFTWARE"]; ?></td>
                        </tr>
                        <tr>
                            <td>Web服务端口：<?php echo $_SERVER['SERVER_PORT'] ?></td>
                            <td>上传最大值：<?php echo ini_get('post_max_size'); ?></td>
                        </tr>
                        <tr>
                            <td>北京时间：<?php echo gmdate("Y-n-j H:i:s", time() + 8 * 3600); ?></td>
                            <td>服务器时间：<?php echo date("Y-n-j H:i:s"); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>
