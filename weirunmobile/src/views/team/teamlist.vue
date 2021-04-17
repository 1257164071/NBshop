<template>
    <div>
        <nav-bar
            title="分享统计"
            left-arrow
            :fixed="true"
            :placeholder="true"
            @click-left="prev"
        />

        <div class="top">
            <span>总分享人数：{{num}} 人</span>
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
                    <div class="list-item clear" v-for="(item, index) in list" :key="index" @click="goParent(item.id)">
                        <div class="box">
                          <div>
                            <div>
                              <img :src="item.avatar">
                            </div>
                            <div>
                              <span>
                                {{item.username}}
                              </span>
                              <span v-if="item.is_consumption === 0">
                                普通会员
                              </span>
                              <span v-if="item.is_consumption === 1">
                                消费者
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
                parent_id: 0,
                num: 0,
            };
        },
        created() {
            let users = this.$storage.get("users",true);
            this.parent_id = this.$route.query.parent_id;
            this.amount = users.amount;
            this.$http.getUcenter().then((res)=>{
                if(res.status){
                    this.amount = users.amount = res.data.amount;
                    this.$store.commit("UPDATEUSERS",users);
                }
            });
        },
        watch: {
          '$route' () {
            this.parent_id = this.$route.query.parent_id;
            this.page = 1;
            this.list = [];
            this.getList();//我的初始化方法
          }
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
              this.getList()
            },
            getList() {
                this.isEmpty = false;
                this.$http.getShareList({
                    type: this.isActive,
                    page: this.page,
                    parent_id: this.parent_id
                }).then(result=>{
                    console.log(result.data)
                    this.num = result.data.num;
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
            goParent(id){
              this.list=[];
              this.page=1;
              this.$router.push('/team/teamlist?parent_id='+id)
            }
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
