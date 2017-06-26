/**
 * image sizes table
 *
 * Created by alpipego on 26.06.2017.
 */
var $section = $('label[for="resizefly_sizes_section"]').parents('tr'),
    $toggle = $('#resizefly_restrict_sizes');

showHideTable($toggle.is(':checked'), $section);
$toggle.on('change', function () {
    showHideTable($toggle.is(':checked'), $section);
});

function showHideTable(toggle, $section) {
    if (toggle) {
        $section.show();
    } else {
        $section.hide();
    }
}
