/*
 * Jquery.JSON
 */
(function($){function toIntegersAtLease(n)
{return n<10?'0'+n:n;}
Date.prototype.toJSON=function(date)
{return this.getUTCFullYear()+'-'+
toIntegersAtLease(this.getUTCMonth())+'-'+
toIntegersAtLease(this.getUTCDate());};var escapeable=/["\\\x00-\x1f\x7f-\x9f]/g;var meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};$.quoteString=function(string)
{if(escapeable.test(string))
{return'"'+string.replace(escapeable,function(a)
{var c=meta[a];if(typeof c==='string'){return c;}
c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+string+'"';};$.toJSON=function(o,compact)
{var type=typeof(o);if(type=="undefined")
return"undefined";else if(type=="number"||type=="boolean")
return o+"";else if(o===null)
return"null";if(type=="string")
{return $.quoteString(o);}
if(type=="object"&&typeof o.toJSON=="function")
return o.toJSON(compact);if(type!="function"&&typeof(o.length)=="number")
{var ret=[];for(var i=0;i<o.length;i++){ret.push($.toJSON(o[i],compact));}
if(compact)
return"["+ret.join(",")+"]";else
return"["+ret.join(", ")+"]";}
if(type=="function"){throw new TypeError("Unable to convert object of type 'function' to json.");}
var ret=[];for(var k in o){var name;type=typeof(k);if(type=="number")
name='"'+k+'"';else if(type=="string")
name=$.quoteString(k);else
continue;var val=$.toJSON(o[k],compact);if(typeof(val)!="string"){continue;}
if(compact)
ret.push(name+":"+val);else
ret.push(name+": "+val);}
return"{"+ret.join(", ")+"}";};$.compactJSON=function(o)
{return $.toJSON(o,true);};$.evalJSON=function(src)
{return eval("("+src+")");};$.secureEvalJSON=function(src)
{var filtered=src;filtered=filtered.replace(/\\["\\\/bfnrtu]/g,'@');filtered=filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']');filtered=filtered.replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*$/.test(filtered))
return eval("("+src+")");else
throw new SyntaxError("Error parsing JSON, source is not valid.");};})(jQuery);


/*
 * SimpleModal 1.2.3
 * Copyright (c) 2009 Eric Martin
 * Dual licensed under the MIT and GPL licenses
 * http://www.ericmmartin.com/projects/simplemodal/
 */
(function($){var ie6=$.browser.msie&&parseInt($.browser.version)==6&&typeof window['XMLHttpRequest']!="object",ieQuirks=null,w=[];$.modal=function(data,options){return $.modal.impl.init(data,options);};$.modal.close=function(){$.modal.impl.close();};$.fn.modal=function(options){return $.modal.impl.init(this,options);};$.modal.defaults={opacity:50,overlayId:'simplemodal-overlay',overlayCss:{},containerId:'simplemodal-container',containerCss:{},dataCss:{},zIndex:1000,close:true,closeHTML:'<a class="modalCloseImg" title="Close"></a>',closeClass:'simplemodal-close',position:null,persist:false,onOpen:null,onShow:null,onClose:null};$.modal.impl={opts:null,dialog:{},init:function(data,options){if(this.dialog.data){return false;}ieQuirks=$.browser.msie&&!$.boxModel;this.opts=$.extend({},$.modal.defaults,options);this.zIndex=this.opts.zIndex;this.occb=false;if(typeof data=='object'){data=data instanceof jQuery?data:$(data);if(data.parent().parent().size()>0){this.dialog.parentNode=data.parent();if(!this.opts.persist){this.dialog.orig=data.clone(true);}}}else if(typeof data=='string'||typeof data=='number'){data=$('<div/>').html(data);}else{alert('SimpleModal Error: Unsupported data type: '+typeof data);return false;}this.dialog.data=data.addClass('simplemodal-data').css(this.opts.dataCss);data=null;this.create();this.open();if($.isFunction(this.opts.onShow)){this.opts.onShow.apply(this,[this.dialog]);}return this;},create:function(){w=this.getDimensions();if(ie6){this.dialog.iframe=$('<iframe src="javascript:false;"/>').css($.extend(this.opts.iframeCss,{display:'none',opacity:0,position:'fixed',height:w[0],width:w[1],zIndex:this.opts.zIndex,top:0,left:0})).appendTo('body');}this.dialog.overlay=$('<div/>').attr('id',this.opts.overlayId).addClass('simplemodal-overlay').css($.extend(this.opts.overlayCss,{display:'none',opacity:this.opts.opacity/100,height:w[0],width:w[1],position:'fixed',left:0,top:0,zIndex:this.opts.zIndex+1})).appendTo('body');this.dialog.container=$('<div/>').attr('id',this.opts.containerId).addClass('simplemodal-container').css($.extend(this.opts.containerCss,{display:'none',position:'fixed',zIndex:this.opts.zIndex+2})).append(this.opts.close?$(this.opts.closeHTML).addClass(this.opts.closeClass):'').appendTo('body');this.setPosition();if(ie6||ieQuirks){this.fixIE();}this.dialog.container.append(this.dialog.data.hide());},bindEvents:function(){var self=this;$('.'+this.opts.closeClass).bind('click.simplemodal',function(e){e.preventDefault();self.close();});$(window).bind('resize.simplemodal',function(){w=self.getDimensions();self.setPosition();if(ie6||ieQuirks){self.fixIE();}else{self.dialog.iframe&&self.dialog.iframe.css({height:w[0],width:w[1]});self.dialog.overlay.css({height:w[0],width:w[1]});}});},unbindEvents:function(){$('.'+this.opts.closeClass).unbind('click.simplemodal');$(window).unbind('resize.simplemodal');},fixIE:function(){var p=this.opts.position;$.each([this.dialog.iframe||null,this.dialog.overlay,this.dialog.container],function(i,el){if(el){var bch='document.body.clientHeight',bcw='document.body.clientWidth',bsh='document.body.scrollHeight',bsl='document.body.scrollLeft',bst='document.body.scrollTop',bsw='document.body.scrollWidth',ch='document.documentElement.clientHeight',cw='document.documentElement.clientWidth',sl='document.documentElement.scrollLeft',st='document.documentElement.scrollTop',s=el[0].style;s.position='absolute';if(i<2){s.removeExpression('height');s.removeExpression('width');s.setExpression('height',''+bsh+' > '+bch+' ? '+bsh+' : '+bch+' + "px"');s.setExpression('width',''+bsw+' > '+bcw+' ? '+bsw+' : '+bcw+' + "px"');}else{var te,le;if(p&&p.constructor==Array){var top=p[0]?typeof p[0]=='number'?p[0].toString():p[0].replace(/px/,''):el.css('top').replace(/px/,'');te=top.indexOf('%')==-1?top+' + (t = '+st+' ? '+st+' : '+bst+') + "px"':parseInt(top.replace(/%/,''))+' * (('+ch+' || '+bch+') / 100) + (t = '+st+' ? '+st+' : '+bst+') + "px"';if(p[1]){var left=typeof p[1]=='number'?p[1].toString():p[1].replace(/px/,'');le=left.indexOf('%')==-1?left+' + (t = '+sl+' ? '+sl+' : '+bsl+') + "px"':parseInt(left.replace(/%/,''))+' * (('+cw+' || '+bcw+') / 100) + (t = '+sl+' ? '+sl+' : '+bsl+') + "px"';}}else{te='('+ch+' || '+bch+') / 2 - (this.offsetHeight / 2) + (t = '+st+' ? '+st+' : '+bst+') + "px"';le='('+cw+' || '+bcw+') / 2 - (this.offsetWidth / 2) + (t = '+sl+' ? '+sl+' : '+bsl+') + "px"';}s.removeExpression('top');s.removeExpression('left');s.setExpression('top',te);s.setExpression('left',le);}}});},getDimensions:function(){var el=$(window);var h=$.browser.opera&&$.browser.version>'9.5'&&$.fn.jquery<='1.2.6'?document.documentElement['clientHeight']:el.height();return[h,el.width()];},setPosition:function(){var top,left,hCenter=(w[0]/2)-((this.dialog.container.height()||this.dialog.data.height())/2),vCenter=(w[1]/2)-((this.dialog.container.width()||this.dialog.data.width())/2);if(this.opts.position&&this.opts.position.constructor==Array){top=this.opts.position[0]||hCenter;left=this.opts.position[1]||vCenter;}else{top=hCenter;left=vCenter;}this.dialog.container.css({left:left,top:top});},open:function(){this.dialog.iframe&&this.dialog.iframe.show();if($.isFunction(this.opts.onOpen)){this.opts.onOpen.apply(this,[this.dialog]);}else{this.dialog.overlay.show();this.dialog.container.show();this.dialog.data.show();}this.bindEvents();},close:function(){if(!this.dialog.data){return false;}if($.isFunction(this.opts.onClose)&&!this.occb){this.occb=true;this.opts.onClose.apply(this,[this.dialog]);}else{if(this.dialog.parentNode){if(this.opts.persist){this.dialog.data.hide().appendTo(this.dialog.parentNode);}else{this.dialog.data.remove();this.dialog.orig.appendTo(this.dialog.parentNode);}}else{this.dialog.data.remove();}this.dialog.container.remove();this.dialog.overlay.remove();this.dialog.iframe&&this.dialog.iframe.remove();this.dialog={};}this.unbindEvents();}};})(jQuery);

/*
 * Cluetip Version 0.9.8
 * http://plugins.learningjquery.com/cluetip/
 */
(function($){var $cluetip,$cluetipInner,$cluetipOuter,$cluetipTitle,$cluetipArrows,$dropShadow,imgCount;$.fn.cluetip=function(js,options){if(typeof js=='object'){options=js;js=null}return this.each(function(index){var $this=$(this);var opts=$.extend(false,{},$.fn.cluetip.defaults,options||{},$.metadata?$this.metadata():$.meta?$this.data():{});var cluetipContents=false;var cluezIndex=parseInt(opts.cluezIndex,10)-1;var isActive=false,closeOnDelay=0;if(!$('#cluetip').length){$cluetipInner=$('<div id="cluetip-inner"></div>');$cluetipTitle=$('<h4 id="cluetip-title"><span class="tip-title"></span></h4>');$cluetipOuter=$('<div id="cluetip-outer"></div>').append($cluetipInner).prepend($cluetipTitle);$cluetip=$('<div id="cluetip"></div>').css({zIndex:opts.cluezIndex}).append($cluetipOuter).append('<div id="cluetip-extra"></div>')[insertionType](insertionElement).hide();$('<div id="cluetip-waitimage"></div>').css({position:'absolute',zIndex:cluezIndex-1}).insertBefore('#cluetip').hide();$cluetip.css({position:'absolute',zIndex:cluezIndex});$cluetipOuter.css({position:'relative',zIndex:cluezIndex+1});$cluetipArrows=$('<div id="cluetip-arrows" class="cluetip-arrows"></div>').css({zIndex:cluezIndex+1}).appendTo('#cluetip')}var dropShadowSteps=(opts.dropShadow)?+opts.dropShadowSteps:0;if(!$dropShadow){$dropShadow=$([]);for(var i=0;i<dropShadowSteps;i++){$dropShadow=$dropShadow.add($('<div></div>').css({zIndex:cluezIndex-i-1,opacity:.1,top:1+i,left:1+i}))};$dropShadow.css({position:'absolute',backgroundColor:'#000'}).prependTo($cluetip)}var tipAttribute=$this.attr(opts.attribute),ctClass=opts.cluetipClass;if(!tipAttribute&&!opts.splitTitle&&!js)return true;if(opts.local&&opts.hideLocal){$(tipAttribute+':first').hide()}var tOffset=parseInt(opts.topOffset,10),lOffset=parseInt(opts.leftOffset,10);var tipHeight,wHeight;var defHeight=isNaN(parseInt(opts.height,10))?'auto':(/\D/g).test(opts.height)?opts.height:opts.height+'px';var sTop,linkTop,posY,tipY,mouseY,baseline;var tipInnerWidth=isNaN(parseInt(opts.width,10))?275:parseInt(opts.width,10);var tipWidth=tipInnerWidth+(parseInt($cluetip.css('paddingLeft'))||0)+(parseInt($cluetip.css('paddingRight'))||0)+dropShadowSteps;var linkWidth=this.offsetWidth;var linkLeft,posX,tipX,mouseX,winWidth;var tipParts;var tipTitle=(opts.attribute!='title')?$this.attr(opts.titleAttribute):'';if(opts.splitTitle){if(tipTitle==undefined){tipTitle=''}tipParts=tipTitle.split(opts.splitTitle);tipTitle=tipParts.shift()}var localContent;var activate=function(event){if(!opts.onActivate($this)){return false}isActive=true;$cluetip.removeClass().css({width:tipInnerWidth});if(tipAttribute==$this.attr('href')){$this.css('cursor',opts.cursor)}$this.attr('title','');if(opts.hoverClass){$this.addClass(opts.hoverClass)}if(opts.clickClass){$this.addClass(opts.clickClass)}linkTop=posY=$this.offset().top;linkLeft=$this.offset().left;mouseX=event.pageX;mouseY=event.pageY;if($this[0].tagName.toLowerCase()!='area'){sTop=$(document).scrollTop();winWidth=$(window).width()}if(opts.positionBy=='fixed'){posX=linkWidth+linkLeft+lOffset;$cluetip.css({left:posX})}else{posX=(linkWidth>linkLeft&&linkLeft>tipWidth)||linkLeft+linkWidth+tipWidth+lOffset>winWidth?linkLeft-tipWidth-lOffset:linkWidth+linkLeft+lOffset;if($this[0].tagName.toLowerCase()=='area'||opts.positionBy=='mouse'||linkWidth+tipWidth>winWidth){if(mouseX+20+tipWidth>winWidth){$cluetip.addClass(' cluetip-'+ctClass);posX=(mouseX-tipWidth-lOffset)>=0?mouseX-tipWidth-lOffset-parseInt($cluetip.css('marginLeft'),10)+parseInt($cluetipInner.css('marginRight'),10):mouseX-(tipWidth/2)}else{posX=mouseX+lOffset}}var pY=posX<0?event.pageY+tOffset:event.pageY;$cluetip.css({left:(posX>0&&opts.positionBy!='bottomTop')?posX:(mouseX+(tipWidth/2)>winWidth)?winWidth/2-tipWidth/2:Math.max(mouseX-(tipWidth/2),0)})}wHeight=$(window).height();if(js){$cluetipInner.html(js);cluetipShow(pY)}else if(tipParts){var tpl=tipParts.length;for(var i=0;i<tpl;i++){if(i==0){$cluetipInner.html(tipParts[i])}else{$cluetipInner.append('<div class="split-body">'+tipParts[i]+'</div>')}};cluetipShow(pY)}else if(!opts.local&&tipAttribute.indexOf('#')!=0){if(cluetipContents&&opts.ajaxCache){$cluetipInner.html(cluetipContents);cluetipShow(pY)}else{var ajaxSettings=opts.ajaxSettings;ajaxSettings.url=tipAttribute;ajaxSettings.beforeSend=function(){$this.addClass('loading');$cluetipOuter.children().empty();if(opts.waitImage){$('#cluetip-waitimage').css({top:mouseY+20,left:mouseX+20}).show()}};ajaxSettings.error=function(){if(isActive){$cluetipInner.html('<em>sorry, the contents could not be loaded</em>')}};ajaxSettings.success=function(data){cluetipContents=opts.ajaxProcess(data);if(isActive){$cluetipInner.html(cluetipContents)}};ajaxSettings.complete=function(){$this.removeClass('loading');imgCount=$('#cluetip-inner img').length;if(imgCount&&!$.browser.opera){$('#cluetip-inner img').load(function(){imgCount--;if(imgCount<1){$('#cluetip-waitimage').hide();if(isActive)cluetipShow(pY)}})}else{$('#cluetip-waitimage').hide();if(isActive)cluetipShow(pY)}};$.ajax(ajaxSettings)}}else if(opts.local){var $localContent=$(tipAttribute+':first');var localCluetip=$.fn.wrapInner?$localContent.wrapInner('<div></div>').children().clone(true):$localContent.html();$.fn.wrapInner?$cluetipInner.empty().append(localCluetip):$cluetipInner.html(localCluetip);cluetipShow(pY)}};var cluetipShow=function(bpY){$cluetip.addClass('cluetip-'+ctClass);if(opts.truncate){var $truncloaded=$cluetipInner.text().slice(0,opts.truncate)+'...';$cluetipInner.html($truncloaded)}function doNothing(){};tipTitle?$cluetipTitle.show().html('<span>'+tipTitle+'</span>'):(opts.showTitle)?$cluetipTitle.show().html('<span>&nbsp;</span>'):$cluetipTitle.hide();if(opts.sticky){var $closeLink=$('<a id="cluetip-close" class="close" href="#">'+opts.closeText+'</a>');(opts.closePosition=='bottom')?$closeLink.appendTo($cluetipInner):(opts.closePosition=='title')?$closeLink.prependTo($cluetipTitle):$closeLink.prependTo($cluetipInner);$closeLink.click(function(){cluetipClose();return false});if(opts.mouseOutClose){if($.fn.hoverIntent&&opts.hoverIntent){$cluetip.hoverIntent({over:doNothing,timeout:opts.hoverIntent.timeout,out:function(){$closeLink.trigger('click')}})}else{$cluetip.hover(doNothing,function(){$closeLink.trigger('click')})}}else{$cluetip.unbind('mouseout')}}var direction='';$cluetipOuter.css({overflow:defHeight=='auto'?'visible':'auto',height:defHeight});tipHeight=defHeight=='auto'?Math.max($cluetip.outerHeight(),$cluetip.height()):parseInt(defHeight,10);tipY=posY;baseline=sTop+wHeight;if(opts.positionBy=='fixed'){tipY=posY-opts.dropShadowSteps+tOffset}else if((posX<mouseX&&Math.max(posX,0)+tipWidth>mouseX)||opts.positionBy=='bottomTop'){if(posY+tipHeight+tOffset>baseline&&mouseY-sTop>tipHeight+tOffset){tipY=mouseY-tipHeight-tOffset;direction='top'}else{tipY=mouseY+tOffset;direction='bottom'}}else if(posY+tipHeight+tOffset>baseline){tipY=(tipHeight>=wHeight)?sTop:baseline-tipHeight-tOffset}else if($this.css('display')=='block'||$this[0].tagName.toLowerCase()=='area'||opts.positionBy=="mouse"){tipY=bpY-tOffset}else{tipY=posY-opts.dropShadowSteps}if(direction==''){posX<linkLeft?direction='left':direction='right'}$cluetip.css({top:tipY+'px'}).removeClass().addClass('clue-'+direction+'-'+ctClass).addClass(' cluetip-'+ctClass);if(opts.arrows){var bgY=(posY-tipY-opts.dropShadowSteps);$cluetipArrows.css({top:(/(left|right)/.test(direction)&&posX>=0&&bgY>0)?bgY+'px':/(left|right)/.test(direction)?0:''}).show()}else{$cluetipArrows.hide()}$dropShadow.hide();$cluetip.hide()[opts.fx.open](opts.fx.open!='show'&&opts.fx.openSpeed);if(opts.dropShadow)$dropShadow.css({height:tipHeight,width:tipInnerWidth}).show();if($.fn.bgiframe){$cluetip.bgiframe()}if(opts.delayedClose>0){closeOnDelay=setTimeout(cluetipClose,opts.delayedClose)}opts.onShow($cluetip,$cluetipInner)};var inactivate=function(){isActive=false;$('#cluetip-waitimage').hide();if(!opts.sticky||(/click|toggle/).test(opts.activation)){cluetipClose();clearTimeout(closeOnDelay)};if(opts.hoverClass){$this.removeClass(opts.hoverClass)}if(opts.clickClass){$this.removeClass(opts.clickClass)}$('.cluetip-clicked').removeClass('cluetip-clicked')};var cluetipClose=function(){$cluetipOuter.parent().hide().removeClass().end().children().empty();if(tipTitle){$this.attr(opts.titleAttribute,tipTitle)}if(opts.clickClass){$this.removeClass(opts.clickClass)}$('.cluetip-clicked').removeClass('cluetip-clicked');$this.css('cursor','');if(opts.arrows)$cluetipArrows.css({top:''})};if((/click|toggle/).test(opts.activation)){$this.click(function(event){if($cluetip.is(':hidden')||!$this.is('.cluetip-clicked')){activate(event);$('.cluetip-clicked').removeClass(opts.clickClass);$('.cluetip-clicked').removeClass('cluetip-clicked');$this.addClass('cluetip-clicked')}else{inactivate(event)}this.blur();return false})}else if(opts.activation=='focus'){$this.focus(function(event){activate(event)});$this.blur(function(event){inactivate(event)})}else{$this.click(function(){if($this.attr('href')&&$this.attr('href')==tipAttribute&&!opts.clickThrough){return false}});var mouseTracks=function(evt){if(opts.tracking==true){var trackX=posX-evt.pageX;var trackY=tipY?tipY-evt.pageY:posY-evt.pageY;$this.mousemove(function(evt){$cluetip.css({left:evt.pageX+trackX,top:evt.pageY+trackY})})}};if($.fn.hoverIntent&&opts.hoverIntent){$this.mouseover(function(){$this.attr('title','')}).hoverIntent({sensitivity:opts.hoverIntent.sensitivity,interval:opts.hoverIntent.interval,over:function(event){activate(event);mouseTracks(event)},timeout:opts.hoverIntent.timeout,out:function(event){inactivate(event);$this.unbind('mousemove')}})}else{$this.hover(function(event){activate(event);mouseTracks(event)},function(event){inactivate(event);$this.unbind('mousemove')})}}})};$.fn.cluetip.defaults={width:275,height:'auto',cluezIndex:97,positionBy:'bottomTop',topOffset:25,leftOffset:20,local:false,hideLocal:true,attribute:'href',titleAttribute:'title',splitTitle:'',showTitle:true,cluetipClass:'help',hoverClass:'',clickClass:'live-help',waitImage:false,cursor:'help',arrows:true,dropShadow:false,dropShadowSteps:6,sticky:true,mouseOutClose:false,activation:'click',clickThrough:false,tracking:false,delayedClose:0,closePosition:'title',closeText:'Close',truncate:0,fx:{open:'fadeIn',openSpeed:'2000'},hoverIntent:{sensitivity:3,interval:50,timeout:0},onActivate:function(e){return true},onShow:function(ct,c){},ajaxCache:true,ajaxProcess:function(data){data=data.replace(/<s(cript|tyle)(.|\s)*?\/s(cript|tyle)>/g,'').replace(/<(link|title)(.|\s)*?\/(link|title)>/g,'');return data},ajaxSettings:{dataType:'html'},debug:false};var insertionType='appendTo',insertionElement='body';$.cluetip={};$.cluetip.setup=function(options){if(options&&options.insertionType&&(options.insertionType).match(/appendTo|prependTo|insertBefore|insertAfter/)){insertionType=options.insertionType}if(options&&options.insertionElement){insertionElement=options.insertionElement}}})(jQuery);

(function($){
$.preloadImages = function() {
	for(var i = 0; i<arguments.length; i++) {
		jQuery("<img />").attr("src", arguments[i]);
	}
}
})(jQuery);

/**
 * jQuery Serialize List
 * Copyright (c) 2009 Mike Botsko, Botsko.net LLC
 * Licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * Copyright notice and license must remain intact for legal use
 * Version .1
 *
 * Serialize an unordered or ordered list item. Optional ability
 * to determine which attributes are included. The serialization
 * will be read by PHP as a multidimensional array which you may
 * use for saving state.
 *
 * http://github.com/botskonet/jquery.serialize-list
 */
(function($){
    $.fn.serializelist = function(options) {
        // Extend the configuration options with user-provided
        var defaults = {
			attributes: ['id', 'class'], // which html attributes should be sent?
			allow_nest: true, // allow nested elements to be included
            prepend: 'ul', // which query string param name should be used?
			att_regex: false, // replacement regex to run on attr values
            is_child: false // determine if we're serializing a child list
        };
        var opts = $.extend(defaults, options);
        var serialStr     = '';
        if(!opts.is_child){ opts.prepend = '&'+opts.prepend; }
        // Begin the core plugin
        this.each(function() {
            var li_count = 0;
            $(this).children().each(function(){
				if(opts.allow_nest || opts.attributes.length > 1){
					for(att in opts.attributes){
						val = rep(opts.attributes[att], $(this).attr(opts.attributes[att]));
						serialStr += opts.prepend+'['+li_count+']['+opts.attributes[att]+']='+val;
					}
				} else {
					val = att_rep(opts.attributes[0], $(this).attr(opts.attributes[0]));
					serialStr += opts.prepend+'['+li_count+']='+val;
				}
                // append any children elements
				if(opts.allow_nest){
					var child_base = opts.prepend+'['+li_count+'][children]';
					$(this).children().each(function(){
						if(this.tagName == 'UL' || this.tagName == 'OL'){
							serialStr += $(this).serializelist({'prepend': child_base, 'is_child': true});
						}
					});
				}
                li_count++;
            });
			function att_rep (att, val){
				if(opts.att_regex){
					for(x in opts.att_regex){
						if(opts.att_regex[x].att == att){
							return val.replace(opts.att_regex[x].regex, '');
						}
					}
				} else {
					return val;
				}
			}
        });
        return(serialStr);
    };
})(jQuery);