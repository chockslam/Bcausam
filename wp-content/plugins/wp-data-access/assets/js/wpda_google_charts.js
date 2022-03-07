// ComboChart
// DonutChart
const CHART_DEFAULT_HINT_1PN = "FORMAT\n\nFirst column: string or date\nOther columns: numeric\n\nAt least 1 string or date and 1 numeric column required\n\nEXAMPLES\n\nselect City, Population from population\nselect City, Population, Density from population";
const CHART_DEFAULT_HINT_1 = "FORMAT\n\nFirst column: string or date\nSecond column: numeric\n\nAdditional columns are not used\n\nEXAMPLES\n\nselect City, Population from population";
const CHART_TYPES = {
	"BarChart": {
		label: "Bar Chart",
		hint: CHART_DEFAULT_HINT_1PN
	},
	"ColumnChart": {
		label: "Column Chart",
		hint: CHART_DEFAULT_HINT_1PN
	},
	"Histogram": {
		label: "Histogram",
		hint: CHART_DEFAULT_HINT_1PN
	},
	"LineChart": {
		label: "Line Chart",
		hint: CHART_DEFAULT_HINT_1PN
	},
	"PieChart": {
		label: "Pie Chart",
		hint: CHART_DEFAULT_HINT_1
	},
	"Gauge": {
		label: "Gauge",
		hint: CHART_DEFAULT_HINT_1
	},
	"Table": {
		label: "Table",
		hint: "FORMAT\n\nAny valid query\n\nEXAMPLES\n\nselect * from customers\nselect ename as 'Employee Name', sal as 'Salary' from emp"
	}
};

const CHART_DEFAULT_LEGEND = 'bottom';
const CHART_ADD_LINENO = "Yes";
const CHART_DEFAULT_WIDTH = "*";
const CHART_DEFAULT_WIDTH_PX = 500;
const CHART_DEFAULT_HEIGHT = "*";
const CHART_DEFAULT_HEIGHT_PX = 300;
const CHARTAREA_DEFAULT_WIDTH = 100;
const CHARTAREA_DEFAULT_HEIGHT = 100;
const CHARTAREA_DEFAULT_TOP = 10;
const CHARTAREA_DEFAULT_LEFT = 100;

var googleChartsLoaded = false;
var googleChartsObjects = {};
var cachedChartData = {};

function getChartData(widgetId, forceUpdate = false) {
	wpda_dbs = dashboardWidgets[widgetId].chartDbs;
	wpda_query = dashboardWidgets[widgetId].chartSql;
	jQuery.ajax({
        type: "POST",
        url: wpda_chart_vars.wpda_ajaxurl + "?action=wpda_widget_chart_refresh",
        data: {
            wp_nonce: wpda_wpnonce_refresh,
			wpda_action: 'get_data',
			wpda_name: dashboardWidgets[widgetId].widgetName,
			wpda_force_update: forceUpdate
        }
    }).done(
        function(data) {
			if (data.status==="ERROR" && data.msg!==undefined) {
				alert("ERROR: " + data.msg);
			} else {
				if (data.error !== "") {
					alert("ERROR: " + data.error);
				} else {
					if (jQuery("#wpda_widget_container_" + widgetId).html() === "") {
						setUserChartSelection(widgetId);

						jQuery("#wpda_widget_chart_selection_" + widgetId + " option").remove();
						jQuery.each(dashboardWidgets[widgetId].chartType, function (i, item) {
							jQuery("#wpda_widget_chart_selection_" + widgetId).append(jQuery("<option/>", {
								value: item,
								text: CHART_TYPES[item].label
							}));
						});

						if (dashboardWidgets[widgetId].chartType.length > 1) {
							chartType = dashboardWidgets[widgetId].chartType[0];
						} else {
							chartType = dashboardWidgets[widgetId].chartType[0];
						}

						createChart(
							chartType,
							widgetId,
							data.cols,
							data.rows
						);
					} else {
						refreshChart(widgetId);
					}
				}
			}
        }
    );
}

function chartOptions(widgetId) {
	if (
		dashboardWidgets[widgetId]!==undefined &&
		dashboardWidgets[widgetId].chartOptions!==undefined &&
		dashboardWidgets[widgetId].chartOptions!==null

	) {
		dashboardWidgets[widgetId].chartOptions.page = 'enable';

		return dashboardWidgets[widgetId].chartOptions;
	} else {
		return {
			legend: {
				position: CHART_DEFAULT_LEGEND
			},
			width: CHART_DEFAULT_WIDTH,
			height: CHART_DEFAULT_HEIGHT,
			page: 'enable'
		};
	}
}

function createChart(outputType, widgetId, columns, rows) {
	cachedChartData[widgetId] = new google.visualization.DataTable({
		cols: columns,
		rows: rows
	});
	addChart(widgetId, outputType);
}

function refreshChart(widgetId) {
	jQuery("#wpda_widget_container_" + widgetId).empty();
	addChart(widgetId, jQuery("#wpda_widget_chart_selection_" + widgetId).val());
}

function printableVersion(url){
	let win = window.open();
	win.document.write('<iframe src="' + url  + '" frameborder="0" style="border:0; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%;" allowfullscreen></iframe>');
}

function addChart(widgetId, outputType) {
	var element = document.getElementById("wpda_widget_container_" + widgetId);
	googleChartsObjects[widgetId] = new google.visualization[outputType](element);

	google.visualization.events.addListener(googleChartsObjects[widgetId], 'ready', function () {
		jQuery("#wpda-widget-" + widgetId + " .wpda-chart-button-print").hide().off();
		if (outputType!=="Table") {
			// Enable print button for charts
			jQuery("#wpda-widget-" + widgetId + " .wpda-chart-button-print").show().on("click", function() {
				printableVersion(googleChartsObjects[widgetId].getImageURI());
			});
		}

		// Create hyperlink with CSV from table data
		let csv = google.visualization.dataTableToCsv(cachedChartData[widgetId]);
		let url = "data:application/csv;charset=utf-8," + encodeURIComponent(csv);
		jQuery("#wpda-widget-" + widgetId + " .wpda-chart-button-export-link")
		.attr("href", url)
		.attr("download", "wp-data-access.csv");

		// Open hyperlink on click
		jQuery("#wpda-widget-" + widgetId + " .wpda-chart-button-export").on("click", function() {
			jQuery("#wpda-widget-" + widgetId + " .wpda-chart-button-export-link")[0].click();
		});
	});

	googleChartsObjects[widgetId].draw(cachedChartData[widgetId], chartOptions(widgetId));
}

function setUserChartSelection(widgetId, action = null) {
	if (action==="hide") {
		// Hide in settings mode
		jQuery("#wpda_widget_container_" + widgetId).parent().find(".wpda_widget_chart_selection").hide();
	} else {
		// Show in view mode
		jQuery("#wpda_widget_container_" + widgetId).parent().find(".wpda_widget_chart_selection").show();
	}

	// Show drop down list only if it contains more than 1 option
	if (dashboardWidgets[widgetId] && dashboardWidgets[widgetId].chartType.length > 1) {
		jQuery("#wpda_widget_chart_selection_" + widgetId).show();
	} else {
		jQuery("#wpda_widget_chart_selection_" + widgetId).hide();
	}
}

function chartTypeOption(id, widgetId) {
	return `
		<li title="${CHART_TYPES[id].hint}" class="wpda_tooltip">
			<input type="checkbox" id="${id}_${widgetId}" data-id="${id}"/>
			<label for="${id}_${widgetId}">
				<img src="${wpda_chart_vars.wpda_chartdir}${id}.png"/>
			</label>
		</li>`;
}

function chartTypeOptions(chartTypes, widgetId) {
	list = "";
	for (var i=0; i<chartTypes.length; i++) {
		list += chartTypeOption(chartTypes[i], widgetId);
	}
	return list;
}

function chartSettings(widgetId) {
	var query = "";
	var chartTypes = Object.keys(CHART_TYPES);
	var realtime = 'checked';
	var cache = '';

	if (dashboardWidgets[widgetId]) {
		if (dashboardWidgets[widgetId].chartSql) {
			query = dashboardWidgets[widgetId].chartSql;
		}

		if (dashboardWidgets[widgetId].userChartTypeList) {
			chartTypesCurrent = dashboardWidgets[widgetId].userChartTypeList;
			for (var i=0; i<chartTypes.length; i++) {
				if (!chartTypesCurrent.includes(chartTypes[i])) {
					chartTypesCurrent.push(chartTypes[i]);
				}
			}
			chartTypes = chartTypesCurrent;
		}

		if (dashboardWidgets[widgetId].chartRefresh) {
			if (dashboardWidgets[widgetId].chartRefresh==='realtime') {
				realtime = 'checked';
				cache = '';
			} else {
				realtime = '';
				cache = 'checked';
			}
		}
	}

	if (jQuery("#wpda_widget_container_" + widgetId).is(":visible")) {
		jQuery("#wpda_widget_container_" + widgetId).hide();
		setUserChartSelection(widgetId, "hide");

		jQuery("#wpda_widget_container_" + widgetId).parent().append(`
			<div class="wpda-settings">
				<div class="wpda-dashboard-chart-settings">
					<fieldset class="wpda_fieldset">
						<legend>
							SQL Query
						</legend>
						<div>
							<select id="wpda_chart_dbs_${widgetId}" class="wpda_chart_dbs">
								${wpda_databases}
							</select>
							<button class="button wpda_insert_query_builder wpda_tooltip" title="Get SQL from Query Builder"><i class="fas fa-tools"></i> Query Builder</button>
						</div>
						<div>
							<textarea id="wpda_chart_sql_${widgetId}" class="wpda_chart_sql">${query}</textarea>
						</div>
						<div>
							<span class="material-icons" style="vertical-align: middle">info</span>
							<span style="vertical-align: middle">Keep your data sets small. Use the Data Publisher for large data sets and server side processing.</span>
						</div>
					</fieldset>
					<fieldset class="wpda_fieldset">
						<legend>
							Table or chart type
						</legend>
						<ul class="wpda_google_chart_types">
							${chartTypeOptions(chartTypes, widgetId)}
						</ul>
					</fieldset>
					<fieldset class="wpda_fieldset" style="display:none" id="wpda_query_refresh_frequency_${widgetId}">
						<legend>
							Query refresh frequency
						</legend>
						<div>
							<label>
								<input type="radio" id="wpda_refresh_frequency_${widgetId}" name="wpda_refresh_frequency_${widgetId}" value="realtime" ${realtime} />
								Always show data in real time (does not cache query results)
							</label>
							<br/>
							<label>
								<input type="radio" name="wpda_refresh_frequency_${widgetId}" value="cache" ${cache} />
								Update every
								<input type="number" id="wpda_refresh_frequency_cache_${widgetId}" value="24" style="vertical-align:baseline" />
								<select id="wpda_refresh_frequency_unit_${widgetId}" style="vertical-align:baseline">
									<option value="min">minutes</option>
									<option value="hours" selected>hours</option>
									<option value="days">days</option>
								</select>
							</label>
						</div>
					</fieldset>
					<div class="wpda-dashboard-chart-settings-buttons">
						<button class="button button-primary wpda-button-ok">OK</button>
						<button class="button wpda-button-cancel">Cancel</button>
					</div>
				</div>
			</div>
		`);

		if (wpda_chart_vars.wpda_premium==="true") {
			jQuery("#wpda_query_refresh_frequency_" + widgetId).show();
		}

		if (dashboardWidgets[widgetId]) {
			if (dashboardWidgets[widgetId].chartDbs && dashboardWidgets[widgetId].chartDbs!==null) {
				jQuery("#wpda_chart_dbs_" + widgetId).val(dashboardWidgets[widgetId].chartDbs);
			}

			for (var i=0; i<dashboardWidgets[widgetId].chartType.length; i++) {
				jQuery("#" + dashboardWidgets[widgetId].chartType[i] + "_" + widgetId).prop("checked", true);
			}

			if (dashboardWidgets[widgetId].chartCache) {
				jQuery("#wpda_refresh_frequency_cache_" + widgetId).val(dashboardWidgets[widgetId].chartCache);
			}

			if (dashboardWidgets[widgetId].chartUnit) {
				jQuery("#wpda_refresh_frequency_unit_" + widgetId).val(dashboardWidgets[widgetId].chartUnit);
			}
		}

		jQuery("#wpda_widget_container_" + widgetId).parent().find(".wpda_insert_query_builder").on("click", function() {
			getSQLFromQueryBuilder(wpda_wpnonce_qb, widgetId);
		})

		jQuery("#wpda_widget_container_" + widgetId).parent().find(".wpda-button-ok").on("click", function() {
			if (jQuery("#wpda_chart_sql_" + widgetId).val().trim()==='') {
				alert("Please enter a valid query");
				return;
			}

			var obj = {};
			obj.chartType = jQuery("#wpda-widget-" + widgetId + " ul.wpda_google_chart_types input[type='checkbox']:checked").map(function() {
				return jQuery(this).data("id");
			}).get();
			if (obj.chartType.length===0) {
				alert("You must select at least one table or chart type");
				return;
			}

			obj.userChartTypeList = jQuery("#wpda-widget-" + widgetId + " ul.wpda_google_chart_types input[type='checkbox']").map(function() {
				return jQuery(this).data("id");
			}).get();
			obj.chartDbs = jQuery("#wpda_chart_dbs_" + widgetId).val();
			obj.chartSql = jQuery("#wpda_chart_sql_" + widgetId).val();
			if (jQuery("#wpda_refresh_frequency_" + widgetId).is(":checked")) {
				obj.chartRefresh = "realtime";
			} else {
				obj.chartRefresh = "cache";
			}
			obj.chartCache = jQuery("#wpda_refresh_frequency_cache_" + widgetId).val();
			obj.chartUnit = jQuery("#wpda_refresh_frequency_unit_" + widgetId).val();

			var share = null;
			if (
				dashboardWidgets[widgetId]!==undefined &&
				dashboardWidgets[widgetId].chartOptions!==undefined &&
				dashboardWidgets[widgetId].chartOptions!==null
			) {
				obj.chartOptions = dashboardWidgets[widgetId].chartOptions;
				share = dashboardWidgets[widgetId].widgetShare;
			}

			jQuery("#wpda_widget_container_" + widgetId).empty(); // force update
			addDashboardWidget(
				widgetId,
				jQuery("#wpda-widget-" + widgetId).data("name"),
				"chart",
				share,
				obj
			);
			saveDashBoard(function() { getChartData(widgetId, true) });

			jQuery("#wpda_widget_container_" + widgetId).show();
			jQuery("#wpda_widget_container_" + widgetId).parent().find(".wpda-settings").remove();
		});

		jQuery("#wpda_widget_container_" + widgetId).parent().find(".wpda-button-cancel").on("click", function() {
			if (dashboardWidgets[widgetId] == undefined) {
				removePanelFromDashboardAction(jQuery(this).closest('.wpda-widget'));
			} else {
				jQuery("#wpda_widget_container_" + widgetId).parent().find(".wpda-settings").remove();
				jQuery("#wpda_widget_container_" + widgetId).show();
				setUserChartSelection(widgetId);
			}
		});

		jQuery(".wpda_google_chart_types").sortable({
			connectWith: ".wpda_google_chart_types",
			cursor: "move",
			opacity: 0.4,
			change: function(event, ui) {
				ui.placeholder.css({visibility: "visible", background : "#ccc"});
			}
		});

		jQuery(".wpda_tooltip").tooltip({
			tooltipClass: "wpda_tooltip_dashboard",
		});
	}
}

function chartLayout(widgetId) {
	var widgetName = "";
	var chartTitle = "";
	var charLineNo = CHART_ADD_LINENO;
	var chartLegend = CHART_DEFAULT_LEGEND;
	var chartWidth = CHART_DEFAULT_WIDTH;
	var chartWidthPx = CHART_DEFAULT_WIDTH_PX;
	var chartHeight = CHART_DEFAULT_HEIGHT;
	var chartHeightPx = CHART_DEFAULT_HEIGHT_PX;
	var chartArea = "";
	var chartAreaWidth = CHARTAREA_DEFAULT_WIDTH;
	var chartAreaHeight = CHARTAREA_DEFAULT_HEIGHT;
	var chartAreaTop = CHARTAREA_DEFAULT_TOP;
	var chartAreaLeft = CHARTAREA_DEFAULT_LEFT;

	if (dashboardWidgets[widgetId]!==undefined) {
		if (dashboardWidgets[widgetId].widgetName!==undefined) {
			widgetName = dashboardWidgets[widgetId].widgetName;
		}
		if (
			dashboardWidgets[widgetId].chartOptions!==undefined &&
			dashboardWidgets[widgetId].chartOptions!==null
		) {
			if (dashboardWidgets[widgetId].chartOptions.title!==undefined) {
				chartTitle = dashboardWidgets[widgetId].chartOptions.title;
			}
			if (dashboardWidgets[widgetId].chartOptions.showRowNumber!==undefined) {
				if (
					dashboardWidgets[widgetId].chartOptions.showRowNumber==="true" ||
					dashboardWidgets[widgetId].chartOptions.showRowNumber===true
				) {
					charLineNo = "Yes";
				} else {
					charLineNo = "No";
				}
			}
			if (
				dashboardWidgets[widgetId].chartOptions.legend!==undefined &&
				dashboardWidgets[widgetId].chartOptions.legend.position!==undefined
			) {
				chartLegend = dashboardWidgets[widgetId].chartOptions.legend.position;
			}
			if (dashboardWidgets[widgetId].chartOptions.width!==undefined) {
				chartWidth = dashboardWidgets[widgetId].chartOptions.width;
				if (chartWidth!=="*") {
					chartWidthPx = dashboardWidgets[widgetId].chartOptions.width;
				}
			}
			if (dashboardWidgets[widgetId].chartOptions.height!==undefined) {
				chartHeight = dashboardWidgets[widgetId].chartOptions.height;
				if (chartHeight!=="*") {
					chartHeightPx = dashboardWidgets[widgetId].chartOptions.height;
				}
			}
			if (dashboardWidgets[widgetId].chartOptions.chartArea!==undefined) {
				chartArea = "checked";
				chartAreaWidth = dashboardWidgets[widgetId].chartOptions.chartArea.width.replace("%","");
				chartAreaHeight = dashboardWidgets[widgetId].chartOptions.chartArea.height.replace("%","");
				chartAreaTop = dashboardWidgets[widgetId].chartOptions.chartArea.top;
				chartAreaLeft = dashboardWidgets[widgetId].chartOptions.chartArea.left;
			}
		}
	} else {
		// Widget name not yet available on insert: grab title from widget header
		widgetName = jQuery("#wpda-widget-" + widgetId).data("name");
	}

	var lineNoYes = charLineNo==="Yes" ? "checked" : "";
	var lineNoNo = charLineNo!=="Yes" ? "checked" : "";

	var fullWidth = chartWidth==="*" ? "checked" : "";
	var customWidth = ! fullWidth ? "checked" : "";

	var fullHeight = chartHeight==="*" ? "checked" : "";
	var customHeight = ! fullHeight ? "checked" : "";

	var dialogHtml = `
        <div class="wpda-dailog-layout">
			<fieldset class="wpda_fieldset">
				<legend>
					Widget
				</legend>
				<div>
					<label htmlFor="wpda_chart_title_${widgetId}">
						Title
					</label>
					<input type="text"
						   id="wpda_chart_title_${widgetId}"
						   value="${chartTitle}"
						   placeholder="A title is optional..."
					/>
				</div>
				<div>
					<label for="wpda_chart_legend_${widgetId}">
						Legend
					</label>
					<select id="wpda_chart_legend_${widgetId}">
						<option value="top">top</option>
						<option value="bottom">bottom</option>
						<option value="right">right</option>
						<option value="left">left</option>
						<option value="none">none</option>
					</select>
				</div>
				<div>
					<label for="wpda_chart_lineno_${widgetId}">
						Add row no
					</label>
					<label>
						<input type="radio"
							   value="yes"
							   id="wpda_chart_lineno_yes_${widgetId}"
							   name="wpda_chart_lineno_${widgetId}"
							   ${lineNoYes}
						/> Yes
					</label>
					<label>
						<input type="radio"
							   value="no"
							   name="wpda_chart_lineno_${widgetId}"
							   ${lineNoNo}
						/> No
					</label>
					<span style="float: right">(tables only)</span>
				</div>
			</fieldset>
			<br/>
			<fieldset class="wpda_fieldset">
				<legend>
					Widget size
				</legend>
				<div>
					<label htmlFor="wpda_chart_width_${widgetId}">
						Width
					</label>
					<input type="radio"
						   value="*"
						   id="wpda_chart_width_select_${widgetId}"
						   name="wpda_chart_width_select_${widgetId}"
						   ${fullWidth}
					/>
					<input type="text"
						   value="Fit to container (100%)"
						   readOnly
					/>
				</div>
				<div>
					<label></label>
					<input type="radio" value="val" name="wpda_chart_width_select_${widgetId}" ${customWidth} />
					<input type="number" id="wpda_chart_width_${widgetId}" value="${chartWidthPx}"/> px
				</div>
				<div>
					<label htmlFor="wpda_chart_height_${widgetId}">
						Height
					</label>
					<input type="radio"
						   value="*"
						   id="wpda_chart_height_select_${widgetId}"
						   name="wpda_chart_height_select_${widgetId}"
						   ${fullHeight}
					/>
					<input type="text"
						   value="Fit to container (100%)"
						   readOnly
					/>
				</div>
				<div>
					<label></label>
					<input type="radio" value="val" name="wpda_chart_height_select_${widgetId}" ${customHeight} />
					<input type="number" id="wpda_chart_height_${widgetId}" value="${chartHeightPx}"/> px
				</div>
			</fieldset>
			<br/>
			<fieldset class="wpda_fieldset">
				<legend>
					<input type="checkbox" id="wpda_chartarea_${widgetId}" ${chartArea} /> Chart area (uncheck to use defaults)
				</legend>
				<div>
					<label for="wpda_chartarea_width_${widgetId}">
						Width
					</label>
					<input id="wpda_chartarea_width_${widgetId}" type="number" min="0" max="100" value="${chartAreaWidth}" /> %
				</div>
				<div>
					<label for="wpda_chartarea_height_${widgetId}">
						Height
					</label>
					<input id="wpda_chartarea_height_${widgetId}" type="number" min="0" max="100" value="${chartAreaHeight}" /> %
				</div>
				<div>
					<label for="wpda_chartarea_top_${widgetId}">
						Top
					</label>
					<input id="wpda_chartarea_top_${widgetId}" type="number" min="0" max="100" value="${chartAreaTop}" /> px
				</div>
				<div>
					<label for="wpda_chartarea_left_${widgetId}">
						Left
					</label>
					<input id="wpda_chartarea_left_${widgetId}" type="number" min="0" max="100" value="${chartAreaLeft}" /> px
				</div>
			</fieldset>
        </div> 
    `;

	var dialog = jQuery("<div/>").html(dialogHtml).dialog({
		title: "Widget layout: " + widgetName,
		width: "max-content",
		modal: true,
		buttons: {
			"OK": function() {
				saveOptions(widgetId);
				refreshChart(widgetId);
				dialog.dialog("destroy");
			},
			"Apply":  function() {
				saveOptions(widgetId);
				refreshChart(widgetId);
			},
			"Cancel":  function() {
				dialog.dialog("destroy");
			}
		}
	});

	jQuery('#wpda_chart_legend_' + widgetId + ' option[value="' + chartLegend + '"]').prop("selected", true);
}

function saveOptions(widgetId) {
	var options = {};

	options.title = jQuery("#wpda_chart_title_" + widgetId).val();
	options.legend = {};
	options.legend.position = jQuery("#wpda_chart_legend_" + widgetId).val();
	if (jQuery("#wpda_chart_width_select_" + widgetId).is(":checked")) {
		options.width = "*";
	} else {
		options.width = jQuery("#wpda_chart_width_" + widgetId).val();
	}
	if (jQuery("#wpda_chart_height_select_" + widgetId).is(":checked")) {
		options.height = "*";
	} else {
		options.height = jQuery("#wpda_chart_height_" + widgetId).val();
	}

	if (jQuery("#wpda_chartarea_" + widgetId).is(":checked")) {
		options.chartArea = {};
		options.chartArea.width = jQuery("#wpda_chartarea_width_" + widgetId).val() + "%";
		options.chartArea.height = jQuery("#wpda_chartarea_height_" + widgetId).val() + "%";
		options.chartArea.top = jQuery("#wpda_chartarea_top_" + widgetId).val();
		options.chartArea.left = jQuery("#wpda_chartarea_left_" + widgetId).val();
	}

	// TODO Add custom options...
	// options.is3D = true;
	// options.vAxes = {
	// 	0: {baseline: 0}
	// };
	// console.log(options);

	options.showRowNumber = jQuery("#wpda_chart_lineno_yes_" + widgetId).is(":checked");

	dashboardWidgets[widgetId].chartOptions = options;
	saveDashBoard();
}

jQuery(function() {
	google.charts.load(
		"current", {
			"packages": [
				"table",
				"corechart",
				"gauge"
			]
		}
	);

	google.charts.setOnLoadCallback(function() {
		googleChartsLoaded = true;
	});
});
