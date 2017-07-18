/**
 * image sizes table
 *
 * Created by alpipego on 26.06.2017.
 */
var $registeredSection = $('label[for="resizefly_sizes_section"]').parents('tr'),
    $userSection = $('label[for="resizefly_user_sizes"]').parents('tr'),
    $toggle = $('#resizefly_restrict_sizes');

showHideTable($toggle.is(':checked'), $registeredSection);
showHideTable($toggle.is(':checked'), $userSection);
$toggle.on('change', function () {
    showHideTable($toggle.is(':checked'), $registeredSection);
    showHideTable($toggle.is(':checked'), $userSection);
});

function showHideTable(toggle, $section) {
    if (toggle) {
        $section.show();
    } else {
        $section.hide();
    }
}
