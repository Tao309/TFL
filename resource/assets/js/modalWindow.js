function ModalWindow() {
    this.fieldShadowId = 'shadowField';
    this.fieldModalWindowId = 'tModalWindow';

    this.typeImage = 'tImage';
    this.typeModalWindow = 'tModalWindow';

    this.shadowHide = 'hide';
    this.shadowShow = 'show';

    //К какому главному элементу добавлять другие элементы для показа
    this.bodyField = null;
    this.bodyFieldObject = null;
}

ModalWindow.prototype.initBody = function() {
    if(document.getElementById('body-view'))
    {
        this.bodyField = document.getElementById('body-view');
        this.bodyFieldObject = $('#body-view');
    }
    else if(document.getElementById('body'))
    {
        this.bodyField = document.getElementById('body');
        this.bodyFieldObject = $('#body');
    }
};

ModalWindow.prototype.hideField = function(type) {
    switch(type)
    {
        case this.typeImage:
            this.shadowField(this.shadowHide);

            let tImage = $('#tImage');

            if(tImage.length) {
                tImage.fadeOut(300,function() {
                    tImage.remove()
                });
            }
            break;
        case this.typeModalWindow:
            let tModalWindow = $('#'+this.fieldModalWindowId);

            if(tModalWindow.length)
            {
                //@todo Сделать хорошее скрытие
                $('#'+this.fieldModalWindowId).remove();
            }
            break;
    }
};

ModalWindow.prototype.shadowField = function(type) {
    let shadowField = document.getElementById(this.fieldShadowId);
    let preloadField = $("#preloadField");

    if(shadowField === null || typeof shadowField === 'undefined') {
        let shadowElement = document.createElement('div');
        shadowElement.id = this.fieldShadowId;

        let body = this.bodyField;

        body.appendChild(shadowElement);
    }

    shadowField = $('#'+this.fieldShadowId);

    switch(type)
    {
        case this.shadowHide:
            if(shadowField.hasClass('show')) {
                shadowField.removeClass('show');
            }

            if(preloadField.length) {
                preloadField.remove();
            }
            break;
        case this.shadowShow:
            if(!shadowField.hasClass('show')) {
                shadowField.addClass('show');
            }
            break;
    }
};

//Инициализация модального окна
ModalWindow.prototype.showModalWindow = function(data) {
    if(typeof data.useShadow !== 'undefined' && data.useShadow)
    {
        this.shadowField(this.shadowShow);
    }

    if(typeof data.headerTitle === 'undefined')
    {
        data.headerTitle = 'Header';
    }

    if(typeof data.content === 'undefined')
    {
        data.content = 'Content';
    }

    if(document.getElementById(this.fieldModalWindowId))
    {
        $("#"+this.fieldModalWindowId).remove();
    }

    let element = this.generateModalWindowHtml(data);

    this.bodyFieldObject.append(element);

    //this.centeringElement();
    this.initModalWindowEvents(data);
};
ModalWindow.prototype.generateModalWindowHtml = function(data) {
    let className = [];

    if (typeof $.fn.draggable !== 'undefined') {
        className.push('draggable');
    }

    let isPopup = (typeof data.popup !== 'undefined' && data.popup);

    if(isPopup)
    {
        className.push('popup');
    }

    //Добавляем свои классы через POST json_encode
    if(typeof data.className !== 'undefined')
    {
        className.push(data.className);
    }

    let element =
        '<div id="'+this.fieldModalWindowId+'" class="'+className.join(' ')+'">';

    if(!isPopup)
    {
        element += '<div class="header ">'+
            data.headerTitle+
            '<a class="close closeModalWindow"></a>'+
            '</div>';
    }
    else
    {
        let declineClasses = [
            'closeModalWindow',
            'html-element',
            'html-button',
            'html-button-decline',
            'size-large'
        ];

        let acceptClasses = [
            'html-element',
            'html-button',
            'html-button-accept',
            'size-large'
        ];

        data.content = '';

        if(typeof data.title !== 'undefined')
        {
            data.content += '<div class="title">'+data.title+'</div>';
        }

        data.content += '<div class="buttons">'+
            '<button data-type="accept" type="button" class="'+acceptClasses.join(' ')+'">Да</button>'+
            '<button data-type="decline" type="button" class="'+declineClasses.join(' ')+'">Нет</button>'+
            ''+
            '</div>';
    }

    element += '<div class="body">'+
                '<div class="content">'+
                    data.content+
                '</div>'+
            '</div>'+
        '</div>';

    return element;
};
ModalWindow.prototype.initModalWindowEvents = function(data) {
    $(function () {
        let el = $('#'+MODAL.fieldModalWindowId);

        MODAL.enableDraggable();

        window.onkeydown = function (event) {
            switch(event.keyCode) {
                case 27:
                    MODAL.hideField(MODAL.typeModalWindow);

                    MODAL.enableElement(data);
                    break;
            }
        };

        el.on('click', '.closeModalWindow', function() {
            MODAL.hideField(MODAL.typeModalWindow);
            MODAL.enableElement(data);
        });

        //Эвенты
        if(typeof data.event !== 'undefined')
        {
            if(typeof data.event.onAccept === 'function')
            {
                el.on('click', '.html-button[data-type="accept"]', function() {
                    data.event.onAccept();
                    MODAL.hideField(MODAL.typeModalWindow);
                    //MODAL.enableElement(data);
                });
            }

            if(typeof data.event.onDecline === 'function')
            {
                el.on('click', '.html-button[data-type="decline"]', function() {
                    data.event.onDecline();
                    MODAL.hideField(MODAL.typeModalWindow);
                    MODAL.enableElement(data);
                });
            }

            //Выбираем модель из списка для вставки в поле с ними
            if(typeof data.event.onChooseModelElement === 'function')
            {
                el.on('click', '.html-list-element', function (e) {
                    data.event.onChooseModelElement(this);
                });
            }

            if(typeof data.event.callback === 'function')
            {
                data.event.callback();
            }
        }

    });
};

//Активируем кнопку при закрытии popup окна
ModalWindow.prototype.enableElement = function (data) {
    if(typeof data.element !== 'undefined')
    {
        data.element.disabled = false;
    }
};

//Центрируем элемент по центру экрана
ModalWindow.prototype.centeringElement = function(el) {

};

//Активируем перенос элемента
ModalWindow.prototype.enableDraggable = function() {
    $('#tModalWindow>.header').mousedown(function(e) {
        $('#tModalWindow').draggable({
            scroll: false,
            containment: 'window',
        }).draggable('enable');
    }).mouseup(function() {
        //$("#modal_window").draggable('disable');
        $('#tModalWindow').draggable('destroy');
    });
};

const MODAL = new ModalWindow();
