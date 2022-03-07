function getDbmsInfo(targetElement, action, args) {
	jQuery.ajax({
		type: "POST",
		url: wpda_dbms_vars.wpda_ajaxurl + "?action=" + action,
		data: args
	}).done(
		function(data) {
			targetElement.closest(".wpda-widget").find(".hostname").html(data['hostname']);
			targetElement.closest(".wpda-widget").find(".post").html(data['post']);
			targetElement.closest(".wpda-widget").find(".ssl").html(data['ssl']);
			targetElement.closest(".wpda-widget").find(".version").html(data['version'] + " " + data['version_comment']);
			targetElement.closest(".wpda-widget").find(".compiled").html(data['version_compile_os'] + " (" + data['version_compile_machine'] + ")");
			targetElement.closest(".wpda-widget").find(".uptime").html(data['uptime']);

			targetElement.closest(".wpda-widget").find(".basedir").html(data['basedir']);
			targetElement.closest(".wpda-widget").find(".datadir").html(data['datadir']);
			targetElement.closest(".wpda-widget").find(".plugin_dir").html(data['plugin_dir']);
			targetElement.closest(".wpda-widget").find(".tmpdir").html(data['tmpdir']);

			targetElement.closest(".wpda-widget").find(".log_error").html(data['log_error']);
			targetElement.closest(".wpda-widget").find(".general_log").html(data['general_log_file'] + "[" + data['general_log'] + "]");
			targetElement.closest(".wpda-widget").find(".slow_query").html(data['slow_query_log_file'] + "[" + data['slow_query_log'] + "]");
		}
	);
}

function getDbmsVars(targetElement, action, args, force = false) {
	if (force || targetElement.html()==="") {
		// Load variables
		targetElement.html("Loading...");
		jQuery.ajax({
			type: "POST",
			url: wpda_dbms_vars.wpda_ajaxurl + "?action=" + action,
			data: args
		}).done(
			function(data) {
				if (data.status==="ERROR") {
					targetElement.html('ERROR: ' + data.msg);
				} else {
					targetElement.html('');
					vars = jQuery('<table class="wpda-widget-dbms"></table>');
					targetElement.append(vars);
					varsHead = jQuery('<thead class="ui-widget-header"><tr><th>Variable</th><th>Value</th></tr></thead>');
					vars.append(varsHead);
					varsBody = jQuery('<tbody class="ui-widget-content"></tbody>');
					vars.append(varsBody);
					for (var prop in data) {
						varsBody.append('<tr><td>' + prop + "</td><td>" + data[prop] + "</td></tr>");
					}
					// Adjust header column width
					jQuery(targetElement).find("thead th:first-child").css("width",
						jQuery(targetElement).find("tbody td:first-child").css("width"));
				}
			}
		);
	}
}

function toggleIcon(elem) {
	if (elem.html().indexOf("fa-caret-right")>-1) {
		elem.html(elem.html().replace("fa-caret-right","fa-caret-down"));
	} else {
		elem.html(elem.html().replace("fa-caret-down","fa-caret-right"));
	}
}