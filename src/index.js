import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import ImageTool from '@editorjs/image';
import ajax from '@codexteam/ajax';
import Table from '@editorjs/table'
import './style.css';

// https://stackoverflow.com/a/61321728/230419
function DataURIToBlob(dataURI) {
    const splitDataURI = dataURI.split(',')
    const byteString = splitDataURI[0].indexOf('base64') >= 0 ? atob(splitDataURI[1]) : decodeURI(splitDataURI[1])
    const mimeString = splitDataURI[0].split(':')[1].split(';')[0]

    const ia = new Uint8Array(byteString.length)
    for (let i = 0; i < byteString.length; i++)
        ia[i] = byteString.charCodeAt(i)

    return new Blob([ia], { type: mimeString })
}

(function($) {

    $.pkp.controllers.form.blockPages =
			$.pkp.controllers.form.blockPages || { };

    /**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQueryObject} $grid The grid this handler is
	 *  attached to.
	 * @param {{features}} options Grid handler configuration.
	 */
    $.pkp.controllers.form.blockPages.BlockPageFormHandler =
			function($formElement, options) {

        const uploadUrl = options.uploadUrl;

        let editorTools = {
            header: Header,
            table: Table,
            image: {
                class: ImageTool,
                config: {
                    uploader: {
                        uploadByFile(file){
                            const formData = new FormData();

                            formData.append('file', file);
                            console.log(uploadUrl);

                            return ajax.post({
                                url: uploadUrl,
                                data: formData,
                                type: ajax.contentType.JSON,
                                headers: {
                                    'X-Csrf-Token': $("input[name=csrfToken]").val(),
                                },
                            }).then(response => {
                                return {
                                    success: 1,
                                    file: {
                                        url: response.body.url
                                    }
                                }
                            });
                        }
                    }
                }
            }
        };

        // We dynamically create blocks here based on the provided configuration from
        // other plugins etc
        for(let blockConfigKey in options.blockConfigs) {
            let blockConfig = options.blockConfigs[blockConfigKey];
            let block = function({data}){ this.data = data; };

            block.prototype.render = function() {
                let container = document.createElement('div');
                container.classList.add('row', 'blockPages-block-element');

                let titleBar = document.createElement('div');
                titleBar.innerText = blockConfig['title'] ?? 'No Title Provided';
                titleBar.classList.add('col-12', 'blockPages-block-title');
                container.appendChild(titleBar);

                let importButton = document.createElement('button');
                importButton.setAttribute('type', 'button');
                importButton.innerText = 'Import JSON';
                importButton.classList.add('blockPages-block-import');
                importButton.addEventListener('click', function(){
                    let json = prompt("Import JSON");
                    json = JSON.parse(json);
                    for(let fieldName in blockConfig['fields']) {
                        let field = blockConfig['fields'][fieldName];
                        let val = json[fieldName];
                        if(val) {
                            if(field['otype'] == 'image') { // Maybe switch in future
                                const file = DataURIToBlob(val);
                                const formData = new FormData();

                                const splitDataURI = val.split(',');
                                const mimeString = splitDataURI[0].split(':')[1].split(';')[0];
                                const ext = mimeString.split("/")[1];
                                formData.append('file', file, "img." + ext);
                                ajax.post({
                                    url: uploadUrl,
                                    data: formData,
                                    type: ajax.contentType.JSON,
                                    headers: {
                                        'X-Csrf-Token': $("input[name=csrfToken]").val(),
                                    },
                                }).then(response => {
                                    let url = response.body.url;
                                    $(".bpp-" + fieldName, container).attr( 'src', url);
                                    $(".bpi-" + fieldName, container).val( url );
                                });
                            } else {
                                $(".bpi-" + fieldName, container).val( val );
                            }
                        }
                    }
                });
                titleBar.appendChild(importButton);

                for(let fieldName in blockConfig['fields']) {
                    let field = blockConfig['fields'][fieldName];
                    let fieldContainer = document.createElement('div');
                    fieldContainer.classList.add('blockPages-block-item', 'col-' + (field['columns'] ?? 12));

                    let fieldRand = Math.random();

                    let label = document.createElement('label');
                    label.innerText = field['title'];
                    label.setAttribute('for', 'field_' + fieldRand);
                    fieldContainer.appendChild(label);

                    // TODO: somehow allow extensions here?
                    if(field['type'] == 'image') {
                        field['otype'] = 'image';
                        field['type'] = 'hidden'; // actual input is hidden please!
                    }

                    let input = document.createElement('input');
                    input.setAttribute('type', field['type']);
                    input.classList.add('cdx-input','bpi-' + fieldName);
                    input.setAttribute('id', 'field_' + fieldRand);
                    if(this.data[fieldName]) {
                        input.value = this.data[fieldName];
                    }
                    fieldContainer.appendChild(input);

                    if(field['otype'] == 'image') {
                        // Image custom handling code
                        let preview = document.createElement('img');
                        preview.classList.add('blockPages-block-imagePreview','bpp-' + fieldName);
                        if(this.data[fieldName]) {
                            preview.setAttribute('src', this.data[fieldName]);
                        }
                        fieldContainer.appendChild(preview);

                        let upload = document.createElement('input');
                        upload.setAttribute('type', 'file');
                        upload.addEventListener('change', function(event){
                            upload.setAttribute('disabled', 'disabled');
                            const formData = new FormData();
                            formData.append('file', event.target.files[0]);
                            console.log(uploadUrl);

                            return ajax.post({
                                url: uploadUrl,
                                data: formData,
                                type: ajax.contentType.JSON,
                                headers: {
                                    'X-Csrf-Token': $("input[name=csrfToken]").val(),
                                },
                            }).then(response => {
                                let url = response.body.url;
                                preview.setAttribute('src', url);
                                upload.removeAttribute('disabled');
                                input.value = url;
                            });
                        });
                        fieldContainer.appendChild(upload);
                    }

                    container.appendChild(fieldContainer);
                }

                return container;
            };

            block.prototype.save = function(blockContent){
                let data = {};
                for(let fieldName in blockConfig['fields']) {
                    let input = blockContent.getElementsByClassName('bpi-' + fieldName);
                    data[fieldName] = input[0].value;
                }
                console.log(data);
                return data;
            };

            block.toolbox = function() {
                return {
                    title: blockConfig['title'] ?? 'No Title Provided',
                    icon: blockConfig['icon'] ?? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Pro 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2024 Fonticons, Inc.--><path d="M342.4 80H248v80H388.4L357 89.5c-2.6-5.8-8.3-9.5-14.6-9.5zM400 208H48V416c0 8.8 7.2 16 16 16H384c8.8 0 16-7.2 16-16V208zM59.6 160H200V80H105.6c-6.3 0-12.1 3.7-14.6 9.5L59.6 160zM342.4 32c25.3 0 48.2 14.9 58.5 38l41.6 93.6c3.6 8.2 5.5 17 5.5 26V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V189.6c0-9 1.9-17.8 5.5-26L47.1 70c10.3-23.1 33.2-38 58.5-38H342.4z"/></svg>`
                }
            }
            editorTools[blockConfigKey] = block;
        }

        this.editor = new EditorJS({ 
            /** 
             * Id of Element that should contain the Editor 
             */ 
            holder: 'editorjs',
            data: JSON.parse($("#content").val()) || {},
            inlineToolbar: ['link', 'bold', 'italic'], 
            tools: editorTools
        });

        this.parent($formElement, options);

        $(".pkpModalWrapper").css("pointer-events", 'none'); // Make it so you can't accidentally dismiss the dialog
        $(".pkpModalWrapper .pkp_modal_panel").css("pointer-events", 'all'); // but you can interact with the box

    };

    $.pkp.classes.Helper.inherits(
        $.pkp.controllers.form.blockPages.BlockPageFormHandler,
        $.pkp.controllers.form.AjaxFormHandler
    );

    $.pkp.controllers.form.blockPages.BlockPageFormHandler.prototype.submitForm =
        function(validator, formElement) {

            if(this.canSave) {
                this.parent('submitForm', validator, formElement);
            } else {

                console.log(this.editor);
                this.editor.save().then((outputData) => {
                    $("#content").val(JSON.stringify(outputData)).attr("type", "text");
                    this.canSave = true;
                    this.submitForm(validator, formElement);
                }).catch((error) => {
                    console.log('Saving failed: ', error);
                    alert('cannot save');
                });

            }

    };

})(window.jQuery);