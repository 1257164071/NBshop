<template>
    <div>
        <nav-bar
                left-arrow
                :fixed="true"
                :transparent="true"
                :z-index="9999"
                @click-left="prev"
        />
        <div class="header">
            <div class="title">我的推广</div>
            <div class="info">
                <div>当前佣金</div>
                <div>100</div>
                <div>
                    <span>今日收益(元)：<span>99</span></span>
                    <span>|</span>
                    <span>累计收益(元)：<span>999</span></span>
                </div>
                <div>
                  <a>提现记录</a>
                  <a>立即提现</a>
                </div>
            </div>
        </div>


        <div class="guide">
            <div @click="$router.push('')">
                <span><img src="../../assets/images/wallet/7.png"></span>
                <span>推广名片</span>
            </div>
            <div @click="$router.push('')">
                <span><img src="../../assets/images/wallet/8.png"></span>
                <span>推广统计</span>
            </div>
            <div @click="$router.push('')">
                <span><img src="../../assets/images/wallet/9.png"></span>
                <span>佣金明细</span>
            </div>
            <div @click="$router.push('')">
                <span><img src="../../assets/images/wallet/10.png"></span>
                <span>推广订单</span>
            </div>
            <div @click="$router.push('')">
                <span><img src="../../assets/images/wallet/9.png"></span>
                <span>推广排名</span>
            </div>
            <div @click="$router.push('')">
                <span><img src="../../assets/images/wallet/10.png"></span>
                <span>佣金排名</span>
            </div>
        </div>
    </div>
</template>

<script>
    import NavBar from '../../components/nav-bar/nav-bar';
    export default {
        name: 'Wallet',
        components: {
            [NavBar.name]: NavBar
        },
        data() {
            return {
            };
        },
        created() {
        },
        methods: {
        }
    }
</script>

<style lang="scss" scoped>
    .header{
        width: 100%;
        background-image: url(../../assets/images/wallet-bg.png);
        background-size: 100%;
        background-repeat: no-repeat;
        height: 210px;
        position: relative;
        z-index: 1;
        .title{
            width: 100%;
            color: #fff;
            font-size: 17px;
            position: absolute;
            top: 13px;
            left: 0;
            font-weight: bold;
            text-align: center;
        }
        .rechange{
            position: absolute;
            top: 75px;
            right: 0;
            width: 90px;
            height: 30px;
            line-height: 30px;
            background-color: #cb565c;
            color: #fff;
            border-top-left-radius: 50px;
            border-bottom-left-radius: 50px;
            text-align: center;
            z-index: 9999;
        }
        .info{
            position: absolute;
            top: 40px;
            color: #fff;
            width: 100%;
            text-align: center;
            div:nth-child(1){
                padding-top: 15px;
                font-size: 13px;
                text-align: center;
            }
            div:nth-child(2){
                font-size: 23px;
            }
            div:nth-child(3){
                font-size: 13px;
                padding-top: 10px;
                span:nth-child(2){
                    padding: 0 5px;
                    position: relative;
                    top: -1px;
                }
                span{
                  font-size: 10px;
                  span{
                    font-size: 20px;
                  }
                }
            }
            div:nth-child(4){
              margin-top: 15px;
              a{
                border-radius: 20px;
                text-decoration: none;
                padding: 3px 15px;
                background: #fff;

                color: red;
                margin-right: 10px;
                margin-left: 10px;
              }
            }
        }
    }
    .log{
        display: flex;
        flex-wrap: nowrap;
        flex-direction: row;
        background-color: #fff;
        div{
            width: 33.333%;
            height: 100px;
            span {
                display: block;
                text-align: center;
                &:first-child{
                    margin-top: 20px;
                }
                &:last-child {
                    margin-top: 10px;
                }
            }
            &:nth-child(1){
                img { width: 31px; height: 29px; }
            }
            &:nth-child(2){
                img { width: 27px; height: 31px; }
            }
            &:nth-child(3){
                img { width: 36px; height: 28px; }
            }
        }
    }
    .receive{
        width: 100%;
        height: 90px;
        margin-top: 10px;
        background-color: #fff;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        .c {
            width: 50%;
            height: 90px;
            div{
                position: relative;
                margin-top: 20px;
                margin-left: 60px;
                span:first-child { font-size: 16px; color: #b91922 }
                span:last-child { padding-top: 4px; font-size: 12px; color: #999999; }
            }
            &:first-child div:before {
                position: absolute;
                left: -40px;
                top: 6px;
                content: " ";
                width: 30px;
                height: 33px;
                background-size: 100%;
                background-repeat: no-repeat;
                background-image: url(../../assets/images/wallet/4.png);
            }
            &:last-child div:before {
                position: absolute;
                left: -32px;
                top: -1px;
                content: " ";
                width: 23px;
                height: 41px;
                background-size: 100%;
                background-repeat: no-repeat;
                background-image: url(../../assets/images/wallet/5.png);
            }
            span {
                display: block;
            }
        }
    }
    .guide{
        padding: 20px 20px 20px 20px;
        margin-top: 10px;
        background-color: #fff;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        margin-bottom: 10px;

        div{
            border-bottom: 2px solid #f9f9f9;
            display: flex;
            flex-direction: column;
            width: 50%;
            flex-wrap: wrap-reverse;
            margin-bottom: 20px;
            img { width: 45px; height: 45px; display: inline-block; }
            div {
                display: inline-block;
            }
            span {
                display: flex;
                justify-content:center;
                margin-bottom: 5px;
            }

        }
    }


</style>
