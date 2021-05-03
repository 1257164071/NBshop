<template>
    <div>
        <van-overlay :show="show">
            <div id="loading">
                <van-loading type="spinner" />
            </div>
        </van-overlay>
    </div>
</template>

<script>
    import {Overlay, Loading} from 'vant';
    export default {
        name: 'Oauth',
        components: {
            [Overlay.name]: Overlay,
            [Loading.name]: Loading
        },
        data() {
            return {
                show: true
            };
        },
        created() {
            if(this.$tools.isWeiXin()){
                let parent_id = this.$storage.get('parent_id');
                let params = {
                    code: this.$route.query.code,
                    state: this.$route.query.state,
                    parent_id: parent_id,
                };

                this.$http.sendOauth(params).then(result=>{
                    if(result.status == 2){
                        this.$store.commit("UPDATEUSERS",result.data);
                        let path = this.$storage.get("VUE_REFERER");
                        this.$storage.delete("VUE_REFERER");
                        if(!path){
                            path = "/";
                        }
                        path = "/";
                        this.$router.push('/');
                    }else{
                        this.$router.push('/');
                    }
                }).catch(err=>{
                    alert('抱歉,因为您没有推荐人无法进行注册')
                });
            }else{
                this.$router.push('/');
            }
        },
        methods: {

        },
    }
</script>

<style lang="scss" scoped>
#loading{
    z-index: 99999;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%,-50%);
}
</style>
