;(function($) {
$.fn.tCarousel = function(options) {
		
	var defaults = {
		buttons:1,//показывать ли кнопки
		disabledButtonHide:0,//кнопки не disabled, а скрывать через добавление класса hide
		scrollItem: 1,//кол-во элементов при прокрутке
		handle:'line',//тип прокрутки
		/*
			line - влево и вправо до конца только
			infinite - бесконечная прокрутка влево и вправо
		*/
		onButtonLeft:function(options) {},//callback после нажатия на кнопку влево
		onButtonRight:function(options) {},//callback после нажатия на кнопку вправо
		readyCallback:function(options) {},//callback перед готовностью к инициализации модуля
		loadedCallback:function(options) {},//callback после заврешения обработки модуля карусели
		lazy:0,//отложенная загрузка картинок
		lazyForce:0,//принудательно заменять source  у картинок для пред-загрузки
		lazyDelay:180,//задержка при показе
		lazy_img_name:'lazy_image',//класс с картинкой, который будет отложен для показа
		justMobile:0,//указывается максимальная ширина экрана, при которой активируется модуль
		css:0,//использовать css translate
		
	};
	var options = $.extend(defaults, options);
	
return this.each(function(e) {
var mainObject = $(this),
	opt = {},//данные по длинам и т.п.
	method = {
		init:function(o) {
			options.readyCallback(options);
			var that = o[0];
			if(typeof o[0] == 'undefined') {return false;}
			
			var ul = that.children[0],
				tag = ul.tagName,
				oClass = that.className,
				li = that.getElementsByTagName('li');
			
			if(tag == 'UL' || tag == 'ul') {
				
				if(options.lazy>0) {
					if(typeof $.fn.lazyload != 'function') {options.lazy = 0;}
				}
				
				that.style.height = 'auto';
				that.style.position = 'relative';
				
				ul.className = ul.className+" tcarousel-list";
				ul.style.overflow = 'hidden';
				ul.style.position = 'relative';
				ul.style.margin = '0px';
				ul.style.padding = '0px';
				ul.style.listStyle = 'none';
				ul.style.display = 'block';
				ul.style.top = '0px';
				ul.style.left = '0px';
				
				var container = document.createElement("div");
				//container.style.position = 'relative';
				container.style.overflow = 'hidden';
				container.className = 'tcarousel-clip';
				container.appendChild(ul);
				o.append(container);
				
				opt.ul = ul;
				
				opt.containerWidth = container.offsetWidth;//clip
				
				that.className = oClass+" tcarousel-container";
				
				if(li!='undefined' && li.length>0) {
					opt.li = li;
					opt.itemLength = 0;
					var length = 0,
						computedStyle = [],
						itemLength = 0,
						img = [];
					
					for(var i=0;i<li.length;i++) {
						li[i].className = li[i].className+" tcarousel-item tcarousel-item-"+(i+1);
						li[i].style.cssFloat = 'left';
						li[i].style.listStyle = 'none';
						//li[i].style.overflow = 'hidden';
						
						if(options.lazy>0) {
							img[i] = $(li[i]).find('img.'+options.lazy_img_name);
							if(img[i].length) {
								img[i].addClass('lazy_notloaded');
								if(options.lazyForce>0) {
									var src = img[i].attr('src');
									img[i].attr({
									'data-original':src,
									'src':'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC'
									});
								}
							}
						}
						
						//if(itemLength==0) {
							li[i].style.margin = '0';
							
							computedStyle[i] = li[i].currentStyle || window.getComputedStyle(li[i], null);
							itemLength = li[i].offsetWidth + parseInt(computedStyle[i].marginLeft,10) + parseInt(computedStyle[i].marginRight,10);
							
							//console.log(li[i].offsetWidth+" , "+parseInt(computedStyle[i].marginLeft,10)+" , "+parseInt(computedStyle[i].marginRight,10)+" , "+parseInt(computedStyle[i].paddingLeft,10)+" , "+parseInt(computedStyle[i].paddingRight,10));
							
							itemLength = Math.ceil(itemLength);
							
							if(itemLength>opt.itemLength) {
								opt.itemLength = itemLength;
							}
						//}
						//length = (length + itemLength);
					}
					
					if(opt.itemLength<=0) {return false;}
					for(var i=0;i<li.length;i++) {
						li[i].style.width = opt.itemLength+"px";
					}
					
					var all_width = (opt.itemLength*li.length);
					
					ul.style.width = all_width+'px';
					opt.listWidth = all_width;//list
					
					//разница длин между всей длиной и то что показана = list-clip
					opt.diff = -(opt.listWidth-opt.containerWidth);
					
					if(options.buttons>0) {
						var buttonLeft = document.createElement("button");
						buttonLeft.className = 'tcarousel-button tcarousel-left';
						var buttonRight = document.createElement("button");
						buttonRight.className = 'tcarousel-button tcarousel-right';
					
						that.appendChild(buttonLeft);
						that.appendChild(buttonRight);
						
						$(buttonLeft).on('click',function() {
							method.move('left',that,ul);
						});
						$(buttonRight).on('click',function() {
							method.move('right',that,ul);
						});
						
						opt.buttonLeft = buttonLeft;
						opt.buttonRight = buttonRight;
						method.checkButtons();
						/*
						eventF.addEvent(buttonLeft, 'mouseover', function() {
							method.ButtonHover('left',buttonLeft);
						});
						eventF.addEvent(buttonRight, 'mouseover', function() {
							method.ButtonHover('right',buttonRight);
						});
						*/
					}
					options.loadedCallback(options);
					if(options.lazy>0) {
						var img = $(ul).find('li img.'+options.lazy_img_name+'.lazy_notloaded');
						method.loadLazy(img,'load');
					}
					
					window.onresize = function(e) {
						ul.style.left = "0px";
						method.checkButtons();
						opt.containerWidth = container.offsetWidth;
					};
					opt.moving = false;
								
					var lastPos = false,prevPos = false,movePos = 0,moveLeft = 0,res,canMove=false;
					
					if(typeof $.fn.swipe !== 'undefined') {
						$(that).find('.tcarousel-clip').swipe({
							min_x: 0,
							max_x: 80,
							min_y: 40,
							max_y: 80,
							swipeLeft:function(length) {
								method.move('right',that,ul);
							},
							swipeRight:function(length) {
								method.move('left',that,ul);
							},
							swipeStart:function(e) {
								if(options.handle == 'line') {
									
									if(opt.diff<0) {canMove=true;}
									//$(e.target).off('click').on('click', function(e){e.preventDefault();});
								}
							},
							swipeEnd:function(e,lengthX,lengthY) {
								if(options.handle == 'line') {
									//if(opt.moving) {
										opt.moving = false;
										//e.preventDefault();
										//$(ul).removeClass('moving');
										ul.setAttribute('type','none');
									//}
									
									lastPos = false,prevPos = false,movePos = 0,moveLeft = 0;
								}
							},
							swipeMove:function(event,lengthX,lengthY,sw) {
								if(options.handle == 'line') {
									/*
									sw.sX - старт
									sw.eX - движение
									*/
									if(!canMove) {return false;}
									
									prevPos = (!prevPos)?sw.sX:lastPos;
									lastPos = sw.eX;
									movePos = (lastPos-prevPos);
									
									if(movePos>0) {
										//console.log('вправо');
									} else if(movePos<0) {
										//console.log('влево');
									} else {
										//return false;
									}
									
									computedStyleUL = ul.currentStyle || window.getComputedStyle(ul, null);
									moveLeft = parseInt(computedStyleUL.left,10);
									moveLeft = moveLeft + movePos;
									
									if(!opt.moving) {
										//$(ul).addClass('moving');
										ul.setAttribute('type','moving');
										opt.moving = true;
									}
									
									if(moveLeft>0 && opt.moving || moveLeft<opt.diff && opt.moving) {} else {
										//ul.style['transform'] = "translate3d("+moveLeft+"px,0,0)";
										ul.style.left = moveLeft+"px";
									}
								}
							},
						});
					}
				} else {return false;}
			} else {return false;}
		},
		move:function(direction,that,ul) {
			
			computedStyleUL = ul.currentStyle || window.getComputedStyle(ul, null);
			//var marginLeft = parseInt(computedStyleUL.marginLeft,10),
				moveLeft = parseInt(computedStyleUL.left,10),//left
				li = opt.li;
			
			if(options.lazy>0) {
				var img = $(ul).find('li img.'+options.lazy_img_name+'.lazy_notloaded');
				method.loadLazy(img,'scroll');
			}
			if(direction == 'left') {
				if(options.handle == 'line') {
					if(opt.moving) {
						var n  = (Math.round(moveLeft/opt.itemLength));
						moveLeft = (n*opt.itemLength)-opt.itemLength;
					}
					
					if(moveLeft >= 0) {
						return false;
					} else if(-moveLeft<=(opt.itemLength*options.scrollItem)) {
						ul.style.left = "0px";
					} else {
						if(opt.moving) {
							ul.style.left = (moveLeft+opt.itemLength)+"px";
						} else {
							ul.style.left = (moveLeft+opt.itemLength*options.scrollItem)+"px";
						}
					}
				} else if(options.handle == 'infinite') {
					/*
					ul.style.marginLeft = marginLeft-(options.scrollItem*opt.itemLength)+"px";
					for(var i=li.length;i>(li.length-options.scrollItem);i--) {$(ul).prepend($(li[li.length-1]));}
					ul.style.left = (left+opt.itemLength*options.scrollItem)+"px";
					*/
				}
				options.onButtonLeft(options);
			} else if(direction == 'right') {
				res = opt.diff-moveLeft;
				
				if(options.handle == 'line') {
					
					if(opt.moving) {
						var n  = (Math.round(moveLeft/opt.itemLength));
						if(-res<opt.itemLength*options.scrollItem) {
							
						} else {
							moveLeft = (n*opt.itemLength)+opt.itemLength;
						}
					}
					
					if(res>0) {
						return false;
					} else if(-res<(opt.itemLength*options.scrollItem)) {
						//ul.style.left = (moveLeft+res)+"px";
						ul.style.left = opt.diff+"px";
					} else {
						if(opt.moving) {
							ul.style.left = (moveLeft-opt.itemLength)+"px";
						} else {
							ul.style.left = (moveLeft-(opt.itemLength*options.scrollItem))+"px";
						}
					}
				} else if(options.handle == 'infinite') {
					/*
					ul.style.marginLeft = marginLeft+(options.scrollItem*opt.itemLength)+"px";
					for(var i=0;i<(options.scrollItem);i++) {$(ul).append($(li[0]));}
					ul.style.left = (left-opt.itemLength*options.scrollItem)+"px";
					*/
				}
				options.onButtonRight(options);
			}
			
			method.checkButtons();
		},
		checkButtons:function() {
			var ul = opt.ul,
				li = opt.li,
				//marginLeft = parseInt(ul.style.marginLeft,10),
				moveLeft = parseInt(ul.style.left,10),
				//res = (left+opt.listWidth-opt.containerWidth);
				res = opt.diff-moveLeft;
			
			if(options.handle == 'line') {
				if(opt.diff>0) {
					if(options.disabledButtonHide>0) {
						$(opt.buttonLeft).addClass('hide');
						$(opt.buttonRight).addClass('hide');
					} else {
						opt.buttonLeft.disabled = true;
						opt.buttonRight.disabled = true;
					}
				} else {
					if(moveLeft >= 0) {
						if(options.disabledButtonHide>0) {
							$(opt.buttonLeft).addClass('hide');
						} else {
							opt.buttonLeft.disabled = true;
						}
					} else {
						if(options.disabledButtonHide>0) {
							$(opt.buttonLeft).removeClass('hide');
						} else {
							opt.buttonLeft.disabled = false;
						}
					}
					if(res>=0) {
						if(options.disabledButtonHide>0) {
							$(opt.buttonRight).addClass('hide');
						} else {
							opt.buttonRight.disabled = true;
						}
					} else {
						if(options.disabledButtonHide>0) {
							$(opt.buttonRight).removeClass('hide');
						} else {
							opt.buttonRight.disabled = false;
						}
					}
				}
			} else if(options.handle == 'infinite') {
				/*
				if(li.length>1 && li.length>options.scrollItem && opt.listWidth>opt.containerWidth) {
					opt.infinite = 1;
				} else {
					opt.infinite = 0;
					if(options.disabledButtonHide>0) {
						$(opt.buttonLeft).addClass('hide');
						$(opt.buttonRight).addClass('hide');
					} else {
						opt.buttonLeft.disabled = true;
						opt.buttonRight.disabled = true;
					}
				}
				*/
			}
		},
		loadLazy:function(img,type) {
			//if(type == 'load') {
				img.lazyload({delay:options.lazyDelay, effect: "fadeIn",event: 'scroll',
					load: function(self, elements_left, settings) {
						$(this).removeClass("lazy_notloaded").addClass('lazy_loaded');
					}
				});
			//} else if(type == 'scroll') {
			//	$(li).lazyload({delay:options.lazyDelay, effect: "fadeIn",event: 'scroll',
			//		load: function(self, elements_left, settings) {
			//			$(this).removeClass("lazy_notloaded").addClass('lazy_loaded');
			//		}
			//	});
			//}
		},
		ButtonHover:function(type,button) {
			
			var ul = $(options.ul);
			if(type === 'left') {
				ul.addClass('hoverLeft');
			} else if(type === 'right') {
				ul.addClass('hoverRight');
			}
			eventF.addEvent(button, 'mouseout', function() {
				method.ButtonOut(type,button);
			});
		},
		ButtonOut:function(type,button) {
			var ul = $(options.ul);
			if(type === 'left') {
				ul.removeClass('hoverLeft');
			} else if(type === 'right') {
				ul.removeClass('hoverRight');
			}
		},
	};
	
	//Принудительное отключение карусели
	if(typeof mainObject.attr('data-nocarousel') != 'undefined' && mainObject.attr('data-nocarousel') > 0) {
		return false;
	}
	
	if(!mainObject.hasClass('tcarousel-container')) {
		if(options.justMobile>0) {
			var widthClient = window.innerWidth;
			if(options.justMobile>=widthClient) {method.init(mainObject);}
		} else {
			method.init(mainObject);
		}
	}
});
};
})($);