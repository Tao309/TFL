//	https://www.html5rocks.com/ru/tutorials/file/xhr2/

function XmlHttpRequest() {
	this.xmlhttp = this.getXmlHttp();
	this.sendForm = 0;
};
XmlHttpRequest.prototype.getXmlHttp = function(data) {
	let xmlhttp;
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {
		try { xmlhttp = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {
		try { xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {
		xmlhttp = false; } }
	}
	return xmlhttp;
};
//Пока не работает внутренний обработчик ошибок
XmlHttpRequest.prototype.getError = function(r) {
	console.log('xmlHttpRequest error');
};
XmlHttpRequest.prototype.send = function(opts) {
	let xmlhttp = this.xmlhttp;

	return new Promise(function (resolve, reject) {

        let params;
        opts.method = opts.method.toUpperCase();

        xmlhttp.open(opts.method, opts.url, true);

		if((typeof opts.sendForm === 'undefined' || !opts.sendForm) && typeof opts.formData === 'undefined') {
			if(opts.method === 'POST')
			{
                xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			}

			params = opts.data;

			if (params && typeof params === 'object') {
				params = Object.keys(params).map(function (key) {
					return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
				}).join('&');
			}
		}
		else
		{
			/*
				Три типа:
				application/x-www-form-urlencoded
				multipart/form-data
				text-plain
			 */
			//Отправка формы
			if(opts.sendForm === null || opts.sendForm === '') {
				console.log("Form is not valid");
				return false;
			}

			//Форма сделана заранее
			if(typeof opts.formData !== 'undefined')
			{
                params = opts.formData;
                opts.sendForm = true;

				//let boundary = Math.random().toString().substr(2);
                //xmlhttp.setRequestHeader("Content-Type", "multipart/form-data; charset=utf-8; boundary=" + boundary);
			}
			else
			{
                params = new FormData(opts.sendForm);

                if(Object.keys(opts.sendForm).length>0) {
                    $.each(opts.sendForm, function(key, value) {
                        if(typeof key !== 'number')
                        {
                            params.append(key, value);
                        }
                    });
                }
			}

			if(typeof opts.appendData !== 'undefined')
			{
                $.each(opts.appendData, function(key, value) {
					params.append(key, value);
                });
			}

			if(opts.method === 'POST' || opts.method === 'PUT' || opts.method === 'PATCH') {
				// xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				// console.log('>'+opts.method);

				// xmlhttp.setRequestHeader("Content-Type", "multipart/form-data");
				// xmlhttp.setRequestHeader('Content-type','application/json; charset=utf-8');
			}

			// opts.method = 'POST';
		}

		xmlhttp.setRequestHeader('X-Requested-With', 'tfl-http-request');

		//Добавляем csrf значения
		xmlhttp.setRequestHeader('X-CSRF-NAME', HTML.getMeta('_csrfName'));
		xmlhttp.setRequestHeader('X-CSRF-VALUE', HTML.getMeta('_csrfValue'));

		if (opts.headers) {
			Object.keys(opts.headers).forEach(function (key) {
				xhr.setRequestHeader(key, opts.headers[key]);
			});
		}
		
		xmlhttp.onreadystatechange = function()  {
			if (xmlhttp.readyState === 4)   {
				if(xmlhttp.status === 200) {
					try {
                        let response = JSON.parse(xmlhttp.responseText);
                        resolve(response)
					} catch (e) {
						reject({
						  status: xmlhttp.status,
						  statusText: 'Response is not valid JSON!'
						});
						return false;
					}
				} else {
					reject({
					  status: xmlhttp.status,
					  statusText: xmlhttp.statusText
					});
				}
			}
		};
		
		xmlhttp.onerror = function() {
			reject({
				status: xmlhttp.status,
				statusText: xmlhttp.statusText
			});
		};
			
		xmlhttp.send(params);

		/*
        // Отправляем файлы.
        if(xmlhttp.sendAsBinary) {
            // Только для Firefox
            xmlhttp.sendAsBinary(params);
        } else {
            // Для остальных (как нужно по спецификации W3C)
            xmlhttp.send(params);
        }
        */
		
		if(typeof opts.sendForm !== 'undefined' && opts.sendForm) {
			return false;
		}
	});
};

const AJAX = new XmlHttpRequest();