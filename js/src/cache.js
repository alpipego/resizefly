/**
 * purge cache - delete all resized images buttons
 *
 * Created by alpipego on 26.06.2017.
 */
var resizefly = window.resizefly,
    buttonIds = ['#' + resizefly.purge_id, '#' + resizefly.resized_id];

$(buttonIds.join(',')).on('click', function (e) {
    e.preventDefault();

    var data = {
            'action': $(this).attr('id'),
            '_ajax_nonce': $(this).data('nonce')
        },
        resultId = '#' + $(this).attr('id') + '-result';

    $.post(ajaxurl, data, function (response) {
        console.log(resultId);
        if (response.files) {
            $(resultId)
                .html(resizefly.purge_result)
                .children('.resizefly-files').text(response.files)
                .parent().children('.resizefly-size').text(byteCalc.humanReadable(response.size));
        } else {
            $(resultId).text(resizefly.purge_empty);
        }
    });
});


var byteCalc = {};
// taken from https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Math/round
byteCalc.round = function (number, precision) {
    var factor = Math.pow(10, precision),
        tempNumber = number * factor,
        roundedTempNumber = Math.round(tempNumber);

    return roundedTempNumber / factor;
};

// taken from http://stackoverflow.com/a/18650828/2105015
byteCalc.humanReadable = function (bytes) {
    if (bytes === 0) {
        return '0 Byte';
    }
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'],
        i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));

    return this.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
};
