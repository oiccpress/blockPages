import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import ImageTool from '@editorjs/image';
import ajax from '@codexteam/ajax';

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

        this.editor = new EditorJS({ 
            /** 
             * Id of Element that should contain the Editor 
             */ 
            holder: 'editorjs',
            data: JSON.parse($("#content").val()) || {},
            inlineToolbar: ['link', 'bold', 'italic'], 
            tools: {
                header: Header,
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
            }
        });

        this.parent($formElement, options);

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