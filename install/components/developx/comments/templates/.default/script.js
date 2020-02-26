$(document).ready(function ($) {
    if (window.DevelopxComments)
        return;

    window.DevelopxComments = function () {
        this.initButtons();
        this.initLoader();
    };

    window.DevelopxComments.prototype = {
        initButtons: function () {
            var $this = this;
            $('body').on('click', '.backFormShowJs', function () {
                $('body').addClass('comments-block-show');
                return false;
            });
            $('body').on('click', '.backFormCloseJs, .overflowJs', function () {
                $('body').removeClass('comments-block-show');
                return false;
            });

            $('body').on('keyup', '.comments-block__form input, .comments-block__form textarea', function () {
                if ($(this).val().length > 0) {
                    $(this).parent().addClass('not-empty').removeClass('comments-block__error');
                } else {
                    $(this).parent().removeClass('not-empty');
                }
            });

            $('body').on('click', '.addCommentJs', function () {
                $(this).hide();
                $('.commentFormJs').slideDown();
                $('.noCommentJs').hide();
                return false;
            });

            $('body').on('click', '.commentCancelJs', function () {
                $('.addCommentJs').show();
                $('.commentFormJs').slideUp();
                $('.noCommentJs').show();
                return false;
            });


            $('body').on('click', '.likeJs', function () {
                var el = $(this);
                var count = parseInt(el.text());
                var gObj = {
                    ACTION: "addLike",
                    AJAX_CALL: 'Y',
                    ID: $(this).data('id'),
                    COUNT: count,
                };

                $.ajax({
                    data: gObj,
                    success: function(result){
                        console.log(result);
                        var result = JSON.parse(result);
                        if (
                            typeof result['COUNT'] !== 'undefined' &&
                            typeof result['LIKED'] !== 'undefined'
                        ){
                            el.toggleClass('liked', result['LIKED']).html(result['COUNT']);
                        }
                    }
                });
            });
        },
        initLoader: function () {
            if (typeof BX == 'undefined'){
                return;
            }
            BX.ready(function(){
                var loader = '<div class="sk-fading-circle ajax loaderJs">\n' +
                    '    <div class="sk-circle sk-circle-1"></div>\n' +
                    '    <div class="sk-circle sk-circle-2"></div>\n' +
                    '    <div class="sk-circle sk-circle-3"></div>\n' +
                    '    <div class="sk-circle sk-circle-4"></div>\n' +
                    '    <div class="sk-circle sk-circle-5"></div>\n' +
                    '    <div class="sk-circle sk-circle-6"></div>\n' +
                    '    <div class="sk-circle sk-circle-7"></div>\n' +
                    '    <div class="sk-circle sk-circle-8"></div>\n' +
                    '    <div class="sk-circle sk-circle-9"></div>\n' +
                    '    <div class="sk-circle sk-circle-10"></div>\n' +
                    '    <div class="sk-circle sk-circle-11"></div>\n' +
                    '    <div class="sk-circle sk-circle-12"></div>\n' +
                    '  </div';
                BX.showWait = function(node, msg) {
                    $('#' + node + ' .loadJs').parent().append(loader);
                    $('#' + node + ' .loadJs').addClass('loading');
                };
                BX.closeWait = function(node, obMsg) {
                    $('#' + node + ' .loadJs').parent().remove();
                    $('#' + node + ' .loadJs').removeClass('loading');
                };
            })
        },
    }

    DevelopxComments_ = new DevelopxComments();
});
