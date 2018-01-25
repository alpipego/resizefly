/**
 * add user size
 *
 * Created by alpipego on 26/06/17.
 */
var $ = jQuery,
    resizefly = window.resizefly;

$('#js-rzf-add-user-size').on('click', function (ev) {
    console.log(ev);
    ev.preventDefault();
    var values = {},
        error = false;
    // sanitize
    $(this).parent('.rzf-user-sizes-single').siblings('.rzf-user-sizes-single').each(function () {
        var $input = $(this).find('input[name!=""]'),
            name = $input.attr('name').match(/resizefly_user_sizes\[clone\]\[([a-z]+?)\]/i);
        values[name[1]] = $.trim($input.val());
    });

    // very simple error handling
    var $error = $('#js-rzf-user-sizes-error'),
        names = [],
        width = parseInt(values.width, 10) || 0,
        height = parseInt(values.height, 10) || 0;
    $error.empty();

    // check if name and unique
    $('.rzf-image-sizes tbody tr td:last-of-type').each(function () {
        names.push($.trim($(this).text()));
    });

    if (values.name.length === 0 || $.inArray(values.name, names) > -1) {
        $error.append($('<div>').text(resizefly.user_size_errors.name));
        error = true;
    }

    if (width === 0 && height === 0) {
        $error.append($('<div>').text(resizefly.user_size_errors.dimension));
        error = true;
    }

    if (!!values.crop && values.crop.toLowerCase() !== 'false' && (width === 0 || height === 0)) {
        $error.append($('<div>').text(resizefly.user_size_errors.crop_dimensions));
        error = true;
    }

    if (error) {
        return false;
    }

    $.each(values, function (key, val) {
        $('#js-rzf-user-sizes').append(
            $('<input>').attr({
                'type': 'hidden',
                'name': 'resizefly_user_sizes[' + values.name + '][' + key + ']'
            }).val(val)
        );
    });

    $('#submit').click();
});

$('.js-rzf-delete-user-size').on('click', function (ev) {
    ev.preventDefault();
    $(this).closest('tr').remove();
    $('#submit').click();
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

$('[name^="resizefly_user_sizes[clone]"').on('keypress keyup', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        $('#js-rzf-add-user-size').click();
    }
});
