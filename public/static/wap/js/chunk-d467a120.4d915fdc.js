(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-d467a120"],{"449d":function(t,e,a){"use strict";a.r(e);var s,n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("van-overlay",{attrs:{show:t.show}},[a("div",{attrs:{id:"loading"}},[a("van-loading",{attrs:{type:"spinner"}})],1)])],1)},r=[],o=(a("b0c0"),a("ade3")),i=(a("68ef"),a("e3b3"),a("543e")),u=(a("a71a"),a("6e47")),c={name:"Oauth",components:(s={},Object(o["a"])(s,u["a"].name,u["a"]),Object(o["a"])(s,i["a"].name,i["a"]),s),data:function(){return{show:!0}},created:function(){var t=this;if(this.$tools.isWeiXin()){var e=this.$cookies.get("parent_id");if(null===e)return alert("抱歉无推荐人无法访问"),void this.$router.push("/");var a={code:this.$route.query.code,state:this.$route.query.state,parent_id:e};this.$http.sendOauth(a).then((function(e){if(2==e.status){t.$store.commit("UPDATEUSERS",e.data);var a=t.$storage.get("VUE_REFERER");t.$storage.delete("VUE_REFERER"),a||(a="/"),t.$router.push({path:a})}else t.$router.push("/")}))}else this.$router.push("/")},methods:{}},h=c,d=(a("5498"),a("2877")),l=Object(d["a"])(h,n,r,!1,null,"7efc5fec",null);e["default"]=l.exports},5498:function(t,e,a){"use strict";var s=a("6c36"),n=a.n(s);n.a},"6c36":function(t,e,a){}}]);