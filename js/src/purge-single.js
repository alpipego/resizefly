$(document).on('click', '.rzf-purge-single', function (e) {
    e.preventDefault();

    var $this = $(this),
        data = {
            'action': window.resizefly.purge_action,
            '_ajax_nonce': $this.data('nonce'),
            'id': $this.data('postid')
        };

    $.post(ajaxurl, data)
        .done(function (response) {
            if (response.files) {
                $this.next('.help')
                    .html(window.resizefly.purge_result)
                    .children('.resizefly-files').text(response.files);
            } else {
                $this.next('.help').text(window.resizefly.purge_empty);
            }
        })
        .fail(function (xhr) {
            alert(xhr.responseText);
        });
});
