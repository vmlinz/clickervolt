class ClickerVoltStatsFunctions {

    /**
     * 
     * @param {object} settings 
     */
    static initStatsTable(settings) {

        var defaultSettings = {
            containerSelector: '#stats-table',
            showTotals: true,
            segmentColumnName: 'Segment',
            ajaxSource: 'wp_ajax_clickervolt_get_stats',
            ajaxData: {
                segments: [
                    // clickerVoltVars.const.ReportingSegments.TYPE_LINK
                ],
                linkIdFilter: null,
                sourceIdFilter: null,
                options: {
                },
                dateStart: "2000-01-01",
                dateEnd: "2100-12-31",
            },
            ajaxDataCallback: null,
            ajaxRenderedback: null,
            datePickerSelector: null,
            datePickerSyncGroup: null,      // Date pickers from the same group will be synced together - if one changes its period, others will too.
            onDateChanged: function (start, end) { },
            dataTableOptions: {
                // preDrawCallback: function( settings ) {
                //     var $table = jQuery(this.api().table().body()).closest('table');
                //     ClickerVoltStatsFunctions.heatmapCalculateMinMax( $table );
                // },
                createdRow: function (row, data, dataIndex) {
                    // var $table = jQuery(this.api().table().body()).closest('table');
                    // ClickerVoltStatsFunctions.heatmapCalculateMinMax( $table );                    
                    // ClickerVoltStatsFunctions.heatmapApply( $table, row, data );
                },
                rowCallback: function (row, data, dataIndex) {
                    // var $table = jQuery(this.api().table().body()).closest('table');
                    // ClickerVoltStatsFunctions.heatmapCalculateMinMax( $table );
                    // ClickerVoltStatsFunctions.heatmapApply( $table, row, data );
                },
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api()
                    var $table = jQuery(api.table().body()).closest('table');

                    var indexCost = ClickerVoltStatsFunctions.getColumnIndex($table, ClickerVoltStatsFunctions.COLUMN_COST);
                    var indexRevenue = ClickerVoltStatsFunctions.getColumnIndex($table, ClickerVoltStatsFunctions.COLUMN_ACTIONS_REVENUE);
                    var indexProfit = ClickerVoltStatsFunctions.getColumnIndex($table, ClickerVoltStatsFunctions.COLUMN_PROFIT);
                    var indexROI = ClickerVoltStatsFunctions.getColumnIndex($table, ClickerVoltStatsFunctions.COLUMN_ROI);

                    var columnsToSum = [
                        ClickerVoltStatsFunctions.getColumnIndex($table, ClickerVoltStatsFunctions.COLUMN_CLICKS),
                        ClickerVoltStatsFunctions.getColumnIndex($table, ClickerVoltStatsFunctions.COLUMN_UNIQUE_CLICKS),
                        ClickerVoltStatsFunctions.getColumnIndex($table, ClickerVoltStatsFunctions.COLUMN_ACTIONS),
                        indexRevenue,
                        indexCost,
                        indexProfit
                    ];

                    // converting to interger to find total
                    var numVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    var totalCost = 0;
                    var totalProfit = 0;
                    for (var i = 0; i < columnsToSum.length; i++) {
                        var colIndex = columnsToSum[i];
                        var colTotal = api
                            .column(colIndex)
                            .data()
                            .reduce(function (a, b) {
                                return numVal(a) + numVal(b);
                            }, 0);

                        var digits = 0;
                        switch (colIndex) {
                            case indexCost:
                                totalCost = colTotal;
                                digits = 2;
                                break;
                            case indexRevenue:
                                digits = 2;
                                break;
                            case indexProfit:
                                totalProfit = colTotal;
                                digits = 2;
                                break;
                        }
                        jQuery(api.column(colIndex).footer()).text(ClickerVoltStatsFunctions.formatNumber(colTotal, digits));
                    }
                    var ROI = ClickerVoltStatsFunctions.calculateROI(totalCost, totalProfit);
                    jQuery(api.column(indexROI).footer()).text(ROI);
                },
            }
        };

        if (!settings) {
            settings = {};
        }

        settings = jQuery.extend(true, {}, defaultSettings, settings);

        if (settings.datePickerSelector !== null) {

            settings.ajaxData.dateStart = moment().format('YYYY-MM-DD');
            settings.ajaxData.dateEnd = moment().format('YYYY-MM-DD');

            jQuery(settings.datePickerSelector).daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 2 Days': [moment().subtract(1, 'days'), moment()],
                    'Last 3 Days': [moment().subtract(2, 'days'), moment()],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 14 Days': [moment().subtract(13, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('month')],
                    'Last Year': [moment().startOf('year').subtract(1, 'year'), moment().endOf('year').subtract(1, 'year')],
                    'All Time': [moment("2000/01/01", "YYYY/MM/DD"), moment().endOf('month')],
                },
                locale: {
                    format: 'YYYY-MM-DD'
                },
                showDropdowns: true,
                linkedCalendars: false,
                // buttonClasses: 'button button-calendar',          
                // applyButtonClasses: 'button-primary',          
            });

            if (settings.datePickerSyncGroup) {

                var syncGroups = ClickerVoltFunctions.getOption('datepicker-sync-groups');
                if (!syncGroups) {
                    syncGroups = {};
                }

                if (!syncGroups[settings.datePickerSyncGroup]) {
                    syncGroups[settings.datePickerSyncGroup] = [];
                }

                syncGroups[settings.datePickerSyncGroup].push({
                    selector: settings.datePickerSelector,
                    settings: settings
                });

                ClickerVoltFunctions.setOption('datepicker-sync-groups', syncGroups);
            }

            settings.ajaxData.dateStart = jQuery(settings.datePickerSelector).data('daterangepicker').startDate.format('YYYY-MM-DD');
            settings.ajaxData.dateEnd = jQuery(settings.datePickerSelector).data('daterangepicker').endDate.format('YYYY-MM-DD');

            jQuery(settings.datePickerSelector).on('apply.daterangepicker', function (ev, picker) {
                settings.ajaxData.dateStart = picker.startDate.format('YYYY-MM-DD');
                settings.ajaxData.dateEnd = picker.endDate.format('YYYY-MM-DD');
                settings.onDateChanged(settings.ajaxData.dateStart, settings.ajaxData.dateEnd);

                if (settings.datePickerSyncGroup) {
                    var syncGroups = ClickerVoltFunctions.getOption('datepicker-sync-groups');
                    for (var i = 0; i < syncGroups[settings.datePickerSyncGroup].length; i++) {

                        var otherSelector = syncGroups[settings.datePickerSyncGroup][i].selector;
                        var otherSettings = syncGroups[settings.datePickerSyncGroup][i].settings;
                        if (otherSelector != settings.datePickerSelector) {

                            jQuery(otherSelector).data('daterangepicker').setStartDate(picker.startDate);
                            jQuery(otherSelector).data('daterangepicker').setEndDate(picker.endDate);

                            otherSettings.ajaxData.dateStart = picker.startDate.format('YYYY-MM-DD');
                            otherSettings.ajaxData.dateEnd = picker.endDate.format('YYYY-MM-DD');

                            if (otherSettings.onDateChanged) {
                                otherSettings.onDateChanged(picker.startDate, picker.endDate);
                            }
                        }
                    }
                }
            });
        }

        var $table = jQuery(settings.containerSelector);
        $table.attr('width', '100%');

        var html = `<thead>
                        <tr>
                            <th class='treegrid'></th>
                            <th class='grouping'>${settings.segmentColumnName}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_CLICKS}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_UNIQUE_CLICKS}</th>
                            <th class='progress-bar'>${ClickerVoltStatsFunctions.COLUMN_SUSPICIOUS_TRAFFIC_RATE}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_ATTENTION_RATE}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_INTEREST_RATE}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_DESIRE_RATE}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_ACTIONS_RATE}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_ACTIONS}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_ACTIONS_REVENUE}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_COST}</th>
                            <th class='profit-metric'>${ClickerVoltStatsFunctions.COLUMN_PROFIT}</th>
                            <th class='metrics'>${ClickerVoltStatsFunctions.COLUMN_ROI}</th>
                        </tr>
                    </thead>`;

        if (settings.showTotals) {
            html += `<tfoot class='totals-row'>         
                        <tr>
                            <th class='total'></th>
                            <th class='total'>Totals</th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                            <th class='total'></th>
                        </tr>          
                    </tfoot>`;
        }

        jQuery(html).appendTo($table);

        var dataTableOptions = {
            dom: '<"stats-table-toolbar">frBtip',
            processing: true,
            buttons: [
                'csvHtml5',
                {
                    extend: 'colvis',
                    columns: ':gt(1)'
                },
                {
                    text: 'Expand all',
                    action: function (e, dt, node, config) {
                        ClickerVoltStatsFunctions.expandAll($table, true);
                    }
                },
                {
                    text: 'Collapse all',
                    action: function (e, dt, node, config) {
                        ClickerVoltStatsFunctions.collapseAll($table);
                    }
                }
            ],
            columnDefs: [
                {
                    targets: 'grouping',
                    className: 'dt-head-left dt-body-nowrap grouping',
                },
                {
                    targets: 'metrics',
                    className: 'dt-head-right dt-body-right metric'
                },
                {
                    targets: 'progress-bar',
                    className: 'dt-head-center dt-body-center metric'
                },
                {
                    targets: 'profit-metric',
                    className: 'dt-head-right dt-body-right metric profit'
                },
                {
                    targets: 'total',
                    className: 'dt-head-right dt-body-right'
                }
            ],
            drawCallback: function (settings) {
                // var api = this.api();
                // console.log('heatmap start');
                // ClickerVoltStatsFunctions.heatmapRefresh( $table );
                // console.log('heatmap end');
            },
            ajax: function (data, callback, dtSettings) {

                if (!data) {
                    data = {};
                }
                data = jQuery.extend(true, {}, data, settings.ajaxData);

                if (settings.ajaxDataCallback) {
                    settings.ajaxDataCallback(data);
                }

                ClickerVoltFunctions.ajax(settings.ajaxSource, null, {
                    data: data,
                    success: function (rows, ajaxData) {
                        var formattedRows = ClickerVoltStatsFunctions.reformatStatsRows($table, rows, ajaxData);
                        ClickerVoltFunctions.setOption('rowsCount', rows.length, $table);
                        ClickerVoltFunctions.setOption('treeGridData', formattedRows, $table);

                        callback({
                            data: formattedRows
                        });

                        $table.trigger('treegrid.sort');
                        ClickerVoltStatsFunctions.heatmapRefresh($table);

                        if (settings.ajaxRenderedCallback) {
                            settings.ajaxRenderedCallback(rows, ajaxData);
                        }
                    }
                });
            },
        };

        if (!clickerVoltVars.is_mobile) {
            dataTableOptions['fixedHeader'] = {
                headerOffset: getAdminBarFixedOffset(),
                header: true,
                footer: true,
            };
        }

        $table.on('treegrid.expanded', function () {
            ClickerVoltStatsFunctions.heatmapRefresh($table);
            ClickerVoltStatsFunctions.updateFixedHeader();
        });

        dataTableOptions = jQuery.extend(true, {}, dataTableOptions, settings.dataTableOptions);
        var dataTable = ClickerVoltFunctions.initTreeGridDataTable(settings.containerSelector, dataTableOptions, function () {
            return ClickerVoltFunctions.getOption('treeGridData', $table);
        });
        return dataTable;
    }

    /** 
     * 
     */
    static updateFixedHeader() {
        if (!clickerVoltVars.is_mobile) {
            jQuery('.stats-table-with-fixed-header').each(function () {
                var $table = jQuery(this);
                if (jQuery.fn.DataTable.isDataTable($table)) {
                    var dt = $table.DataTable();
                    if (dt) {
                        dt.fixedHeader.headerOffset(getAdminBarFixedOffset());
                        dt.fixedHeader.adjust();
                    }
                }
            });
        }
    }

    /**
     * 
     * @param {Object} $table 
     */
    static heatmapRefresh($table) {
        ClickerVoltStatsFunctions.heatmapCalculateMinMax($table);

        var api = $table.DataTable();
        api.rows().every(function (rowIdx, tableLoop, rowLoop) {
            ClickerVoltStatsFunctions.heatmapApply($table, this.node(), this.data());
        });
    }

    /**
     * 
     * @param {Object} $row 
     */
    static heatmapGetRowParentId($row) {
        return 'is-root';
        // var parentId = $row.attr('parent-index');
        // if( !parentId ) {
        //     parentId = "is-root";
        // }
        // return parentId;
    }

    /**
     * 
     * @param {Object} $table 
     */
    static heatmapCalculateMinMax($table) {

        var heatmap = ClickerVoltStatsFunctions.getOption($table, 'heatmap');
        if (heatmap && heatmap.enabled) {
            var colIndex = ClickerVoltStatsFunctions.getColumnIndex($table, heatmap.columnTitle);
            if (colIndex != -1) {
                // Search for min/max values in that column
                var minMaxes = {};
                var api = $table.DataTable();
                api.rows().every(function (rowIdx, tableLoop, rowLoop) {
                    var parentId = ClickerVoltStatsFunctions.heatmapGetRowParentId(jQuery(this.node()));
                    if (!minMaxes[parentId]) {
                        minMaxes[parentId] = {
                            min: Number.MAX_SAFE_INTEGER,
                            max: Number.MIN_SAFE_INTEGER,
                        };
                    }

                    var data = this.data();
                    var value = '' + data[colIndex];
                    value = parseFloat(value.replace('%', ''));
                    if (value < minMaxes[parentId].min) {
                        minMaxes[parentId].min = value;
                    }
                    if (value > minMaxes[parentId].max) {
                        minMaxes[parentId].max = value;
                    }
                });
                heatmap.minMaxes = minMaxes;
                ClickerVoltStatsFunctions.setOption($table, 'heatmap', heatmap);
            }
        }
    }

    /**
     * 
     * @param {Object} $table 
     * @param {Object} row 
     * @param {Object} data 
     */
    static heatmapApply($table, row, data) {
        var heatmap = ClickerVoltStatsFunctions.getOption($table, 'heatmap');
        if (heatmap && heatmap.enabled) {
            var colIndex = ClickerVoltStatsFunctions.getColumnIndex($table, heatmap.columnTitle);
            if (colIndex != -1) {

                var colClicksIndex = ClickerVoltStatsFunctions.getColumnIndex($table, ClickerVoltStatsFunctions.COLUMN_CLICKS);
                var clicks = ClickerVoltFunctions.removeBetweenBrackets(
                    ClickerVoltFunctions.removeHTMLTags(data[colClicksIndex])
                );

                var minClicksForNegative = 0;
                var ceilPositive = 0;
                var ceilNegative = 0;

                switch (heatmap.columnTitle) {

                    case ClickerVoltStatsFunctions.COLUMN_ATTENTION_RATE:
                        ceilPositive = 40;
                        ceilNegative = 20;
                        minClicksForNegative = 10;
                        break;

                    case ClickerVoltStatsFunctions.COLUMN_INTEREST_RATE:
                        ceilPositive = 35;
                        ceilNegative = 15;
                        minClicksForNegative = 15;
                        break;

                    case ClickerVoltStatsFunctions.COLUMN_DESIRE_RATE:
                        ceilPositive = 20;
                        ceilNegative = 10;
                        minClicksForNegative = 20;
                        break;
                }

                var parentId = ClickerVoltStatsFunctions.heatmapGetRowParentId(jQuery(row));
                var minMax = heatmap.minMaxes[parentId];
                if (minMax) {
                    var value = '' + data[colIndex];
                    value = parseFloat(value.replace('%', ''));
                    if (clicks > 0 && value > ceilPositive) {
                        var scale = (value - ceilPositive) / (minMax.max - ceilPositive);
                        jQuery(row).css('background-color', `rgba(200, 230, 200, ${scale})`);
                    }
                    else if (clicks > minClicksForNegative && value < ceilNegative) {
                        var scale = Math.abs((value - ceilNegative) / (minMax.min - ceilNegative));
                        jQuery(row).css('background-color', `rgba(255, 200, 190, ${scale})`);
                    } else {
                        jQuery(row).css('background-color', '');
                    }
                }
            }
        }
    }

    /**
     * 
     * @param {Object} $table 
     * @param {string} columnTitle 
     */
    static getColumnIndex($table, columnTitle) {
        var id = $table.attr('id');
        if (!id) {
            id = ClickerVoltFunctions.uuid();
            $table.attr('id', id);
        }

        var key = `columnIndexCache_${id}_${columnTitle}`;
        var index = ClickerVoltFunctions.getOption(key, $table);
        if (index === undefined) {
            index = $table.find(`thead tr th:contains(${columnTitle})`).index();
            ClickerVoltFunctions.setOption(key, index, $table);
        }

        return index;
    }

    /**
     * 
     * @param {Object} $table 
     * @param {string} key 
     * @param {mixed} value 
     */
    static setOption($table, key, value) {
        ClickerVoltFunctions.setOption(key, value, $table);
    }

    /**
     * 
     * @param {Object} $table 
     * @param {string} key 
     */
    static getOption($table, key) {
        return ClickerVoltFunctions.getOption(key, $table);
    }

    /**
     * 
     * @param {Object} $table
     * @param {string} columnTitle 
     * @param {boolean} enable 
     */
    static enableHeatmap($table, columnTitle, enable) {

        ClickerVoltStatsFunctions.setOption($table, 'heatmap', {
            columnTitle: columnTitle,
            enabled: enable
        });

        if (!enable) {
            $table.find("tr").css("background-color", "");
        } else {
            $table.DataTable().draw();
            ClickerVoltStatsFunctions.expandAll($table);
            ClickerVoltStatsFunctions.heatmapRefresh($table);
        }
    }

    /**
     * 
     * @param {object} $table 
     * @param {boolean} showWarningIfTooManyRows
     */
    static expandAll($table, showWarningIfTooManyRows) {
        var rowsCount = ClickerVoltFunctions.getOption('rowsCount', $table);
        if (rowsCount > 0) {
            if (rowsCount >= 500) {
                if (showWarningIfTooManyRows) {
                    ClickerVoltModals.error("Please drilldown manually", "This report contains too many rows to expand them all at once");
                }
            } else {
                var nbLevels = ClickerVoltStatsFunctions.getOption($table, 'tree-max-level');
                for (var i = 0; i < nbLevels - 1; i++) {
                    $table.trigger('treegrid.expand');
                }
            }
        }
    }

    /**
     * 
     * @param {*} $table 
     */
    static collapseAll($table) {
        $table.trigger('treegrid.collapseAll');
    }

    /**
     * 
     */
    static getStatsMetricColumns() {
        return [
            'clicks', 'clicksUnique', 'revenue', 'actions', 'cost',
            'hasAttention', 'hasInterest', 'hasDesire',
            'suspiciousScoreSum'
        ];
    }

    /**
     * 
     * @param {Object} row 
     * @return {int}
     */
    static getSegmentsCount(row) {

        var count = 0;

        for (var k in row) {
            if (k.indexOf('segment') === 0) {
                count++;
            }
        }

        return count;
    }

    /**
     * 
     * @param {Object} $table
     * @param {array} rows 
     * @param {Object} ajaxData 
     * @return {array}
     */
    static reformatStatsRows($table, rows, ajaxData) {

        var formattedRows = [];

        if (rows.length > 0) {

            var nbSegments = ClickerVoltStatsFunctions.getSegmentsCount(rows[0]);
            if (nbSegments > 0 && ajaxData.options[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS]) {

                var segmentIconsOptions = ajaxData.options[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS];
                for (var sio = 0; sio < segmentIconsOptions.length; sio++) {

                    var iconOptions = segmentIconsOptions[sio];

                    var segmentColumnKey = null;
                    for (var i = 0; i < ajaxData.segments.length; i++) {
                        if (ajaxData.segments[i] == iconOptions[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_WHICH_SEGMENT]) {
                            segmentColumnKey = `segment${i}`;
                        }
                    }

                    if (segmentColumnKey !== null) {

                        for (var y = 0; y < rows.length; y++) {

                            var segmentValue = rows[y][segmentColumnKey];

                            var iconTags = [];
                            for (var d = 0; d < iconOptions[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS].length; d++) {

                                var iconDetails = iconOptions[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS][d];
                                var segmentIcon = iconDetails[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_ICON] || '';
                                var segmentTitle = iconDetails[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_TITLE] || '';
                                iconTags.push(`<a title='${segmentTitle}' sio='${sio}' details='${d}' segment_value='${segmentValue}'><i class='material-icons stats-row ${segmentIcon}'></i></a>`);
                            }
                            iconTags = iconTags.join(' ');

                            rows[y][segmentColumnKey] = `
                                <div class='grouping-cell'>
                                    <span class='grouping-name'>${segmentValue}</span>
                                    <span class='grouping-actions'>
                                        ${iconTags}
                                    </span>
                                </div>`;
                        }
                    }
                }

                jQuery(document).off('click', 'span.grouping-actions a');
                jQuery(document).on('click', 'span.grouping-actions a', function () {
                    var $element = jQuery(this);
                    var segmentValue = $element.attr('segment_value');
                    var sio = $element.attr('sio');
                    var details = $element.attr('details');
                    details = segmentIconsOptions[sio][clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS][details];
                    var segmentCallback = details[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_CALLBACK] || null;
                    if (segmentCallback) {
                        segmentCallback(segmentValue, $element);
                    }
                });
            }

            ClickerVoltStatsFunctions.setOption($table, 'tree-max-level', nbSegments);

            if (nbSegments > 1) {
                // We need to collapse the segments to 1 single column
                rows = ClickerVoltStatsFunctions.collapseSegments(rows, nbSegments, 0, ajaxData);
            }

            var rowsAddIDs = function (rows, chain) {
                var chainPrefix;
                if (chain.length > 0) {
                    chainPrefix = chain.join('.') + '.';
                } else {
                    chainPrefix = '';
                }
                for (var y = 0; y < rows.length; y++) {
                    rows[y]['treegrid-row-id'] = chainPrefix + y;

                    if (rows[y]['children']) {
                        var childrenChain = chain.slice();  // copy array
                        childrenChain.push(y);
                        rowsAddIDs(rows[y]['children'], childrenChain);
                    }
                }
            };
            rowsAddIDs(rows, []);

            for (var y = 0; y < rows.length; y++) {

                var formattedRow = ClickerVoltStatsFunctions.reformatStatsRow(rows[y]);
                formattedRows.push(formattedRow);
            }
        }

        return formattedRows;
    }

    /**
     * 
     * @param {array} rows 
     * @param {int} nbSegments
     * @param {int} segmentIndex 
     * @param {object} ajaxData
     * @return {array} new collapsed rows
     */
    static collapseSegments(rows, nbSegments, segmentIndex, ajaxData) {

        if (segmentIndex === undefined) {
            segmentIndex = 0;
        }

        var segmentToRows = {};
        var metricColumns = ClickerVoltStatsFunctions.getStatsMetricColumns();

        // Flag all funnel links rows for this segment index
        for (var y = 0; y < rows.length; y++) {
            rows[y]['isFunnelLink'] = false;
            for (var s = segmentIndex + 1; s < nbSegments; s++) {
                var segmentKey = `segment${s}`;
                var segmentName = rows[y][segmentKey];
                if (segmentName.indexOf(clickerVoltVars.const.HandlerWholePath.FUNNEL_LINK_PREFIX) === 0) {
                    //rows[y][segmentKey] = segmentName.replace( clickerVoltVars.const.HandlerWholePath.FUNNEL_LINK_PREFIX, '' );
                    rows[y]['isFunnelLink'] = true;
                }
            }
        }

        for (var y = 0; y < rows.length; y++) {

            var segmentKey = `segment${segmentIndex}`;
            var segmentName = rows[y][segmentKey];
            if (segmentName !== '') {

                if (!segmentToRows[segmentName]) {

                    segmentToRows[segmentName] = {};
                    segmentToRows[segmentName]['root'] = jQuery.extend({}, rows[y]);
                    segmentToRows[segmentName]['root']['segment0'] = segmentName;

                    for (var k = 1; k < nbSegments; k++) {
                        delete segmentToRows[segmentName]['root'][`segment${k}`];
                    }

                    segmentToRows[segmentName]['children'] = [];

                } else {

                    if (rows[y]['isFunnelLink']) {
                        rows[y]['needsCTR'] = true;
                    }

                    for (var m = 0; m < metricColumns.length; m++) {
                        var metric = metricColumns[m];
                        if (!rows[y]['isFunnelLink'] || metric == 'actions' || metric == 'revenue') {
                            // If the row is a funnel link, we only add actions/revenue to its parent
                            segmentToRows[segmentName]['root'][metric] += rows[y][metric];
                        }
                    }
                }

                segmentToRows[segmentName]['children'].push(rows[y]);
            }
        }

        if (segmentIndex < (nbSegments - 1)) {
            for (var segmentName in segmentToRows) {
                segmentToRows[segmentName]['root']['children'] = ClickerVoltStatsFunctions.collapseSegments(segmentToRows[segmentName]['children'], nbSegments, segmentIndex + 1, ajaxData);
            }
        }

        var rootRows = [];
        for (var segmentName in segmentToRows) {
            rootRows.push(segmentToRows[segmentName]['root']);
        }

        if (segmentIndex === 0) {

            var rowsCleanup = function (rows) {
                for (var y = 0; y < rows.length; y++) {
                    var segmentKey = `segment0`;
                    var segmentName = rows[y][segmentKey];
                    if (segmentName.indexOf(clickerVoltVars.const.HandlerWholePath.FUNNEL_LINK_PREFIX) !== -1) {
                        rows[y][segmentKey] = segmentName.replace(clickerVoltVars.const.HandlerWholePath.FUNNEL_LINK_PREFIX, '');
                    }

                    if (rows[y]['children']) {
                        rowsCleanup(rows[y]['children']);
                    }
                }
            };
            rowsCleanup(rootRows);

            var applyCostToChilds = function (rows) {
                for (var y = 0; y < rows.length; y++) {
                    var row = rows[y];

                    if (row['cost'] == 0 && row['meta'] && row['meta']['costType'] == clickerVoltVars.const.CostTypes.CPA) {
                        row['cost'] = row['meta']['costValue'] * row['actions'];
                    }

                    if (row['children']) {

                        var rootCost = row['cost'];
                        var totalChildrenClicks = 0;
                        for (var i = 0; i < row['children'].length; i++) {
                            totalChildrenClicks += row['children'][i]['clicks'];
                        }
                        var childCPC = totalChildrenClicks > 0 ? rootCost / totalChildrenClicks : 0;

                        for (var i = 0; i < row['children'].length; i++) {
                            var childRow = row['children'][i];
                            childRow['cost'] = childRow['clicks'] * childCPC;
                        }

                        applyCostToChilds(row['children']);
                    }
                }
            };
            applyCostToChilds(rootRows);

            var addCTRToChildren = function (rows) {
                for (var y = 0; y < rows.length; y++) {
                    var row = rows[y];
                    if (row['children']) {
                        var rootClicks = row['clicks'];
                        if (rootClicks > 0) {
                            for (var i = 0; i < row['children'].length; i++) {
                                var childRow = row['children'][i];

                                if (true) //childRow['needsCTR'] ) 
                                {
                                    var childClicks = childRow['clicks'];
                                    var percent = (100 * childClicks / rootClicks).toFixed(0);
                                    childRow['clicks.formatted'] = `<span class="stats-ctr-hint">(${percent} %)</span>` + ClickerVoltStatsFunctions.formatNumber(childRow['clicks'], 0);
                                }
                            }
                        }
                        addCTRToChildren(row['children']);
                    }
                }
            };
            addCTRToChildren(rootRows);
        }

        return rootRows;
    }

    /**
     * 
     * @param {Object} row
     * @return {Object} 
     */
    static reformatStatsRow(row) {

        var nbSegments = ClickerVoltStatsFunctions.getSegmentsCount(row);

        ClickerVoltStatsFunctions.addSecondaryMetrics(row);

        var formattedRow = [
            ''  // first column for treegrid
        ];

        // Push all segments
        for (var i = 0; i < nbSegments; i++) {
            var value = '' + row[`segment${i}`];
            if (value.indexOf('<') == -1) {
                value = ClickerVoltFunctions.htmlEntities(value);
                if (value.length > 64) {
                    var title = value;
                    var short = value.substr(0, 64);
                    value = `<span title="${title}">${short}...</span>`;
                }
            }
            formattedRow.push(value);
        }

        // And push all metrics
        Array.prototype.push.apply(formattedRow, [
            row['clicks.formatted'] !== undefined ? row['clicks.formatted'] : ClickerVoltStatsFunctions.formatNumber(row['clicks'], 0),
            ClickerVoltStatsFunctions.formatNumber(row['clicksUnique'], 0),
            row['suspiciousTrafficRate'],
            row['hasAttentionRate'],
            row['hasInterestRate'],
            row['hasDesireRate'],
            row['actionRate'],
            ClickerVoltStatsFunctions.formatNumber(row['actions'], 0),
            ClickerVoltStatsFunctions.formatNumber(row['revenue'], 2),
            ClickerVoltStatsFunctions.formatNumber(row['cost'], 2),
            ClickerVoltStatsFunctions.formatNumber(row['profit'], 2),
            row['roi']
        ]);

        formattedRow = ClickerVoltFunctions.arrayToObject(formattedRow);

        if (row.children) {

            var formattedChildren = [];
            for (var i = 0; i < row.children.length; i++) {
                formattedChildren.push(ClickerVoltStatsFunctions.reformatStatsRow(row.children[i]));
            }

            formattedRow.children = formattedChildren;
        }

        formattedRow['treegrid-row-id'] = row['treegrid-row-id'];

        return formattedRow;
    }

    /**
     * 
     * @param {Object} row
     * @return {Object} 
     */
    static addSecondaryMetrics(row) {

        var clicks = row['clicks'];
        var revenue = row['revenue'];
        var actions = row['actions'];
        var hasAttention = row['hasAttention'];
        var hasInterest = row['hasInterest'];
        var hasDesire = row['hasDesire'];
        var cost = row['cost'];
        var suspiciousScoreSum = row['suspiciousScoreSum'];
        var suspiciousTrafficRate = clicks > 0
            ? Math.min(100.0, ClickerVoltFunctions.roundToDecimalPlaces(parseFloat(suspiciousScoreSum / clicks), 2))
            : null;

        var suspiciousTrafficRateHtml;
        if (suspiciousTrafficRate === null) {
            suspiciousTrafficRateHtml = "n/a";
        } else {
            suspiciousTrafficRateHtml = `<div class="suspicious-traffic-bar" title="${suspiciousTrafficRate}%"><div style="width: ${suspiciousTrafficRate}%;"></div><span>${suspiciousTrafficRate}</span></div>`;
        }

        row['suspiciousTrafficRate'] = suspiciousTrafficRateHtml;
        row['hasAttentionRate'] = clicks > 0 ? (100.0 * hasAttention / clicks).toFixed(1) + '%' : '0.0%';
        row['hasInterestRate'] = clicks > 0 ? (100.0 * hasInterest / clicks).toFixed(1) + '%' : '0.0%';
        row['hasDesireRate'] = clicks > 0 ? (100.0 * hasDesire / clicks).toFixed(1) + '%' : '0.0%';
        row['actionRate'] = clicks > 0 ? (100.0 * actions / clicks).toFixed(1) + '%' : '0.0%';
        row['profit'] = revenue - cost;
        row['roi'] = ClickerVoltStatsFunctions.calculateROI(cost, revenue - cost);

        return row;
    }

    /**
     * 
     * @param {double} cost 
     * @param {double} profit 
     * @return string
     */
    static calculateROI(cost, profit) {
        return cost > 0 ? ClickerVoltStatsFunctions.formatNumber((100.0 * profit / cost), 1) + '%' : '-';
    }

    /**
     * 
     * @param float|int number 
     * @param int digits 
     * @return string
     */
    static formatNumber(number, digits) {
        return number.toFixed(digits);
        // if (typeof number == 'number') {
        //     number = number.toFixed(digits);
        // } else {
        //     console.log("not a number: " + number);
        // }
        // return number;
        // return number.toLocaleString('en-US', { style: 'decimal', minimumFractionDigits: digits, maximumFractionDigits: digits });
    }
};

ClickerVoltStatsFunctions.COLUMN_CLICKS = 'Clicks';
ClickerVoltStatsFunctions.COLUMN_UNIQUE_CLICKS = 'Uniques';
ClickerVoltStatsFunctions.COLUMN_SUSPICIOUS_TRAFFIC_RATE = 'Suspicious';
ClickerVoltStatsFunctions.COLUMN_ATTENTION_RATE = 'Att. %';
ClickerVoltStatsFunctions.COLUMN_INTEREST_RATE = 'I. %';
ClickerVoltStatsFunctions.COLUMN_DESIRE_RATE = 'D. %';
ClickerVoltStatsFunctions.COLUMN_ACTIONS_RATE = 'A. %';
ClickerVoltStatsFunctions.COLUMN_ACTIONS = 'Actions #';
ClickerVoltStatsFunctions.COLUMN_ACTIONS_REVENUE = 'Actions $';
ClickerVoltStatsFunctions.COLUMN_COST = 'Cost';
ClickerVoltStatsFunctions.COLUMN_PROFIT = 'Profit';
ClickerVoltStatsFunctions.COLUMN_ROI = 'ROI';
