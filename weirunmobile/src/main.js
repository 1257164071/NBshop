import Vue from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';
import tools from './utils/Tools';
import request from './utils/Request';
import Storage from "./utils/Storage";
import Cookie from './utils/Cookie';
import wx from "weixin-js-sdk";
import * as users from './libs/Users';
import * as http from './api/Http';
import './assets/css/font-awesome.min.css';
import './assets/css/iconfont.css';
Vue.config.devtools = process.env.NODE_ENV === 'development'
Vue.config.productionTip = false
Vue.prototype.$tools = tools;
Vue.prototype.$request = request;
Vue.prototype.$http = http;
Vue.prototype.$storage = Storage;
Vue.prototype.$cookie = Cookie;
Vue.prototype.$wx = wx;

function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}
let parent_id =  getQueryString('parent_id');

users.init();
if (parent_id !== null){
  Storage.set("parent_id",parent_id)
}

new Vue({
  router,
  store,
  render: h => h(App),
}).$mount('#app')
