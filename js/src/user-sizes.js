/**
 * add user size
 *
 * Created by alpipego on 26/06/17.
 */
var $ = jQuery,
    resizefly = window.resizefly,
    unsaved = false,
    $error = $('#js-rzf-user-sizes-error'),
    $add = $('#js-rzf-user-size-add');

$(document).keypress(function (e) {
    if (e.which === 13 && $('#rzf-user-sizes').find('input:focus').length) {
        e.preventDefault();
        $add.trigger('click');
    }
});

$add.on('click', function (ev) {
    ev.preventDefault();
    var values = {},
        $button = $(this),
        invalid;

    $button.attr('disabled', 'disabled');
    $error.empty();

    // sanitize
    $button.parent('.rzf-user-sizes-single').siblings('.rzf-user-sizes-single').each(function () {
        var $input = $(this).find('input[name!=""]'),
            name = $input.attr('name').match(/resizefly_user_sizes\[clone]\[([a-z]+?)]/i);
        values[name[1]] = $.trim($input.val());

        $input[0].reportValidity();

        if (!$input[0].checkValidity()) {
            invalid = name[1];
            return false;
        }

        if (name[1] === 'name') {
            var names = $('.rzf-size-name').map(function () {
                return $.trim($(this).text());
            }).toArray();
            if ($.inArray($.trim($input.val()), names) > -1) {
                $error.append($('<div>').text(resizefly.user_size_errors.name));
                invalid = name[1];
                return false;
            }
        }
    });

    if (invalid) {
        $button.removeAttr('disabled');
        return;
    }

    $error.empty();

    if ($('input[name="resizefly_user_sizes[clone][name]"]').length === 0) {
        $error.append($('<div>').text(resizefly.user_size_errors.name));
    }

    // make ajax call to save user size
    $.post(window.ajaxurl, {
        'action': $button.data('action'),
        'ajax_nonce': $button.data('nonce'),
        'size': values
    })
        .always(function (r) {
            $button.removeAttr('disabled');
            console.log(r);
        })
        .done(function (r) {
            unsaved = true;
            r.data.crop = typeof r.data.crop === 'boolean' ? !!r.data.crop : r.data.crop.join(',');

            // get row template
            var row = wp.template('rzf-size-row'),
                pos = 0;

            // find position for row
            $('.rzf-size').each(function (i, val) {
                var atts = $(val).data();
                pos = i;

                if (atts.width < r.data.width) {
                    return;
                }

                if (atts.height > r.data.height) {
                    return false;
                }

                if (atts.width === r.data.width && atts.height === r.data.height) {
                    if (atts.name < r.data.name) {
                        return;
                    }
                }

                return false;
            });

            if (pos === 0) {
                $('.rzf-image-sizes tbody').prepend(row(r.data));
            } else {
                $('' + row(r.data)).insertAfter('.rzf-size:nth-child(' + pos + ')');
            }

            $('.rzf-user-sizes-single').find('input').each(function () {
                $(this).val('').prop('checked', false);
            });
        })
        .fail(function (r) {
            $.each(r.responseJSON.data, function (i, val) {
                $error.append($('<div>').text(resizefly.user_size_errors[val]));
            });
        });
});

$('.js-rzf-user-size-readd').on('click', function (ev) {
    ev.preventDefault();
    var $button = $(this),
        $row = $(this).parents('tr').eq(0);

    $button.attr('disabled', 'disabled');

    $error.empty();

    // make ajax call to save user size
    $.post(window.ajaxurl, {
        'action': $button.data('action'),
        'ajax_nonce': $button.data('nonce'),
        'size': $row.data()
    })
        .always(function (r) {
            $button.removeAttr('disabled');
            console.log(r);
        })
        .done(function (r) {
            unsaved = true;

            // get row template
            var row = wp.template('rzf-size-row');

            $(row(r.data)).insertBefore($row);
            $row.remove();
        })
        .fail(function (r) {
            $.each(r.responseJSON.data, function (i, val) {
                $error.append($('<div>').text(resizefly.user_size_errors[val]));
            });
        });
});

$('.js-rzf-user-size-delete').on('click', function (ev) {
    ev.preventDefault();
    var $button = $(this);

    $button.attr('disabled', 'disabled');

    $.post(window.ajaxurl, {
        'action': $button.data('action'),
        'ajax_nonce': $button.data('nonce'),
        'size': $button.data('size-name')
    })
        .always(function (r) {
            $button.removeAttr('disabled');
        })
        .done(function () {
            $button.closest('tr')
                .addClass('rzf-size-status-missing')
                .find('input').each(function () {
                $(this).remove();
            });

            unsaved = true;
        })
        .fail(function () {
            // handle fail state
        });
});

$('#js-rzf-toggle-crop').on('change', function () {
    var $input = $('[name="resizefly_user_sizes[clone][crop]"]'),
        $cropX = $('#js-rzf-crop-x'),
        $cropY = $('#js-rzf-crop-y'),
        $crops = $cropX.add($cropY);

    if (this.checked) {
        $crops.removeAttr('disabled');
        $input.val($cropX.val() + ', ' + $cropY.val());
        $crops.on('change', function () {
            $input.val($cropX.val() + ', ' + $cropY.val());
        });
    } else {
        $crops.attr('disabled', 'disabled');
        $input.val(this.checked);
    }
});

$('#rzf-size-missing-save').on('click', function (e) {
    e.preventDefault();
    console.log($(this).parents('tr'));
});

$('form[action="options.php"]').on('submit', function () {
    unsaved = false;
});

// window.addEventListener('beforeunload', function (e) {
//     if (unsaved) {
//         e.returnValue = true;
//     }
// });
