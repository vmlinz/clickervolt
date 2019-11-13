<div id="tab-reports" class="tab-content">

    <div id="reports-header">

        <div class="stat-buttons-shortcuts">
            <button class="button stat-button" list="Funnel Links|Funnel Links > URL|Source > Funnel Links|Source > URL > Funnel Links|Source > Suspicious VS Clean > Funnel Links">Funnel Links</button>
            <button class="button stat-button" list="Source|Source > Suspicious VS Clean|Source > Suspicious Buckets|Source > URL|Source > V1|Source > V2|Source > V3|Source > V4|Source > V5|Source > V6|Source > V7|Source > V8|Source > V9|Source > V10">Source</button>
            <button class="button stat-button" list="V1|V2|V3|V4|V5|V6|V7|V8|V9|V10">Source Vars</button>
            <button class="button stat-button" list="Suspicious VS Clean|Suspicious Buckets">Suspicious Traffic</button>
            <button class="button stat-button">URL</button>
            <button class="button stat-button" list="Device Type|Device Brand|Device Name">Device</button>
            <button class="button stat-button" list="OS Name|OS Version">OS</button>
            <button class="button stat-button" list="Browser Name|Browser Version">Browser</button>
            <button class="button stat-button" list="Country|Country > Region|Country > City > ZIP|Country Tier > Country|Timezone">Geo</button>
            <button class="button stat-button">Language</button>
            <!-- <button class="button stat-button" list="Connection Type|ISP|Proxy|Connection Type > ISP">Connection</button> -->
            <button class="button stat-button">ISP</button>
            <button class="button stat-button" list="IP-Range 1.2.3.xxx|IP-Range 1.2.xxx.xxx">IP</button>
            <button class="button stat-button" list="Referrer Domain|Referrer URL">Referrer</button>
            <button class="button stat-button" list="Date|Day of Week|Hour of Day">Time</button>
        </div>

        <table id="segments-and-controls">
            <tbody>
                <tr>
                    <td>
                        <div class="segment-selects">
                            <span class="segment-filters">
                                <select class="segment-filter" id='link-filter'></select>
                                <select class="segment-filter" id='source-filter'>
                                    <option value='' reserved="true">All Sources</option>
                                </select>
                            </span>

                            <select class="segment-select" id='segment1'>
                                <option value='' reserved="true">Choose segment 1</option>
                            </select>
                            <select class="segment-select" id='segment2'>
                                <option value='' reserved="true">Choose segment 2</option>
                            </select>
                            <select class="segment-select" id='segment3'>
                                <option value='' reserved="true">Choose segment 3</option>
                            </select>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <span class="reports-toolbar" style="display: none;">
            <button id="btn-stats-refresh" class="button button-primary"><i class="material-icons for-button refresh"></i>Refresh</button>
            <input class="daterange" type="text" name="reports-daterange" readonly />
            <select class="heatmap" for="#datatables-reports"></select>
        </span>

    </div>

    <table id="datatables-reports" class="reporting-table stats-table-with-fixed-header"></table>
</div>