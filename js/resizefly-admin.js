(function ($) {
    function showHideTable(toggle, $section) {
        toggle ? $section.show() : $section.hide();
    }

    var byteCalc, $registeredSection, $userSection, $toggle, unsaved, $error, $add, buttonIds = [ "#" + window.resizefly.purge_id, "#" + window.resizefly.resized_id ], $buttons = $(buttonIds.join(","));

    $("#" + window.resizefly.purge_id + "-smart").on("change", function() {
        $("#" + window.resizefly.purge_id + "-text").text($(this).prop("checked") ? window.resizefly.purge_most : window.resizefly.purge_all);
    });

    $buttons.on("click", function(e) {
        e.preventDefault();
        var $this = $(this), data = {
            action: $this.attr("id"),
            _ajax_nonce: $this.data("nonce"),
            "smart-purge": $("#" + $this.attr("id") + "-smart").is(":checked")
        }, resultId = "#" + $this.attr("id") + "-result";
        $this.next(".spinner").addClass("is-active");
        $buttons.prop("disabled", true);
        $.post(window.ajaxurl, data).done(function(response) {
            response.files ? $(resultId).html(window.resizefly.purge_result).children(".resizefly-files").text(response.files).parent().children(".resizefly-size").text(byteCalc.humanReadable(response.size)) : $(resultId).text(window.resizefly.purge_empty);
        }).fail(function(xhr) {
            alert(xhr.responseText);
        }).always(function() {
            $this.next(".spinner").removeClass("is-active");
            $buttons.prop("disabled", false);
        });
    });

    (byteCalc = {}).round = function(number, precision) {
        var factor = Math.pow(10, precision), tempNumber = number * factor;
        return Math.round(tempNumber) / factor;
    };

    byteCalc.humanReadable = function(bytes) {
        if (0 === bytes) {
            return "0 Byte";
        }
        var sizes = [ "Bytes", "KB", "MB", "GB", "TB" ], i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        return this.round(bytes / Math.pow(1024, i), 2) + " " + sizes[i];
    };

    $registeredSection = $('label[for="resizefly_sizes_section"]').parents("tr"), 
    $userSection = $('label[for="resizefly_user_sizes"]').parents("tr");

    showHideTable(($toggle = $("#resizefly_restrict_sizes")).is(":checked"), $registeredSection);

    showHideTable($toggle.is(":checked"), $userSection);

    $toggle.on("change", function() {
        showHideTable($toggle.is(":checked"), $registeredSection);
        showHideTable($toggle.is(":checked"), $userSection);
    });

    unsaved = false, $error = $("#js-rzf-user-sizes-error"), $add = $("#js-rzf-user-size-add");

    $(document).keypress(function(e) {
        if (13 === e.which && $("#rzf-user-sizes").find("input:focus").length) {
            e.preventDefault();
            $add.trigger("click");
        }
    });

    $add.on("click", function(ev) {
        ev.preventDefault();
        var invalid, values = {}, $button = $(this);
        $button.attr("disabled", "disabled");
        $error.empty();
        $button.parent(".rzf-user-sizes-single").siblings(".rzf-user-sizes-single").each(function() {
            var names, $input = $(this).find('input[name!=""]'), name = $input.attr("name").match(/resizefly_user_sizes\[clone]\[([a-z]+?)]/i);
            values[name[1]] = $.trim($input.val());
            $input[0].reportValidity();
            if (!$input[0].checkValidity()) {
                invalid = name[1];
                return false;
            }
            if ("name" === name[1]) {
                names = $(".rzf-size-name").map(function() {
                    return $.trim($(this).text());
                }).toArray();
                if ($.inArray($.trim($input.val()), names) > -1) {
                    $error.append($("<div>").text(window.resizefly.user_size_errors.name));
                    invalid = name[1];
                    return false;
                }
            }
        });
        if (invalid) {
            $button.removeAttr("disabled");
        } else {
            $error.empty();
            0 === $('input[name="resizefly_user_sizes[clone][name]"]').length && $error.append($("<div>").text(window.resizefly.user_size_errors.name));
            $.post(window.ajaxurl, {
                action: $button.data("action"),
                ajax_nonce: $button.data("nonce"),
                size: values
            }).always(function(r) {
                $button.removeAttr("disabled");
            }).done(function(r) {
                unsaved = true;
                r.data.crop = "boolean" === typeof r.data.crop ? !!r.data.crop : r.data.crop.join(",");
                var row = wp.template("rzf-size-row"), pos = 0;
                $(".rzf-size").each(function(i, val) {
                    var atts = $(val).data();
                    pos = i;
                    if (!(atts.width < r.data.width)) {
                        if (atts.height > r.data.height) {
                            return false;
                        }
                        if (!(atts.width === r.data.width && atts.height === r.data.height && atts.name < r.data.name)) {
                            return false;
                        }
                    }
                });
                0 === pos ? $(".rzf-image-sizes tbody").prepend(row(r.data)) : $("" + row(r.data)).insertAfter(".rzf-size:nth-child(" + pos + ")");
                $(".rzf-user-sizes-single").find("input").each(function() {
                    $(this).val("").prop("checked", false);
                });
            }).fail(function(r) {
                $.each(r.responseJSON.data, function(i, val) {
                    $error.append($("<div>").text(window.resizefly.user_size_errors[val]));
                });
            });
        }
    });

    $(document).on("click", ".js-rzf-user-size-readd", function(ev) {
        ev.preventDefault();
        var $button = $(this), $row = $(this).parents("tr").eq(0);
        $button.attr("disabled", "disabled");
        $error.empty();
        $.post(window.ajaxurl, {
            action: $button.data("action"),
            ajax_nonce: $button.data("nonce"),
            size: $row.data()
        }).always(function(r) {
            $button.removeAttr("disabled");
        }).done(function(r) {
            unsaved = true;
            var row = wp.template("rzf-size-row");
            $(row(r.data)).insertBefore($row);
            $row.remove();
            $(".rzf-size.rzf-size-status-missing").length || $(".rzf-size-status-desc .rzf-size-status-missing").addClass("hidden");
        }).fail(function(r) {
            $.each(r.responseJSON.data, function(i, val) {
                $error.append($("<div>").text(window.resizefly.user_size_errors[val]));
            });
        });
    });

    $(document).on("click", ".js-rzf-user-size-delete", function(ev) {
        ev.preventDefault();
        var $button = $(this);
        $button.attr("disabled", "disabled");
        $.post(window.ajaxurl, {
            action: $button.data("action"),
            ajax_nonce: $button.data("nonce"),
            size: $button.data("size-name")
        }).always(function(r) {
            $button.removeAttr("disabled");
        }).done(function() {
            $button.closest("tr").addClass("rzf-size-status-missing").find("input").each(function() {
                $(this).remove();
            });
            $button.remove();
            unsaved = true;
        }).fail(function() {});
    });

    $("#js-rzf-toggle-crop").on("change", function() {
        var $input = $('[name="resizefly_user_sizes[clone][crop]"]'), $cropX = $("#js-rzf-crop-x"), $cropY = $("#js-rzf-crop-y"), $crops = $cropX.add($cropY);
        if (this.checked) {
            $crops.removeAttr("disabled");
            $input.val($cropX.val() + ", " + $cropY.val());
            $crops.on("change", function() {
                $input.val($cropX.val() + ", " + $cropY.val());
            });
        } else {
            $crops.attr("disabled", "disabled");
            $input.val(this.checked);
        }
    });

    $("#rzf-size-missing-save").on("click", function(e) {
        e.preventDefault();
    });

    $('form[action="options.php"]').on("submit", function() {
        unsaved = false;
    });

    window.addEventListener("beforeunload", function(e) {
        unsaved && (e.returnValue = true);
    });
})(jQuery);