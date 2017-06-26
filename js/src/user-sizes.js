/**
 * add user size
 *
 * Created by alpipego on 26/06/17.
 */
$('#js-rzf-add-user-size').on('click', function (ev) {
    ev.preventDefault();
    var values = {},
        error = false;
    // sanitize
    $(this).parent('.rzf-user-sizes-single').siblings('.rzf-user-sizes-single').each(function () {
        var $input = $(this).find('input'),
            name = $input.attr('name').match(/resizefly_user_sizes\[clone\]\[([a-z]+?)\]/i);
        values[name[1]] = $.trim($input.val());
    });

    // very simple error handling
    var $error = $('#js-rzf-user-sizes-error'),
        names = [];
    $error.empty();

    // check if name and unique
    $('.rzf-image-sizes tbody tr td:last-of-type').each(function () {
        names.push($.trim($(this).text()));
    });

    if (values.name.length === 0 || $.inArray(values.name, names) > -1) {
        $error.append($('<div>').text(resizefly.user_size_errors.name));
        error = true;
    }

    if (!(parseInt(values.width, 10) > 0) && !(parseInt(values.height, 10) > 0)) {
        $error.append($('<div>').text(resizefly.user_size_errors.dimension));
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
