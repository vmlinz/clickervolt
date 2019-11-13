<div id="tab-clicklog" class="tab-content">

    <table id="datatables-clicklog" class="stripe stats-table-with-fixed-header" style="width:100%">
        <thead>
            <tr>
                <th>Time (Local)</th>
                <th class="hidden-default">Click ID</th>
                <th>Link</th>
                <th class="user-data-icons">User</th>
                <th class="hidden-default">ISP</th>
                <th class="hidden-default">IP</th>
                <th>Source</th>
                <th>Referrer</th>
                <th class="profit-metric">Action $</th>
                <th>Time To Action</th>
                <th>V1</th>
                <th class="hidden-default">V2</th>
                <th class="hidden-default">V3</th>
                <th class="hidden-default">V4</th>
                <th class="hidden-default">V5</th>
                <th class="hidden-default">V6</th>
                <th class="hidden-default">V7</th>
                <th class="hidden-default">V8</th>
                <th class="hidden-default">V9</th>
                <th class="hidden-default">V10</th>
            </tr>
        </thead>
    </table>

</div>

<script>
    function refreshClickLogTable() {
        var $table = jQuery('#datatables-clicklog');
        $table.DataTable().ajax.reload();
    }

    jQuery(document).ready(function() {

        var $table = jQuery('#datatables-clicklog');
        var refreshIntervalId = -1;

        jQuery('#tabs-for-stats').on('tab-change', function(e, data) {
            if (data.selectedTabId == 'tab-clicklog') {
                if (refreshIntervalId == -1) {
                    refreshClickLogTable();
                    refreshIntervalId = setInterval(refreshClickLogTable, 1500);
                }
            } else {
                if (refreshIntervalId != -1) {
                    clearInterval(refreshIntervalId);
                    refreshIntervalId = -1;
                }
            }
        });

        var options = {
            ordering: true,
            order: [0, 'desc'],
            paging: false,
            stateSave: true,
            stateSaveParams: function(settings, data) {
                // do not save search query and paging
                data.search.search = '';
                data.start = 0;
            },
            dom: '<"clicklog-table-toolbar">frBtip',
            buttons: [{
                extend: 'colvis',
            }, ],
            columnDefs: [{
                    targets: 0,
                    type: 'date',
                },
                {
                    targets: 'user-data-icons',
                    className: 'dt-head-center dt-body-left dt-body-nowrap user-data-icons'
                },
                {
                    targets: 'profit-metric',
                    className: 'dt-head-center dt-body-center dt-body-nowrap profit-metric'
                },
                {
                    targets: 'hidden-default',
                    className: 'dt-head-center dt-body-center dt-body-nowrap',
                    visible: false
                },
                {
                    targets: '_all',
                    className: 'dt-head-center dt-body-center dt-body-nowrap',
                    orderable: false
                },
            ],
            ajax: function(data, callback, dtSettings) {
                if (!data) {
                    data = {};
                }
                var table = $table.DataTable();
                data.currentRowsCount = table.data().count();
                data.length = 50;
                data.timezoneOffset = new Date().getTimezoneOffset();

                ClickerVoltFunctions.ajax('wp_ajax_clickervolt_get_clicklog', null, {
                    data: data,
                    success: function(rows, ajaxData) {
                        if (rows['set'].length > 0) {
                            callback({
                                data: rows['set']
                            });
                        }

                        if (rows['add'].length > 0) {
                            table.rows.add(rows['add']).nodes().to$().hide().fadeIn(800);
                            table.draw();

                            // Trim oldest rows if rows count > data.length
                            {
                                var numRows = table.rows().indexes().length;
                                if (numRows > data.length) {
                                    var rowsIndices = table.rows({
                                        order: 'applied'
                                    }).indexes().toArray();
                                    rowsIndices = rowsIndices.slice(data.length);
                                    table.rows(rowsIndices).remove().draw();
                                }
                            }
                        }
                    }
                });
            },
            createdRow: function(row, data, dataIndex) {
                var actionDollarColumnIndex = 8;
                if (data[actionDollarColumnIndex]) {
                    jQuery(row).css('background-color', `rgba(200, 230, 200, 1)`);
                }
            },
        };

        if (!clickerVoltVars.is_mobile) {
            options['fixedHeader'] = {
                headerOffset: getAdminBarFixedOffset()
            };
        }

        var dt = $table.DataTable(options);
    });
</script>