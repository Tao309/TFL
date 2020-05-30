function Html() {

}
/**
 * Проверка пустых значений в форме
 * @param form Форма
 * @param names Поля для проверки
 */
Html.prototype.checkEmptyFormFields = function(form, names)
{
    let className = 'fill-required',
        result = true,
        byField = false;

    if(typeof names === 'undefined')
    {
        byField = true;
        names = form.querySelectorAll('.element-required');
    }

    let element;

    for(let i = 0; i < names.length; i++)
    {
        if(byField)
        {
            element = names[i];
        }
        else
        {
            if(typeof form[names[i]] === 'undefined')
            {
                continue;
            }

            element = form[names[i]];
        }

        if(element.value.trim() === '')
        {
            result = false;
            if(!$(element).hasClass(className))
            {
                $(element).addClass(className);
            }
        }
        else{
            $(element).removeClass(className);
            element.value = element.value.trim();
        }
    }

    return result;
};

Html.prototype.disableSubmitButton = function(form) {
    $(form).find('[type=submit]').attr('disabled', true);
};
Html.prototype.enableSubmitButton = function(form) {
    $(form).find('[type=submit]').attr('disabled', false);
};
Html.prototype.clearForm = function(form) {
    //@todo Проверка visible
    $(form).find('.html-element-text').val('');
    $(form).find('.html-element-textarea').val('');
    $(form).find('.html-element-select').prop('selectedIndex', 0);

    $('.image-add-file').removeClass('loaded');
    $('.image-cover-field').html('');
    $('.image-screen-field').html('');
    $('.html-model-list .list').html('');
};

Html.prototype.getMeta = function(name) {
    const metatags = document.getElementsByTagName('meta');

    for (let i = 0; i < metatags.length; i++) {
        if (metatags[i].getAttribute('name') === name) {
            return metatags[i].getAttribute('content');
        }
    }

    return '';
};

const HTML = new Html();