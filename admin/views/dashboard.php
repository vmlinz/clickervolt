<div class='wrap clickervolt-section-dashboard'>

    <!-- This invisible h1 tag is only here to define where to display alert/badge messages -->
    <h1 style="display: none;">ClickerVolt</h1>

    <div id="clickervolt-logo"></div>

    <div id="options-accordion">
        <h3 class="button">Create/Edit Link</h3>
        <div style="display: none;">
            <p>
                <?php
                ClickerVolt\ViewLoader::newLink();
                ?>
            </p>
        </div>
    </div>
    <a href="https://help.clickervolt.com/" target="_blank" class="button">Docs & Tutorials</a>

    <div id="tabs-for-stats" class="tabs-wrapper">

        <ul class="tabs">
            <li class="tab-link current" data-tab="tab-links"><i class="material-icons links"></i>Links</li>
            <li class="tab-link" data-tab="tab-reports"><i class="material-icons reports"></i>Reports</li>
            <li class="tab-link" data-tab="tab-clicklog"><i class="material-icons live-clicks"></i>Live Clicks</li>
            <li class="tab-link right" data-tab="tab-settings"><i class="material-icons settings"></i>Settings</li>
            <li class="tab-link right" data-tab="tab-news"><i class="material-icons latest-news"></i>Latest News</li>
        </ul>

        <?php include __DIR__ . '/dashboardTabLinks.php'; ?>
        <?php include __DIR__ . '/dashboardTabReports.php'; ?>
        <?php include __DIR__ . '/dashboardTabClickLog.php'; ?>
        <?php include __DIR__ . '/dashboardTabLatestNews.php'; ?>
        <?php include __DIR__ . '/dashboardTabSettings.php'; ?>

    </div>

</div>


<script>
    jQuery(document).ready(function() {

        ClickerVoltFunctions.extendJQueryValidator();

        ClickerVoltFunctions.initAccordionButton('#options-accordion', ClickerVoltStatsFunctions.updateFixedHeader, ClickerVoltStatsFunctions.updateFixedHeader);
        ClickerVoltFunctions.initTabs('#tabs-for-stats', function(selectedTabId) {
            ClickerVoltStatsFunctions.updateFixedHeader();
            jQuery('#tabs-for-stats').trigger('tab-change', [{
                selectedTabId: selectedTabId
            }]);
        });

        var statsTables = [];
        statsTables.push(initLinksTable());
        statsTables.push(initReportsTable());

        ClickerVoltLinkController.onLinkSaved(function() {
            for (var i = 0; i < statsTables.length; i++) {
                statsTables[i].ajax.reload(ClickerVoltStatsFunctions.updateFixedHeader);
            }
            initSlugFilter();
        });

        ClickerVoltLinkController.onSourceSaved(function() {
            initSourceFilter();
        });

        jQuery('select.heatmap').each(function() {
            var $select = jQuery(this);

            ClickerVoltFunctions.addOptionToSelect($select, ClickerVoltStatsFunctions.COLUMN_PROFIT, 'Heatmap: Profit');
            ClickerVoltFunctions.addOptionToSelect($select, ClickerVoltStatsFunctions.COLUMN_ACTIONS, 'Heatmap: Actions #');
            ClickerVoltFunctions.addOptionToSelect($select, ClickerVoltStatsFunctions.COLUMN_ATTENTION_RATE, 'Heatmap: Attention Rate');
            ClickerVoltFunctions.addOptionToSelect($select, ClickerVoltStatsFunctions.COLUMN_INTEREST_RATE, 'Heatmap: Interest Rate');
            ClickerVoltFunctions.addOptionToSelect($select, ClickerVoltStatsFunctions.COLUMN_DESIRE_RATE, 'Heatmap: Desire Rate');
            ClickerVoltFunctions.addOptionToSelect($select, 'none', 'Heatmap: None');

            ClickerVoltFunctions.initSelect2($select);

            $select.on('change', function() {
                var forTable = jQuery(this).attr('for');
                var columnTitle = jQuery(this).find('option:selected').val();

                if (columnTitle == 'none') {
                    ClickerVoltStatsFunctions.enableHeatmap(jQuery(forTable), columnTitle, false);
                } else {
                    ClickerVoltStatsFunctions.enableHeatmap(jQuery(forTable), columnTitle, true);
                }
            });

            $select.trigger('change');
        });

        jQuery(window).on('resize clickervolt-resized', function() {
            monitorStickyAdminBar();
        });

        jQuery(window).trigger('clickervolt-resized');
    });

    /**
     * 
     */
    function monitorStickyAdminBar() {
        var cvAdminBarPosition = jQuery('#wpadminbar').css('position');
        if (cvAdminBarPosition != window.cvAdminBarPosition) {
            window.cvAdminBarPosition = cvAdminBarPosition;

            ClickerVoltStatsFunctions.updateFixedHeader();
        }
    }

    /**
     * 
     */
    function getAdminBarFixedOffset() {
        var headerOffset = 0;
        if (jQuery('#wpadminbar').css('position') == 'fixed') {
            headerOffset = jQuery('#wpadminbar').outerHeight();
        }
        return headerOffset;
    }

    /**
     * 
     */
    function initLinksTable() {

        var options = {};
        var optionsSlugIcons = {};

        optionsSlugIcons[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_WHICH_SEGMENT] = clickerVoltVars.const.ReportingSegments.TYPE_LINK;
        optionsSlugIcons[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS] = [{
                [clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_ICON]: "edit",
                [clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_TITLE]: "Edit",
                [clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_CALLBACK]: function(segmentValue, $element) {
                    if (segmentValue !== undefined) {
                        editSlugClicked($element, segmentValue);
                    }
                }
            },
            {
                [clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_ICON]: "reports",
                [clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_TITLE]: "Open link stats",
                [clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_CALLBACK]: function(segmentValue, $element) {
                    if (segmentValue !== undefined) {
                        openLinkStats($element, segmentValue);
                    }
                }
            },
            {
                [clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_ICON]: "delete",
                [clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_TITLE]: "Delete link",
                [clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS_DETAILS_WHICH_CALLBACK]: function(segmentValue, $element) {
                    if (segmentValue !== undefined) {
                        ClickerVoltModals.confirm(`Are you sure you want to delete this link: '${segmentValue}'?<br><br>This cannot be reversed`, function() {
                            deleteSlug(segmentValue);
                        });
                    }
                }
            }
        ]

        options[clickerVoltVars.const.AjaxStats.OPTION_INCLUDE_SLUGS_WITHOUT_TRAFFIC] = true;
        options[clickerVoltVars.const.AjaxStats.OPTION_SEGMENT_ICONS] = [optionsSlugIcons];

        var table = ClickerVoltStatsFunctions.initStatsTable({
            containerSelector: '#datatables-links',
            segmentColumnName: 'Links',
            ajaxSource: 'wp_ajax_clickervolt_get_stats',
            ajaxData: {
                segments: [
                    clickerVoltVars.const.ReportingSegments.TYPE_LINK,
                    clickerVoltVars.const.ReportingSegments.TYPE_URL,
                ],
                options: options
            },
            datePickerSyncGroup: 'stats',
            datePickerSelector: 'input[name="links-daterange"]',
            onDateChanged: function(start, end) {
                table.ajax.reload(ClickerVoltStatsFunctions.updateFixedHeader);
            },
            // dataTableOptions: {
            //     select: {
            //         style: 'single',
            //         selector: 'td:not(:first-child)'
            //     },
            // }
        });

        jQuery('.links-toolbar')
            .appendTo(jQuery('#datatables-links').parent().find('div.stats-table-toolbar'))
            .show();

        jQuery('#btn-links-refresh').on('click', function() {
            forceProcessClicksQueue(function() {
                jQuery('#datatables-links').DataTable().ajax.reload(ClickerVoltStatsFunctions.updateFixedHeader);
            });
        });

        ClickerVoltStatsFunctions.enableHeatmap(jQuery('#datatables-links'), ClickerVoltStatsFunctions.COLUMN_PROFIT, true);

        return table;
    }

    /**
     * 
     */
    function deleteSlug(slug) {

        ClickerVoltFunctions.ajax('wp_ajax_clickervolt_delete_link_by_slug', null, {

            data: {
                slug: slug
            },
            success: function() {
                ClickerVoltLinkController.refreshLinksLists();
                jQuery('#datatables-links').DataTable().ajax.reload(ClickerVoltStatsFunctions.updateFixedHeader);
            },
            complete: function() {}
        });
    }

    /**
     * 
     */
    function openLinkStats($element, slug) {

        var htmlBackup = $element.html();
        var replaceFrom = '<i class="material-icons stats-row reports"></i>';

        var pluginUrl = clickerVoltVars.urls.plugin;
        var loadingImageUrl = pluginUrl + '/admin/images/icons/report-loading-18px.gif?v=2';
        var replaceTo = `<img src='${loadingImageUrl}' style='position: relative; top: 4px; left: 0px;' />`;

        $element.prop('disabled', true).html(htmlBackup.replace(replaceFrom, replaceTo));

        ClickerVoltFunctions.ajax('wp_ajax_clickervolt_get_link_by_slug', null, {
            data: {
                slug: slug
            },
            success: function(link) {

                jQuery('#link-filter').val(link['id']).trigger('change');
                jQuery('#source-filter').val('').trigger('change');

                var $linksDatePicker = jQuery('input[name="links-daterange"]');
                var startDate = $linksDatePicker.data('daterangepicker').startDate;
                var endDate = $linksDatePicker.data('daterangepicker').endDate;

                var $reportsDatePicker = jQuery('input[name="reports-daterange"]');
                $reportsDatePicker.data('daterangepicker').setStartDate(startDate);
                $reportsDatePicker.data('daterangepicker').setEndDate(endDate);

                var hasFunnelLinks = false;
                if (link.settings.funnelLinks &&
                    link.settings.funnelLinks.length > 0) {
                    hasFunnelLinks = true;
                } else if (link.settings.voltifyOptions &&
                    link.settings.voltifyOptions.linkReplacements &&
                    Object.keys(link.settings.voltifyOptions.linkReplacements).length > 0) {
                    hasFunnelLinks = true;
                }

                if (hasFunnelLinks) {
                    refreshSegments("Source > Funnel Links");
                } else {
                    refreshSegments("Source > URL");
                }

                refreshDrilldownReport(function() {
                    ClickerVoltFunctions.selectTab('#tabs-for-stats', 'tab-reports');
                    jQuery('html,body').animate({
                        scrollTop: 0
                    }, 'slow');
                    $element.prop('disabled', false).html(htmlBackup);
                });

            },
            complete: function() {}
        });
    }

    /**
     * 
     */
    function editSlugClicked($element, slug) {

        var htmlBackup = $element.html();
        var replaceFrom = '<i class="material-icons stats-row edit"></i>';

        var pluginUrl = clickerVoltVars.urls.plugin;
        var loadingImageUrl = pluginUrl + '/admin/images/icons/loading-18px.gif?v=2';
        var replaceTo = `<img src='${loadingImageUrl}' style='position: relative; top: 4px; left: 0px;' />`;

        $element.prop('disabled', true).html(htmlBackup.replace(replaceFrom, replaceTo));

        ClickerVoltLinkController.loadSlugFromSlugName(slug, null, function() {
            $element.prop('disabled', false).html(htmlBackup);
            jQuery("#options-accordion").accordion('option', 'active', 0);
            jQuery('html,body').animate({
                scrollTop: 0
            }, 'fast');
        });
    }

    /**
     * 
     */
    function initReportsTable() {

        initReportsFilters();
        initSegmentSelects();
        jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE).trigger('change');

        var table = ClickerVoltStatsFunctions.initStatsTable({
            containerSelector: '#datatables-reports',
            ajaxSource: 'wp_ajax_clickervolt_get_stats',
            ajaxData: {
                segments: [],
                linkIdFilter: clickerVoltVars.const.ReportTypes.LINKS_ALL_AGGREGATED,
            },
            ajaxDataCallback: function(d) {
                setRequestSegments(d);
            },
            ajaxRenderedCallback: function(rows, ajaxData) {
                if (!window.clickerVoltVars.initReportsTableRenderedAtLeastOnce) {
                    window.clickerVoltVars.initReportsTableRenderedAtLeastOnce = true;
                    ClickerVoltStatsFunctions.expandAll(jQuery('#datatables-reports'));
                }
            },
            datePickerSyncGroup: 'stats',
            datePickerSelector: 'input[name="reports-daterange"]',
            onDateChanged: function(start, end) {
                changeReportRefreshButtonToApply();
            }
        });

        ClickerVoltStatsFunctions.enableHeatmap(jQuery('#datatables-reports'), ClickerVoltStatsFunctions.COLUMN_PROFIT, true);

        initStatButtons();
        initReportsHeader();
        changeReportApplyButtonToRefresh();

        return table;
    }

    /**
     * 
     */
    function initReportsHeader() {
        jQuery('.reports-toolbar')
            .appendTo(jQuery('#datatables-reports').parent().find('div.stats-table-toolbar'))
            .show();
    }

    /**
     * 
     */
    function initReportsFilters() {

        initSlugFilter();
        initSourceFilter();

        jQuery('.segment-filter').on('change', function() {
            changeReportRefreshButtonToApply();
        });
    }

    /**
     * 
     */
    function initSlugFilter() {

        var $select = jQuery('#link-filter');

        $select.find('option').each(function() {
            var $option = jQuery(this);
            if (!$option.attr('reserved')) {
                $option.remove();
            }
        });

        $select.append(`<option selected="true" value="${clickerVoltVars.const.ReportTypes.LINKS_ALL_AGGREGATED}">All Links (Aggregated)</option>`);
        $select.append(`<option value="${clickerVoltVars.const.ReportTypes.LINKS_ALL_SEPARATED}">All Links (Separated)</option>`);

        ClickerVoltFunctions.ajax('wp_ajax_clickervolt_get_all_slugs', null, {

            success: function(slugInfos) {

                for (var i = 0; i < slugInfos.length; i++) {
                    var id = slugInfos[i]['id'];
                    var slug = slugInfos[i]['slug'];
                    $select.append(`<option value="${id}">${slug}</option>`);
                }

                ClickerVoltFunctions.initSelect2($select, {
                    theme: 'default segment-filter'
                });
            },
            complete: function() {}
        });
    }

    /**
     * 
     */
    function initSourceFilter() {

        var $select = jQuery('#source-filter');

        $select.find('option').each(function() {
            var $option = jQuery(this);
            if (!$option.attr('reserved')) {
                $option.remove();
            }
        });

        ClickerVoltFunctions.ajax('wp_ajax_clickervolt_get_sources', null, {

            success: function(response) {

                var sources = response.sources;
                ClickerVoltFunctions.setOption('sourceTemplates', sources, $select);

                for (var sourceId in sources) {
                    var name = sources[sourceId]['sourceName'];
                    $select.append(`<option value="${sourceId}">${name}</option>`);
                }

                ClickerVoltFunctions.initSelect2($select, {
                    theme: 'default segment-filter'
                });
            },
            complete: function() {}
        });

        $select.off('change', refreshVarNames);
        $select.on('change', refreshVarNames);
    }

    /**
     * 
     */
    function initSegmentSelects(varNameSuffixes) {

        var $select = jQuery('select.segment-select');

        var selectedOptions = {};
        $select.each(function(index) {
            selectedOptions[index] = jQuery(this).find('option:selected').val();
        })

        $select.find('option').each(function() {
            var $option = jQuery(this);
            if (!$option.attr('reserved')) {
                $option.remove();
            }
        });

        var segments = getSegments(varNameSuffixes);
        for (var i = 0; i < segments.length; i++) {
            var segmentId = removeSourceVarName(segments[i]);
            var segmentName = segments[i];
            $select.append(`<option value="${segmentId}">${segmentName}</option>`);
        }

        ClickerVoltFunctions.initSelect2($select);

        $select.each(function(index) {
            jQuery(this).val(selectedOptions[index]).trigger('change');
        })

        $select.off('change', changeReportRefreshButtonToApply);
        $select.on('change', changeReportRefreshButtonToApply);
    }

    function changeLinksRefreshButtonToApply() {
        changeRefreshButtonToApply('#btn-links-refresh');
    }

    function changeLinksApplyButtonToRefresh() {
        changeApplyButtonToRefresh('#btn-links-refresh');
    }

    function changeReportRefreshButtonToApply() {
        changeRefreshButtonToApply('#btn-stats-refresh');
    }

    function changeReportApplyButtonToRefresh() {
        changeApplyButtonToRefresh('#btn-stats-refresh');
    }

    function changeRefreshButtonToApply(buttonSelector) {
        jQuery(buttonSelector).html('<i class="material-icons for-button apply"></i>Apply').addClass('green');
    }

    function changeApplyButtonToRefresh(buttonSelector) {
        jQuery(buttonSelector).html('<i class="material-icons for-button refresh"></i>Refresh').removeClass('green');
    }

    /**
     * 
     */
    function initStatButtons() {

        jQuery('button.stat-button').each(function() {

            var $button = jQuery(this);
            var list = $button.attr('list');
            if (list) {
                var id = ClickerVoltFunctions.uuid();
                var tags = [
                    `<div id="${id}" class="jq-dropdown jq-dropdown-tip jq-dropdown-relative">`,
                    '<ul class="jq-dropdown-menu">'
                ];

                var subItems = list.split('|');
                for (var i = 0; i < subItems.length; i++) {
                    tags.push(`<li><a class="stat-button">${subItems[i]}</a></li>`);
                }

                tags.push('</ul></div>');
                var html = tags.join('');
                jQuery(html).insertAfter($button);

                $button.addClass('not-triggerable');
                $button.attr('data-jq-dropdown', `#${id}`);
                $button.append('<i class="material-icons for-button dropdown"></i>');
            }
        });

        jQuery('.stat-button:not(.not-triggerable)').on('click', function() {
            var name = jQuery(this).text();
            refreshSegments(name);
        });

        jQuery('#btn-stats-refresh').on('click', function() {
            var $button = jQuery(this);
            $button.prop('disabled', true);
            forceProcessClicksQueue(function() {
                refreshDrilldownReport(function() {
                    $button.prop('disabled', false);
                });
            });
        });
    }

    /**
     * 
     */
    function forceProcessClicksQueue(onComplete) {
        ClickerVoltFunctions.ajax('wp_ajax_clickervolt_process_clicks_queue', null, {
            success: function() {
                if (onComplete) {
                    onComplete();
                }
            }
        });
    }

    /**
     * 
     */
    function refreshDrilldownReport(onComplete) {
        var dt = jQuery('#datatables-reports').DataTable();
        dt.ajax.reload(function() {
            changeReportApplyButtonToRefresh();
            ClickerVoltStatsFunctions.expandAll(jQuery('#datatables-reports'));

            if (onComplete) {
                onComplete();
            }
        });
    }

    /**
     * 
     */
    function refreshSegments(name) {

        name = removeSourceVarName(name);

        jQuery("#segment1").val("");
        jQuery("#segment2").val("");
        jQuery("#segment3").val("");

        switch (name) {
            case "Source > URL":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_URL);
                break;

            case "Source > Suspicious VS Clean":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_SUSPICIOUS_VS_CLEAN);
                break;

            case "Source > Suspicious Buckets":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_SUSPICIOUS_BUCKETS);
                break;

            case "Source > V1":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_1);
                break;

            case "Source > V2":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_2);
                break;

            case "Source > V3":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_3);
                break;

            case "Source > V4":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_4);
                break;

            case "Source > V5":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_5);
                break;

            case "Source > V6":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_6);
                break;

            case "Source > V7":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_7);
                break;

            case "Source > V8":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_8);
                break;

            case "Source > V9":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_9);
                break;

            case "Source > V10":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_10);
                break;

            case "Source":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                break;

            case "V1":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_1);
                break;

            case "V2":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_2);
                break;

            case "V3":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_3);
                break;

            case "V4":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_4);
                break;

            case "V5":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_5);
                break;

            case "V6":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_6);
                break;

            case "V7":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_7);
                break;

            case "V8":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_8);
                break;

            case "V9":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_9);
                break;

            case "V10":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_VAR_10);
                break;

            case "URL":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_URL);
                break;

            case "Funnel Links":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_FUNNEL_LINK);
                break;

            case "Funnel Links > URL":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_FUNNEL_LINK);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_URL);
                break;

            case "Source > Funnel Links":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_FUNNEL_LINK);
                break;

            case "Source > URL > Funnel Links":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_URL);
                jQuery("#segment3").val(clickerVoltVars.const.ReportingSegments.TYPE_FUNNEL_LINK);
                break;

            case "Source > Suspicious VS Clean > Funnel Links":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SOURCE);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_SUSPICIOUS_VS_CLEAN);
                jQuery("#segment3").val(clickerVoltVars.const.ReportingSegments.TYPE_FUNNEL_LINK);
                break;

            case "Suspicious VS Clean":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SUSPICIOUS_VS_CLEAN);
                break;

            case "Suspicious Buckets":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_SUSPICIOUS_BUCKETS);
                break;

            case "Device Type":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_TYPE);
                break;

            case "Device Brand":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_BRAND);
                break;

            case "Device Name":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_NAME);
                break;

            case "OS Name":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_OS);
                break;

            case "OS Version":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_OS_VERSION);
                break;

            case "Browser Name":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_BROWSER);
                break;

            case "Browser Version":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_BROWSER_VERSION);
                break;

            case "Country":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_GEO_COUNTRY);
                break;

            case "Country > Region":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_GEO_COUNTRY);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_GEO_REGION);
                break;

            case "Country > City > ZIP":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_GEO_COUNTRY);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_GEO_CITY);
                jQuery("#segment3").val(clickerVoltVars.const.ReportingSegments.TYPE_GEO_ZIP);
                break;

            case "Country Tier > Country":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_GEO_COUNTRY_TIER);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_GEO_COUNTRY);
                break;

            case "Timezone":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_GEO_TIMEZONE);
                break;

            case "Language":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_LANGUAGE);
                break;

            case "ISP":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_CONNECTION_ISP);
                break;

            case "Proxy":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_CONNECTION_PROXY);
                break;

            case "Connection Type":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_CONNECTION_CELLULAR);
                break;

            case "Connection Type > ISP":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_CONNECTION_CELLULAR);
                jQuery("#segment2").val(clickerVoltVars.const.ReportingSegments.TYPE_CONNECTION_ISP);
                break;

            case "IP-Range 1.2.3.xxx":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_IP_RANGE_C);
                break;

            case "IP-Range 1.2.xxx.xxx":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_IP_RANGE_B);
                break;

            case "Referrer Domain":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_REFERRER_DOMAIN);
                break;

            case "Referrer URL":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_REFERRER);
                break;

            case "Date":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_TIME_DATES);
                break;

            case "Day of Week":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_TIME_DAY_OF_WEEK);
                break;

            case "Hour of Day":
                jQuery("#segment1").val(clickerVoltVars.const.ReportingSegments.TYPE_TIME_HOUR_OF_DAY);
                break;
        }

        jQuery('.segment-select').trigger('change');
    }

    /**
     * 
     * @return { id1: name1, id2: name2, etc... }
     */
    function getSegments(varNameSuffixes) {

        if (!varNameSuffixes) {
            varNameSuffixes = {};
        }

        var segments = [
            clickerVoltVars.const.ReportingSegments.TYPE_FUNNEL_LINK,
            clickerVoltVars.const.ReportingSegments.TYPE_SOURCE,
            clickerVoltVars.const.ReportingSegments.TYPE_SUSPICIOUS_VS_CLEAN,
            clickerVoltVars.const.ReportingSegments.TYPE_SUSPICIOUS_BUCKETS,
            clickerVoltVars.const.ReportingSegments.TYPE_URL,
            clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_TYPE,
            clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_BRAND,
            clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_NAME,
            clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_OS,
            clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_OS_VERSION,
            clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_BROWSER,
            clickerVoltVars.const.ReportingSegments.TYPE_DEVICE_BROWSER_VERSION,
            clickerVoltVars.const.ReportingSegments.TYPE_GEO_COUNTRY,
            clickerVoltVars.const.ReportingSegments.TYPE_GEO_COUNTRY_TIER,
            clickerVoltVars.const.ReportingSegments.TYPE_GEO_REGION,
            clickerVoltVars.const.ReportingSegments.TYPE_GEO_CITY,
            clickerVoltVars.const.ReportingSegments.TYPE_GEO_ZIP,
            clickerVoltVars.const.ReportingSegments.TYPE_GEO_TIMEZONE,
            clickerVoltVars.const.ReportingSegments.TYPE_LANGUAGE,
            clickerVoltVars.const.ReportingSegments.TYPE_CONNECTION_ISP,
            // clickerVoltVars.const.ReportingSegments.TYPE_CONNECTION_PROXY,
            // clickerVoltVars.const.ReportingSegments.TYPE_CONNECTION_CELLULAR,
            clickerVoltVars.const.ReportingSegments.TYPE_IP_RANGE_C,
            clickerVoltVars.const.ReportingSegments.TYPE_IP_RANGE_B,
            clickerVoltVars.const.ReportingSegments.TYPE_REFERRER,
            clickerVoltVars.const.ReportingSegments.TYPE_REFERRER_DOMAIN,
            clickerVoltVars.const.ReportingSegments.TYPE_TIME_DATES,
            clickerVoltVars.const.ReportingSegments.TYPE_TIME_DAY_OF_WEEK,
            clickerVoltVars.const.ReportingSegments.TYPE_TIME_HOUR_OF_DAY,
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_1 + (varNameSuffixes['V1'] || ''),
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_2 + (varNameSuffixes['V2'] || ''),
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_3 + (varNameSuffixes['V3'] || ''),
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_4 + (varNameSuffixes['V4'] || ''),
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_5 + (varNameSuffixes['V5'] || ''),
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_6 + (varNameSuffixes['V6'] || ''),
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_7 + (varNameSuffixes['V7'] || ''),
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_8 + (varNameSuffixes['V8'] || ''),
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_9 + (varNameSuffixes['V9'] || ''),
            clickerVoltVars.const.ReportingSegments.TYPE_VAR_10 + (varNameSuffixes['V10'] || ''),
        ];

        return segments;
    }

    /**
     * 
     */
    function refreshVarNames() {

        var sourceId = jQuery('#source-filter option:selected').val();
        var varNameSuffixes = {
            'V1': '',
            'V2': '',
            'V3': '',
            'V4': '',
            'V5': '',
            'V6': '',
            'V7': '',
            'V8': '',
            'V9': '',
            'V10': '',
        };

        if (sourceId) {
            var sourceTemplates = ClickerVoltFunctions.getOption('sourceTemplates', jQuery('#source-filter'));
            var source = sourceTemplates[sourceId];
            if (source) {
                varNameSuffixes['V1'] = getSourceVarNameSuffix(source['v1Name']);
                varNameSuffixes['V2'] = getSourceVarNameSuffix(source['v2Name']);
                varNameSuffixes['V3'] = getSourceVarNameSuffix(source['v3Name']);
                varNameSuffixes['V4'] = getSourceVarNameSuffix(source['v4Name']);
                varNameSuffixes['V5'] = getSourceVarNameSuffix(source['v5Name']);
                varNameSuffixes['V6'] = getSourceVarNameSuffix(source['v6Name']);
                varNameSuffixes['V7'] = getSourceVarNameSuffix(source['v7Name']);
                varNameSuffixes['V8'] = getSourceVarNameSuffix(source['v8Name']);
                varNameSuffixes['V9'] = getSourceVarNameSuffix(source['v9Name']);
                varNameSuffixes['V10'] = getSourceVarNameSuffix(source['v10Name']);
            }
        }

        jQuery('ul.jq-dropdown-menu li a').each(function() {
            var text = removeSourceVarName(jQuery(this).text());
            for (varNum in varNameSuffixes) {
                if (text.match(new RegExp(`(|\s)${varNum}(\s|$)`))) {
                    jQuery(this).text(text.replace(varNum, varNum + varNameSuffixes[varNum]));
                }
            }
        });

        initSegmentSelects(varNameSuffixes);
    }

    function getSourceVarNameSeparator() {
        return ': ';
    }

    function getSourceVarNameSuffix(varName) {
        if (varName) {
            return getSourceVarNameSeparator() + varName
        }

        return '';
    }

    /**
     * 
     */
    function removeSourceVarName(text) {

        var index = text.indexOf(getSourceVarNameSeparator());
        if (index != -1) {
            text = text.substring(0, index);
        }

        return text;
    }

    /**
     * 
     */
    function setRequestSegments(data) {

        var seg0 = clickerVoltVars.const.ReportingSegments.TYPE_LINK;
        var seg1 = jQuery("#segment1 option:selected").val();
        var seg2 = jQuery("#segment2 option:selected").val();
        var seg3 = jQuery("#segment3 option:selected").val();

        if (seg0) {
            data.segments.push(seg0);
        }
        if (seg1) {
            data.segments.push(seg1);
        }
        if (seg2) {
            data.segments.push(seg2);
        }
        if (seg3) {
            data.segments.push(seg3);
        }

        data.linkIdFilter = jQuery('#link-filter option:selected').val();
        data.sourceIdFilter = jQuery('#source-filter option:selected').val();
    }
</script>