(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-e1f01e44"],{1219:function(t,e,n){"use strict";n.r(e);var a,i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("nav-bar",{attrs:{"left-arrow":"",fixed:!0,transparent:!0,"z-index":9999},on:{"click-left":t.prev}}),n("div",{staticClass:"wrap"},[t._m(0),n("van-form",{on:{submit:t.onSubmit}},[n("div",{staticClass:"the-form-box"},[n("div",{staticClass:"the-form-fields"},[n("van-field",{staticClass:"the-form-field",attrs:{type:"tel",name:"用户名","left-icon":"contact",placeholder:"请输入手机号码"},model:{value:t.username,callback:function(e){t.username=e},expression:"username"}}),n("van-field",{staticClass:"the-form-field",attrs:{type:"password",name:"密码","left-icon":"lock",placeholder:"请填写密码"},model:{value:t.password,callback:function(e){t.password=e},expression:"password"}})],1),n("div",{staticClass:"btn"},[n("van-button",{attrs:{loading:t.loading,"loading-text":"数据提交中",block:"",color:"#b91922",type:"info","native-type":"submit"}},[t._v(" 登 录 ")])],1),n("div",{staticClass:"hp-box"},[n("span",{on:{click:t.goRegister}},[t._v("立即注册")]),n("span",{on:{click:t.goForget}},[t._v("忘记密码？")])])])])],1)],1)},r=[function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"top"},[n("div",{staticClass:"title"},[t._v("A3Mall")]),n("div",{staticClass:"ctitle"},[t._v("素烟姿")])])}],o=(n("b0c0"),n("ac1f"),n("5319"),n("e7e5"),n("d399")),s=n("ade3"),l=(n("66b9"),n("b650")),c=(n("be7f"),n("565f")),u=(n("38d5"),n("772a")),f=n("3050"),d={name:"Login",components:(a={},Object(s["a"])(a,f["a"].name,f["a"]),Object(s["a"])(a,u["a"].name,u["a"]),Object(s["a"])(a,c["a"].name,c["a"]),Object(s["a"])(a,l["a"].name,l["a"]),a),data:function(){return{username:"",password:"",loading:!1,clientHeight:window.outerHeight-46-50}},created:function(){},methods:{prev:function(){this.$router.replace("/")},goRegister:function(){this.$router.push({path:"/public/register/"})},goForget:function(){this.$router.push({path:"/public/forget/"})},onSubmit:function(t){var e=this;if(!this.loading)if(""!=this.username)if(/^1[3-9]\d{9}$/.test(this.username))if(""!=this.password){var n={username:this.username,password:this.password},a=parseInt(this.$cookie.get("spread_id"));a>0&&(n.spread_id=a),this.loading=!0,this.$http.sendLogin(n).then((function(t){if(t.status){e.$store.commit("UPDATEUSERS",t.data);var n=e.$storage.get("VUE_REFERER");e.$storage.delete("VUE_REFERER"),n||(n="/"),e.$router.push({path:n})}else Object(o["a"])(t.info);e.loading=!1})).catch((function(t){e.loading=!1,Object(o["a"])("连接网络错误，请检查网络是否连接！")}))}else Object(o["a"])("请填写密码！");else Object(o["a"])("您填写的手机号码不正确！");else Object(o["a"])("请填写手机号码")}}},p=d,h=(n("9f49"),n("2877")),b=Object(h["a"])(p,i,r,!1,null,"e334182c",null);e["default"]=b.exports},3050:function(t,e,n){"use strict";var a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{class:{wrap:t.placeholder}},[n("div",{staticClass:"nav-bar",class:{"nav-bar-fixed":t.fixed,"nav-bar-transparent":t.transparent},style:t.obj},[t.leftArrow?n("div",{staticClass:"nav-bar-left",on:{click:t.left}},[n("i",{staticClass:"icon iconfont",class:{"nav-bar-icon":t.transparent},staticStyle:{"font-size":"18px"}},[t._v("")])]):t._e(),n("div",{staticClass:"nav-bar-middle",class:{"nav-bar-title":t.transparent}},[t._v(t._s(t.title))]),t.rightArrow?n("div",{staticClass:"nav-bar-right",on:{click:t.right}},["share"==t.rightIcon?n("i",{staticClass:"icon iconfont",class:{"nav-bar-icon":t.transparent},staticStyle:{"font-size":"18px"}},[t._v("")]):t._e(),"delete"==t.rightIcon?n("i",{staticClass:"icon iconfont",class:{"nav-bar-icon":t.transparent},staticStyle:{"font-size":"16px"}},[t._v("")]):t._e()]):t._e()])])},i=[],r=(n("a9e3"),{name:"NavBar",props:{title:{type:String,default:""},zIndex:{type:Number,default:0},fixed:{type:Boolean,default:!1},transparent:{type:Boolean,default:!1},backgroundColor:{type:String,default:""},placeholder:{type:Boolean,default:!1},leftArrow:{type:Boolean,default:!1},rightArrow:{type:Boolean,default:!1},rightIcon:{type:String,default:""}},data:function(){return{obj:{}}},mounted:function(){this.zIndex>0&&(this.obj={"z-index":this.zIndex}),""!=this.backgroundColor&&Object.assign(this.obj,{"background-color":this.backgroundColor})},methods:{left:function(){this.$emit("click-left")},right:function(){this.$emit("click-right")}}}),o=r,s=(n("f18a"),n("2877")),l=Object(s["a"])(o,a,i,!1,null,"646e11e8",null);e["a"]=l.exports},"38d5":function(t,e,n){"use strict";n("68ef")},3971:function(t,e,n){},5899:function(t,e){t.exports="\t\n\v\f\r                　\u2028\u2029\ufeff"},"58a8":function(t,e,n){var a=n("1d80"),i=n("5899"),r="["+i+"]",o=RegExp("^"+r+r+"*"),s=RegExp(r+r+"*$"),l=function(t){return function(e){var n=String(a(e));return 1&t&&(n=n.replace(o,"")),2&t&&(n=n.replace(s,"")),n}};t.exports={start:l(1),end:l(2),trim:l(3)}},"66b9":function(t,e,n){"use strict";n("68ef"),n("9d70"),n("3743"),n("e3b3"),n("bc1b")},7156:function(t,e,n){var a=n("861d"),i=n("d2bb");t.exports=function(t,e,n){var r,o;return i&&"function"==typeof(r=e.constructor)&&r!==n&&a(o=r.prototype)&&o!==n.prototype&&i(t,o),t}},"772a":function(t,e,n){"use strict";var a=n("d282"),i=n("db85"),r=Object(a["a"])("form"),o=r[0],s=r[1];e["a"]=o({props:{colon:Boolean,labelWidth:[Number,String],labelAlign:String,inputAlign:String,scrollToError:Boolean,validateFirst:Boolean,errorMessageAlign:String,submitOnEnter:{type:Boolean,default:!0},validateTrigger:{type:String,default:"onBlur"},showError:{type:Boolean,default:!0},showErrorMessage:{type:Boolean,default:!0}},provide:function(){return{vanForm:this}},data:function(){return{fields:[]}},methods:{validateSeq:function(){var t=this;return new Promise((function(e,n){var a=[];t.fields.reduce((function(t,e){return t.then((function(){if(!a.length)return e.validate().then((function(t){t&&a.push(t)}))}))}),Promise.resolve()).then((function(){a.length?n(a):e()}))}))},validateAll:function(){var t=this;return new Promise((function(e,n){Promise.all(t.fields.map((function(t){return t.validate()}))).then((function(t){t=t.filter((function(t){return t})),t.length?n(t):e()}))}))},validate:function(t){return t?this.validateField(t):this.validateFirst?this.validateSeq():this.validateAll()},validateField:function(t){var e=this.fields.filter((function(e){return e.name===t}));return e.length?new Promise((function(t,n){e[0].validate().then((function(e){e?n(e):t()}))})):Promise.reject()},resetValidation:function(t){this.fields.forEach((function(e){t&&e.name!==t||e.resetValidation()}))},scrollToField:function(t,e){this.fields.forEach((function(n){n.name===t&&n.$el.scrollIntoView(e)}))},addField:function(t){this.fields.push(t),Object(i["a"])(this.fields,this)},removeField:function(t){this.fields=this.fields.filter((function(e){return e!==t}))},getValues:function(){return this.fields.reduce((function(t,e){return t[e.name]=e.formValue,t}),{})},onSubmit:function(t){t.preventDefault(),this.submit()},submit:function(){var t=this,e=this.getValues();this.validate().then((function(){t.$emit("submit",e)})).catch((function(n){t.$emit("failed",{values:e,errors:n}),t.scrollToError&&t.scrollToField(n[0].name)}))}},render:function(){var t=arguments[0];return t("form",{class:s(),on:{submit:this.onSubmit}},[this.slots()])}})},"9f49":function(t,e,n){"use strict";var a=n("b758"),i=n.n(a);i.a},a9e3:function(t,e,n){"use strict";var a=n("83ab"),i=n("da84"),r=n("94ca"),o=n("6eeb"),s=n("5135"),l=n("c6b6"),c=n("7156"),u=n("c04e"),f=n("d039"),d=n("7c73"),p=n("241c").f,h=n("06cf").f,b=n("9bf2").f,v=n("58a8").trim,g="Number",m=i[g],y=m.prototype,S=l(d(y))==g,x=function(t){var e,n,a,i,r,o,s,l,c=u(t,!1);if("string"==typeof c&&c.length>2)if(c=v(c),e=c.charCodeAt(0),43===e||45===e){if(n=c.charCodeAt(2),88===n||120===n)return NaN}else if(48===e){switch(c.charCodeAt(1)){case 66:case 98:a=2,i=49;break;case 79:case 111:a=8,i=55;break;default:return+c}for(r=c.slice(2),o=r.length,s=0;s<o;s++)if(l=r.charCodeAt(s),l<48||l>i)return NaN;return parseInt(r,a)}return+c};if(r(g,!m(" 0o1")||!m("0b1")||m("+0x1"))){for(var E,_=function(t){var e=arguments.length<1?0:t,n=this;return n instanceof _&&(S?f((function(){y.valueOf.call(n)})):l(n)!=g)?c(new m(x(e)),n,_):x(e)},w=a?p(m):"MAX_VALUE,MIN_VALUE,NaN,NEGATIVE_INFINITY,POSITIVE_INFINITY,EPSILON,isFinite,isInteger,isNaN,isSafeInteger,MAX_SAFE_INTEGER,MIN_SAFE_INTEGER,parseFloat,parseInt,isInteger".split(","),k=0;w.length>k;k++)s(m,E=w[k])&&!s(_,E)&&b(_,E,h(m,E));_.prototype=y,y.constructor=_,o(i,g,_)}},b650:function(t,e,n){"use strict";var a=n("c31d"),i=n("2638"),r=n.n(i),o=n("d282"),s=n("ba31"),l=n("b1d2"),c=n("48f4"),u=n("ad06"),f=n("543e"),d=Object(o["a"])("button"),p=d[0],h=d[1];function b(t,e,n,a){var i,o=e.tag,d=e.icon,p=e.type,b=e.color,v=e.plain,g=e.disabled,m=e.loading,y=e.hairline,S=e.loadingText,x={};function E(t){m||g||(Object(s["a"])(a,"click",t),Object(c["a"])(a))}function _(t){Object(s["a"])(a,"touchstart",t)}b&&(x.color=v?b:l["f"],v||(x.background=b),-1!==b.indexOf("gradient")?x.border=0:x.borderColor=b);var w=[h([p,e.size,{plain:v,loading:m,disabled:g,hairline:y,block:e.block,round:e.round,square:e.square}]),(i={},i[l["c"]]=y,i)];function k(){var a,i=[];return m?i.push(t(f["a"],{class:h("loading"),attrs:{size:e.loadingSize,type:e.loadingType,color:"currentColor"}})):d&&i.push(t(u["a"],{attrs:{name:d,classPrefix:e.iconPrefix},class:h("icon")})),a=m?S:n.default?n.default():e.text,a&&i.push(t("span",{class:h("text")},[a])),i}return t(o,r()([{style:x,class:w,attrs:{type:e.nativeType,disabled:g},on:{click:E,touchstart:_}},Object(s["b"])(a)]),[t("div",{class:h("content")},[k()])])}b.props=Object(a["a"])(Object(a["a"])({},c["c"]),{},{text:String,icon:String,color:String,block:Boolean,plain:Boolean,round:Boolean,square:Boolean,loading:Boolean,hairline:Boolean,disabled:Boolean,iconPrefix:String,nativeType:String,loadingText:String,loadingType:String,tag:{type:String,default:"button"},type:{type:String,default:"default"},size:{type:String,default:"normal"},loadingSize:{type:String,default:"20px"}}),e["a"]=p(b)},b758:function(t,e,n){},bc1b:function(t,e,n){},be7f:function(t,e,n){"use strict";n("68ef"),n("9d70"),n("3743"),n("1a04"),n("1146")},f18a:function(t,e,n){"use strict";var a=n("3971"),i=n.n(a);i.a}}]);