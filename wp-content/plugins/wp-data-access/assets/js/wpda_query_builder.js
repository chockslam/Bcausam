const TAB_DEFAULT_LABEL = 'New Query';

var editors = {};
var tabIndex = 0;
var tabs = [];
var isChanged = {};
var isVisual = {};
var dbHints = {}
var columnLink = {};

function tabActivate(activeIndex) {
	jQuery(".wpda_query_builder").hide();
	jQuery("#wpda_query_builder_" + activeIndex).show();

	jQuery(".nav-tab").removeClass("nav-tab-active");
	jQuery("#wpda_query_builder_label_" + activeIndex).addClass("nav-tab-active");
}

function tabNew(tabName = TAB_DEFAULT_LABEL, query = '', schema_name = wpda_default_database) {
	tabIndex++;
	if (tabName===TAB_DEFAULT_LABEL) {
		tabName += " (" + tabIndex + ")";
		dbsName = '';
	} else {
		dbsName = tabName;
	}

	tabLabel = `
		<a id="wpda_query_builder_label_${tabIndex}" 
			class="nav-tab wpda_query_builder_label wpda_tooltip" 
			data-id="${tabIndex}" 
			href="javascript:void(0)"
		   	title="Double click to change query name"
		>
			<i class="fas fa-pen"></i>
			<span id="wpda_query_builder_label_value_${tabIndex}" 
				  class="wpda_query_builder_label_value"
				  contenteditable="true" 
				  data-dbs-name="${dbsName}"
				  onclick="tabActivate('${tabIndex}')"
				  ondblclick="selectContent(event)"
			>${tabName}</span>
			<span id="tab-${tabIndex}-icon"
				  class="dashicons dashicons-dismiss icon_close"
				  style="vertical-align: middle"
				  onclick="tabClose('${tabIndex}')"
			></span>
		</a>
	`;
	jQuery("#wpda_query_builder nav.nav-tab-wrapper").append(tabLabel);
	document.getElementById("wpda_query_builder_label_value_" + tabIndex).ondblclick = function(){
		event.preventDefault();
		var sel = window.getSelection();
		var range = document.createRange();
		range.selectNodeContents(this);
		sel.removeAllRanges();
		sel.addRange(range);
	};

	//
	let vqbButton = '';
	if (vqbInstalled) {
		vqbButton = `
			<span>
				<a href="javascript:void(0)" onclick="addVisual('${tabIndex}')" class="wpda_tooltip button button-primary wpda_vqb_button" title="Enable Visual Query Builder for this query">
					<span class="material-icons wpda_icon_on_button">remove_red_eye</span> Visual Query Builder</a>
			</span>
		`;
	}

	tabContent = `
		<div id="wpda_query_builder_${tabIndex}" class="wpda_query_builder" data-id="${tabIndex}">
			<div class="wpda_query_builder_taskbar">
				${vqbButton}
				<span>
					<label>
						Select database
						<select id="wpda_query_builder_dbs_${tabIndex}" onchange="setHints('${tabIndex}')">${wpda_databases}</select>
					</label>
				</span>
				<span>
					<label>
						<input id="wpda_query_builder_wordpress_protect_${tabIndex}" type="checkbox" checked />
						Protect WordPress tables
					</label>
				</span>
				<span class="wpda_query_builder_actions">
					<label>
						<input id="use_max_rows_${tabIndex}" type="checkbox" checked/>
						Max rows:
						<input id="max_rows_${tabIndex}" type="number" value="100" min="1" onblur="if (jQuery(this).val()==='') { jQuery(this).val(100) }" style="width: 100px"/>
					</label>
					<a href="javascript:void(0)" onclick="executeQuery('${tabIndex}')" class="wpda_tooltip button button-primary" title="Execute query">
						<span class="material-icons wpda_icon_on_button">play_arrow</span> Execute</a>
					<span id="executing_query_${tabIndex}" style="display: none">
						<img src="${wpda_loader_url}" class="wpda_spinner" />
					</span>
					<a href="javascript:void(0)" onclick="saveQuery('${tabIndex}')" class="wpda_tooltip button button-secondary" title="Save query">
						<span class="material-icons wpda_icon_on_button">cloud_upload</span> Save</a>
					<button href="javascript:void(0)" class="wpda_tooltip button button-secondary wpda_copy_to_clipboard" title="Copy query to clipboard" data-clipboard-text="ABC">
						<span class="material-icons wpda_icon_on_button">content_copy</span> Copy to clipboard</button>
						
					<a href="javascript:void(0)" class="wpda_tooltip button button-secondary wpda-query-help" title="Use / to separate multiple SQL commands:

select * from dept
/
select * from emp
/

The / must be on an empty line
">?</a>
				</span>
			</div>
			<div id="wpda_query_builder_sql_container_${tabIndex}" class="wpda_query_builder_sql">
				<textarea id="wpda_query_builder_sql_${tabIndex}">${query}</textarea>
			</div>
			<div id="wpda_query_builder_tabs_${tabIndex}" class="wpda_query_builder_tabs" style="display: none"></div>
			${queryResult(tabIndex)}
		</div>`;
	jQuery("#wpda_query_builder").append(tabContent);

	editors['tab' + tabIndex] = wp.codeEditor.initialize(jQuery('#wpda_query_builder_sql_' + tabIndex), cm_settings);
	editors['tab' + tabIndex].codemirror.setOption('tabindex', tabIndex);
	editors['tab' + tabIndex].codemirror.on('change', function(cm_editor) {
		isChanged[cm_editor.getOption('tabindex')] = true;
	});

	jQuery("#wpda_query_builder_dbs_" + tabIndex).val(schema_name);
	jQuery('.wpda_tooltip').tooltip({
		track: true
	});
	new ClipboardJS(".wpda_copy_to_clipboard");
	jQuery(".wpda_copy_to_clipboard").on("click", { tabIndex: tabIndex }, function() {
		cm = editors['tab' + tabIndex].codemirror;
		cm.save();
		jQuery(this).attr("data-clipboard-text", jQuery("#wpda_query_builder_sql_" + tabIndex).val());
		jQuery.notify('Query copied to clipboard', 'success');
	});

	tabActivate(tabIndex);
	isChanged[tabIndex] = false;
	isVisual[tabIndex] = false;
	setHints(tabIndex);

	jQuery('.wpda_tooltip').tooltip({
		tooltipClass: "wpda_tooltip_dashboard"
	});
}

function setHints(activeIndex) {
	var schemaName = jQuery("#wpda_query_builder_dbs_" + activeIndex).val();
	if (!dbHints[schemaName]) {
		jQuery.ajax({
			method: 'POST',
			url: wpda_home_url + "?action=wpda_query_builder_get_db_hints",
			data: {
				wpda_wpnonce: wpda_wpnonce,
				wpda_schemaname: schemaName
			}
		}).done(
			function (msg) {
				if (msg.status && msg.tables && msg.status === "OK") {
					tabTables = Object.assign({}, msg.tables);
					for (var table in msg.tables) {
						for (var i = 0; i < msg.tables[table].length; i++) {
							tabTables[msg.tables[table][i]] = [];
						}
					}
					editors['tab' + activeIndex].codemirror.options.hintOptions = {
						tables: tabTables
					}
					// Save tables for new tabs
					dbHints[schemaName] = tabTables;
					// Update visual component if enabled
					if (isVisual[activeIndex]) {
						updateVisual(activeIndex);
					}
				} else {
					editors['tab' + activeIndex].codemirror.options.hintOptions = {
						tables: null
					}
				}
			}
		).fail(
			function (msg) {
				console.log("WP Data Access ERROR:");
				console.log(msg);
				editors['tab' + activeIndex].codemirror.options.hintOptions = {
					tables: null
				}
			}
		);
	} else {
		editors['tab' + activeIndex].codemirror.options.hintOptions = {
			tables: dbHints[schemaName]
		}
		// Update visual component if enabled
		if (isVisual[activeIndex]) {
			updateVisual(activeIndex);
		}
	}

	editors['tab' + activeIndex].codemirror.on('keyup', (cm, event) => {
		if (!jQuery("#wpda_sql_hints").is(":checked")) {
			return;
		}

		if (
			event.key==="Backspace" ||
			event.key==="Escape" ||
			event.key==="ArrowUp" ||
			event.key==="ArrowDown"
		) {
			return;
		}

		editors['tab' + activeIndex].codemirror.execCommand('autocomplete');
	});
}

function queryResult(activeIndex) {
	return `
		<div id="wpda_query_builder_menubar_${activeIndex}" class="wpda_query_builder_menubar" style="display: none">
			<label>Export to</label>
			<button class="button button-primary" onclick="exportTable('CSV', ${activeIndex})">CSV</button>
			<button class="button button-primary" onclick="exportTable('JSON', ${activeIndex})">JSON</button>
			<button class="button button-primary" onclick="exportTable('XML', ${activeIndex})">XML</button>
		</div>
		<div id="wpda_query_builder_result_${activeIndex}" class="wpda_query_builder_result"></div>
		<div id="wpda_query_builder_statusbar_${activeIndex}" style="display: none" class="wpda_query_builder_statusbar">
			<a href="javascript:void(0)" onclick="jQuery('#wpda_query_builder_viewer_${activeIndex}').toggle(); jQuery('html, body').animate({ scrollTop: jQuery(window).height()-200}, 600);" class="wpda_tooltip button button-primary" title="View raw output">
				<span class="material-icons wpda_icon_on_button">code</span></a>
			<span class="wpda_query_builder_statusbar_message"></span>
		</div>
		<div id="wpda_query_builder_viewer_${activeIndex}" style="display: none" class="wpda_query_builder_viewer">
			<pre id="wpda_query_builder_json_${activeIndex}"></pre>
		</div>
	`;
}

function tabClose(activeIndex) {
	if (isChanged[activeIndex]) {
		if (!confirm('Your changes will not be saved! Are you sure you want to leave this page?')) {
			return;
		}
	}
	jQuery("#wpda_query_builder_label_" + activeIndex).remove();
	jQuery("#wpda_query_builder_" + activeIndex).remove();
	delete editors['tab' + activeIndex];
	delete isChanged[activeIndex];
	if (jQuery(".wpda_query_builder").length>0) {
		if (jQuery(".nav-tab-active").data("id")===undefined) {
			tabActivate(jQuery(".nav-tab").data("id"));
		}
	} else {
		tabNew();
	}
}

function tabOpen() {
	var queryName = jQuery("#wpda_query_builder_open_select").find(":selected").text();
	tabNew(
		queryName,
		jQuery("#wpda_query_builder_open_select").find(":selected").data("sql"),
		jQuery("#wpda_query_builder_open_select").find(":selected").data("dbs")
	);
	closeQuery();

	if (jQuery("#wpda_query_builder_open_select").find(":selected").data("vqb")==true) {
		// Restore Visual Query Builder
		getVisualQueryBuilder(tabIndex, queryName);
	}
}

function tabOpenAll() {
	jQuery("#wpda_query_builder_open_select option").each(
		function() {
			tabNew(
				jQuery(this).text(),
				jQuery(this).data("sql"),
				jQuery(this).data("dbs")
			);
			closeQuery();
		}
	);
}

function showData(activeIndex, msg) {
	if ( msg.tabs.length > 0 ) {
		// Multiple SQL commands
		jQuery("#wpda_query_builder_result_" + activeIndex).html('');
		showTabs(activeIndex, msg);
	} else {
		// Single SQL command
		jQuery("#wpda_query_builder_tabs_" + activeIndex).empty().hide();
		showRows(activeIndex, msg);
	}
}

function queryTabClose(activeIndex, tabIndex) {
	jQuery("li#litab" + activeIndex + "-" + tabIndex).remove();
	jQuery("div#tab" + activeIndex + "-" + tabIndex).remove();
	var li = jQuery("div#tabs" + activeIndex + " ul li")[0].id;
	jQuery("#" + li).find("a").click();
}

function showTabs(activeIndex, msg) {
	var ul = jQuery("<ul/>");
	for (var i=0; i<msg.tabs.length; i++) {
		if (msg.tabs[i]['cmd']===null || msg.tabs[i]['cmd']===undefined) {
			sql = "SQL ERROR";
		} else {
			sql = msg.tabs[i]['cmd'];
		}
		ul.append(jQuery("<li/>", { "id": "litab" + activeIndex + "-" + i })
			.append(jQuery("<a/>", { "href": "#tab" + activeIndex + "-" + i, "title": sql, "class": "wpda_tooltip" })
			.html("<span class='dashicons dashicons-database-view'></span> " + (i+1) + ". sql cmd <span class='dashicons dashicons-dismiss icon_close' style='vertical-align: middle' onclick='queryTabClose(" + activeIndex + "," + i + ")'></span>")));
	}
	var tabs = jQuery("<div/>", { "id": "tabs" + activeIndex }).append(ul);

	for (var i=0; i<msg.tabs.length; i++) {
		var tabResultDiv = queryResult("" + activeIndex + i);
		var style = i===0 ? "block" : "none";
		tabs.append(jQuery("<div/>", { "id": "tab" + activeIndex + "-" + i, "style": "display:"+style })
			.append(tabResultDiv));
	}
	jQuery("#wpda_query_builder_tabs_" + activeIndex).empty().append(tabs);
	jQuery("#wpda_query_builder_tabs_" + activeIndex).show();

	for (var i=0; i<msg.tabs.length; i++) {
		showRows("" + activeIndex + i, msg.tabs[i]);
	}
	jQuery("div#tabs" + activeIndex).tabs();
	jQuery('.wpda_tooltip').tooltip();
}

function showRows(activeIndex, msg) {
	if (msg.status===null || msg.status===undefined) {
		jQuery("#wpda_query_builder_result_" + activeIndex).html("<strong>WP Data Access error:</strong> Query failed");
	} else {
		if (msg.status.last_result===null || msg.status.last_result===undefined) {
			if (typeof msg.status!=="string") {
				jQuery("#wpda_query_builder_result_" + activeIndex).html("<strong>WP Data Access error:</strong> Query OK");
			} else {
				jQuery("#wpda_query_builder_result_" + activeIndex).html(msg.status);
			}
		} else {
			if (msg.status.last_error==="") {
				if (msg.status.last_result.length > 0) {
					rows = msg.status.last_result;
					first_row = rows[0];
					header = "<tr>";
					for (var col in first_row) {
						header += "<th>" + col + "</th>";
					}
					header += "</tr>";
					body = "";
					for (var i = 0; i < rows.length; i++) {
						body += "<tr>";
						for (var col in rows[i]) {
							body += "<td>" + rows[i][col] + "</td>";
						}
						body += "</tr>";
					}
					table =
						jQuery('<table class="wpda_query_builder_table" data-id="' + activeIndex + '"/>')
						.append(jQuery('<thead/>').append(header))
						.append(jQuery('<tbody/>').append(body));
					jQuery("#wpda_query_builder_menubar_" + activeIndex).show();
					jQuery("#wpda_query_builder_result_" + activeIndex).html(table);
					rowLabel = rows.length === 1 ? "row" : "rows";
					html = rows.length + " " + rowLabel;
					if (msg.status.queries !== null) {
						html += " (" + msg.status.queries[msg.status.num_queries - 1][1].toFixed(5) + " sec)";
					}
					jQuery("#wpda_query_builder_statusbar_" + activeIndex + " span.wpda_query_builder_statusbar_message").html(
						html
					);
					jQuery("#wpda_query_builder_statusbar_" + activeIndex).show();
					jQuery("#wpda_query_builder_json_" + activeIndex).jsonViewer(msg.status);
					jQuery("#wpda_query_builder_json_" + activeIndex + " ul li a.json-toggle").click();

					setResultDivHeight(activeIndex);
				} else {
					rowLabel = msg.status.rows_affected === 1 ? "row" : "rows";
					html = "Query OK, " + msg.status.rows_affected + " " + rowLabel + " affected";
					if (msg.status.queries !== null) {
						html += " (" + msg.status.queries[msg.status.num_queries - 1][1].toFixed(5) + " sec)"
					}
					jQuery("#wpda_query_builder_result_" + activeIndex).html(
						html
					);

					jQuery("#wpda_query_builder_statusbar_" + activeIndex).show();
					jQuery("#wpda_query_builder_json_" + activeIndex).jsonViewer(msg.status);
					jQuery("#wpda_query_builder_json_" + activeIndex + " ul li a.json-toggle").click();
				}
			} else {
				error = `<strong>WordPress database error:</strong> ${msg.status.last_error}<br/><br/><code>${msg.status.last_query}</code>`;
				jQuery("#wpda_query_builder_result_" + activeIndex).html(error);
			}
		}
	}
}

function setResultDivHeight(activeIndex) {
	viewHeight = jQuery(window).height();
	positionX = jQuery("#wpda_query_builder_result_" + activeIndex).offset().top;
	if (positionX===0) {
		positionX = viewHeight/2;
	}
	divHeight = viewHeight - positionX - 140;
	if (divHeight<400) {
		divHeight = 400;
	}
	jQuery("#wpda_query_builder_result_" + activeIndex + " table.wpda_query_builder_table tbody").height(divHeight);
}

function showError(activeIndex,msg) {
	jQuery("#wpda_query_builder_menubar_" + activeIndex).hide();
	jQuery("#wpda_query_builder_result_" + activeIndex).html(msg.responseText);
	jQuery("#wpda_query_builder_statusbar_" + activeIndex).hide();
}

function executeQuery(activeIndex) {
	if (isVisual[activeIndex]) {
		var currentStatus = isChanged[activeIndex];
		if (!updateQuery(activeIndex, true)) {
			return;
		}
		isChanged[activeIndex] = currentStatus;
	}

	// Execute query
	cm = editors['tab' + activeIndex].codemirror;
	cm.save();

	sql = jQuery("#wpda_query_builder_sql_" + activeIndex).val();
	limit = '';

	if (jQuery("#use_max_rows_" + activeIndex).is(":checked")) {
		limit = jQuery("#max_rows_" + activeIndex).val();
	}

	jQuery("#executing_query_" + activeIndex).show();

	if (isVisual[activeIndex]) {
		if (isVisualQueryBuilderActive(activeIndex)) {
			jQuery("#visualOutputContainer" + activeIndex).tabs("option", "active", 1);
		}
	}

	jQuery.ajax({
		method: 'POST',
		url: wpda_home_url + "?action=wpda_query_builder_execute_sql",
		data: {
			wpda_wpnonce: wpda_wpnonce,
			wpda_schemaname: jQuery("#wpda_query_builder_dbs_" + activeIndex).val(),
			wpda_sqlquery: sql,
			wpda_sqllimit: limit,
			wpda_protect: jQuery("#wpda_query_builder_wordpress_protect_" + activeIndex).is(":checked")
		}
	}).done(
		function (msg) {
			jQuery("#executing_query_" + activeIndex).hide();
			showData(activeIndex, msg);
		}
	).fail(
		function (msg) {
			jQuery("#executing_query_" + activeIndex).hide();
			showError(activeIndex, msg);
		}
	);
}

function saveQuery(activeIndex) {
	// Save query
	cm = editors['tab' + activeIndex].codemirror;
	cm.save();

	var data = {
		wpda_wpnonce: wpda_wpnonce,
		wpda_schemaname: jQuery("#wpda_query_builder_dbs_" + activeIndex).val(),
		wpda_sqlqueryname: jQuery("#wpda_query_builder_label_value_" + activeIndex).html(),
		wpda_sqlqueryname_old: jQuery("#wpda_query_builder_label_value_" + activeIndex).data("dbs-name"),
		wpda_sqlquery: jQuery("#wpda_query_builder_sql_" + activeIndex).val()
	};

	if (isVisual[activeIndex]) {
		data.wpda_vqb = getWidgets(activeIndex);
	}

	jQuery.ajax({
		method: 'POST',
		url: wpda_home_url + "?action=wpda_query_builder_save_sql",
		data: data
	}).done(
		function (msg) {
			jQuery("#wpda_query_builder_label_value_" + activeIndex)
				.attr("data-dbs-name", jQuery("#wpda_query_builder_label_value_" + activeIndex).text());
			isChanged[activeIndex] = false;
			jQuery.notify('Query saved', 'success');
		}
	).fail(
		function (msg) {
			console.log(activeIndex, msg);
			jQuery.notify('Could not save query', 'error');
		}
	);
}

function openQuery() {
	activeDbsNames = [];
	jQuery(".wpda_query_builder_label_value").each(
		function() {
			activeDbsNames.push(jQuery(this).data("dbs-name"));
		}
	);

	jQuery.ajax({
		method: 'POST',
		url: wpda_home_url + "?action=wpda_query_builder_open_sql",
		data: {
			wpda_wpnonce: wpda_wpnonce,
			wpda_exclude: activeDbsNames.join(",")
		}
	}).done(
		function (msg) {
			jQuery("#wpda_query_builder_open_select").find("option").remove();
			if (!Array.isArray(msg.data)) {
				for (var queryName in msg.data) {
					jQuery("#wpda_query_builder_open_select")
					.append(
						jQuery("<option/>", {
							value: queryName,
							text: queryName
						})
						.attr("data-dbs", msg.data[queryName].schema_name)
						.attr("data-sql", msg.data[queryName].query)
						.attr("data-vqb", msg.data[queryName].is_visual===true)
					);
				}
				jQuery("#wpda_query_builder_open_select").attr("disabled", false);
				jQuery("#wpda_query_builder_open_open").attr("disabled", false);
				jQuery("#wpda_query_builder_open_openall").attr("disabled", false);
				jQuery("#wpda_query_builder_open_delete").attr("disabled", false);
			} else {
				jQuery("#wpda_query_builder_open_select")
				.append(
					jQuery("<option/>", {
						value: "",
						text: "Nothing found..."
					})
				);
				jQuery("#wpda_query_builder_open_select").attr("disabled", true);
				jQuery("#wpda_query_builder_open_open").attr("disabled", true);
				jQuery("#wpda_query_builder_open_openall").attr("disabled", true);
				jQuery("#wpda_query_builder_open_delete").attr("disabled", true);
			}
		}
	).fail(
		function (msg) {
			console.log("ERROR");
			console.log(msg);
		}
	);

	jQuery("#wpda_query_builder_open").show();
}

function closeQuery() {
	jQuery("#wpda_query_builder_open").hide();
}

function deleteQuery() {
	if ( confirm("Delete query? This action cannot be undone!") ) {
		wpda_sqlqueryname = jQuery("#wpda_query_builder_open_select").find(":selected").text();

		jQuery.ajax({
			method: 'POST',
			url: wpda_home_url + "?action=wpda_query_builder_delete_sql",
			data: {
				wpda_wpnonce: wpda_wpnonce,
				wpda_sqlqueryname: wpda_sqlqueryname
			}
		}).done(
			function (msg) {
				closeQuery();
			}
		).fail(
			function (msg) {
				console.log(msg);
			}
		);
	}
}

function exportTable(exportType, tabIndex) {
	switch (exportType) {
		case "CSV":
			downloadCSV(
				jQuery("#wpda_query_builder_result_" + tabIndex + " table").html(),
				jQuery("#wpda_query_builder_label_value_" + tabIndex).text() + ".csv"
			);
			break;
		case "JSON":
			downloadJSON(
				jQuery("#wpda_query_builder_result_" + tabIndex + " table").html(),
				jQuery("#wpda_query_builder_label_value_" + tabIndex).text() + ".json"
			);
			break;
		case "XML":
			downloadXML(
				jQuery("#wpda_query_builder_result_" + tabIndex + " table").html(),
				jQuery("#wpda_query_builder_label_value_" + tabIndex).text() + ".xml"
			);
	}
}

function downloadCSV(html, fileName) {
	csv = [];
	rows = jQuery(html).find("tr");
	for (i=0; i<rows.length; i++) {
		row = [];
		cols = jQuery(rows[i]).find("td, th");
		for (j=0; j<cols.length; j++) {
			row.push(cols[j].innerText);
		}
		csv.push(row);
	}
	downloadExport(fileName, "text/csv", encodeURIComponent(csv.join("\n")));
}

function createXML(html, fileName) {
	headerCols = [];
	header = jQuery(html).find("tr th");
	body = jQuery(html)[1];
	bodyRows = jQuery(body).find("tr");
	table = jQuery("<table/>");
	for (i=0; i<header.length; i++) {
		headerCols.push(header[i].innerText);
	}
	for (i=0; i<bodyRows.length; i++) {
		bodyCols = jQuery(bodyRows[i]).find("td");
		row = jQuery("<rows/>");
		for (j=0; j<bodyCols.length; j++) {
			row.append(jQuery("<" + headerCols[j] + "/>").text(bodyCols[j].innerText));
		}
		table.append(row);
	}
	xml = jQuery("<xml/>").append(table);
	return jQuery.parseXML(xml[0].outerHTML);
}

function downloadJSON(html, fileName) {
	xmlDoc = createXML(html, fileName);
	json = jQuery.xml2json(new XMLSerializer().serializeToString(xmlDoc.documentElement));
	downloadExport(fileName, "text/json", JSON.stringify(json));
}

function downloadXML(html, fileName) {
	xmlDoc = createXML(html, fileName);
	downloadExport(fileName, "text/xml", new XMLSerializer().serializeToString(xmlDoc.documentElement));
}

function downloadExport(fileName, mimeType, content) {
	download = jQuery("<a/>", {
		href: "data:" + mimeType + ";charset=utf-8," + content,
		download: fileName
	}).appendTo('body');
	download[0].click();
	download.remove();
}

function unsavedChanges() {
	hasEdited = false;
	for (var tabindex in isChanged) {
		if (isChanged[tabindex]===true) {
			hasEdited = true;
		}
	}
	return hasEdited;
}

jQuery(window).on('keydown', function(event) {
	if ((event.ctrlKey || event.metaKey) && String.fromCharCode(event.which).toLowerCase()==='s') {
		if (jQuery(event.target).hasClass('CodeMirror-code')) {
			saveQuery(jQuery(event.target).closest(".wpda_query_builder").data("id"))
			event.preventDefault();
		}
	}
});

jQuery(window).on('beforeunload', function() {
	if (unsavedChanges()) {
		return 'Your changes will not be saved! Are you sure you want to leave this page?';
	}
});

