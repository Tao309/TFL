;(function ($) {
    $.fn.tGallery = function (tOptions) {
        let defaults = {
                title: 0,//показывать ли название файла внизу
                margin: 0.9,//отсуп по краям экрана
                storage: false,//где все изображения
            },
            options = $.extend(defaults, tOptions);

        let tGalleryMethods = {
            prev_next: function (obj) {
                $('#tImage a.nav_left').remove();
                $('#tImage a.nav_right').remove();

                let my_class = tGalleryMethods.take_class(obj);
                switch (my_class) {
                    case 'tGallery': {
                        let prev_div = $(obj).parent().prev('li').find('.' + my_class);
                        let next_div = $(obj).parent().next('li').find('.' + my_class);

                        if (prev_div.length) {
                            $("#tImage").prepend("<a class='nav nav_left fonticonready' title='влево'></a>");
                        }
                        if (next_div.length) {
                            $("#tImage").append("<a class='nav nav_right fonticonready' title='вправо'></a>");
                        }
                        break;
                    }
                }
                ;
            },
            take_class: function (o) {
                let my_class = "";
                my_class = ($(o).hasClass("taogal")) ? "taogal" : my_class;
                my_class = ($(o).hasClass("taogalin")) ? "taogalin" : my_class;
                my_class = ($(o).hasClass("tao_jcar")) ? "tao_jcar" : my_class;
                return my_class;
            },
            fullsize: function () {
                let mainimage = $("#tImage");

                mainimage.addClass('fullsize');
                mainimage.find("a.img").addClass('notransition').css({
                    'width': '100%',
                    'height': '100%',
                });
                mainimage.find("#mainimage").css({
                    'max-width': '100%',
                    'max-height': '100%',
                });
                mainimage.find("div.full").hide();

                if (typeof $.fn.draggable !== 'undefined') {
                    mainimage.draggable({
                        scroll: false, stop: function (event, ui) {
                            return true;
                        }
                    });
                }
            },
            prev: function (obj) {
                let my_class = tGalleryMethods.take_class(obj);
                let prev_div = $(obj).parent().prev('li').find('.' + my_class);
                if (!prev_div.length) {
                    return false;
                }

                $("#tImage div.full").hide();
                tGalleryMethods.click(prev_div);
            },
            next: function (obj) {
                let my_class = tGalleryMethods.take_class(obj);
                let next_div = $(obj).parent().next('li').find('.' + my_class);
                if (!next_div.length) {
                    return false;
                }

                $("#tImage div.full").hide();
                tGalleryMethods.click(next_div);
            },
            find_parent: function (obj) {
                let parent = obj.parentNode;

                if (parent.nodeName === "DIV") {
                    return parent;
                } else {
                    tGalleryMethods.find_parent(parent);
                    return false;
                }
            },
            click: function (obj) {
                let my_obj = obj;

                if (typeof my_obj !== 'object') {
                    my_obj = this;
                }

                let storage = options.storage;

                if (storage !== false && storage.length) {
                    let fName = my_obj.getAttribute("data-id"),
                        name = (typeof fName === 'undefined' || fName === '' || fName === null) ? "0" : fName,
                        ah = storage.find("li[name=" + name + "]>a");

                    if (ah.length && ah !== 'undefined') {
                        ah.click();
                        return false;
                    }
                }

                let url = $(my_obj).attr("href"),
                    my_class = tGalleryMethods.take_class(my_obj),
                    truewidth = '',
                    trueheight = '',
                    clientwidth = $(window).width(),
                    clientheight = $(window).height(),
                    title = '';

                //@todo исправить, чтобы внизу работало
                MODAL.shadowField(MODAL.shadowShow);
                //preload('create');

                $("#preload").on('click', function () {
                    disablegal("preload");
                });

                let placeExist = 0,
                    imgplace = $('#tImage'),
                    divImg = imgplace.find('a.img'),
                    mainimage = imgplace.find('#mainimage');

                if (imgplace.length) {
                    placeExist = 1;
                    //$("#tImage").hide();
                    divImg.removeClass('show').removeClass('notransition');
                    //mainimage.removeClass('show').removeClass('notransition');
                    mainimage.attr('src', url);
                    imgplace.css({
                        'left': '50%',
                        'top': '50%',
                    });
                } else {
                    //$("body").append("<div id='tImage'><div class='full'><a title='В полном размере'><img src='"+root+"images/taogal/full.png?v=1'/></a></div><a class='close' title='Закрыть'></a><a class='img'><img id='mainimage' src='"+url+"' /></a>"+title+"</div>");

                    let imagePlaceHtml = "<div class='full'>"+
                        "<a title='В полном размере'></a>"+
                        "</div>"+
                        "<a class='close' title='Закрыть'></a>"+
                        "<a class='img'><img id='mainimage' src='" + url + "' /></a>" +
                        title;

                    MODAL.bodyFieldObject.append("<div id='tImage'>" + imagePlaceHtml + "</div>");
                }

                let title_a = '';
                if (options.title > 0) {
                    let url_title = url.replace(/(.*)\/(.*)\.(.*)/, '$2');
                    title_a = $(my_obj).attr("title");

                    //title = (title_a!="" && typeof title_a !="undefined")?"<div class='title'>"+title_a+"</div>":"<div class='title'>"+url_title+"</div>";
                    title = (title_a !== "" && typeof title_a !== "undefined") ? "<div class='title'>" + title_a + "</div>" : "";

                    let titlePlace = imgplace.find('div.title');
                    if (title_a !== '') {
                        if (titlePlace.length) {
                            titlePlace.html(title_a);
                        } else {
                            $("#tImage").append(title);
                        }
                    } else {
                        if (titlePlace.length) {
                            titlePlace.remove();
                        }
                    }
                }

                //$("#tImage #mainimage").attr("src", url).load(function(){
                $('#tImage #mainimage').on('load', function () {
                    let imagePlace = $('#tImage');

                    imagePlace.find('a.close').on('click', function () {
                        MODAL.hideField(MODAL.typeImage);
                    });

                    let image = new Image();
                    image.src = url;
                    let truewidth = image.width;
                    let trueheight = image.height;

                    let type = 0,
                        resize = 0;

                    //Создаём отступы к окну
                    clientwidth = Math.round(clientwidth * options.margin);
                    clientheight = Math.round(clientheight * options.margin);

                    //console.log("Image: "+truewidth+"x"+trueheight+" Window: "+clientwidth+"x"+clientheight);
                    let width,
                        marginleftDefault,
                        height,
                        margintopDefalt;

                    //длина и ширина больше
                    if (truewidth >= clientwidth && trueheight >= clientheight) {
                        type = 1;
                        resize = 1;

                        let marginleft, margintop;

                        let delit_weight = (truewidth / clientwidth);
                        let delit_height = (trueheight / clientheight);

                        width = clientwidth;
                        height = clientheight;

                        //отношение длин больше отношения высот
                        if (delit_weight > delit_height) {
                            type = '1.1';
                            marginleft = (width / 2);
                            height = (trueheight / delit_weight);
                            margintop = (height / 2);
                            //отношение длин меньше отношения высот
                        } else if (delit_weight < delit_height) {
                            type = '1.2';

                            width = (truewidth / delit_height);
                            marginleft = (width / 2);
                            margintop = (height / 2);
                            //отношение длин равно отношению высот
                        } else {
                            type = '1.3';
                            let delit = 1;
                            marginleft = (width / 2);
                            margintop = (height / 2);
                        }

                        //console.log("delit_weight: "+delit_weight+" delit_height: "+delit_height);

                        //длина больше, ширина меньше
                    } else if (truewidth >= clientwidth && trueheight < clientheight) {
                        let delit = (truewidth / clientwidth);
                        width = clientwidth;
                        marginleft = (width / 2);
                        height = (trueheight / delit);
                        margintop = (height / 2);
                        resize = 1;
                        type = 2;
                        //длина меньше, ширина больше
                    } else if (truewidth < clientwidth && trueheight >= clientheight) {
                        let delit = (trueheight / clientheight);
                        width = (truewidth / delit);
                        marginleftDefault = (width / 2);
                        height = clientheight;
                        margintopDefalt = (height / 2);
                        resize = 1;
                        type = 3;
                        //длина меньше, ширина меньше
                    } else if (truewidth < clientwidth && trueheight < clientheight) {
                        width = truewidth;
                        marginleftDefault = (width / 2);
                        height = trueheight;
                        margintopDefalt = (height / 2);
                        resize = 0;
                        type = 4;
                    }

                    //console.log("Картинка открытая: "+width+"x"+height);
                    //console.log("Тип изменения: "+type+" Менять: "+resize+" Делитель: "+delit);
                    //console.log("Margin-left: "+marginleftDefault+" Margin-top: "+margintopDefalt);

                    let divNull = imagePlace.find('div.full');

                    if (resize > 0) {
                        divNull.show();
                        divNull.on('click', function () {
                            tGalleryMethods.fullsize();
                        });
                    } else {
                        divNull.hide();
                    }

                    //console.log(width+" x "+height);
                    //console.log("более чем margintopDefalt = "+margintopDefalt+"; clienheight = "+clientheight+"; height="+height);

                    let divImg = imagePlace.find('a.img'),
                        mainimage = imagePlace.find('#mainimage'),
                        maxWidth,
                        maxHeight,
                        margintop,
                        marginleft;

                    if (placeExist > 0) {
                        margintop = -margintopDefalt;
                        marginleft = -marginleftDefault;

                        let PmarginTop = imagePlace.css('margin-top').replace("px", ""),
                            PmarginLeft = imagePlace.css('margin-left').replace("px", ""),
                            difmarginTop = (margintop - PmarginTop),
                            difmarginLeft = (marginleft - PmarginLeft),
                            difmarginTopNew = (difmarginTop >= 0) ? difmarginTop : -difmarginTop,
                            difmarginLeftNew = (difmarginLeft >= 0) ? difmarginLeft : -difmarginLeft;

                        margintop = (margintop > PmarginTop) ? "+=" + difmarginTopNew : "-=" + difmarginTopNew;
                        marginleft = (marginleft > PmarginLeft) ? "+=" + difmarginLeftNew : "-=" + difmarginLeftNew;

                        maxWidth = width;
                        maxHeight = height;
                    } else {
                        margintop = "-" + margintopDefalt;
                        marginleft = "-" + marginleftDefault;

                        maxWidth = width;
                        maxHeight = height;
                    }

                    imagePlace.css({
                        "margin-top": margintop + "px",
                        "margin-left": marginleft + "px",
                    });

                    if (!obj) {
                        //imagePlace.find("#mainimage").animate({'max-width':width+"px",'max-height':height+"px",});
                        //$("#tImage #mainimage").animate({'max-width':maxWidth+"px",'max-height':maxHeight+"px",});
                        mainimage.animate({'max-width': width + "px", 'max-height': height + "px",});
                        divImg.animate({'width': maxWidth + "px", 'height': maxHeight + "px",});
                    } else {
                        //$("#tImage #mainimage").css("max-width",width+"px").css("max-height",height+"px");
                        mainimage.css("max-width", maxWidth + "px").css("max-height", maxHeight + "px");
                        divImg.css("width", maxWidth + "px").css("height", maxHeight + "px");
                    }

                    let preloadField = $("#preload");

                    if(preloadField.length)
                    {
                        preloadField.fadeOut(122, function () {
                            preloadField.remove();
                            //$("#tImage").css("position","fixed").css("top","50%").css("left","50%").css("margin-top","-"+margintop+"px").css("margin-left","-"+marginleft+"px").fadeIn(380);
                            //imagePlace = document.getElementById('imageplace');
                            if (placeExist <= 0) {
                                imagePlace.fadeIn(160);
                            }

                            //imagePlace.find("#mainimage").addClass('show');
                            divImg.addClass('show');
                        });

                    }
                    else
                    {
                        if (placeExist <= 0) {
                            imagePlace.fadeIn(160);
                        }
                        divImg.addClass('show');
                    }

                    //нажатие клавиш - НАЧАЛО
                    window.onkeydown = function (event) {
                        let modalWindow = $('#'+MODAL.typeModalWindow),
                            imagePlace = $('#'+MODAL.typeImage);

                        switch (event.keyCode) {
                            case 27://картинка и модальное окно

                                if (modalWindow.length && imagePlace.length) {// + модальное окно + галерея
                                    MODAL.hideField(MODAL.typeImage);
                                    MODAL.hideField(MODAL.typeModalWindow);
                                } else if (!modalWindow.length && imagePlace.length) {// - модальное окно + галерея
                                    MODAL.hideField(MODAL.typeImage);
                                } else {
                                    MODAL.hideField(MODAL.typeImage);
                                    $('[name=m-window]:visible').fadeOut(340);
                                }
                                break;
                            case 37://влево
                                tGalleryMethods.prev(my_obj);
                                break;
                            case 39://вправо
                                tGalleryMethods.next(my_obj);
                                break;
                            case 38:
                            case 40://вправо
                                disablegal("imageplace");
                                break;
                        }
                    };
                    //нажатие клавиш - КОНЕЦ
                    //запрос на навигацию
                    switch (my_class) {
                        case 'tao_jcar': {
                            tGalleryMethods.prev_next(my_obj);
                            break;
                        }
                        case 'tImage': {
                            tGalleryMethods.prev_next(my_obj);
                            break;
                        }
                    }

                    $(function () {
                        let imagePlace = $("#tImage");

                        let width = imagePlace.width();
                        let height = imagePlace.height();
                        let w_width = $(window).width();
                        let w_height = $(window).height();
                        let x1 = (width / 2) + 2;
                        let y1 = (height / 2) + 2;
                        let x2 = (w_width - (width / 2) - 8);
                        let y2 = (w_height - height / 2 - 6);
                        if (typeof $.fn.draggable !== 'undefined') {
                            if (resize === 1) {
                                //$("#tImage").draggable({scroll: false,});
                                $("#tImage").draggable({
                                    scroll: false,
                                    containment: 'window',
                                    stop: function (event, ui) {
                                        // ui.helper.window_limit();
                                    }
                                });
                            } else {
                                $("#tImage").draggable({
                                    scroll: false,
                                    containment: 'window',
                                    stop: function (event, ui) {
                                        // ui.helper.window_limit();
                                    }
                                });
                            }
                        }
                        imagePlace.find('a.nav_left').click(function () {
                            tGalleryMethods.prev(my_obj);
                        });
                        imagePlace.find('nav_right').click(function () {
                            tGalleryMethods.next(my_obj);
                        });
                    });
                    //$(my_obj).animate({"opacity":"0"},200);

                });
                return false;
            },
        };

        $(this).click(function (e) {
            e.preventDefault();
            return tGalleryMethods.click(this);
        });
    };
})($);