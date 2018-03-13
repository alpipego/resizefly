<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 09.02.2018
 * Time: 12:20
 */
?>
<script type="text/html" id="tmpl-rzf-size-row">
    <tr
        class="rzf-size rzf-size-status-new"
        data-width="{{ data.width }}"
        data-height="{{ data.height }}"
        data-crop="<# !!data.crop #>"
        data-name="{{ data.name }}"
    >
        <td class="rzf-size-status"></td>
        <td class="rzf-size-active">
            <input type="checkbox" name="resizefly_sizes[{{{ data.name }}}][active]" checked="checked">
        </td>
        <td class="rzf-size-action"></td>
        <td class="rzf-size-width">
            {{{ data.width }}}
            <input type="hidden" name="resizefly_sizes[{{{ data.name }}}][width]" value="{{{ data.width }}}">
        </td>
        <td class="rzf-size-height">
            {{{ data.height }}}
            <input type="hidden" name="resizefly_sizes[{{{ data.name }}}][height]" value="{{{ data.height }}}">
        </td>
        <td class="rzf-size-crop">
            <# if (typeof data.crop === 'boolean') { #>
            <# if(data.crop) { #>
            center, center
            <# } #>
            <# } else { #>
            {{{ data.crop }}}
            <# } #>

            <input type="hidden" name="resizefly_sizes[{{{ data.name }}}][crop]" value="{{ data.crop }}">
        </td>
        <td class="rzf-size-name">{{{ data.name }}}</td>
    </tr>
</script>
