function Autoload() {

}

//@todo Добавить data-event или типа такое, что уже есть эвент на эелменте

//Загрузка файла через один input
Autoload.prototype.requestUpload = function () {
    let requestUpload = document.querySelectorAll('.http-request-upload:not([data-eventF])');

    if (requestUpload) {
        for (let n = 0; n < requestUpload.length; n++) {
            eventF.addEvent('change', requestUpload[n], function (e) {
                if (typeof this.files[0] === 'undefined') {
                    return false;
                }
                let element = e.target;
                let div = $(element).closest('.image-add-file');
                div.addClass('loading');

                eventF.preventDefault(e);

                //@todo Перенести в одно место
                let indexType = e.target.getAttribute('data-section'),
                    indexPage = e.target.getAttribute('data-route'),
                    indexPageSection = e.target.getAttribute('data-routeType'),
                    url = '/' + indexType + '/' + indexPage + '/' + indexPageSection,
                    method = e.target.getAttribute('data-method');

                if (!indexType || !indexPage || !indexPageSection) {
                    return false;
                }

                let dataParams = null;
                if (element.getAttribute('data-params')) {
                    dataParams = JSON.parse(element.getAttribute('data-params'));
                }

                ActionElement.showInfo(ActionElement.TYPE_LOADING);

                let response = function (r) {

                    element.value = '';
                    div.removeClass('loading');

                    if (r.result > 0) {
                        AUTOLOAD.handleResponse(r, e.target);
                    } else {
                        ActionElement.showInfo(ActionElement.TYPE_ERROR, r.message);
                    }
                };

                let formData = new FormData();
                formData.append('filename', this.files[0]);

                AJAX.send({
                    method: 'POST',
                    url: url,
                    formData: formData,
                    appendData: dataParams
                })
                    .then(response);
            });

            requestUpload[n].setAttribute('data-eventF', 1);
        }
    }
};

//Обработка форм
Autoload.prototype.submitForm = function () {
    let requestForms = document.querySelectorAll('.http-request-form:not([data-eventF]');

    //	@todo Переместить в общий сборщик эвентов для формы
    //	@todo Сделать общий обработчик, менять только switch case
    if (requestForms) {
        for (let n = 0; n < requestForms.length; n++) {
            eventF.addEvent('submit', requestForms[n], function (e) {
                eventF.preventDefault(e);

                let indexType = e.target.getAttribute('data-section'),
                    indexPage = e.target.getAttribute('data-route'),
                    indexPageSection = e.target.getAttribute('data-routeType'),
                    url = '/' + indexType + '/' + indexPage,
                    method = e.target.getAttribute('data-method');

                if(indexPageSection) {
                    url += '/' + indexPageSection;
                }

                method = method ? method.toUpperCase() : 'POST';

                if (!indexType || !indexPage) {
                    return false;
                }

                let response = null,
                    result = null;

                switch (url) {
                    case '/section/auth/register':
                        result = HTML.checkEmptyFormFields(e.target, [
                            'User[login]',
                            'User[email]',
                            'User[password]'
                        ]);

                        if (!result) {
                            return false;
                        }

                        HTML.disableSubmitButton(e.target);

                        response = function (r) {
                            if (r.code === 200 || r.code === 210) {
                                ActionElement.showInfo(ActionElement.TYPE_NEW, r.message);
                                console.log('Gratz, you are register! Please, login');
                                //location.reload();

                                HTML.clearForm(e.target);
                            } else {
                                HTML.enableSubmitButton(e.target);
                                ActionElement.showInfo(ActionElement.TYPE_ERROR, r.message);
                            }

                            AUTOLOAD.handleResponse(r, e.target);
                        };
                        break;
                    case '/section/auth/login':
                        result = HTML.checkEmptyFormFields(e.target, [
                            'User[login]',
                            'User[password]'
                        ]);

                        if (!result) {
                            return false;
                        }

                        HTML.disableSubmitButton(e.target);

                        response = function (r) {
                            if (r.code === 200) {
                                console.log('Gratz, you are log in!');
                                location.reload();
                            } else {
                                HTML.enableSubmitButton(e.target);
                                ActionElement.showInfo(ActionElement.TYPE_ERROR, r.message);
                            }

                            AUTOLOAD.handleResponse(r, e.target);
                        };
                        break;
                    default:
                        if (!HTML.checkEmptyFormFields(e.target)) {
                            return false;
                        }

                        HTML.disableSubmitButton(e.target);

                        ActionElement.showInfo(ActionElement.TYPE_LOADING);

                        response = function (r) {
                            HTML.enableSubmitButton(e.target);

                            if (r.result > 0) {
                                if (typeof r.action !== 'undefined') {
                                    if (r.action === ActionElement.TYPE_NEW) {
                                        HTML.clearForm(e.target);
                                    }
                                }
                            }

                            AUTOLOAD.handleResponse(r, e.target);
                        };
                }

                if (!response) {
                    return false;
                }

                AJAX.send({
                    method: method,
                    url: url,
                    sendForm: e.target
                })
                    .then(response);
            });

            requestForms[n].setAttribute('data-eventF', 1);
        }
    }
};

//Обработка кнопок
Autoload.prototype.clickButton = function () {
    let requestButtons = document.querySelectorAll('.http-request-button:not([data-eventF])');

    if (requestButtons) {
        for (let n = 0; n < requestButtons.length; n++) {
            eventF.addEvent('click', requestButtons[n], function (e) {
                eventF.preventDefault(e);

                let indexType = e.target.getAttribute('data-section'),
                    indexPage = e.target.getAttribute('data-route'),
                    indexPageSection = e.target.getAttribute('data-routeType'),
                    requestMethod = e.target.getAttribute('data-method'),
                    url = '/' + indexType + '/' + indexPage + '/' + indexPageSection;

                if (!indexType || !indexPage || !indexPageSection) {
                    return false;
                }

                let response = null;
                let dataParams = null;
                if (e.target.getAttribute('data-params')) {
                    dataParams = JSON.parse(e.target.getAttribute('data-params'));
                } else {
                    dataParams = {};

                    if (e.target.getAttribute('data-id')) {
                        dataParams.id = e.target.getAttribute('data-id');
                    }
                    if (e.target.getAttribute('data-type')) {
                        dataParams.type = e.target.getAttribute('data-type');
                    }
                }

                switch (url) {
                    case '/admin/section/modal/choose':
                        //Выбор модели из списка в модальном окне
                        //@todo оптимизировать

                        console.log('Here!');
                        break;
                    case '/section/auth/exit':
                        response = function (r) {
                            if (r.result > 0) {
                                console.log('Bye, Bye!');
                                location.reload()
                            }
                        };
                        break;
                    default:
                        e.target.disabled = true;

                        //Подтверждение удаления
                        if (indexPageSection === ActionElement.TYPE_DELETE) {
                            let data = {
                                title: 'Подтвердить удаление',
                                element: e.target,
                                popup: 1,
                                event: {
                                    onAccept: function () {
                                        e.target.disabled = false;
                                        e.target.setAttribute('data-checked', 1);
                                        e.target.click();
                                    }
                                }
                            };

                            if (!ActionElement.canDelete(data)) {
                                MODAL.showModalWindow(data);
                                return;
                            }
                        }

                        ActionElement.showInfo(ActionElement.TYPE_LOADING);

                        response = function (r) {
                            e.target.disabled = false;

                            if (r.result > 0) {
                                AUTOLOAD.handleResponse(r, e.target);
                            } else {
                                ActionElement.showInfo(ActionElement.TYPE_ERROR, r.message);
                            }
                        };
                }

                if (!response) {
                    return false;
                }

                AJAX.send({
                    method: requestMethod,
                    url: url,
                    data: dataParams
                })
                    .then(response);
            });

            requestButtons[n].setAttribute('data-eventF', 1);
        }
    }
};

//Обработка ответов
Autoload.prototype.handleResponse = function (r, el) {

    let isCatsItem = false;

    if (typeof r.model !== 'undefined') {
        let id = '#' + r.model.element_id;

        switch (r.model.name) {
            case 'image':
                if (r.action === ActionElement.TYPE_NEW) {
                    if (r.model.type === 'cover') {
                        $(el).closest('.image-add-file').addClass('loaded')
                            .find('.image-cover-field').html(r.model.element);
                    } else if (r.model.type === 'screen') {
                        $(el).closest('.image-model-field')
                            .find('.image-screen-field').prepend(r.model.element);
                    }
                }
                if (r.action === ActionElement.TYPE_DELETE) {
                    if (r.model.type === 'cover') {
                        $(el).closest('.image-add-file').removeClass('loaded')
                            .find('.image-cover-field').html('');
                    } else if (r.model.type === 'screen') {
                        $(el).closest('.one-image').remove();
                    }
                }
                break;
            case 'catsitem':
                isCatsItem = true;

                if (typeof cats !== 'undefined') {
                    if (r.action === ActionElement.TYPE_UPDATE) {
                        cats.updateElement(clearId, r.model);
                    } else if (r.action === ActionElement.TYPE_NEW) {
                        cats.addElement(r.htmlElement, r.model);
                    } else if (r.action === ActionElement.TYPE_DELETE) {
                        cats.deleteElement(r.modelId);
                    }
                }

                break;
        }
    }

    if (typeof r.action !== 'undefined') {
        if (r.action === ActionElement.TYPE_DELETE) {
            if (typeof r.model !== 'undefined') {
                //Если удаление, то удалить с DOM элемент
                $('#' + r.model.element_id).remove();
            }

            //Обновление страницы, если удаляем на странице
            if ($(el).hasClass('html-button-delete')) {
                location.reload();
            }
        }
        let message = (typeof r.message !== 'undefined') ? r.message : null;
        ActionElement.showInfo(r.action, message, r, el);
    }

    if (typeof r.event !== 'undefined') {
        //@todo Провера наличия эвента
        let funcName = r.event;

        if (Autoload.prototype.hasOwnProperty(funcName)) {
            switch (funcName) {
                case 'requestUpload':
                    AUTOLOAD.requestUpload();
                    break;
                case 'submitForm':
                    AUTOLOAD.submitForm();
                    break;
                case 'clickButton':
                    AUTOLOAD.clickButton();
                    break;
            }
        }

        if (r.action === ActionElement.TYPE_NEW && r.model.name === 'image') {
            AUTOLOAD.tImage();
        }
    }

    let modelElelemnt = el;
    if (isCatsItem) {
        modelElelemnt = '#tModalWindow.edit-catsitem';
    }

    //Какие поля надо заполнить
    $(modelElelemnt).find('.fill-required').removeClass('fill-required');
    if (typeof r.requiredFields !== 'undefined') {
        let arr = r.requiredFields;
        arr.forEach(function (item, i) {
            $(modelElelemnt).find('[name="' + item + '"]').addClass('fill-required');
        });
    }
};

//Элементы с датой
Autoload.prototype.datetimeELements = function () {
    $('.html-element-datetime').datepicker({
        dateFormat: "dd.mm.yy"
    });
};

//Вставка бб-тэгов
Autoload.prototype.bbTagsEvent = function () {
    let buttons = document.querySelectorAll('.insert-tag');
    if (buttons) {
        for (let n = 0; n < buttons.length; n++) {
            eventF.addEvent('click', buttons[n], function (e) {
                let el = e.target;
                let tag = el.getAttribute('data-tag');
                let field = el.getAttribute('data-field');
                let value = el.getAttribute('data-value');

                let findField = document.getElementById(field);

                if (findField) {
                    let text = "[" + tag + "][/" + tag + "]";
                    let end = "[/" + tag + "]";

                    switch (tag) {
                        case 'img':
                        case 'thumb':
                            text = "[" + tag + "]" + value + "[/" + tag + "]";
                            end = '';
                            break;
                        case 'space':
                            text = String.fromCharCode(160);
                            end = '';
                            break;
                        default:
                            text = "[" + tag + "]" + findField.value.substring(findField.selectionStart, findField.selectionEnd) + "[/" + tag + "]";
                    }


                    let home = findField.value.substring(0, findField.selectionStart);
                    let selectedValue = findField.value.substring(findField.selectionStart, findField.selectionEnd);

                    findField.focus();
                    findField.value = findField.value.substring(0, findField.selectionStart) + text + findField.value.substring(findField.selectionEnd);

                    if (selectedValue.length > 0) {//если есть выделенный текст
                        cursor = home.length + text.length;
                        findField.selectionStart = cursor;
                        findField.selectionEnd = cursor;
                    } else {
                        cursor = home.length + text.length - end.length;
                        findField.selectionStart = cursor;
                        findField.selectionEnd = cursor;
                    }
                }
            });
        }
    }

};

//Открытие картинок через tGallery
Autoload.prototype.tImage = function () {
    $('.tImage').tGallery();
    /*
    let images = document.querySelectorAll('.tImage');
    if(images)
    {
        for(let n = 0;n < images.length; n++) {
            eventF.addEvent('click', images[n], function (e) {
                eventF.preventDefault(e);
                let el = e.target;

                console.log(el);
            });
        }
    }
    */
};

Autoload.prototype.clickWindow = function (e) {
    MODAL.initBody();

    eventF.addEvent('click', document, function (e) {
        //Текущий элемент
        let el = eventF.getTarget(e);

        switch (el.getAttribute('id')) {
            case MODAL.fieldShadowId:
                MODAL.hideField(MODAL.typeImage);
                MODAL.hideField(MODAL.typeModalWindow);
                break;
        }
    });
};

/**
 * Удалить элемент с классом findClass, нажав на кнопку внутри
 * @param findClass
 */
Autoload.prototype.removeClosestElement = function (findClass) {
    let buttons = document.querySelectorAll('.html-remove-closest:not([data-eventF])');
    if (typeof findClass === 'undefined') {
        findClass = 'element';
    }

    if (buttons) {
        for (let n = 0; n < buttons.length; n++) {
            eventF.addEvent('click', buttons[n], function (e) {
                eventF.preventDefault(e);

                $(e.target).closest('.'+findClass).remove();
            });

            buttons[n].setAttribute('data-eventF', 1);
        }
    }
};

const AUTOLOAD = new Autoload();