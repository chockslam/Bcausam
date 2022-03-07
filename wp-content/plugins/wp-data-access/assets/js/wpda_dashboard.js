var widgetSequenceNr = 0;
var dashboardWidgets = {};
var dashboardWidgetPosition = {};
var dashboardWidgetDeleted = [];

function increaseWidgetSequenceNr() {
    widgetSequenceNr++;
}

function addDashboardWidget(widgetId, widgetName, widgetType, widgetShare, obj = {}, save = true) {
    // Create widget with default properties
    dashboardWidgets[widgetId] = {};
    dashboardWidgets[widgetId].widgetName = widgetName;
    dashboardWidgets[widgetId].widgetType = widgetType;
    dashboardWidgets[widgetId].widgetShare = widgetShare;

    for (var prop in obj) {
        // Add custom properties
        dashboardWidgets[widgetId][prop] = obj[prop];
    }

    // Update dashboard
    if (save) {
        saveDashBoard();
    } else {
        if (typeof saveWidgetPositions === 'function') {
            saveWidgetPositions();
        }
    }
}

function delDashboardWidget(widgetId) {
	delete dashboardWidgets[widgetId];
}

function toggleDashboard() {
    if (jQuery("#screen-meta").css("display")==="block") {
        jQuery("#wpda-dashboard").hide();
        jQuery("#wpda-dashboard-mobile").hide();
    } else {
        showMenu();
    }
}

function toggleMenu() {
    if (jQuery("#wpda-dashboard-mobile ul").is(":visible")) {
        jQuery("#wpda-dashboard-mobile ul").hide();
    } else {
        jQuery("#wpda-dashboard-mobile ul").show();
    }
}

function showMenu() {
    if (jQuery("#wpcontent").width()<760) {
        jQuery("#wpda-dashboard").hide();
        jQuery("#wpda-dashboard-mobile").fadeIn(400);
    } else {
        if (jQuery("#wpcontent").width()<840) {
            wd = 38;
            fs = 17;
            tx = 6;
        } else if (jQuery("#wpcontent").width()<960) {
            wd = 44;
            fs = 22;
            tx = 7;
        } else if (jQuery("#wpcontent").width()<1080) {
            wd = 52;
            fs = 24;
            tx = 8;
        } else {
            wd = 62;
            fs = 28;
            tx = 9;
        }

        jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-dashboard").css("width",
            (wd*jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-dashboard .wpda-dashboard-item").length) + "px");
        jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-administration").css("width",
            (wd*jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-administration .wpda-dashboard-item").length) + "px");
        jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-publisher").css("width",
            (wd*jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-publisher .wpda-dashboard-item").length) + "px");
        jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-projects").css("width",
            (wd*jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-projects .wpda-dashboard-item").length) + "px");
        jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-settings").css("width",
            (wd*jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-settings .wpda-dashboard-item").length) + "px");
        jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-support").css("width",
            (wd*jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group-support .wpda-dashboard-item").length) + "px");

        jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group .wpda-dashboard-item").css("width", wd + "px");
        jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group .wpda-dashboard-item .label").css("font-size", tx + "px");
        jQuery("#wpda-dashboard .wpda-dashboard .wpda-dashboard-group .wpda-dashboard-item .fas").css("font-size", fs + "px");

        jQuery("#wpda-dashboard-mobile").hide();
        jQuery("#wpda-dashboard").fadeIn(1000);
        jQuery("#wpda-dashboard-toolbar").fadeIn(2000);

        jQuery(".wpda_tooltip").tooltip({
            tooltipClass: "wpda_tooltip_css",
        });
        jQuery(".wpda_tooltip_icons").tooltip({
            tooltipClass: "wpda_tooltip_icons_css",
            position: {
                my: "center bottom-24",
                at: "center top",
                using: function (position, feedback) {
                    jQuery(this).css(position);
                    jQuery("<div>")
                    .addClass("arrow")
                    .addClass(feedback.vertical)
                    .addClass(feedback.horizontal)
                    .appendTo(this);
                }
            }
        });
    }
}

function setDashboardWidth() {
    showMenu();
    refreshAllPanels();
}

function resizeFont(fontSize) {
    jQuery(".wpda-widget").css("font-size", fontSize + "px");
}

function addPanelCodeToDashboard(wp_nonce, panel_name, panel_code_id, panel_column, column_position) {
    increaseWidgetSequenceNr();
    jQuery.ajax({
        type: "POST",
        url: wpda_dashboard_vars.wpda_ajaxurl + "?action=wpda_widget_code_add",
        data: {
            wp_nonce: wp_nonce,
            wpda_panel_name: panel_name,
            wpda_panel_code_id: panel_code_id,
            wpda_panel_column: panel_column,
            wpda_column_position: column_position,
            wpda_widget_sequence_nr: widgetSequenceNr,
        }
    }).done(
        function(data) {
            jQuery("#wpbody-content").append(data);
            closePanel();

            setTimeout(function() {
                obj = {};
                obj.codeId = panel_code_id;
                addDashboardWidget(
                    widgetSequenceNr,
                    panel_name,
                    'code',
                    null,
                    obj
                );
            }, 2000);
        }
    );
}

function addPanelPublicationToDashboard(wp_nonce, panel_name, panel_pub_id, panel_column, column_position) {
    increaseWidgetSequenceNr();
    jQuery.ajax({
        type: "POST",
        url: wpda_dashboard_vars.wpda_ajaxurl + "?action=wpda_widget_pub_add",
        data: {
            wp_nonce: wp_nonce,
            wpda_panel_name: panel_name,
            wpda_panel_pub_id: panel_pub_id,
            wpda_panel_column: panel_column,
            wpda_column_position: column_position,
            wpda_widget_sequence_nr: widgetSequenceNr,
        }
    }).done(
        function(data) {
            jQuery("#wpbody-content").append(data);
            closePanel();

            setTimeout(function() {
                obj = {};
                obj.pubId = panel_pub_id;
                addDashboardWidget(
                    widgetSequenceNr,
                    panel_name,
                    'pub',
                    null,
                    obj
                );
            }, 2000);
        }
    );
}

function addPanelDbmsToDashboard(wp_nonce, panel_name, panel_dbms, panel_column, column_position) {
    increaseWidgetSequenceNr();
    jQuery.ajax({
        type: "POST",
        url: wpda_dashboard_vars.wpda_ajaxurl + "?action=wpda_widget_dbms_add",
        data: {
            wp_nonce: wp_nonce,
            wpda_panel_name: panel_name,
            wpda_panel_dbms: panel_dbms,
            wpda_panel_column: panel_column,
            wpda_column_position: column_position,
            wpda_widget_sequence_nr: widgetSequenceNr,
        }
    }).done(
        function(data) {
            jQuery("#wpbody-content").append(data);
            closePanel();

            setTimeout(function() {
                obj = {};
                obj.dbsDbms = panel_dbms;
                addDashboardWidget(
                    widgetSequenceNr,
                    panel_name,
                    'dbs',
                    null,
                    obj
                );
            }, 500);
        }
    );
}

function addPanelChartToDashboard(wp_nonce, panel_name, panel_dbs, panel_query, panel_column, column_position) {
    increaseWidgetSequenceNr();
    jQuery.ajax({
        type: "POST",
        url: wpda_dashboard_vars.wpda_ajaxurl + "?action=wpda_widget_chart_add",
        data: {
            wp_nonce: wp_nonce,
            wpda_panel_name: panel_name,
            wpda_panel_dbs: panel_dbs,
            wpda_panel_query: panel_query,
            wpda_panel_column: panel_column,
            wpda_column_position: column_position,
            wpda_widget_sequence_nr: widgetSequenceNr,
        }
    }).done(
        function(data) {
            jQuery("#wpbody-content").append(data);
            closePanel();
        }
    );
}

function addPanelProjectToDashboard(wp_nonce, panel_name, panel_project_id, panel_column, column_position) {
    increaseWidgetSequenceNr();
    jQuery.ajax({
        type: "POST",
        url: wpda_dashboard_vars.wpda_ajaxurl + "?action=wpda_widget_project_add",
        data: {
            wp_nonce: wp_nonce,
            wpda_panel_name: panel_name,
            wpda_panel_project_id: panel_project_id,
            wpda_panel_column: panel_column,
            wpda_column_position: column_position,
            wpda_widget_sequence_nr: widgetSequenceNr,
        }
    }).done(
        function(data) {
            jQuery("#wpbody-content").append(data);
            closePanel();

            setTimeout(function() {
                obj = {};
                obj.projectId = panel_project_id;
                addDashboardWidget(
                    widgetSequenceNr,
                    panel_name,
                    'project',
                    null,
                    obj
                );
            }, 2000);
        }
    );
}

function removePanelFromDashboard(e) {
    var dialogHtml = " \
        <p><strong>Remove widget from dashboard?</strong></p> \
        <div><span style='display:inline-block;width:55px'><strong>Delete</strong> </span>= Remove widget from dashboard and database </div> \
        <div><span style='display:inline-block;width:55px'><strong>Keep</strong> </span>= Remove widget from dashboard and keep in database </div> \
        <div><span style='display:inline-block;width:55px'><strong>Cancel</strong> </span>= Cancel action </div> \
    ";
    var dialog = jQuery("<div/>").html(dialogHtml).dialog({
        title: "Remove widget",
        width: "max-content",
        buttons: {
            "Delete": function() {
                delDashboardWidget(jQuery(e).data('id'));
                dashboardWidgetDeleted.push(jQuery(e).data("name"));
                removePanelFromDashboardAction(e);
                dialog.dialog("close");
            },
            "Keep":  function() {
                delDashboardWidget(jQuery(e).data('id'));
                removePanelFromDashboardAction(e);
                dialog.dialog("close");
            },
            "Cancel":  function() {
                dialog.dialog("close");
            }
        }
    });
}

function loadPanel() {
    if (jQuery("#wpda-open-panel-column").val()===null) {
        alert("Invalid column selection");
        return;
    }

    increaseWidgetSequenceNr();
    jQuery.ajax({
        type: "POST",
        url: wpda_dashboard_vars.wpda_ajaxurl + "?action=wpda_widget_load_panel",
        data: {
            wpda_wpnonce: wpda_wpnonce_refresh,
            wpda_panel_name: jQuery("#wpda-open-panel-name").val(),
            wpda_panel_column: jQuery("#wpda-open-panel-column").val(),
            wpda_panel_position: jQuery("#wpda-open-panel-position").val(),
            wpda_widget_id: widgetSequenceNr,
        }
    }).done(
        function(msg) {
            if (typeof msg === 'string') {
                jQuery("#wpbody-content").append(msg);
                setTimeout( function() {
                    saveDashBoard();
                }, 500);
            } else {
                if (msg.status==="ERROR" && msg.msg!==undefined) {
                    alert(msg.msg);
                }
            }

            closePanel();
        }
    ).fail(
        function (msg) {
            console.log("WP Data Access error (loadPanel):", msg);
        }
    );
}

function refreshAllPanels() {
    for (var prop in dashboardWidgets) {
        jQuery("#wpda-widget-" + prop).find(".wpda-widget-refresh").trigger("click", ["refresh"]);
    }
}

function removePanelFromDashboardAction(e) {
    id = jQuery(e).attr('id');
    jQuery(e).remove(); // Remove widget
    jQuery("." + id).remove(); // Remove widget script blocks
    saveDashBoard();
}

function addPanel() {
    closePanel();
    jQuery("#wpda-add-panel-name").val("");
    jQuery("#wpda-select-panel-type").show();
}

function closePanel() {
    jQuery("#wpda-add-panel").hide();
    jQuery("#wpda-select-panel-type").hide();
    jQuery("#wpda-open-panel").hide();
    jQuery("#wpda-manage-tabs").hide();
}

function openPanel() {
    closePanel();

    var exclude = [];
    for (var position in dashboardWidgetPosition) {
        for (var i=0; i<dashboardWidgetPosition[position].length; i++) {
            exclude.push(dashboardWidgetPosition[position][i]);
        }
    }

    resetColumnSelection();

    // Update listbox
    jQuery.ajax({
        method: 'POST',
        url: wpda_dashboard_vars.wpda_ajaxurl + "?action=wpda_dashboard_list",
        data: {
            wpda_wpnonce: wpda_wpnonce_refresh,
            wpda_exclude: exclude
        }
    }).done(
        function(msg) {
            if (typeof msg === 'string') {
                if (msg.startsWith("<option")) {
                    jQuery("#wpda-open-panel-name option").remove();
                    jQuery("#wpda-open-panel-name").append(msg);

                    jQuery("#wpda-open-panel").show();
                } else {
                    alert("No widgets found");
                }
            } else {
                if (msg.status==="ERROR" && msg.msg!==undefined) {
                    alert(msg.msg);
                }
            }
        }
    ).fail(
        function (msg) {
            console.log("WP Data Access error (openPanel):", msg);
        }
    );
}

function resetColumnSelection() {
    for (var i=1; i<=4; i++) {
        jQuery("#wpda-open-panel-column option[value=" + i + "]").removeAttr("disabled");
    }

    for (var i=jQuery(".wpda-dashboard-column").length+1; i<=4; i++) {
        jQuery("#wpda-open-panel-column option[value=" + i + "]").attr("disabled", true);
    }
}

function deletePanel() {
    panelName = jQuery("#wpda-open-panel-name").val();
    if (confirm('Delete panel "' + panelName + '"? This cannot be undone!')) {
        jQuery.ajax({
            method: 'POST',
            url: wpda_dashboard_vars.wpda_ajaxurl + "?action=wpda_widget_delete",
            data: {
                wpda_wpnonce: wpda_wpnonce_save,
                wpda_widget_name: panelName
            }
        }).done(
            function(msg) {
                if (msg.status===undefined) {
                    alert("Error deleting panel. Please refresh page and try again...");
                } else {
                    alert(msg.msg);
                    if (msg.status==="SUCCESS") {
                        jQuery("#wpda-open-panel-name option[value='" + panelName + "']").remove();
                        if (jQuery("#wpda-open-panel-name option").length===0) {
                            closePanel();
                        }
                    }
                }
            }
        ).fail(
            function (msg) {
                console.log("WP Data Access error (deletePanel):", msg);
            }
        );
    }
}

function getSQLFromQueryBuilder(wpnonce, widget_id) {
	url = location.pathname + '?action=wpda_query_builder_open_sql';
	jQuery.ajax({
		method: 'POST',
		url: url,
		data: {
			wpda_wpnonce: wpnonce,
			wpda_exclude: ""
		}
	}).done(
		function (msg) {
			if (!Array.isArray(msg.data)) {
				// Show queries
				list = jQuery("<ul/>");
				for (var queryName in msg.data) {
					dbs = msg.data[queryName].schema_name;
					qry = msg.data[queryName].query;

					query = jQuery(`
                        <div class="wpda-query-select">
                            <div class="wpda-query-select-title ui-widget-header">
                                ${queryName}
                                <span class="fas fa-copy wpda-query-select-title-copy wpda_tooltip_left" title="Copy SQL"></span>
                            </div>
                            <div class="wpda-query-select-content">
                                <textarea>${qry}</textarea>
                            </div>
                        </div>
					`);
					listitem = jQuery("<li/>").attr("data-dbs", dbs);
					listitem.append(query);
					
					list.append(listitem);
				}
				dialog = jQuery("<div class='wpda-query'/>").attr("title", "Select from Query Builder");
				dialog.append(list);
				dialog.dialog({
                    modal: true,
                    resizable: false,
                    width: "700px"
                });
                jQuery(".wpda_tooltip_left").tooltip({
                    tooltipClass: "wpda_tooltip_dashboard",
                    position: { my: "right top", at: "right bottom" }
                });

				jQuery(".wpda-query-select-title-copy").on("click", function() {
                    selectedQuery = jQuery(this).closest("li").find("textarea").val();
					selectedDbs = jQuery(this).closest("li").data("dbs");

					jQuery("#wpda_chart_dbs_" + widget_id).val(selectedDbs);
					jQuery("#wpda_chart_sql_" + widget_id).val(selectedQuery);
					
					jQuery(this).closest('.ui-dialog-content').dialog('close'); 
				});
			} else {
				// No queries found
			}
		}
	).fail(
		function (msg) {
			console.log("WP Data Access error (getSQLFromQueryBuilder):", msg);
		}
	);
}

function copyTexToClipboard(text) {
    var tempTextarea = jQuery("<textarea></textarea>");
    jQuery("body").append(tempTextarea);
    tempTextarea.val(text).select();
    document.execCommand("copy");
    tempTextarea.remove();
}

function makeSortable() {
    jQuery('.wpda-dashboard-column').sortable({
        connectWith: '.wpda-dashboard-column',
        cursor: 'move',
        opacity: 0.4,
        change: function(event, ui) {
            ui.placeholder.css({visibility: 'visible', background : '#ccc'});
        },
        update: function(event, ui) {
            saveDashBoard();
        }
    });
}

jQuery(function() {
    // jQuery("#show-settings-link").on("click", function() {
    //     setTimeout(function() { toggleDashboard(); }, 500);
    // });

    jQuery(window).on("resize", function() { setDashboardWidth() });
    setDashboardWidth();

    makeSortable();
});
