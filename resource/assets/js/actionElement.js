function ActionElementView() {
    this.TYPE_SAVE = 'save';
    this.TYPE_UPDATE = 'update';
    this.TYPE_NEW = 'insert';
    this.TYPE_DELETE = 'delete';
    this.TYPE_LOADING = 'loading';
    this.TYPE_ERROR = 'error';
    this.TYPE_SHOW_MODAL_WINDOW = 'modal_window';
    this.TYPE_HTML_ELEMENT = 'htmlElement';

    this.LINK_HAS_ONE_TO_ONE = 'oneToOne';
    this.LINK_HAS_ONE_TO_MANY = 'oneToMany';
    this.LINK_HAS_MANY_TO_MANY = 'manyToMany';

    //ID элемента
    this.ELEMENT_ID = 'modal-window-action';

    //Время показа элемента
    this.PERIOD_SHOW = 2400;
}

/**
 * @param type
 * @param message
 * @param data
 * @param parentEl С какого элемента начался запрос на пока модального окна
 * @returns {boolean}
 */
ActionElementView.prototype.showInfo = function (type, message, data, parentEl) {
    if (typeof message === 'undefined' || !message || message === 'Ok') {
        switch (type) {
            case this.TYPE_SAVE:
                message = 'Saved';
                type = this.TYPE_UPDATE;
                break;
            case this.TYPE_UPDATE:
                message = 'Updated';
                break;
            case this.TYPE_NEW:
                message = 'Added';
                break;
            case this.TYPE_DELETE:
                message = 'Deleted';
                break;
            case this.TYPE_LOADING:
                message = 'Loading';
                break;
            case this.TYPE_ERROR:
                message = 'Error';
                break;
            case this.TYPE_SHOW_MODAL_WINDOW:

                break;
            default:
                return false;
        }
    }

    let elementId = this.ELEMENT_ID;

    if (document.getElementById(elementId)) {
        $("#" + elementId).remove();
    }

    if (type === this.TYPE_SHOW_MODAL_WINDOW) {
        data.event = {
            callback: function () {
                AUTOLOAD.submitForm();
            },
            onChooseModelElement: function (chooseElement) {
                if (typeof parentEl !== 'undefined') {
                    let params = JSON.parse(chooseElement.getAttribute('data-params'));
                    let list = $(parentEl).closest('.html-model-list').find('>.list');
                    let modelClassId = 'model-'+params.id;

                    if(
                        params.elementName === 'undefined' || params.elementName == null
                        || params.id === 'undefined' || params.id == null
                    ) {
                        return;
                    }

                    if(list.find('.'+modelClassId).length)
                    {
                        ActionElement.showInfo(ActionElement.TYPE_ERROR, 'Already added');
                        return;
                    }

                    let newElement = chooseElement.cloneNode(true);
                    newElement.className = 'element '+modelClassId;
                    newElement.removeAttribute('data-params');
                    newElement.removeAttribute('data-section');
                    newElement.removeAttribute('data-route');
                    newElement.removeAttribute('data-routetype');
                    newElement.removeAttribute('data-method');

                    let button = document.createElement("button");
                    button.setAttribute('class', 'html-icon-button icon-remove font-icon-tfl html-remove-closest');
                    button.setAttribute('type', 'button');

                    let input = document.createElement('input');
                    input.setAttribute('name', params.elementName);
                    input.setAttribute('value', params.id);
                    input.setAttribute('type', 'hidden');

                    newElement.appendChild(button);
                    newElement.appendChild(input);

                    if(params.typeLink === ActionElement.LINK_HAS_ONE_TO_MANY)
                    {
                        list.prepend(newElement);
                    }
                    else
                    {
                        list.html(newElement);
                    }

                    AUTOLOAD.removeClosestElement();
                }
            }
        };

        MODAL.showModalWindow(data);
        return false;
    }

    let el = '<div id="' + elementId + '" class="' + type + '">' +
        '<div class="message">' +
        message +
        '</div>' +
        '<div>';

    $("#body-view").append(el);

    if (type !== this.TYPE_ERROR) {
        setTimeout(function () {
            $("#" + elementId).remove();
        }, this.PERIOD_SHOW);
    }

    $('#' + elementId).on('click', function (e) {
        $(this).remove();
    });
};

//
/**
 * Можно ли удалить элемент, не показывать popup окно с подтверждением
 * data берётся для modalWindow
 */
ActionElementView.prototype.canDelete = function (data) {
    if (typeof data.element !== 'undefined') {
        let checked = data.element.getAttribute('data-checked');
        if (checked) {
            return true;
        }
    }

    return false;
};


const ActionElement = new ActionElementView();