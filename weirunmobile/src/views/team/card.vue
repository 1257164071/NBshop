<template>
  <div>
    <nav-bar
        title="推广名片"
        left-arrow
        :fixed="true"
        :placeholder="true"
        @click-left="prev"
    />
    <div class="container">
      <img :src="url">
    </div>
  </div>
</template>

<script>
  import NavBar from '../../components/nav-bar/nav-bar';

  export default {
    name: "card",
    components: {NavBar},
    data(){
      return {
        url: 'http://www.baidu.com',
      }
    },
    created() {
      this.onLoad();
    },
    methods: {
      onLoad(){
        this.$http.getCardBackground().then(response => {
          this.url = response.data.path
        });
      },
      prev(){
        this.$tools.prev();
      }
    }
  }
</script>

<style  lang="scss" scoped>
 .container{
   width: 100%;
   img {
     width: 100%;
   }
 }
</style>
