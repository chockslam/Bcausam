!function(t){var e={};function i(r){if(e[r])return e[r].exports;var n=e[r]={i:r,l:!1,exports:{}};return t[r].call(n.exports,n,n.exports,i),n.l=!0,n.exports}i.m=t,i.c=e,i.d=function(t,e,r){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)i.d(r,n,function(e){return t[e]}.bind(null,n));return r},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="/",i(i.s=230)}({1:function(t,e,i){"use strict";function r(t,e,i,r,n,o,a,s){var l,c="function"==typeof t?t.options:t;if(e&&(c.render=e,c.staticRenderFns=i,c._compiled=!0),r&&(c.functional=!0),o&&(c._scopeId="data-v-"+o),a?(l=function(t){(t=t||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext)||"undefined"==typeof __VUE_SSR_CONTEXT__||(t=__VUE_SSR_CONTEXT__),n&&n.call(this,t),t&&t._registeredComponents&&t._registeredComponents.add(a)},c._ssrRegister=l):n&&(l=s?function(){n.call(this,(c.functional?this.parent:this).$root.$options.shadowRoot)}:n),l)if(c.functional){c._injectStyles=l;var u=c.render;c.render=function(t,e){return l.call(e),u(t,e)}}else{var d=c.beforeCreate;c.beforeCreate=d?[].concat(d,l):[l]}return{exports:t,options:c}}i.d(e,"a",(function(){return r}))},10:function(t,e){t.exports={indexOf:function(t,e){var i,r;if(Array.prototype.indexOf)return t.indexOf(e);for(i=0,r=t.length;i<r;i++)if(t[i]===e)return i;return-1},forEach:function(t,e,i){var r,n;if(Array.prototype.forEach)return t.forEach(e,i);for(r=0,n=t.length;r<n;r++)e.call(i,t[r],r,t)},trim:function(t){return String.prototype.trim?t.trim():t.replace(/(^\s*)|(\s*$)/g,"")},spaceIndex:function(t){var e=/\s|\n|\t/.exec(t);return e?e.index:-1}}},11:function(t,e,i){var r=i(13),n=i(16),o=i(33);function a(t,e){return new o(e).process(t)}for(var s in(e=t.exports=a).filterXSS=a,e.FilterXSS=o,r)e[s]=r[s];for(var s in n)e[s]=n[s];"undefined"!=typeof window&&(window.filterXSS=t.exports),"undefined"!=typeof self&&"undefined"!=typeof DedicatedWorkerGlobalScope&&self instanceof DedicatedWorkerGlobalScope&&(self.filterXSS=t.exports)},12:function(t,e,i){"use strict";var r={mixins:[i(2).a],data:function(){return{whitelabel:defender.whitelabel,is_free:parseInt(defender.is_free)}}},n=i(1),o=Object(n.a)(r,(function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",[i("div",{staticClass:"sui-footer",domProps:{innerHTML:t._s(t.whitelabel.footer_text)}}),t._v(" "),!1===t.whitelabel.change_footer?i("div",[1===t.is_free?i("ul",{staticClass:"sui-footer-nav"},[i("li",[i("a",{attrs:{href:"https://profiles.wordpress.org/wpmudev#content-plugins",target:"_blank"}},[t._v(t._s(t.__("Free Plugins")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/features/",target:"_blank"}},[t._v(t._s(t.__("Membership")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/roadmap/",target:"_blank"}},[t._v(t._s(t.__("Roadmap")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wordpress.org/support/plugin/defender-security/",target:"_blank"}},[t._v(t._s(t.__("Support")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/docs/",target:"_blank"}},[t._v(t._s(t.__("Docs")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/hub-welcome/",target:"_blank"}},[t._v(t._s(t.__("The Hub")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/terms-of-service/",target:"_blank"}},[t._v(t._s(t.__("Terms of Service")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://incsub.com/privacy-policy/",target:"_blank"}},[t._v(t._s(t.__("Privacy Policy")))])])]):i("ul",{staticClass:"sui-footer-nav"},[i("li",[i("a",{attrs:{href:"https://wpmudev.com/hub2/",target:"_blank"}},[t._v(t._s(t.__("The Hub")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/projects/category/plugins/",target:"_blank"}},[t._v(t._s(t.__("Plugins")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/roadmap/",target:"_blank"}},[t._v(t._s(t.__("Roadmap")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/hub2/support/",target:"_blank"}},[t._v(t._s(t.__("Support")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/docs/",target:"_blank"}},[t._v(t._s(t.__("Docs")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/hub2/community/",target:"_blank"}},[t._v(t._s(t.__("Community")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/academy/",target:"_blank"}},[t._v(t._s(t.__("Academy")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://wpmudev.com/terms-of-service/",target:"_blank"}},[t._v(t._s(t.__("Terms of Service")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://incsub.com/privacy-policy/",target:"_blank"}},[t._v(t._s(t.__("Privacy Policy")))])])]),t._v(" "),i("ul",{staticClass:"sui-footer-social"},[i("li",[i("a",{attrs:{href:"https://www.facebook.com/wpmudev",target:"_blank"}},[i("i",{staticClass:"sui-icon-social-facebook",attrs:{"aria-hidden":"true"}}),t._v(" "),i("span",{staticClass:"sui-screen-reader-text"},[t._v(t._s(t.__("Facebook")))])])]),t._v(" "),i("li",[t._m(0),t._v(" "),i("span",{staticClass:"sui-screen-reader-text"},[t._v(t._s(t.__("Twitter")))])]),t._v(" "),i("li",[i("a",{attrs:{href:"https://www.instagram.com/wpmu_dev/",target:"_blank"}},[i("i",{staticClass:"sui-icon-instagram",attrs:{"aria-hidden":"true"}}),t._v(" "),i("span",{staticClass:"sui-screen-reader-text"},[t._v(t._s(t.__("Instagram")))])])])])]):t._e()])}),[function(){var t=this.$createElement,e=this._self._c||t;return e("a",{attrs:{href:"https://twitter.com/wpmudev",target:"_blank"}},[e("i",{staticClass:"sui-icon-social-twitter",attrs:{"aria-hidden":"true"}})])}],!1,null,null,null);e.a=o.exports},13:function(t,e,i){var r=i(9).FilterCSS,n=i(9).getDefaultWhiteList,o=i(10);function a(){return{a:["target","href","title"],abbr:["title"],address:[],area:["shape","coords","href","alt"],article:[],aside:[],audio:["autoplay","controls","loop","preload","src"],b:[],bdi:["dir"],bdo:["dir"],big:[],blockquote:["cite"],br:[],caption:[],center:[],cite:[],code:[],col:["align","valign","span","width"],colgroup:["align","valign","span","width"],dd:[],del:["datetime"],details:["open"],div:[],dl:[],dt:[],em:[],font:["color","size","face"],footer:[],h1:[],h2:[],h3:[],h4:[],h5:[],h6:[],header:[],hr:[],i:[],img:["src","alt","title","width","height"],ins:["datetime"],li:[],mark:[],nav:[],ol:[],p:[],pre:[],s:[],section:[],small:[],span:[],sub:[],sup:[],strong:[],table:["width","border","align","valign"],tbody:["align","valign"],td:["width","rowspan","colspan","align","valign"],tfoot:["align","valign"],th:["width","rowspan","colspan","align","valign"],thead:["align","valign"],tr:["rowspan","align","valign"],tt:[],u:[],ul:[],video:["autoplay","controls","loop","preload","src","height","width"]}}var s=new r;function l(t){return t.replace(c,"&lt;").replace(u,"&gt;")}var c=/</g,u=/>/g,d=/"/g,p=/&quot;/g,f=/&#([a-zA-Z0-9]*);?/gim,h=/&colon;?/gim,g=/&newline;?/gim,m=/((j\s*a\s*v\s*a|v\s*b|l\s*i\s*v\s*e)\s*s\s*c\s*r\s*i\s*p\s*t\s*|m\s*o\s*c\s*h\s*a)\:/gi,v=/e\s*x\s*p\s*r\s*e\s*s\s*s\s*i\s*o\s*n\s*\(.*/gi,b=/u\s*r\s*l\s*\(.*/gi;function _(t){return t.replace(d,"&quot;")}function w(t){return t.replace(p,'"')}function x(t){return t.replace(f,(function(t,e){return"x"===e[0]||"X"===e[0]?String.fromCharCode(parseInt(e.substr(1),16)):String.fromCharCode(parseInt(e,10))}))}function y(t){return t.replace(h,":").replace(g," ")}function k(t){for(var e="",i=0,r=t.length;i<r;i++)e+=t.charCodeAt(i)<32?" ":t.charAt(i);return o.trim(e)}function C(t){return t=k(t=y(t=x(t=w(t))))}function T(t){return t=l(t=_(t))}var S=/<!--[\s\S]*?-->/g;e.whiteList={a:["target","href","title"],abbr:["title"],address:[],area:["shape","coords","href","alt"],article:[],aside:[],audio:["autoplay","controls","loop","preload","src"],b:[],bdi:["dir"],bdo:["dir"],big:[],blockquote:["cite"],br:[],caption:[],center:[],cite:[],code:[],col:["align","valign","span","width"],colgroup:["align","valign","span","width"],dd:[],del:["datetime"],details:["open"],div:[],dl:[],dt:[],em:[],font:["color","size","face"],footer:[],h1:[],h2:[],h3:[],h4:[],h5:[],h6:[],header:[],hr:[],i:[],img:["src","alt","title","width","height"],ins:["datetime"],li:[],mark:[],nav:[],ol:[],p:[],pre:[],s:[],section:[],small:[],span:[],sub:[],sup:[],strong:[],table:["width","border","align","valign"],tbody:["align","valign"],td:["width","rowspan","colspan","align","valign"],tfoot:["align","valign"],th:["width","rowspan","colspan","align","valign"],thead:["align","valign"],tr:["rowspan","align","valign"],tt:[],u:[],ul:[],video:["autoplay","controls","loop","preload","src","height","width"]},e.getDefaultWhiteList=a,e.onTag=function(t,e,i){},e.onIgnoreTag=function(t,e,i){},e.onTagAttr=function(t,e,i){},e.onIgnoreTagAttr=function(t,e,i){},e.safeAttrValue=function(t,e,i,r){if(i=C(i),"href"===e||"src"===e){if("#"===(i=o.trim(i)))return"#";if("http://"!==i.substr(0,7)&&"https://"!==i.substr(0,8)&&"mailto:"!==i.substr(0,7)&&"tel:"!==i.substr(0,4)&&"data:image/"!==i.substr(0,11)&&"ftp://"!==i.substr(0,6)&&"./"!==i.substr(0,2)&&"../"!==i.substr(0,3)&&"#"!==i[0]&&"/"!==i[0])return""}else if("background"===e){if(m.lastIndex=0,m.test(i))return""}else if("style"===e){if(v.lastIndex=0,v.test(i))return"";if(b.lastIndex=0,b.test(i)&&(m.lastIndex=0,m.test(i)))return"";!1!==r&&(i=(r=r||s).process(i))}return i=T(i)},e.escapeHtml=l,e.escapeQuote=_,e.unescapeQuote=w,e.escapeHtmlEntities=x,e.escapeDangerHtml5Entities=y,e.clearNonPrintableCharacter=k,e.friendlyAttrValue=C,e.escapeAttrValue=T,e.onIgnoreTagStripAll=function(){return""},e.StripTagBody=function(t,e){"function"!=typeof e&&(e=function(){});var i=!Array.isArray(t),r=[],n=!1;return{onIgnoreTag:function(a,s,l){if(function(e){return!!i||-1!==o.indexOf(t,e)}(a)){if(l.isClosing){var c="[/removed]",u=l.position+c.length;return r.push([!1!==n?n:l.position,u]),n=!1,c}return n||(n=l.position),"[removed]"}return e(a,s,l)},remove:function(t){var e="",i=0;return o.forEach(r,(function(r){e+=t.slice(i,r[0]),i=r[1]})),e+=t.slice(i)}}},e.stripCommentTag=function(t){return t.replace(S,"")},e.stripBlankChar=function(t){var e=t.split("");return(e=e.filter((function(t){var e=t.charCodeAt(0);return 127!==e&&(!(e<=31)||(10===e||13===e))}))).join("")},e.cssFilter=s,e.getDefaultCSSWhiteList=n},14:function(t,e){function i(){var t={"align-content":!1,"align-items":!1,"align-self":!1,"alignment-adjust":!1,"alignment-baseline":!1,all:!1,"anchor-point":!1,animation:!1,"animation-delay":!1,"animation-direction":!1,"animation-duration":!1,"animation-fill-mode":!1,"animation-iteration-count":!1,"animation-name":!1,"animation-play-state":!1,"animation-timing-function":!1,azimuth:!1,"backface-visibility":!1,background:!0,"background-attachment":!0,"background-clip":!0,"background-color":!0,"background-image":!0,"background-origin":!0,"background-position":!0,"background-repeat":!0,"background-size":!0,"baseline-shift":!1,binding:!1,bleed:!1,"bookmark-label":!1,"bookmark-level":!1,"bookmark-state":!1,border:!0,"border-bottom":!0,"border-bottom-color":!0,"border-bottom-left-radius":!0,"border-bottom-right-radius":!0,"border-bottom-style":!0,"border-bottom-width":!0,"border-collapse":!0,"border-color":!0,"border-image":!0,"border-image-outset":!0,"border-image-repeat":!0,"border-image-slice":!0,"border-image-source":!0,"border-image-width":!0,"border-left":!0,"border-left-color":!0,"border-left-style":!0,"border-left-width":!0,"border-radius":!0,"border-right":!0,"border-right-color":!0,"border-right-style":!0,"border-right-width":!0,"border-spacing":!0,"border-style":!0,"border-top":!0,"border-top-color":!0,"border-top-left-radius":!0,"border-top-right-radius":!0,"border-top-style":!0,"border-top-width":!0,"border-width":!0,bottom:!1,"box-decoration-break":!0,"box-shadow":!0,"box-sizing":!0,"box-snap":!0,"box-suppress":!0,"break-after":!0,"break-before":!0,"break-inside":!0,"caption-side":!1,chains:!1,clear:!0,clip:!1,"clip-path":!1,"clip-rule":!1,color:!0,"color-interpolation-filters":!0,"column-count":!1,"column-fill":!1,"column-gap":!1,"column-rule":!1,"column-rule-color":!1,"column-rule-style":!1,"column-rule-width":!1,"column-span":!1,"column-width":!1,columns:!1,contain:!1,content:!1,"counter-increment":!1,"counter-reset":!1,"counter-set":!1,crop:!1,cue:!1,"cue-after":!1,"cue-before":!1,cursor:!1,direction:!1,display:!0,"display-inside":!0,"display-list":!0,"display-outside":!0,"dominant-baseline":!1,elevation:!1,"empty-cells":!1,filter:!1,flex:!1,"flex-basis":!1,"flex-direction":!1,"flex-flow":!1,"flex-grow":!1,"flex-shrink":!1,"flex-wrap":!1,float:!1,"float-offset":!1,"flood-color":!1,"flood-opacity":!1,"flow-from":!1,"flow-into":!1,font:!0,"font-family":!0,"font-feature-settings":!0,"font-kerning":!0,"font-language-override":!0,"font-size":!0,"font-size-adjust":!0,"font-stretch":!0,"font-style":!0,"font-synthesis":!0,"font-variant":!0,"font-variant-alternates":!0,"font-variant-caps":!0,"font-variant-east-asian":!0,"font-variant-ligatures":!0,"font-variant-numeric":!0,"font-variant-position":!0,"font-weight":!0,grid:!1,"grid-area":!1,"grid-auto-columns":!1,"grid-auto-flow":!1,"grid-auto-rows":!1,"grid-column":!1,"grid-column-end":!1,"grid-column-start":!1,"grid-row":!1,"grid-row-end":!1,"grid-row-start":!1,"grid-template":!1,"grid-template-areas":!1,"grid-template-columns":!1,"grid-template-rows":!1,"hanging-punctuation":!1,height:!0,hyphens:!1,icon:!1,"image-orientation":!1,"image-resolution":!1,"ime-mode":!1,"initial-letters":!1,"inline-box-align":!1,"justify-content":!1,"justify-items":!1,"justify-self":!1,left:!1,"letter-spacing":!0,"lighting-color":!0,"line-box-contain":!1,"line-break":!1,"line-grid":!1,"line-height":!1,"line-snap":!1,"line-stacking":!1,"line-stacking-ruby":!1,"line-stacking-shift":!1,"line-stacking-strategy":!1,"list-style":!0,"list-style-image":!0,"list-style-position":!0,"list-style-type":!0,margin:!0,"margin-bottom":!0,"margin-left":!0,"margin-right":!0,"margin-top":!0,"marker-offset":!1,"marker-side":!1,marks:!1,mask:!1,"mask-box":!1,"mask-box-outset":!1,"mask-box-repeat":!1,"mask-box-slice":!1,"mask-box-source":!1,"mask-box-width":!1,"mask-clip":!1,"mask-image":!1,"mask-origin":!1,"mask-position":!1,"mask-repeat":!1,"mask-size":!1,"mask-source-type":!1,"mask-type":!1,"max-height":!0,"max-lines":!1,"max-width":!0,"min-height":!0,"min-width":!0,"move-to":!1,"nav-down":!1,"nav-index":!1,"nav-left":!1,"nav-right":!1,"nav-up":!1,"object-fit":!1,"object-position":!1,opacity:!1,order:!1,orphans:!1,outline:!1,"outline-color":!1,"outline-offset":!1,"outline-style":!1,"outline-width":!1,overflow:!1,"overflow-wrap":!1,"overflow-x":!1,"overflow-y":!1,padding:!0,"padding-bottom":!0,"padding-left":!0,"padding-right":!0,"padding-top":!0,page:!1,"page-break-after":!1,"page-break-before":!1,"page-break-inside":!1,"page-policy":!1,pause:!1,"pause-after":!1,"pause-before":!1,perspective:!1,"perspective-origin":!1,pitch:!1,"pitch-range":!1,"play-during":!1,position:!1,"presentation-level":!1,quotes:!1,"region-fragment":!1,resize:!1,rest:!1,"rest-after":!1,"rest-before":!1,richness:!1,right:!1,rotation:!1,"rotation-point":!1,"ruby-align":!1,"ruby-merge":!1,"ruby-position":!1,"shape-image-threshold":!1,"shape-outside":!1,"shape-margin":!1,size:!1,speak:!1,"speak-as":!1,"speak-header":!1,"speak-numeral":!1,"speak-punctuation":!1,"speech-rate":!1,stress:!1,"string-set":!1,"tab-size":!1,"table-layout":!1,"text-align":!0,"text-align-last":!0,"text-combine-upright":!0,"text-decoration":!0,"text-decoration-color":!0,"text-decoration-line":!0,"text-decoration-skip":!0,"text-decoration-style":!0,"text-emphasis":!0,"text-emphasis-color":!0,"text-emphasis-position":!0,"text-emphasis-style":!0,"text-height":!0,"text-indent":!0,"text-justify":!0,"text-orientation":!0,"text-overflow":!0,"text-shadow":!0,"text-space-collapse":!0,"text-transform":!0,"text-underline-position":!0,"text-wrap":!0,top:!1,transform:!1,"transform-origin":!1,"transform-style":!1,transition:!1,"transition-delay":!1,"transition-duration":!1,"transition-property":!1,"transition-timing-function":!1,"unicode-bidi":!1,"vertical-align":!1,visibility:!1,"voice-balance":!1,"voice-duration":!1,"voice-family":!1,"voice-pitch":!1,"voice-range":!1,"voice-rate":!1,"voice-stress":!1,"voice-volume":!1,volume:!1,"white-space":!1,widows:!1,width:!0,"will-change":!1,"word-break":!0,"word-spacing":!0,"word-wrap":!0,"wrap-flow":!1,"wrap-through":!1,"writing-mode":!1,"z-index":!1};return t}var r=/javascript\s*\:/gim;e.whiteList=i(),e.getDefaultWhiteList=i,e.onAttr=function(t,e,i){},e.onIgnoreAttr=function(t,e,i){},e.safeAttrValue=function(t,e){return r.test(e)?"":e}},15:function(t,e){t.exports={indexOf:function(t,e){var i,r;if(Array.prototype.indexOf)return t.indexOf(e);for(i=0,r=t.length;i<r;i++)if(t[i]===e)return i;return-1},forEach:function(t,e,i){var r,n;if(Array.prototype.forEach)return t.forEach(e,i);for(r=0,n=t.length;r<n;r++)e.call(i,t[r],r,t)},trim:function(t){return String.prototype.trim?t.trim():t.replace(/(^\s*)|(\s*$)/g,"")},trimRight:function(t){return String.prototype.trimRight?t.trimRight():t.replace(/(\s*$)/g,"")}}},16:function(t,e,i){var r=i(10);function n(t){var e=r.spaceIndex(t);if(-1===e)var i=t.slice(1,-1);else i=t.slice(1,e+1);return"/"===(i=r.trim(i).toLowerCase()).slice(0,1)&&(i=i.slice(1)),"/"===i.slice(-1)&&(i=i.slice(0,-1)),i}function o(t){return"</"===t.slice(0,2)}var a=/[^a-zA-Z0-9_:\.\-]/gim;function s(t,e){for(;e<t.length;e++){var i=t[e];if(" "!==i)return"="===i?e:-1}}function l(t,e){for(;e>0;e--){var i=t[e];if(" "!==i)return"="===i?e:-1}}function c(t){return function(t){return'"'===t[0]&&'"'===t[t.length-1]||"'"===t[0]&&"'"===t[t.length-1]}(t)?t.substr(1,t.length-2):t}e.parseTag=function(t,e,i){"use strict";var r="",a=0,s=!1,l=!1,c=0,u=t.length,d="",p="";t:for(c=0;c<u;c++){var f=t.charAt(c);if(!1===s){if("<"===f){s=c;continue}}else if(!1===l){if("<"===f){r+=i(t.slice(a,c)),s=c,a=c;continue}if(">"===f){r+=i(t.slice(a,s)),d=n(p=t.slice(s,c+1)),r+=e(s,r.length,d,p,o(p)),a=c+1,s=!1;continue}if('"'===f||"'"===f)for(var h=1,g=t.charAt(c-h);" "===g||"="===g;){if("="===g){l=f;continue t}g=t.charAt(c-++h)}}else if(f===l){l=!1;continue}}return a<t.length&&(r+=i(t.substr(a))),r},e.parseAttr=function(t,e){"use strict";var i=0,n=[],o=!1,u=t.length;function d(t,i){if(!((t=(t=r.trim(t)).replace(a,"").toLowerCase()).length<1)){var o=e(t,i||"");o&&n.push(o)}}for(var p=0;p<u;p++){var f,h=t.charAt(p);if(!1!==o||"="!==h)if(!1===o||p!==i||'"'!==h&&"'"!==h||"="!==t.charAt(p-1))if(/\s|\n|\t/.test(h)){if(t=t.replace(/\s|\n|\t/g," "),!1===o){if(-1===(f=s(t,p))){d(r.trim(t.slice(i,p))),o=!1,i=p+1;continue}p=f-1;continue}if(-1===(f=l(t,p-1))){d(o,c(r.trim(t.slice(i,p)))),o=!1,i=p+1;continue}}else;else{if(-1===(f=t.indexOf(h,p+1)))break;d(o,r.trim(t.slice(i+1,f))),o=!1,i=(p=f)+1}else o=t.slice(i,p),i=p+1}return i<t.length&&(!1===o?d(t.slice(i)):d(o,c(r.trim(t.slice(i))))),r.trim(n.join(" "))}},2:function(t,e,i){"use strict";var r=i(11);function n(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var i=t&&("undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"]);if(null==i)return;var r,n,o=[],a=!0,s=!1;try{for(i=i.call(t);!(a=(r=i.next()).done)&&(o.push(r.value),!e||o.length!==e);a=!0);}catch(t){s=!0,n=t}finally{try{a||null==i.return||i.return()}finally{if(s)throw n}}return o}(t,e)||function(t,e){if(!t)return;if("string"==typeof t)return o(t,e);var i=Object.prototype.toString.call(t).slice(8,-1);"Object"===i&&t.constructor&&(i=t.constructor.name);if("Map"===i||"Set"===i)return Array.from(t);if("Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i))return o(t,e)}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function o(t,e){(null==e||e>t.length)&&(e=t.length);for(var i=0,r=new Array(e);i<e;i++)r[i]=t[i];return r}var a=wp.i18n,s={whiteList:{a:["href","title","target"],span:["class"],strong:["*"]},safeAttrValue:function(t,e,i,n){return"a"===t&&"href"===e&&"%s"===i?"%s":Object(r.safeAttrValue)(t,e,i,n)}},l=new r.FilterXSS(s),c=[];e.a={data:function(){return{width:{document:0},mobileWidth:768,tabletWidth:1024,isMobile:!1,isTablet:!1}},methods:{__:function(t){var e=a.__(t,"wpdef");return l.process(e)},multipleTranslation:function(t,e,i){var r=a._n(t,e,i,"wpdef");return l.process(r)},xss:function(t){return l.process(t)},vsprintf:function(t){var e=a.sprintf.apply(null,arguments);return e},siteUrl:function(t){return void 0!==t?defender.site_url+t:defender.site_url},adminUrl:function(t){return void 0!==t?defender.admin_url+t:defender.admin_url},assetUrl:function(t){return defender.defender_url+t},maybeHighContrast:function(){return{"sui-color-accessible":!0===defender.misc.high_contrast}},maybeHideBranding:function(){return defender.whitelabel.hide_branding},showSupportLinks:function(){return"disabled"===defender.is_whitelabel&&0===parseInt(defender.is_free)},isWhitelabelDisabled:function(){return"disabled"===defender.is_whitelabel},whitelabelHeroImage:function(){var t=defender.whitelabel.hero_image;return t||!1},campaign_url:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"project/wp-defender";return"https://wpmudev.com/"+e+"/?utm_source=defender&utm_medium=plugin&utm_campaign="+t},httpRequest:function(t,e,i,r,n){var o=this;void 0===n&&(this.state.on_saving=!0);var a=ajaxurl+"?action="+this.endpoints[e]+"&_def_nonce="+this.nonces[e],s=jQuery.ajax({url:a,method:t,data:i,success:function(t){var e=t.data;o.state.on_saving=!1,void 0!==e&&void 0!==e.message&&(t.success?Defender.showNotification("success",e.message):Defender.showNotification("error",e.message)),void 0!==r&&r(t)}});c.push(s)},httpGetRequest:function(t,e,i,r){this.httpRequest("get",t,e,i,r)},httpPostRequest:function(t,e,i,r){this.httpRequest("post",t,e,i,r)},abortAllRequests:function(){for(var t=0;t<c.length;t++)c[t].abort()},getQueryStringParams:function(t){return t?(/^[?#]/.test(t)?t.slice(1):t).split("&").reduce((function(t,e){var i=n(e.split("="),2),r=i[0],o=i[1];return t[r]=o?decodeURIComponent(o.replace(/\+/g," ")):"",t}),{}):{}},rebindSUI:function(){jQuery(".sui-accordion").each((function(){SUI.suiAccordion(this)})),SUI.tabs(),SUI.modalDialog(),jQuery(".sui-select").SUIselect2({placeholder:function(){$(this).data("placeholder")},dropdownCssClass:"sui-select-dropdown"})},ucFirst:function(t){return t.charAt(0).toUpperCase()+t.slice(1)},windowWidth:function(t){this.width.document=window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth,this.isMobile=this.mobileWidth>=this.width.document,this.isTablet=this.tabletWidth>=this.width.document}},created:function(){this.$nextTick().then(this.windowWidth),window.addEventListener("resize",this.windowWidth)},beforeDestroy:function(){window.removeEventListener("resize",this.windowWidth)}}},20:function(t,e,i){"use strict";var r={name:"OPcacheNotice",mixins:[i(2).a],data:function(){return{opcacheSaveComments:defender.opcache_save_comments}},methods:{opcacheMessage:function(){var t=this.__("We have detected that your {value-1} is disabled on your hosting. For defender to function properly,  please contact your hosting provider and ask them to enabled {value-2}.");return t=(t=t.replace("{value-1}","<strong>opcache.save_comments</strong>")).replace("{value-2}","<strong>OPcache Save Comments</strong>")}}},n=i(1),o=Object(n.a)(r,(function(){var t=this,e=t.$createElement,i=t._self._c||e;return"disabled"==t.opcacheSaveComments?i("div",{staticClass:"sui-notice sui-notice-info"},[i("div",{staticClass:"sui-notice-content"},[i("div",{staticClass:"sui-notice-message"},[i("h3",{staticClass:"m-0"},[t._v(t._s(t.__("Enable OPcache Save Comments")))]),t._v(" "),i("p",{domProps:{innerHTML:t._s(t.opcacheMessage())}})])])]):t._e()}),[],!1,null,null,null);e.a=o.exports},230:function(t,e,i){t.exports=i(242)},242:function(t,e,i){"use strict";i.r(e);var r=i(7),n=i.n(r),o={mixins:[i(2).a],name:"tutorial",data:function(){return{width:{document:0},tutorialLink1:"https://wpmudev.com/blog/stop-hackers-with-defender-wordpress-security-plugin/",tutorialLink2:"https://wpmudev.com/blog/delete-suspicious-code-defender/",tutorialLink3:"https://wpmudev.com/blog/how-to-get-the-most-out-of-defender-security/",tutorialLink4:"https://wpmudev.com/blog/defender-ip-address-lockout-firewall/",utmCode:"?utm_source=defender&utm_medium=tutorial-page&utm_campaign=defender_tutorial_read_article",timeRead:tutorial.time_read,titleReadLink:tutorial.title_read_link}},created:function(){this.tutorialLink1=this.tutorialLink1+this.utmCode,this.tutorialLink2=this.tutorialLink2+this.utmCode,this.tutorialLink3=this.tutorialLink3+this.utmCode,this.tutorialLink4=this.tutorialLink4+this.utmCode},computed:{documentWidth:function(){return this.width.document}},mounted:function(){var t=this;this.$nextTick((function(){window.addEventListener("resize",t.getWidthDocument),t.getWidthDocument()}))},methods:{tutorialTitle:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,e=arguments.length>1?arguments[1]:void 0,i="";switch(t){case 1:i=this.__("How to Stop Hackers in Their Tracks with Defender");break;case 2:i=1640<=this.width.document||500<this.width.document&&793>this.width.document?this.__("Find Out if You’re Hacked: How to Find and Delete Suspicious Code with Defender"):this.__("Find Out if You’re Hacked: How to Find and Delete Suspicious Code...");break;case 3:i=this.__("How to Get the Most Out of Defender Security");break;case 4:i=1540<=this.width.document||430<this.width.document&&793>this.width.document?this.__("How to Create a Powerful and Secure Customized Firewall with Defender"):this.__("How to Create a Powerful and Secure Customized Firewall...")}return this.vsprintf('<a class="text-gray-500 font-medium tracking-dec-n-22px leading-18px" href="%s" target="_blank">%s</a>',e,i)},tutorialDesc:function(){var t,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,i=arguments.length>1?arguments[1]:void 0;switch(e){case 1:t=this.__("Defender deters hackers with IP banning, login lockout, updating security keys, and more.");break;case 2:t=this.__("Detecting suspicious code within a site isn’t always that simple and can easily go unnoticed.");break;case 3:t=this.__("Keeping your WordPress site safe often requires no more than the click of a button with Defender.");break;case 4:t=860>this.width.document?this.__("Hackers can be persistent at trying to get into your site and drop malicious code..."):this.__("Hackers can be persistent at trying to get into your site and drop malicious code, figuring out your credentials, leaving spam.")}return this.vsprintf('<a class="text-gray-400 font-normal"  href="%s" target="_blank">%s</a>',i,t)},getWidthDocument:function(){this.width.document=window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth}},beforeDestroy:function(){window.removeEventListener("resize",this.getWidthDocument)}},a=i(1),s=Object(a.a)(o,(function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"sui-wrap",class:t.maybeHighContrast(),attrs:{id:"tutorial"}},[i("div",{staticClass:"sui-header"},[i("h1",{staticClass:"sui-header-title"},[t._v("\n      "+t._s(t.__("Tutorials"))+"\n    ")]),t._v(" "),i("div",{staticClass:"sui-actions-right"},[i("a",{staticClass:"sui-button",attrs:{href:"https://wpmudev.com/blog/category/tutorials/?utm_source=defender&utm_medium=plugin&utm_campaign=defender_tutorials_alltutorials",target:"_blank"}},[i("i",{staticClass:"sui-icon-open-new-window sui-sm"}),t._v(" "+t._s(t.__("View All"))+"\n      ")])])]),t._v(" "),i("opcache-notice"),t._v(" "),i("div",{staticClass:"sui-box"},[i("div",{staticClass:"sui-box-header"},[i("h3",{staticClass:"sui-box-title"},[t._v("\n        "+t._s(t.__("Defender Tutorials"))+"\n      ")])]),t._v(" "),i("div",{staticClass:"sui-box-body bg-gray-100"},[i("div",{staticClass:"sui-row"},[i("div",{staticClass:"sui-col-lg-3 sui-col-md-4 sui-col-sm-6 wd-tutorial-post"},[i("div",{staticClass:"wd-tutorial-post-wrapper bg-white border  border-solid border-silver-light rounded-4px h-336px"},[i("div",{staticClass:"wd-tutorial-post-img"},[i("img",{staticClass:"sui-image",attrs:{"aria-hidden":"true",src:t.assetUrl("assets/img/tab-tutorial1@1x.png"),srcset:t.assetUrl("assets/img/tab-tutorial1@2x.png")}})]),t._v(" "),i("div",{staticClass:"wd-tutorial-post-body px-20px pt-20px pb-30px flex justify-between flex-col"},[i("small",{staticClass:"no-margin-bottom wd-tutorial-post-body-title",domProps:{innerHTML:t._s(t.tutorialTitle(1,t.tutorialLink1))}}),t._v(" "),i("p",{staticClass:"sui-description mt-10px mb-0 h-66px",domProps:{innerHTML:t._s(t.tutorialDesc(1,t.tutorialLink1))}}),t._v(" "),i("footer",{staticClass:"wd-tutorial-post-body-footer absolute text-base h-18px tracking-dec-n-25px leading-18px bottom-30px left-0 px-35px w-full"},[i("div",{staticClass:"float-left"},[i("a",{staticClass:"text-blue-normal font-medium",attrs:{href:t.tutorialLink1,target:"_blank"}},[t._v(t._s(t.titleReadLink))])]),t._v(" "),i("span",{staticClass:"text-gray-400 float-right flex items-center"},[i("i",{staticClass:"mr-5px",attrs:{"aria-hidden":"true"}}),t._v(" 5 "+t._s(t.timeRead))])])])])]),t._v(" "),i("div",{staticClass:"sui-col-lg-3 sui-col-md-4 sui-col-sm-6 wd-tutorial-post"},[i("div",{staticClass:"wd-tutorial-post-wrapper bg-white border  border-solid border-silver-light rounded-4px h-336px"},[i("div",{staticClass:"wd-tutorial-post-img"},[i("img",{staticClass:"sui-image",attrs:{"aria-hidden":"true",src:t.assetUrl("assets/img/tab-tutorial2@1x.png"),srcset:t.assetUrl("assets/img/tab-tutorial2@2x.png")}})]),t._v(" "),i("div",{staticClass:"wd-tutorial-post-body px-20px pt-20px pb-30px flex justify-between flex-col"},[i("small",{staticClass:"no-margin-bottom wd-tutorial-post-body-title",domProps:{innerHTML:t._s(t.tutorialTitle(2,t.tutorialLink2))}}),t._v(" "),i("p",{staticClass:"sui-description mt-10px mb-0 h-66px",domProps:{innerHTML:t._s(t.tutorialDesc(2,t.tutorialLink2))}}),t._v(" "),i("footer",{staticClass:"wd-tutorial-post-body-footer absolute text-base h-18px tracking-dec-n-25px leading-18px bottom-30px left-0 px-35px w-full"},[i("div",{staticClass:"float-left"},[i("a",{staticClass:"text-blue-normal font-medium",attrs:{href:t.tutorialLink2,target:"_blank"}},[t._v(t._s(t.titleReadLink))])]),t._v(" "),i("span",{staticClass:"text-gray-400 float-right flex items-center"},[i("i",{staticClass:"mr-5px",attrs:{"aria-hidden":"true"}}),t._v(" 6 "+t._s(t.timeRead))])])])])]),t._v(" "),i("div",{staticClass:"sui-col-lg-3 sui-col-md-4 sui-col-sm-6 wd-tutorial-post"},[i("div",{staticClass:"wd-tutorial-post-wrapper bg-white border  border-solid border-silver-light rounded-4px h-336px"},[i("div",{staticClass:"wd-tutorial-post-img"},[i("img",{staticClass:"sui-image",attrs:{"aria-hidden":"true",src:t.assetUrl("assets/img/tab-tutorial3@1x.png"),srcset:t.assetUrl("assets/img/tab-tutorial3@2x.png")}})]),t._v(" "),i("div",{staticClass:"wd-tutorial-post-body px-20px pt-20px pb-30px flex justify-between flex-col"},[i("small",{staticClass:"no-margin-bottom wd-tutorial-post-body-title",domProps:{innerHTML:t._s(t.tutorialTitle(3,t.tutorialLink3))}}),t._v(" "),i("p",{staticClass:"sui-description mt-10px mb-0 h-66px",domProps:{innerHTML:t._s(t.tutorialDesc(3,t.tutorialLink3))}}),t._v(" "),i("footer",{staticClass:"wd-tutorial-post-body-footer absolute text-base h-18px tracking-dec-n-25px leading-18px bottom-30px left-0 px-35px w-full"},[i("div",{staticClass:"float-left"},[i("a",{staticClass:"text-blue-normal font-medium",attrs:{href:t.tutorialLink3,target:"_blank"}},[t._v(t._s(t.titleReadLink))])]),t._v(" "),i("span",{staticClass:"text-gray-400 float-right flex items-center"},[i("i",{staticClass:"mr-5px",attrs:{"aria-hidden":"true"}}),t._v(" 7 "+t._s(t.timeRead))])])])])]),t._v(" "),i("div",{staticClass:"sui-col-lg-3 sui-col-md-4 sui-col-sm-6 wd-tutorial-post"},[i("div",{staticClass:"wd-tutorial-post-wrapper bg-white border  border-solid border-silver-light rounded-4px h-336px"},[i("div",{staticClass:"wd-tutorial-post-img"},[i("img",{staticClass:"sui-image",attrs:{"aria-hidden":"true",src:t.assetUrl("assets/img/tab-tutorial4@1x.png"),srcset:t.assetUrl("assets/img/tab-tutorial4@2x.png")}})]),t._v(" "),i("div",{staticClass:"wd-tutorial-post-body px-20px pt-20px pb-30px flex justify-between flex-col"},[i("small",{staticClass:"no-margin-bottom wd-tutorial-post-body-title",domProps:{innerHTML:t._s(t.tutorialTitle(4,t.tutorialLink4))}}),t._v(" "),i("p",{staticClass:"sui-description mt-10px mb-0 h-66px",domProps:{innerHTML:t._s(t.tutorialDesc(4,t.tutorialLink4))}}),t._v(" "),i("footer",{staticClass:"wd-tutorial-post-body-footer absolute text-base h-18px tracking-dec-n-25px leading-18px bottom-30px left-0 px-35px w-full"},[i("div",{staticClass:"float-left"},[i("a",{staticClass:"text-blue-normal font-medium",attrs:{href:t.tutorialLink4,target:"_blank"}},[t._v(t._s(t.titleReadLink))])]),t._v(" "),i("span",{staticClass:"text-gray-400 float-right flex items-center"},[i("i",{staticClass:"mr-5px",attrs:{"aria-hidden":"true"}}),t._v(" 6 "+t._s(t.timeRead))])])])])])])])]),t._v(" "),i("app-footer")],1)}),[],!1,null,null,null).exports,l=i(12),c=i(20);n.a.component("app-footer",l.a),n.a.component("opcache-notice",c.a);new n.a({el:"#defender",components:{tutorial:s},render:function(t){return t(s)}})},31:function(t,e,i){var r=i(14),n=i(32);i(15);function o(t){return null==t}function a(t){(t=function(t){var e={};for(var i in t)e[i]=t[i];return e}(t||{})).whiteList=t.whiteList||r.whiteList,t.onAttr=t.onAttr||r.onAttr,t.onIgnoreAttr=t.onIgnoreAttr||r.onIgnoreAttr,t.safeAttrValue=t.safeAttrValue||r.safeAttrValue,this.options=t}a.prototype.process=function(t){if(!(t=(t=t||"").toString()))return"";var e=this.options,i=e.whiteList,r=e.onAttr,a=e.onIgnoreAttr,s=e.safeAttrValue;return n(t,(function(t,e,n,l,c){var u=i[n],d=!1;if(!0===u?d=u:"function"==typeof u?d=u(l):u instanceof RegExp&&(d=u.test(l)),!0!==d&&(d=!1),l=s(n,l)){var p,f={position:e,sourcePosition:t,source:c,isWhite:d};return d?o(p=r(n,l,f))?n+":"+l:p:o(p=a(n,l,f))?void 0:p}}))},t.exports=a},32:function(t,e,i){var r=i(15);t.exports=function(t,e){";"!==(t=r.trimRight(t))[t.length-1]&&(t+=";");var i=t.length,n=!1,o=0,a=0,s="";function l(){if(!n){var i=r.trim(t.slice(o,a)),l=i.indexOf(":");if(-1!==l){var c=r.trim(i.slice(0,l)),u=r.trim(i.slice(l+1));if(c){var d=e(o,s.length,c,u,i);d&&(s+=d+"; ")}}}o=a+1}for(;a<i;a++){var c=t[a];if("/"===c&&"*"===t[a+1]){var u=t.indexOf("*/",a+2);if(-1===u)break;o=(a=u+1)+1,n=!1}else"("===c?n=!0:")"===c?n=!1:";"===c?n||l():"\n"===c&&l()}return r.trim(s)}},33:function(t,e,i){var r=i(9).FilterCSS,n=i(13),o=i(16),a=o.parseTag,s=o.parseAttr,l=i(10);function c(t){return null==t}function u(t){(t=function(t){var e={};for(var i in t)e[i]=t[i];return e}(t||{})).stripIgnoreTag&&(t.onIgnoreTag&&console.error('Notes: cannot use these two options "stripIgnoreTag" and "onIgnoreTag" at the same time'),t.onIgnoreTag=n.onIgnoreTagStripAll),t.whiteList=t.whiteList||n.whiteList,t.onTag=t.onTag||n.onTag,t.onTagAttr=t.onTagAttr||n.onTagAttr,t.onIgnoreTag=t.onIgnoreTag||n.onIgnoreTag,t.onIgnoreTagAttr=t.onIgnoreTagAttr||n.onIgnoreTagAttr,t.safeAttrValue=t.safeAttrValue||n.safeAttrValue,t.escapeHtml=t.escapeHtml||n.escapeHtml,this.options=t,!1===t.css?this.cssFilter=!1:(t.css=t.css||{},this.cssFilter=new r(t.css))}u.prototype.process=function(t){if(!(t=(t=t||"").toString()))return"";var e=this.options,i=e.whiteList,r=e.onTag,o=e.onIgnoreTag,u=e.onTagAttr,d=e.onIgnoreTagAttr,p=e.safeAttrValue,f=e.escapeHtml,h=this.cssFilter;e.stripBlankChar&&(t=n.stripBlankChar(t)),e.allowCommentTag||(t=n.stripCommentTag(t));var g=!1;if(e.stripIgnoreTagBody){g=n.StripTagBody(e.stripIgnoreTagBody,o);o=g.onIgnoreTag}var m=a(t,(function(t,e,n,a,g){var m,v={sourcePosition:t,position:e,isClosing:g,isWhite:i.hasOwnProperty(n)};if(!c(m=r(n,a,v)))return m;if(v.isWhite){if(v.isClosing)return"</"+n+">";var b=function(t){var e=l.spaceIndex(t);if(-1===e)return{html:"",closing:"/"===t[t.length-2]};var i="/"===(t=l.trim(t.slice(e+1,-1)))[t.length-1];return i&&(t=l.trim(t.slice(0,-1))),{html:t,closing:i}}(a),_=i[n],w=s(b.html,(function(t,e){var i,r=-1!==l.indexOf(_,t);return c(i=u(n,t,e,r))?r?(e=p(n,t,e,h))?t+'="'+e+'"':t:c(i=d(n,t,e,r))?void 0:i:i}));a="<"+n;return w&&(a+=" "+w),b.closing&&(a+=" /"),a+=">"}return c(m=o(n,a,v))?f(a):m}),f);return g&&(m=g.remove(m)),m},t.exports=u},7:function(t,e){t.exports=Vue},9:function(t,e,i){var r=i(14),n=i(31);for(var o in(e=t.exports=function(t,e){return new n(e).process(t)}).FilterCSS=n,r)e[o]=r[o];"undefined"!=typeof window&&(window.filterCSS=t.exports)}});