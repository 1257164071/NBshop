<template>
    <div>
        <nav-bar
            title="推广排名"
            left-arrow
            :fixed="true"
            :placeholder="true"
            @click-left="prev"
        />

        <div class="top">
            <span>我的排名：10 名</span>
        </div>

        <div class="list-wrap">
            <van-empty v-if="isEmpty" :image="emptyImage" :description="emptyDescription" />
            <van-list
                    v-if="!isEmpty"
                    v-model="loading"
                    :finished="finished"
                    finished-text="没有更多了"
                    @load="onLoad"
            >

                <div class="list-box clear">
                    <div class="list-item clear" v-for="(item, index) in list" :key="index">
                        <div class="box">
                          <div>
                            <div>
                              <img :src="item.avatar">
                            </div>
                            <div>
                              <span style="margin-bottom: 10px">
                                {{item.nickname}}
                              </span>
                              <span>
                                第{{index+1}}名
                              </span>
                            </div>
                          </div>
                          <div>
                            <div>
                              <span>
                                注册时间
                              </span>
                            </div>
                            <div>
                              <span>{{item.create_time}}</span>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>

            </van-list>
        </div>

    </div>
</template>

<script>
    import NavBar from '../../components/nav-bar/nav-bar';
    import { List,Empty,Toast } from 'vant';
    export default {
        components: {
            [NavBar.name]: NavBar,
            [List.name]: List,
            [Empty.name]: Empty
        },
        data() {
            return {
                amount:'',
                list: [],
                loading: false,
                finished: false,
                isActive:1,
                page: 1,
                isEmpty: false,
                emptyImage: "search",
                emptyDescription: "暂无内容",
            };
        },
        created() {
            let users = this.$storage.get("users",true);
            console.log(users);
            this.amount = users.amount;
            this.$http.getUcenter().then((res)=>{
                if(res.status){
                    this.amount = users.amount = res.data.amount;
                    this.$store.commit("UPDATEUSERS",users);
                }
            });
        },
        methods: {
            changeData(index){
                this.page = 1;
                this.isActive = index;
                this.list = [];
                this.loading = true;
                this.onLoad();
            },
            prev() {
                this.$tools.prev();
            },
            onLoad() {
                this.isEmpty = false;
                this.$http.getSortList({
                    type: this.isActive,
                    page: this.page
                }).then(result=>{
                    if(result.data.list == undefined && this.page == 1){
                        this.isEmpty = true;
                        this.emptyImage = "search";
                        this.emptyDescription = "暂无内容";
                    } else if(result.status == 1){
                        this.list = this.list.concat(result.data.list);
                        this.loading = false;
                        this.page++;
                    }else if(result.status == -1){
                        if(result.data == undefined && this.page == 1){
                            this.isEmpty = true;
                            this.emptyImage = "search";
                            this.emptyDescription = "暂无内容";
                        }else{
                            this.loading = false;
                            this.finished = true;
                        }
                    }
                }).catch((error)=>{
                    this.isEmpty = true;
                    this.emptyImage = "network";
                    this.emptyDescription = "网络出错，请检查网络是否连接";
                });
            },
        },
    }
</script>

<style lang="scss" scoped>
    .top{
        height: 70px;
        line-height: 70px;
        background-color: #b91922;
        color: #fff;
        padding: 0 15px;font-size: 16px;
        span:first-child { float: left; }
    }
    .list-wrap{
        width: 100%;
        margin-top: 10px;
        .list-item{
            width: 100%;
            height: auto !important;
            height: 110px;
            background-color: #fff;
            font-size: 13px;
            margin-bottom: 10px;
            .t {
                height: 40px;
                line-height: 40px;
                border-bottom: 1px solid #ebebeb;
                span { font-size: 16px; color: #333; }
                span:first-child {
                    padding-left: 16px; float: left;
                }
                span:last-child {
                    padding-right: 16px; float: right;
                }
            }
            .box {
                height: 60px;
                width: 100%;
               padding-bottom: 20px;
                div {
                  display: inline-block;
                }
                div:first-child{
                  padding: 5px 5px 5px 5px;
                  img {
                    width: 50px;
                    height: 50px;
                  }
                  div {
                    display: inline-block;
                  }
                  div:last-child{
                    padding: 5px 5px 5px 5px;
                    span{
                      display: block;
                    }
                    span:first-child{
                      font-size: 14px;
                      width: 100%;
                      text-align: left;
                    }
                    span:last-child{
                      color: #b91922;
                      width: 100%;
                      text-align: left;
                      margin-top: 6px;
                    }
                  }
                }
                div:last-child{
                  padding: 5px 5px 5px 5px;
                  float: right;
                  height: 60px;
                  font-size: 14px;
                  div{
                    display: block;
                  }
                  span{
                    display:table-cell;text-align:center;margin: 0 auto;
                    line-height: 20px;
                    height: 20px;
                  }
                }
            }
        }

    }
</style>
