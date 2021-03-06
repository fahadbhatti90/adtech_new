/**
 * Select multiple email by jquery.email_multiple
 * **/

(function($){

    $.fn.addEmailMultiple = function(options) {

        let defaults = {
            reset: false,
            fill: false,
            data: null
        };

        let settings = $.extend(defaults, options);
        let email = "";


        return this.each(function()
        {
            $(this).after(
                "<div class=\"all-mail\"></div>\n" +
                "<input type=\"text\" name=\"email\" class=\"enter-mail-id form-control\" placeholder=\"Enter Email \" />");
            let $orig = $(this);

            let $element = $('.enter-mail-id');
            $element.keydown(function (e) {
                $element.css('border', '');
                if (e.keyCode === 13 || e.keyCode === 32) {
                    e.preventDefault();
                    e.stopPropagation();
                    let getValue = $element.val();
                    if (/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,6}$/.test(getValue)){
                        if(!email.includes(getValue)){
                            $('.all-mail').append('<span class="email-ids">' + getValue + '<span class="cancel-email">x</span></span>');
                            $element.val('');
                            email += getValue + ';'
                        }
                    } else {
                        $element.css('border', '1px solid red')
                    }
                }

                $orig.val(email.slice(0, -1))
            });

            $(document).on('click','.cancel-email',function(){
                $(this).parent().remove();
                //$(".ccEmailSelect2").val("");
            });

            return $orig.hide()
        });
    };

    $.fn.editEmailMultiple = function(options) {

        let defaults = {
            reset: false,
            fill: false,
            data: null
        };

        let settings = $.extend(defaults, options);
        let email = "";


        return this.each(function()
        {
            $(this).after(
                "<div class=\"all-mail-edit\"></div>\n" +
                "<input type=\"text\" name=\"email\" class=\"enter-mail-id-edit form-control\" placeholder=\"Enter Email \" />");
            let $orig = $(this);

            let $element = $('.enter-mail-id-edit');
            $element.keydown(function (e) {
                $element.css('border', '');
                if (e.keyCode === 13 || e.keyCode === 32) {
                    e.preventDefault();
                    e.stopPropagation();
                    let getValue = $element.val();

                    if (/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,6}$/.test(getValue)){
                        if(!email.includes(getValue)){
                            $('.all-mail-edit').append('<span class="email-ids-edit">' + getValue + '<span class="cancel-email">x</span></span>');
                            $element.val('');
                            email += getValue + ';'
                        }
                    } else {
                        $element.css('border', '1px solid red')
                    }
                }

                $orig.val(email.slice(0, -1))
            });

            $(document).on('click','.cancel-email',function(){
                $(this).parent().remove();
            });

            if(settings.data){
                $.each(settings.data, function (x, y) {
                    if (/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,6}$/.test(y)){
                        $('.all-mail-edit').append('<span class="email-ids-edit">' + y + '<span class="cancel-email">x</span></span>');
                        $element.val('');

                        email += y + ';'
                    } else {
                        $element.css('border', '1px solid red')
                    }
                })

                $orig.val(email.slice(0, -1))
            }

            if(settings.reset){
                $('.email-ids-edit').remove()
            }

            return $orig.hide()
        });
    };

})(jQuery);
