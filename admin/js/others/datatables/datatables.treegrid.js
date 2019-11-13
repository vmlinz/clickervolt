/**
 * @summary     TreeGrid
 * @description TreeGrid extension for DataTable
 * @version     1.0.0
 * @file dataTables.treeGrid.js
 * @author homfen(homfen@outlook.com)
 * 
 * https://github.com/homfen/dataTables.treeGrid.js
 * 
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net'], function ($) {
            return factory($, window, document);
        });
    }
    else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }

            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net')(root, $).$;
            }

            return factory($, root, root.document);
        };
    }
    else {
        // Browser
        factory(jQuery, window, document);
    }
}(function ($, window, document) {
    'use strict';
    var DataTable = $.fn.dataTable;

    var TreeGrid = function (dt, init) {
        var that = this;

        if (!(this instanceof TreeGrid)) {
            alert('TreeGrid warning: TreeGrid must be initialised with the "new" keyword.');
            return;
        }

        if (init === undefined || init === true) {
            init = {};
        }

        var dtSettings = new $.fn.dataTable.Api(dt).settings()[0];

        this.s = {
            dt: dtSettings
        };

        if (dtSettings._oTreeGrid) {
            throw 'TreeGrid already initialised on this table';
        }

        dtSettings._oTreeGrid = this;

        if (!dtSettings._bInitComplete) {
            dtSettings.oApi._fnCallbackReg(dtSettings, 'aoInitComplete', function () {
                that.fnConstruct(init);
            }, 'TreeGrid');
        }
        else {
            this.fnConstruct(init);
        }
    };

    $.extend(TreeGrid.prototype, {
        fnConstruct: function (oInit) {
            this.s = $.extend(true, this.s, TreeGrid.defaults, oInit);

            var dataCallback = this.s.dataCallback;
            var settings = this.s.dt;
            var select = settings._select;
            var dataTable = $(settings.nTable).dataTable().api();
            var sLeft = this.s.left;
            var treeGridRows = {};
            var expandIcon = $(this.s.expandIcon);
            var collapseIcon = $(this.s.collapseIcon);
            var $table = $(dataTable.table().body()).closest('table');
            var tableID = $table.attr('id');
            if (!tableID) {
                tableID = Math.random().toString(36);
                $table.attr('id', tableID);
            }

            $table.addClass('treegrid-datatable');
            $table.find('thead th').not(':first').addClass('sorting');
            $(document).on('click', 'table.treegrid-datatable thead th:not(:first)', function () {
                // Manage sorting manually instead of via datatable's internal sorting, 
                // so we can sort tree data... 
                var $th = $(this);
                if ($th.closest('table').attr('id') == tableID) {
                    var columnIndex = thIndexToDataIndex($th.index());
                    if ($th.hasClass('sorting_desc')) {
                        $th.removeClass('sorting_desc');
                        $th.addClass('sorting_asc');
                    } else {
                        $table.find('thead th').removeClass('sorting_asc');
                        $table.find('thead th').removeClass('sorting_desc');
                        $th.addClass('sorting_desc');
                    }

                    $table.trigger('treegrid.sort');
                }
            });

            /**
             * Calling examples:
             *   $table.trigger('treegrid.sortBy', [{ colName: 'Price', orderWay: 'asc' }])
             *   $table.trigger('treegrid.sortBy', [{ colIndex: 4, orderWay: 'desc' }])
             */
            $table.on('treegrid.sortBy', function (e, params) {
                if ((params.colName === undefined && params.colIndex === undefined) || params.orderWay === undefined) {
                    throw new Error("params.orderWay must be passed as well as params.colName or params.colIndex");
                } else if (params.orderWay != 'asc' && params.orderWay != 'desc') {
                    throw new Error('params.orderWay must be "asc" or "desc"');
                }
                var $ths = $table.find('thead th');
                $ths.removeClass('sorting_asc');
                $ths.removeClass('sorting_desc');
            });

            $table.on('treegrid.sort', function () {
                var columnIndex = -1;
                var way;
                var $th = $table.find('thead th.sorting_asc').first();
                if ($th && $th.length) {
                    columnIndex = thIndexToDataIndex($th.index());
                    way = 'asc';
                } else {
                    $th = $table.find('thead th.sorting_desc').first();
                    if ($th && $th.length) {
                        columnIndex = thIndexToDataIndex($th.index());
                        way = 'desc';
                    }
                }

                if (columnIndex != -1) {
                    sortRowsByColumn(columnIndex, way);
                }
            });

            var thIndexToDataIndex = function (thIndex) {
                var foundAt = -1;
                var i = 0;
                dataTable.columns().every(function (index) {
                    if (foundAt == -1 && this.visible()) {
                        if (i == thIndex) {
                            foundAt = index;
                        } else {
                            i++;
                        }
                    }
                });
                return foundAt;
            };

            var sortRowsByColumn = function (orderCol, orderWay) {
                var orderWay = orderWay == 'asc' ? 1 : -1;
                var data = dataCallback();

                // Get IDs of opened rows
                var openedRowIDs = [];
                $table.find('td.treegrid-control-open').closest('tr').each(function () {
                    openedRowIDs.push($(this).attr('id'));
                });

                // Sort rows in the data tree, recursively...
                var sortRows = function (rows) {

                    var getColVal = function (val) {
                        // remove HTML tags, parantheses and text between them, and percent chars
                        val = ('' + val)
                            .replace(/<(.|\n)*?>/g, '')
                            .replace(/ *\([^)]*\) */g, '')
                            .replace('%', '');

                        if ($.isNumeric(val)) {
                            val = parseFloat(val);
                        }
                        return val;
                    };

                    rows.sort(function (row1, row2) {
                        var val1 = getColVal(row1[orderCol]);
                        var val2 = getColVal(row2[orderCol]);
                        if ($.isNumeric(val1) && !$.isNumeric(val2)) {
                            val2 = 0;
                        } else if (!$.isNumeric(val1) && $.isNumeric(val2)) {
                            val1 = 0;
                        }

                        if (val1 < val2) {
                            return -1 * orderWay;
                        } else if (val1 > val2) {
                            return +1 * orderWay;
                        }
                        return 0;
                    });

                    for (var i = 0; i < rows.length; i++) {
                        if (rows[i]['children']) {
                            rows[i]['children'] = sortRows(rows[i]['children']);
                        }
                    }

                    return rows;
                };
                data = sortRows(data);

                // Replace datatable rows with the sorted ones
                treeGridRows = {};
                dataTable.clear();
                dataTable.rows.add(data);
                dataTable.draw();

                // And reopen previously opened rows
                for (var i = 0; i < openedRowIDs.length; i++) {
                    var id = openedRowIDs[i];
                    $table.find(`tr#${id} td.treegrid-control`).trigger('click');
                }
                if (openedRowIDs.length == 0) {
                    $table.trigger('treegrid.expanded');
                }
            };

            var resetTreeGridRows = function (index) {
                var indexes = [];
                if (index !== undefined) {
                    indexes.push(index);
                }
                else {
                    for (var id in treeGridRows) {
                        if (treeGridRows.hasOwnProperty(id)) {
                            indexes.push(id);
                        }
                    }
                }
                indexes.forEach(function (index) {
                    var subRows = treeGridRows[index];
                    if (subRows && subRows.length) {
                        subRows.forEach(function (node) {
                            var subRow = dataTable.row($(node));
                            var subRowIndex = $(node).attr('id');
                            var subRowData = subRow.data();
                            if (subRowData.children) {
                                resetTreeGridRows(subRowIndex);
                            }
                            subRow.remove();
                            $(node).remove();
                        });
                        delete treeGridRows[index];
                        $(dataTable.row(`#${index}`).node()).find('.treegrid-control-open').each(function (i, td) {
                            $(td).removeClass('treegrid-control-open').addClass('treegrid-control');
                            $(td).html('').append(expandIcon.clone());
                        });
                    }
                });
            };

            var resetEvenOddClass = function (dataTable) {
                var classes = ['odd', 'even'];
                $(dataTable.table().body()).find('tr').each(function (index, tr) {
                    $(tr).removeClass('odd even').addClass(classes[index % 2]);
                });
            };

            var expandTreeGridControlRow = function (element) {
                if (!$(this).html()) {
                    return;
                }

                // record selected indexes
                var selectedIndexes = [];
                select && (selectedIndexes = dataTable.rows({ selected: true }).indexes().toArray());

                var row = dataTable.row(getParentTr(element));
                var index = $(row.node()).attr('id');
                var data = row.data();

                var td = $(dataTable.cell(getParentTd(element)).node());
                var paddingLeft = parseInt(td.css('padding-left'), 10);
                var layer = parseInt(td.find('span').css('margin-left') || 0, 10) / sLeft;
                var icon = collapseIcon.clone();
                icon.css('marginLeft', layer * sLeft + 'px');
                td.removeClass('treegrid-control').addClass('treegrid-control-open');
                td.html('').append(icon);

                if (data.children && data.children.length) {
                    var subRows = treeGridRows[index] = [];
                    var prevRow = row.node();
                    data.children.forEach(function (item) {
                        var newRow = dataTable.row.add(item);
                        var node = newRow.node();
                        var treegridTd = $(node).find('.treegrid-control');
                        var left = (layer + 1) * sLeft;
                        $(node).attr('parent-index', index);
                        treegridTd.find('span').css('marginLeft', left + 'px');
                        treegridTd.next().css('paddingLeft', paddingLeft + left + 'px');
                        $(node).insertAfter(prevRow);
                        prevRow = node;
                        subRows.push(node);
                    });

                    resetEvenOddClass(dataTable);
                    select && setTimeout(function () {
                        dataTable.rows(selectedIndexes).select();
                    }, 0);
                }
            }

            // Expand TreeGrid
            dataTable.on('click', 'td.treegrid-control', function (e) {
                expandTreeGridControlRow.call(this, e.target);
                $table.trigger('treegrid.expanded');
            });

            // Collapse TreeGrid
            dataTable.on('click', 'td.treegrid-control-open', function (e) {
                var selectedIndexes = [];
                select && (selectedIndexes = dataTable.rows({ selected: true }).indexes().toArray());

                var index = $(dataTable.row(getParentTr(e.target)).node()).attr('id');
                var td = $(dataTable.cell(getParentTd(e.target)).node());
                var layer = parseInt(td.find('span').css('margin-left') || 0, 10) / sLeft;
                var icon = expandIcon.clone();
                icon.css('marginLeft', layer * sLeft + 'px');
                td.removeClass('treegrid-control-open').addClass('treegrid-control');
                td.html('').append(icon);

                resetTreeGridRows(index);
                resetEvenOddClass(dataTable);
                select && setTimeout(function () {
                    dataTable.rows(selectedIndexes).select();
                }, 0);
            });

            // resetTreeGridRows on pagination
            dataTable.on('page.dt', function () {
                resetTreeGridRows();
            });

            // resetTreeGridRows on sorting
            dataTable.on('order.dt', function () {
                // resetTreeGridRows();
            });

            // resetTreeGridRows when ajax data is updated
            dataTable.on('xhr.dt', function () {
                resetTreeGridRows();
            });

            // resetTreeGridRows when resetting search
            dataTable.on('search.dt', function () {
                if (dataTable.search() === '') {
                    // resetTreeGridRows();
                }
            });

            // event to expand (only one level)
            dataTable.on('treegrid.expand', function () {
                $table.find('td.treegrid-control').each(function () {
                    expandTreeGridControlRow.call(this, this);
                });
                $table.trigger('treegrid.expanded');
            });

            // event to collapse all
            dataTable.on('treegrid.collapseAll', function () {
                $table.find('td.treegrid-control-open').trigger('click');
            });

            var inProgress = false;
            // Check parents and children on select
            select && select.style === 'multi' && dataTable.on('select', function (e, dt, type, indexes) {
                if (inProgress) {
                    return;
                }
                inProgress = true;
                indexes.forEach(function (index) {
                    // Check parents
                    selectParent(dataTable, index);

                    // Check children
                    selectChildren(dataTable, index);
                });
                inProgress = false;
            });

            // Check parents and children on deselect
            select && select.style === 'multi' && dataTable.on('deselect', function (e, dt, type, indexes) {
                if (inProgress) {
                    return;
                }
                inProgress = true;
                indexes.forEach(function (index) {
                    // Check parents
                    deselectParent(dataTable, index);

                    // Check children
                    deselectChildren(dataTable, index);
                });
                inProgress = false;
            });
        }
    });

    function selectParent(dataTable, index) {
        var row = dataTable.row(index);
        var parentIndex = $(row.node()).attr('parent-index');
        if (parentIndex != null) {
            var selector = '[parent-index="' + parentIndex + '"]';
            var allChildRows = dataTable.rows(selector).nodes();
            var selectedChildRows = dataTable.rows(selector, { selected: true }).nodes();
            if (allChildRows.length === selectedChildRows.length) {
                var parentRow = dataTable.row(`#${parentIndex}`, { selected: false });
                parentRow.select();
                if (parentRow.node()) {
                    selectParent(dataTable, parentIndex);
                }
            }
        }
    }

    function selectChildren(dataTable, index) {
        var rows = dataTable.rows('[parent-index="' + index + '"]', { selected: false });
        var childIndexes = rows.indexes().toArray();
        if (childIndexes.length) {
            rows.select();
            childIndexes.forEach(function (childIndex) {
                selectChildren(dataTable, childIndex);
            });
        }
    }

    function deselectParent(dataTable, index) {
        var row = dataTable.row(index);
        var parentIndex = $(row.node()).attr('parent-index');
        if (parentIndex != null) {
            var parentRow = dataTable.row(`#${parentIndex}`, { selected: true });
            parentRow.deselect();
            if (parentRow.node()) {
                deselectParent(dataTable, parentIndex);
            }
        }
    }

    function deselectChildren(dataTable, index) {
        var rows = dataTable.rows('[parent-index="' + index + '"]', { selected: true });
        var childIndexes = rows.indexes().toArray();
        if (childIndexes.length) {
            rows.deselect();
            childIndexes.forEach(function (childIndex) {
                deselectChildren(dataTable, childIndex);
            });
        }
    }

    function getParentTr(target) {
        return $(target).parents('tr')[0];
    }

    function getParentTd(target) {
        var parent = target.tagName === 'TD' ? target : $(target).parents('td')[0];
        return parent;
    }

    TreeGrid.defaults = {
        left: 12,
        expandIcon: '<span>+</span>',
        collapseIcon: '<span>-</span>',
        dataCallback: null,
        dataRowID: 'treegrid-row-id',
    };

    TreeGrid.version = '1.0.0';

    DataTable.Api.register('treeGrid()', function () {
        return this;
    });

    $(document).on('init.dt.treeGrid', function (e, settings) {
        if (e.namespace !== 'dt') {
            return;
        }

        var init = settings.oInit.treeGrid;
        var defaults = DataTable.defaults.treeGrid;

        if (init || defaults) {
            var opts = $.extend({}, init, defaults);

            if (init !== false) {
                if (!opts.dataCallback) {
                    alert("TreeGrid options MUST have a 'dataCallback' to retrieve tree's data");
                } else {
                    new TreeGrid(settings, opts);
                }
            }
        }
    });

    $.fn.dataTable.TreeGrid = TreeGrid;
    $.fn.DataTable.TreeGrid = TreeGrid;

    return TreeGrid;
}));