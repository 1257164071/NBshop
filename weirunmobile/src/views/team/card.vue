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
      <img src="http://127.0.0.1:8083/upload/swiper/20210224/cd11abfc840fa6b8758553a15d740cf4.jpg">
    </div>
  </div>
</template>

<script>
  import NavBar from '../../components/nav-bar/nav-bar';

  export default {
    name: "card",
    components: {NavBar},
    methods: {
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
