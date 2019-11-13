<?php
require_once __DIR__ . '/../../utils/countryCodes.php';
require_once __DIR__ . '/../../utils/languageCodes.php';
require_once __DIR__ . '/../../others/device-detector/Parser/OperatingSystem.php';
require_once __DIR__ . '/../../others/device-detector/Parser/Client/Browser.php';
require_once __DIR__ . '/../../others/device-detector/Parser/Device/DeviceParserAbstract.php';

ClickerVolt\ViewLoader::trackingURL();
?>

<div class='wrap clickervolt-section-new-link'>

    <form id="create-link-form" method="post" novalidate="novalidate">

        <table class="link-settings">
            <tbody>
                <tr>
                    <th scope="row"></th>
                    <td id="td-link-slug">
                        <span id="linkslug-nav-summary" style="display: none;">
                            <label>Currently opened: </label>
                            <input data-min-size='10' readonly="true" type="text" id="linkslug-ro" value="" class="alphanum-and-dash lowercase input-as-change auto-resize">
                        </span>
                        <select id="select-slug-copy-from" class="select2">
                            <option value="" reserved="reserved">Create from...</option>
                            <option value="reset" reserved="reserved">Empty template</option>
                        </select>
                        <select id="select-slug-edit-existing" class="select2">
                            <option value="" reserved="reserved">Edit existing...</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="all-link-settings"></label></th>
                    <td id="td-all-settings">

                        <div id="tabs-for-link-edit" class="tabs-wrapper">

                            <ul class="tabs">
                                <li class="tab-link current" data-tab="tab-link-general">Basic Settings</li>
                                <li class="tab-link" data-tab="tab-link-rules">Redirect Rules</li>
                                <li class="tab-link" data-tab="tab-link-funnel-links">Funnel Links</li>
                                <li class="tab-link" data-tab="tab-link-cost">Cost</li>
                                <li class="tab-link" data-tab="tab-link-aida">AIDA</li>
                                <li class="tab-link" data-tab="tab-link-pixels">Conversion Pixels</li>
                                <li class="tab-link" data-tab="tab-link-hooks">Hooks</li>
                                <li class="tab-link" data-tab="tab-link-bot-detection">Fraud Detection</li>
                            </ul>

                            <div id="tab-link-general" class="tab-content current">
                                <table class="link-settings">
                                    <tbody>
                                        <tr>
                                            <th scope="row"><label for="linkslug">Link slug *</label></th>
                                            <td>
                                                <input data-min-size='10' type="text" name="linkslug" id="linkslug" value="" class="alphanum-and-dash lowercase input-as-change auto-resize" placeholder="your-link-slug">
                                                <a id="slug-aliases" class="button">Slug Aliases</a>
                                                <input type="hidden" name="linkid" id="linkid" readonly>
                                                <p class="description">Only alpha-numeric and dash allowed, all in lowercase.</p>
                                                <div id="slug-aliases-panel" style='display: none;'>
                                                    <p>Slug Aliases are synonyms of your main link slug above.</p>
                                                    <p>They can be used interchangeably in your tracking URLs.</p>
                                                    <textarea name="slug-aliases" rows="8" class="alphanum-and-dash lowercase input-as-change" data-max-chars-per-line="128"></textarea>
                                                    <p><a class="button add-random-slug-aliases" data-number="10">+ 10 Random Aliases</a></p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style="display: none;">
                                            <th scope="row"><label for="linkdist">Link distribution *</label></th>
                                            <td>
                                                <select name="linkdist" id="linkdist">
                                                    <option value="<?= ClickerVolt\DistributionRandom::TYPE ?>" selected="selected">Random</option>
                                                    <option value="<?= ClickerVolt\DistributionSequential::TYPE ?>">Sequential</option>
                                                    <option value="<?= ClickerVolt\DistributionAutoOptim::TYPE ?>">Auto-Optimization</option>
                                                </select>
                                                <label class="linkdist-sequential-counter" style="display: none;">Counter:
                                                    <input type="text" name="linkdist-sequential-counter" id="linkdist-sequential-counter" value="1" class="regular-text numeric positive input-as-change">
                                                </label>
                                                <p id="description-for-linkdist" class="description"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="default-path">Target URLs *</label></th>
                                            <td id="default-path">
                                                <table id="target-urls-template" class="target-urls" style="display: none;">
                                                    <thead>
                                                        <tr>
                                                            <th>URL</th>
                                                            <th class="weight-info">Weight</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="urls-list">
                                                        <tr>
                                                            <td class="url-row">
                                                                <input type="text" required value="" class="regular-text url" placeholder="http(s)://">
                                                                <a class="button url-variables-open" title="Click to open variables editor...">Vars...</a>
                                                                <div class="url-variables" style="display: none;">
                                                                    <table>
                                                                        <thead style="display: none;">
                                                                            <tr>
                                                                                <th>Variable Name</th>
                                                                                <th></th>
                                                                                <th>Variable Value</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr class="variable-row variable-row-template" style="display: none;">
                                                                                <td><input type="text" class="url-variable-key" value="cid"></td>
                                                                                <td> = </td>
                                                                                <td>
                                                                                    <select class="url-variable-value"></select>
                                                                                    <i class="url-variable-delete material-icons inline-delete" style=""></i>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                        <tfoot>
                                                                            <tr>
                                                                                <td colspan="3">
                                                                                    <input type="button" class="button add-url-variable" value="+ Variable">
                                                                                    <span class="aff-networks-tracking-ids"></span>
                                                                                    <a class="button aff-network-postback-url-copy"><i class="material-icons for-button copy"></i>Copy Postback URL</a>
                                                                                    <input style="display: none;" type="text" class="aff-network-postback-url" value="">
                                                                                </td>
                                                                            </tr>
                                                                        </tfoot>
                                                                    </table>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <input type="text" value="100" class="regular-text weight-info weight numeric">
                                                                <i class="path-delete-url material-icons inline-delete" style="display: none;"></i>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="2"><input type="button" class="button add-url" value="+ URL"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="redirect-mode">Redirect Mode</label></th>
                                            <td>
                                                <select name="redirect-mode" id="redirect-mode">
                                                    <option value="<?= ClickerVolt\Link::REDIRECT_MODE_PERMANENT ?>" selected="selected">301: Permanent Redirect</option>
                                                    <option value="<?= ClickerVolt\Link::REDIRECT_MODE_TEMPORARY ?>">302: Temporary Redirect</option>
                                                    <option value="<?= ClickerVolt\Link::REDIRECT_MODE_DMR ?>">Double Meta Refresh</option>
                                                    <option value="<?= ClickerVolt\Link::REDIRECT_MODE_CLOAKING ?>">Cloaked (iframe)</option>
                                                    <option value="<?= ClickerVolt\Link::REDIRECT_MODE_VOLTIFY ?>">Voltify Takeover</option>
                                                </select>
                                                <span id="cloaking-options" style="display: none;">
                                                    <h3 class="button">Cloaking Options</h3>
                                                    <div class="options-panel" style="display: none;">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Meta Title</th>
                                                                    <td><input name="cloaking-option-meta-title" type="text" value=""></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Meta Description</th>
                                                                    <td><input name="cloaking-option-meta-description" type="text" value=""></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Meta Keywords</th>
                                                                    <td><input name="cloaking-option-meta-keywords" type="text" value=""></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>No Index</th>
                                                                    <td><input name="cloaking-option-no-index" type="checkbox"></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>No Follow</th>
                                                                    <td><input name="cloaking-option-no-follow" type="checkbox"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </span>
                                                <span id="voltify-options" style="display: none;">
                                                    <h3 class="button">Voltify Options</h3>
                                                    <div class="options-panel" style="display: none;">
                                                        <table>
                                                            <tbody>
                                                                <tr class="toggles">
                                                                    <th>Keep in Cache (recommended)</th>
                                                                    <td><input name="voltify-option-cache" type="checkbox"></td>
                                                                </tr>
                                                                <tr class="toggles">
                                                                    <th>Add AIDA Script</th>
                                                                    <td><input name="voltify-inject-aida" type="checkbox"></td>
                                                                </tr>
                                                                <tr class="toggles">
                                                                    <th>Voltify All Internal URLs</th>
                                                                    <td><input name="voltify-internal-urls" type="checkbox"></td>
                                                                </tr>
                                                                <tr class="toggles">
                                                                    <th>Disable Analytics (beta)</th>
                                                                    <td><input name="voltify-option-disable-analytics" type="checkbox"></td>
                                                                </tr>
                                                                <tr class="toggles">
                                                                    <th>Disable Popups (beta)</th>
                                                                    <td><input name="voltify-option-disable-popups" type="checkbox"></td>
                                                                </tr>

                                                                <tr class="section link-replacements">
                                                                    <th>Link Replacements (you can use * as wildcard)</th>
                                                                    <td></td>
                                                                </tr>
                                                                <tr class="link-replacement-row template" style="display: none;">
                                                                    <td colspan="2">
                                                                        <input class="link-replacement-from from-to" type="text">
                                                                        <span class="link-replacement-from-to-symbol"> >> </span>
                                                                        <select class="link-replacement-to">
                                                                            <option value="" reserved="reserved">Select destination...</option>
                                                                        </select>
                                                                        <i class="link-replacement-delete material-icons inline-delete"></i>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2"><a class="button add-link-replacement">+ Link Replacement</a></td>
                                                                </tr>

                                                                <tr class="section static-content-replacements">
                                                                    <th>Static Content Replacements</th>
                                                                    <td></td>
                                                                </tr>
                                                                <tr class="static-content-replacement-row template" style="display: none;">
                                                                    <td colspan="2">
                                                                        <input class="static-content-replacement-from from-to" type="text">
                                                                        <span class="static-content-replacement-from-to-symbol"> >> </span>
                                                                        <input class="static-content-replacement-to" type="text">
                                                                        <i class="static-content-replacement-delete material-icons inline-delete"></i>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2"><a class="button add-static-content-replacement">+ Static Content Replacement</a></td>
                                                                </tr>

                                                                <tr class="section dynamic-content-replacements">
                                                                    <th>Dynamic Content Replacements</th>
                                                                    <td></td>
                                                                </tr>
                                                                <tr class="dynamic-content-replacement-row template" style="display: none;">
                                                                    <td colspan="2">
                                                                        <input class="dynamic-content-replacement-from from-to" type="text">
                                                                        <span class="dynamic-content-replacement-from-to-symbol"> >> </span>
                                                                        <select class="dynamic-content-replacement-to">
                                                                            <option value="" reserved="reserved">Select Token...</option>
                                                                        </select>
                                                                        <i class="dynamic-content-replacement-delete material-icons inline-delete"></i>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2"><a class="button add-dynamic-content-replacement">+ Dynamic Content Replacement</a></td>
                                                                </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </span>
                                                <p id="description-for-redirect-mode" class="description">
                                                    <div id="voltify-disabled-due-to-permalinks" class="alert" style="display: none;">WARNING: The Voltify redirect mode only works with Pretty URLs. You must set your <a href="<?php echo admin_url("options-permalink.php") ?>">"Permalink Settings"</a> to anything else other than "Plain")</div>
                                                    <div id="voltify-teaser" style="display: none;">"Voltify Takeover" is a premium add-on (<a href="https://doc.clickervolt.com/clickervolt-add-ons/voltify-takeover-redirect-mode" target="_blank">more info here</a>) - <a href="<?= \ClickerVolt\cli_fs()->get_upgrade_url(); ?>">upgrade</a> to use it now</div>
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="tab-link-rules" class="tab-content">

                                <div class="rule-block template" style="display: none;">

                                    <div class="rule-header">
                                        <span class="rule-name">Rule Conditions</span>
                                        <i class="rule-block-delete material-icons inline-delete"></i>
                                    </div>

                                    <div class="rule-conditions">
                                        <div class="rule-condition template" style="display: none;">

                                            <div class="if">IF</div>

                                            <select class="rule-type">
                                                <option value="">Select Rule Type...</option>
                                            </select>

                                            <select class="rule-operator" style="display: none;">
                                                <option value="is">IS</option>
                                                <option value="is-not">IS NOT</option>
                                                <option value="contains">CONTAINS</option>
                                                <option value="contains-not">DOES NOT CONTAIN</option>
                                                <option value="empty">IS EMPTY</option>
                                                <option value="empty-not">IS NOT EMPTY</option>
                                                <option value="lt">LESS THAN</option>
                                                <option value="lte">LESS THAN OR EQUAL</option>
                                                <option value="gt">MORE THAN</option>
                                                <option value="gte">MORE THAN OR EQUAL</option>
                                            </select>

                                            <i class="rule-condition-delete material-icons inline-delete"></i>

                                            <div class="rule-values">

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_COUNTRY ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Select countries...">
                                                        <?php
                                                        foreach (ClickerVolt\CountryCodes::MAP as $code => $name) {
                                                            echo "<option value='{$code}'>{$name}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_REGION ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Type to search (3+ chars)..." data-ajaxsource="wp_ajax_clickervolt_search_regions"></select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_CITY ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Type to search (3+ chars)..." data-ajaxsource="wp_ajax_clickervolt_search_cities"></select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_ISP ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Type to search (3+ chars)..." data-ajaxsource="wp_ajax_clickervolt_search_isps"></select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_IP ?>" style="display: none;">
                                                    <span>
                                                        <textarea class="rule-value" rows="8"></textarea>
                                                    </span>
                                                    <span class="inline-doc">
                                                        <p>One entry per line. IPv4 Supported formats:</p>
                                                        <p>
                                                            <ul>
                                                                <li>1.2.3.4 for a single IP</li>
                                                                <li>1.2.3.* where * are wildcards</li>
                                                                <li>1.2.3.4-5.6.7.8 for an IP range</li>
                                                                <li>1.2.3.4/24 or 1.2.3.4/255.255.255.0 for an IP range in CIDR format</li>
                                                            </ul>
                                                        </p>
                                                        <p>IPv6 Supported formats:</p>
                                                        <p>
                                                            <ul>
                                                                <li>2401:fa00:c:14:65c5:ab2b:3ebe:41d9 for a single IP</li>
                                                                <li>2001:5c0:1400::/39 for an IP range in CIDR format</li>
                                                            </ul>
                                                        </p>
                                                    </span>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_LANGUAGE ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Select languages...">
                                                        <?php
                                                        foreach (ClickerVolt\LanguageCodes::MAP as $code => $name) {
                                                            echo "<option value='{$code}'>{$name}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_USER_AGENT ?>" style="display: none;">
                                                    <span>
                                                        <textarea class="rule-value" rows="8"></textarea>
                                                    </span>
                                                    <span class="inline-doc">
                                                        <p>One entry per line.</p>
                                                    </span>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_DEVICE_TYPE ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Select Device Types...">
                                                        <?php
                                                        foreach (ClickerVolt\DeviceDetection::DEVICE_TYPES as $code => $name) {
                                                            if ($code != ClickerVolt\DeviceDetection::DEVICE_TYPE_UNKNOWN) {
                                                                echo "<option value='{$code}'>{$name}</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_DEVICE_BRAND ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Select Operating Systems...">
                                                        <?php
                                                        foreach (DeviceDetector\Parser\Device\DeviceParserAbstract::$deviceBrands as $code => $name) {
                                                            if ($name != 'Unknown') {
                                                                echo "<option value='{$name}'>{$name}</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_DEVICE_NAME ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Type to search (3+ chars)..." data-ajaxsource="wp_ajax_clickervolt_search_device_names"></select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_OS ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Select Operating Systems...">
                                                        <?php
                                                        foreach (DeviceDetector\Parser\OperatingSystem::getAvailableOperatingSystems() as $code => $name) {
                                                            echo "<option value='{$code}'>{$name}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_OS_VERSION ?>" style="display: none;">
                                                    <input class="rule-value" type="text">
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_BROWSER ?>" style="display: none;">
                                                    <select class="rule-value" multiple="true" placeholder="Select Browsers...">
                                                        <?php
                                                        foreach (DeviceDetector\Parser\Client\Browser::getAvailableBrowsers() as $code => $name) {
                                                            echo "<option value='{$name}'>{$name}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_BROWSER_VERSION ?>" style="display: none;">
                                                    <input class="rule-value" type="text">
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_DATE ?>" style="display: none;">
                                                    <input class="rule-value daterange" type="text" readonly>
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_URL ?>" style="display: none;">
                                                    <input class="rule-value regular-text" type="text">
                                                </div>

                                                <div class="for-<?= ClickerVolt\Rules::RULE_TYPE_REFERRER ?>" style="display: none;">
                                                    <input class="rule-value regular-text" type="text">
                                                </div>

                                            </div>
                                        </div>

                                        <a class="button rule-add-condition">+ Condition</a>
                                    </div>
                                    <div class="rule-then">
                                        <h4>Then go to...</h4>
                                        <div class="rule-path"></div>
                                    </div>
                                </div>

                                <a class="button rule-add-block">+ Rule</a>

                            </div>

                            <div id="tab-link-funnel-links" class="tab-content">
                                <p>The selected links below will be treated as "funnel links" for this current link.</p>
                                <p>A funnel link works like this: let's imagine that you have 3 links: A, B and C; where B and C are funnel links of A. If someone clicks on B or C without having clicked on A before, then B and C will work as any other regular link. But if someone clicks on A first, and then goes on clicking on B/C, then the actions (conversions) happening after that will also be reflected in A's reports, and not only in B/C's reports.</p>
                                <p>Furthermore, any source and source vars passed to B/C will be ignored, and the clicks/actions will be attributed to A's source instead.</p>
                                <select multiple="true" id="select-funnel-links" name="select-funnel-links[]" class="select2 select-funnel-links" placeholder="Click to add funnel links..."></select>
                            </div>

                            <div id="tab-link-cost" class="tab-content">
                                <table class="link-cost-table">
                                    <tr>
                                        <td>Default Cost Type:</td>
                                        <td>
                                            <select name="link-cost-type" id="link-cost-type">
                                                <option value="<?= ClickerVolt\Link::COST_TYPE_TOTAL ?>" selected="selected">Total Cost</option>
                                                <option value="<?= ClickerVolt\Link::COST_TYPE_CPC ?>">CPC</option>
                                                <option value="<?= ClickerVolt\Link::COST_TYPE_CPA ?>">CPA</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Default Cost Value:</td>
                                        <td>
                                            <input name="link-cost-value" id="link-cost-value" type="text" value="0" class="regular-text numeric float ge0">
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div id="tab-link-aida" class="tab-content">
                                <table class="link-aida-table">
                                    <tr>
                                        <td><b>A</b>ttention Triggered After:</td>
                                        <td><input type="text" name="aida-attention" value="20" class="regular-text numeric positive"> seconds</td>
                                    </tr>
                                    <tr>
                                        <td><b>I</b>nterest Triggered After:</td>
                                        <td><input type="text" name="aida-interest" value="120" class="regular-text numeric positive"> seconds</td>
                                    </tr>
                                    <tr>
                                        <td><b>D</b>esire Triggered After:</td>
                                        <td><input type="text" name="aida-desire" value="300" class="regular-text numeric positive"> seconds</td>
                                    </tr>
                                    <tr>
                                        <td><b>A</b>ction Triggered After:</td>
                                        <td>Any Conversion</td>
                                    </tr>
                                </table>

                                <div id="aida-explanation">
                                    <p>To measure AIDA (or to record organic views), you must add the script below to the pages you are sending traffic to.</p>
                                    <p>When you use the "Cloaked" redirect mode, this script is automatically added for you, even for pages hosted on other domains (and even if these are not your own domains/pages).</p>

                                    <div id="aida-script">
                                        <textarea readonly rows="7" width="100%"></textarea>
                                        <a class="button aida-script-copy"><i class="material-icons for-button copy"></i>Copy</a>
                                    </div>
                                </div>
                            </div>

                            <div id="tab-link-pixels" class="tab-content">
                                <table class="link-pixels-table">
                                    <tr>
                                        <td>Pixel HTML:</td>
                                        <td>
                                            <input id="link-pixel-html" type="text" readonly value="" class="regular-text">
                                            <a class="button link-pixel-html-copy"><i class="material-icons for-button copy"></i>Copy</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Postback URL:</td>
                                        <td>
                                            <input id="link-pixel-postback" type="text" readonly value="" class="regular-text">
                                            <a class="button link-pixel-postback-copy"><i class="material-icons for-button copy"></i>Copy</a>
                                        </td>
                                    </tr>
                                </table>

                                <div class="link-pixels-variable-editor-section">

                                    <p>Your pixel URLs above all have optional parameters.</p>
                                    <p>You can use the editor below to change their values before clicking any of the "Copy" buttons.</p>
                                    <p>Note that the variables below are not saved when you save the link. They are only used to update your pixels above before you copy them and paste them into your other systems.</p>

                                    <table class="link-pixels-variable-editor">
                                        <tr>
                                            <td>Conversion Type:</td>
                                            <td><input class="link-pixels-variable" id="link-pixels-variable-type" value=""></td>
                                            <td>This is an optional conversion type that you will be able to see in your reports. Values can be things like Lead, Sale, Upsell, etc...</td>
                                        </tr>
                                        <tr>
                                            <td>Conversion Name:</td>
                                            <td><input class="link-pixels-variable" id="link-pixels-variable-name" value=""></td>
                                            <td>This is an optional conversion name. It can be used for transaction IDs</td>
                                        </tr>
                                        <tr>
                                            <td>Conversion Amount:</td>
                                            <td><input class="link-pixels-variable" id="link-pixels-variable-amount" value=""></td>
                                            <td>This is the amount you earn when this conversion triggers</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div id="tab-link-hooks" class="tab-content">
                                <div class="hook-panel redirect template" style="display: none;">
                                    <select name="hook-redirect-actions[x]" class="hook-redirect-action">
                                        <option value="">Select Action</option>
                                        <option value="html">Execute HTML/Javascript</option>
                                        <option value="php">Execute PHP</option>
                                        <!-- <option value="get">Call URL (GET)</option>
                                        <option value="post">Call URL (POST)</option> -->
                                    </select>
                                    <i class="hook-redirect-delete material-icons inline-delete" style=""></i>
                                    <div class="hook-redirect-params html" style="display: none;">
                                        <textarea name="hook-redirect-html-code[x]" class="html-editor" data-editor="html" data-width="100%" data-height="200px"></textarea>
                                        <input name="hook-redirect-html-when[x]" type="hidden" class="hook-redirect-html-when-value">
                                        <label>When to execute:
                                            <fieldset>
                                                <label>
                                                    <input type="radio" value="before-redirect" checked="checked">
                                                    <span>Before Redirect</span>
                                                </label>
                                                <label>
                                                    <input type="radio" value="after-redirect">
                                                    <span>After Redirect</span>
                                                </label>
                                            </fieldset>
                                        </label>
                                        <div class="hook-redirect-html-when-params before-redirect" style="display: none;">
                                            <label>Display duration in ms:
                                                <input name="hook-redirect-html-duration[x]" class="html-duration regular-text numeric positive input-as-change" value="1000">
                                            </label>
                                        </div>
                                        <div class="hook-redirect-html-when-params after-redirect" style="display: none;">
                                            <p>
                                                To execute an HTML/JS hook after the link redirect completes, you must either:
                                                <ul>
                                                    <li> * Place the AIDA script on the target URL's page</li>
                                                    <li> * Or use the Cloaked redirect mode</li>
                                                </ul>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="hook-redirect-params php" style="display: none;">
                                        <textarea name="hook-redirect-php[x]" class="php-editor" data-editor="php" data-width="100%" data-height="200px">&lt;?php </textarea>
                                    </div>
                                    <div class="hook-redirect-params get" style="display: none;">
                                        <input class="url-get" placeholder="http(s)://">
                                    </div>
                                    <div class="hook-redirect-params post" style="display: none;">
                                        <input class="url-post" placeholder="http(s)://">
                                    </div>
                                </div>
                                <div class="buttons-panel">
                                    <a id="hook-button-on-redirect" class="button">Add "On Redirect" Hook</a>
                                    <!-- <a id="hook-button-on-conversion" class="button">Add "On Conversion" Hook</a> -->
                                </div>
                            </div>

                            <div id="tab-link-bot-detection" class="tab-content">
                                <select id="bot-detection-type-select" name="bot-detection-type-mode">
                                    <option value="<?= ClickerVolt\Link::FRAUD_DETECTION_MODE_NONE ?>" selected="selected">Disabled</option>
                                    <option value="<?= ClickerVolt\Link::FRAUD_DETECTION_MODE_RECAPTCHA_V3 ?>">Google Recaptcha V3</option>
                                    <option value="<?= ClickerVolt\Link::FRAUD_DETECTION_MODE_HUMAN ?>">Advanced</option>
                                </select>

                                <div class="bot-detection-section <?= ClickerVolt\Link::FRAUD_DETECTION_MODE_RECAPTCHA_V3 ?>" style="display: none;">
                                    <h2>reCAPTCHA v3</h2>
                                    <p class="description">For detecting suspicious traffic, we use Google's AI with their invisible reCAPTCHA v3. Before it can work, you must get your site and secret keys <a href="https://www.google.com/recaptcha/admin/create#v3" target="_blank">here</a>. After entering your keys below, your traffic quality will be recorded for all pages embedding the AIDA script (or using the Cloaked redirect mode)</p>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td>Site Key</td>
                                                <td><input type="text" name="recaptchav3-site-key" class="input-as-change auto-resize" data-min-size="10"></td>
                                            </tr>
                                            <tr>
                                                <td>Secret Key</td>
                                                <td><input type="text" name="recaptchav3-secret-key" class="input-as-change auto-resize" data-min-size="10"></td>
                                            </tr>
                                            <tr>
                                                <td>Hide Badge</td>
                                                <td><input type="checkbox" name="recaptchav3-hide-badge"> (<span class="description">If you hide reCAPTCHA's badge, you must link to Google's <a href="https://policies.google.com/privacy" target="_blank">privacy</a> and <a href="https://policies.google.com/terms" target="_blank">terms</a> pages wherever you place the AIDA script)</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="bot-detection-section <?= ClickerVolt\Link::FRAUD_DETECTION_MODE_HUMAN ?>" style="display: none;">
                                    <h2>Advanced Fraud Detection</h2>
                                    <p class="description">This suspicious traffic detection mode works backward. Instead of trying to detect the thousands and growing number of existing bots, it rather tags all traffic as suspicious except for visitors that are detected as being real humans. This is actually a much more simple task and gives extremely accurate results.</p>
                                    <p class="description">For this mode to work, it is mandatory to put the AIDA script on the page you are sending traffic to (or use the Cloaked redirect mode).</p>
                                </div>

                            </div>

                        </div>

                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="all-link-submit"></label></th>
                    <td id="td-link-submit">
                        <input type="submit" name="submit" id="submit" class="save-link button button-primary" value="Save Link">
                        <input type="submit" name="submit-and-new" id="submit-and-new" class="save-link button" value="Save + Create New">
                        <label id="saved-link-confirmation-message" class="confirmation-message"></label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"></th>
                    <td id="your-tracking-url-container"></td>
                </tr>

            </tbody>
        </table>

    </form>

</div>

<script>
    /**
     * 
     */
    jQuery(document).ready(function() {
        ClickerVoltLinkController.init();
    });

    class ClickerVoltLinkController {

        static setLinksList(list) {
            ClickerVoltLinkController.linksList = list;
        }

        static getLinksList() {
            return ClickerVoltLinkController.linksList || null;
        }

        static onLinkSaved(callback) {
            if (!ClickerVoltLinkController.linkSavedCallbacks) {
                ClickerVoltLinkController.linkSavedCallbacks = [];
            }
            ClickerVoltLinkController.linkSavedCallbacks.push(callback);
        }

        static onSourceSaved(callback) {
            if (!ClickerVoltLinkController.sourceSavedCallbacks) {
                ClickerVoltLinkController.sourceSavedCallbacks = [];
            }
            ClickerVoltLinkController.sourceSavedCallbacks.push(callback);
        }

        static init() {
            ClickerVoltFunctions.ajax('wp_ajax_clickervolt_get_aida_script_template', null, {
                success: function(template) {
                    ClickerVoltLinkController.templateAIDAScript = template;
                },
            });

            ClickerVoltFunctions.initTabs('#tabs-for-link-edit');
            ClickerVoltFunctions.initAccordionButton('#cloaking-options', ClickerVoltStatsFunctions.updateFixedHeader, ClickerVoltStatsFunctions.updateFixedHeader);
            ClickerVoltFunctions.initAccordionButton('#voltify-options', ClickerVoltStatsFunctions.updateFixedHeader, ClickerVoltStatsFunctions.updateFixedHeader);

            ClickerVoltLinkController._trackingURLHtml = new TrackingURLHtml(jQuery('#your-tracking-url-container'));
            ClickerVoltLinkController.setupForm();

            jQuery('input#linkslug').on('change', function() {
                ClickerVoltLinkController.refreshLinkSlugNavSummary();
                ClickerVoltLinkController.refreshTrackingURL();
            });

            jQuery('#redirect-mode').on('change', function() {
                ClickerVoltLinkController.refreshRedirectModeOptions();
            });

            jQuery('#bot-detection-type-select').on('change', function() {
                ClickerVoltLinkController.refreshBotDetection();
            });

            jQuery('#slug-aliases-panel textarea').on('change', ClickerVoltLinkController.refreshTrackingURL);
            jQuery('a#slug-aliases').on('click', ClickerVoltLinkController.toggleSlugAliases);
            jQuery('.add-random-slug-aliases').on('click', function() {
                ClickerVoltLinkController.addRandomSlugAliases(jQuery(this));
            });
            jQuery('input#linkdist-sequential-counter').on('change', ClickerVoltLinkController.linkDistTypeUpdated);
            jQuery('#linkdist').on('change', ClickerVoltLinkController.linkDistTypeUpdated);
            jQuery('#select-slug-copy-from').on('change', ClickerVoltLinkController.copySlugFromSelected);
            jQuery('#select-slug-edit-existing').on('change', ClickerVoltLinkController.editSelectedSlug);
            jQuery('.button.aida-script-copy').on('click', ClickerVoltLinkController.copyAIDAScriptToClipboard);
            jQuery('.button.link-pixel-html-copy').on('click', ClickerVoltLinkController.copyPixelHTMLToClipboard);
            jQuery('.button.link-pixel-postback-copy').on('click', ClickerVoltLinkController.copyPostbackToClipboard);
            jQuery('.link-pixels-variable').on('change input', ClickerVoltLinkController.refreshConversionPixels);
            jQuery('#hook-button-on-redirect').on('click', ClickerVoltLinkController.addOnRedirectHook);
            jQuery('a.button.rule-add-block').on('click', ClickerVoltLinkController.addRedirectRule);
            jQuery('a.button.add-link-replacement').on('click', function() {
                ClickerVoltLinkController.addLinkReplacement();
            });
            jQuery('a.button.add-dynamic-content-replacement').on('click', function() {
                ClickerVoltLinkController.addDynamicContentReplacement();
            });
            jQuery('a.button.add-static-content-replacement').on('click', function() {
                ClickerVoltLinkController.addStaticContentReplacement();
            });

            ClickerVoltFunctions.initAccordionButton('#source-details-accordion');

            ClickerVoltLinkController.linkDistTypeUpdated();
            ClickerVoltLinkController.refreshTrackingURL();

            ClickerVoltLinkController.getNewTargetUrlsBlock('default-path').appendTo(jQuery('td#default-path'));

            ClickerVoltLinkController.onLinkSaved(function() {
                ClickerVoltLinkController.refreshLinksLists();
            });

            ClickerVoltLinkController.refreshLinksLists();
            ClickerVoltLinkController.resetLinkFields();
        }

        /**
         * 
         */
        static refreshBotDetection() {
            var selected = jQuery('#bot-detection-type-select').val();
            jQuery('.bot-detection-section').hide();
            if (selected) {
                jQuery(`.bot-detection-section.${selected}`).show();
            }
        }

        /**
         * 
         */
        static refreshRedirectModeOptions() {
            var selected = jQuery('#redirect-mode').val();

            if (selected == clickerVoltVars.const.RedirectModes.CLOAKING) {
                jQuery('#cloaking-options').show();
            } else {
                jQuery('#cloaking-options').hide();
            }

            jQuery('#voltify-disabled-due-to-permalinks').hide();
            jQuery('#voltify-teaser').hide();
            if (selected == clickerVoltVars.const.RedirectModes.VOLTIFY) {
                if (!clickerVoltVars.settings.permalinkStructure) {
                    jQuery('#voltify-disabled-due-to-permalinks').show();
                } else {
                    if (ClickerVoltFeatures.canUseVoltify) {
                        jQuery('#voltify-options').show();
                        ClickerVoltLinkController._trackingURLHtml.allowFastestURLMode(false);
                    } else {
                        jQuery('#voltify-teaser').show();
                    }
                }
            } else {
                jQuery('#voltify-options').hide();
                ClickerVoltLinkController._trackingURLHtml.allowFastestURLMode(true);
            }
        }

        /**
         * 
         */
        static refreshLinkSlugNavSummary(force) {
            // We update the link slug summary only if force=true or if we're creating a new link 
            if (force || !jQuery('#linkid').val()) {
                var text = jQuery('input#linkslug').val();
                if (text && text.length > 0) {
                    var $linkSlugSummaryLabel = jQuery('#linkslug-nav-summary label');
                    var $linkslugSummaryInput = jQuery('#linkslug-nav-summary input');
                    jQuery('#linkslug-nav-summary').show();
                    $linkslugSummaryInput.val(text).trigger('change');
                    $linkSlugSummaryLabel.css('vertical-align', 'baseline');
                    $linkSlugSummaryLabel.css('line-height', $linkslugSummaryInput.css('height'));
                } else {
                    jQuery('#linkslug-nav-summary').hide();
                }
            }
        }

        /**
         * 
         */
        static toggleSlugAliases() {
            var $button = jQuery('a#slug-aliases');
            if ($button.hasClass('opened')) {
                $button.removeClass('opened');
                jQuery('#slug-aliases-panel').hide();
            } else {
                $button.addClass('opened');
                jQuery('#slug-aliases-panel').show();
            }
        }

        /**
         * 
         */
        static addRandomSlugAliases($button) {
            var nb = $button.data('number');
            if (!nb) {
                nb = 1;
            }

            var lines = ClickerVoltLinkController.getSlugAliases();
            for (var i = 0; i < nb; i++) {
                var alias = ClickerVoltFunctions.shortId() + ClickerVoltFunctions.shortId();
                lines.push(alias);
            }

            var $textArea = jQuery('#slug-aliases-panel textarea');
            $textArea.val(lines.join('\n')).trigger('change');
        }

        /**
         *
         */
        static addOnRedirectHook(options) {

            var block = jQuery('.hook-panel.redirect.template').clone();
            block.removeClass('template');
            block.show();

            var inputs = block.find('.hook-redirect-params.html input[type=radio]');
            inputs.attr('name', ClickerVoltFunctions.uuid());
            inputs.on('change', {
                cvBlock: block
            }, function(event) {
                var input = jQuery(this);
                if (input.is(':checked')) {
                    var when = input.attr('value');
                    event.data.cvBlock.find('.hook-redirect-html-when-params').hide();
                    event.data.cvBlock.find(`.hook-redirect-html-when-params.${when}`).show();
                    event.data.cvBlock.find('.hook-redirect-html-when-value').val(when);
                }
            });

            var blockId = ClickerVoltFunctions.uuid();
            block.attr('id', blockId);

            block.find('.hook-redirect-delete').on('click', function() {
                jQuery(this).closest('.hook-panel.redirect').remove();
            });

            var $select = block.find('select.hook-redirect-action');
            $select.on('change', function() {
                var selected = jQuery(this).find('option:selected').val();
                var $panel = jQuery(this).closest('.hook-panel.redirect');
                $panel.find('.hook-redirect-params').hide();
                if (selected) {
                    $panel.find(`.hook-redirect-params.${selected}`).show();
                }
            });

            if (options && options.action) {
                $select.val(options.action).trigger('change');
            }

            block.insertBefore(jQuery('#tab-link-hooks').find('.buttons-panel'));

            var idHtmlEditor = ClickerVoltFunctions.uuid();
            var idPHPEditor = ClickerVoltFunctions.uuid();

            var $htmlTextArea = block.find('.html-editor');
            var $phpTextArea = block.find('.php-editor');

            if (options && options.value) {

                if (options.action == 'html' && options.value.code) {
                    $htmlTextArea.val(options.value.code);
                    block.find('.html-duration').val(options.value.duration || 0);

                    var when = options.value.when || 'before-redirect';
                    block.find(`.hook-redirect-params.html input[type=radio][value=${when}]`).prop('checked', true);

                } else if (options.action == 'php' && options.value) {
                    $phpTextArea.val(options.value);
                }
            }

            $htmlTextArea.attr('id', idHtmlEditor);
            $phpTextArea.attr('id', idPHPEditor);

            ClickerVoltFunctions.textAreaToAceEditor($htmlTextArea);
            ClickerVoltFunctions.textAreaToAceEditor($phpTextArea);

            block.find('[name*=\\[x\\]]').each(function() {
                var $elem = jQuery(this);
                $elem.attr('name', $elem.attr('name').replace('[x]', `[${blockId}]`));
            });

            inputs.trigger('change');

            return block;
        }

        /**
         * 
         */
        static copyPixelHTMLToClipboard() {
            var $elem = jQuery('#link-pixel-html');
            ClickerVoltFunctions.copyToClipboard($elem.val(), $elem);
        }

        /**
         * 
         */
        static copyPostbackToClipboard() {
            var $elem = jQuery('#link-pixel-postback');
            ClickerVoltFunctions.copyToClipboard($elem.val(), $elem);
        }

        /**
         * 
         */
        static copyAIDAScriptToClipboard() {
            var $elem = jQuery('#aida-script textarea');
            ClickerVoltFunctions.copyToClipboard($elem.val(), $elem);
        }

        /**
         * 
         */
        static replaceVarsFromConvPixel(pixel, values) {
            if (!values) {
                values = {
                    'cid': '',
                    'slug': jQuery('#linkslug').val(),
                    'type': jQuery('#link-pixels-variable-type').val(),
                    'name': jQuery('#link-pixels-variable-name').val(),
                    'rev': jQuery('#link-pixels-variable-amount').val()
                };
            }
            return pixel
                .replace('-CID-', values['cid'])
                .replace('-SLUG-', values['slug'])
                .replace('-TYPE-', values['type'])
                .replace('-NAME-', values['name'])
                .replace('-REV-', values['rev']);
        }

        /** 
         * 
         */
        static refreshConversionPixels() {

            var pixelTemplate = ClickerVoltLinkController.replaceVarsFromConvPixel(clickerVoltVars.const.ConvPixelHTMLTemplate);
            var pixelPostback = ClickerVoltLinkController.replaceVarsFromConvPixel(clickerVoltVars.const.ConvPostbackURLTemplate);

            jQuery('#link-pixel-html').val(pixelTemplate);
            jQuery('#link-pixel-postback').val(pixelPostback);
        }

        /**
         * 
         */
        static refreshLinksLists(onDone) {

            ClickerVoltFunctions.ajax('wp_ajax_clickervolt_get_all_slugs', null, {
                success: function(slugInfos) {
                    ClickerVoltLinkController.setLinksList(slugInfos);

                    ClickerVoltLinkController.refreshSlugs(jQuery('#select-slug-copy-from'), slugInfos, '')
                    ClickerVoltLinkController.refreshSlugs(jQuery('#select-slug-edit-existing'), slugInfos, '')

                    ClickerVoltLinkController.refreshFunnelLinksLists(jQuery('#select-funnel-links'), slugInfos);
                    ClickerVoltLinkController.refreshFunnelLinksLists(jQuery('select.link-replacement-to'), slugInfos);

                    if (onDone) {
                        onDone(slugInfos);
                    }
                },
            });
        }

        /**
         * 
         */
        static refreshFunnelLinksLists($selects, slugInfos) {

            $selects.each(function() {
                var $select = jQuery(this);
                var currentLinkId = jQuery('#linkid').val();

                if (currentLinkId) {
                    $select.find(`option[value=${currentLinkId}]`).remove();
                }

                var existingLinkIds = [];
                for (var i = 0; i < slugInfos.length; i++) {

                    var id = slugInfos[i]['id'];
                    existingLinkIds.push(id);

                    if (id != currentLinkId) {
                        if (!$select.find(`option[value=${id}]`).length) {

                            var slug = slugInfos[i]['slug'];
                            $select.append(`<option value="${id}">${slug}</option>`);
                        }
                    }
                }

                $select.find('option').each(function() {
                    var $option = jQuery(this);
                    if (!$option.attr('reserved') && existingLinkIds.indexOf($option.val()) == -1) {
                        // This option shouldn't exist anymore... 
                        $option.remove();
                    }
                });
            });
        }

        /**
         * 
         */
        static copySlugFromSelected() {

            ClickerVoltLinkController.loadSlugFromSelect(jQuery('#select-slug-copy-from'), function(link) {
                link.id = "";
                link.slug += "-" + ClickerVoltFunctions.shortId()
            });
        }

        /**
         * 
         */
        static editSelectedSlug() {

            ClickerVoltLinkController.loadSlugFromSelect(jQuery('#select-slug-edit-existing'));
        }

        /**
         * 
         */
        static loadSlugFromSlugName(slug, editLinkCallback, onDoneCallback) {

            var $select = jQuery('#select-slug-edit-existing');

            $select.find("option").each(function() {
                if (jQuery(this).text() === slug) {
                    $select.val(jQuery(this).val());
                    ClickerVoltLinkController.loadSlugFromSelect($select, editLinkCallback, onDoneCallback);
                }
            });
        }

        /**
         * 
         */
        static loadSlugFromSelect($select, editLinkCallback, onDoneCallback) {

            var selectedSlugId = $select.find('option:selected').val();
            if (selectedSlugId) {

                if (selectedSlugId === 'reset') {

                    ClickerVoltLinkController.resetLinkFields();
                    $select.val('').trigger('change');

                } else {

                    $select.prop('disabled', true);

                    ClickerVoltFunctions.ajax('wp_ajax_clickervolt_get_link', null, {

                        data: {
                            id: selectedSlugId
                        },
                        success: function(link) {

                            if (editLinkCallback) {
                                editLinkCallback(link);
                            }

                            ClickerVoltLinkController.initLinkFields(link);

                            jQuery('#create-link-form').find('input').removeClass('error');
                            jQuery('#create-link-form').find('label.error').remove();

                            $select.prop('disabled', false);
                            $select.val('').trigger('change');

                            if (onDoneCallback) {
                                onDoneCallback(link);
                            }
                        },
                        complete: function() {}
                    });
                }
            }
        }

        /**
         *
         */
        static resetLinkFields() {

            ClickerVoltLinkController.initLinkFields({
                id: '',
                slug: '',
                settings: {
                    type: "<?= ClickerVolt\DistributionRandom::TYPE ?>",
                    redirectMode: "<?= ClickerVolt\Link::REDIRECT_MODE_PERMANENT ?>",
                    sequentialCounter: 1,
                    defaultUrls: [],
                    defaultWeights: [],
                    defaultAffNetworks: [],
                    redirectRules: [],
                    cloakingOptions: {},
                    aida: {
                        a: 20,
                        i: 120,
                        d: 300
                    },
                    funnelLinks: [],
                    hooks: [],
                    aliases: [],
                    fraudOptions: {
                        mode: clickerVoltVars.settings.recaptchaV3SiteKey != '' ? "<?= ClickerVolt\Link::FRAUD_DETECTION_MODE_RECAPTCHA_V3 ?>" : "<?= ClickerVolt\Link::FRAUD_DETECTION_MODE_NONE ?>",
                        recaptcha3SiteKey: clickerVoltVars.settings.recaptchaV3SiteKey,
                        recaptcha3SecretKey: clickerVoltVars.settings.recaptchaV3SecretKey,
                        recaptcha3HideBadge: clickerVoltVars.settings.recaptchaV3HideBadge
                    },
                },
                costType: "<?= ClickerVolt\Link::COST_TYPE_TOTAL ?>",
                costValue: 0,
            });
        }

        /**
         * 
         */
        static initLinkFields(link) {
            ClickerVoltLinkController.closeAllUrlVariables();

            jQuery('#linkslug').val(link.slug).trigger('change');
            jQuery('#linkid').val(link.id);
            jQuery('#linkdist').val(link.settings.type);
            jQuery('#redirect-mode').val(link.settings.redirectMode).trigger('change');
            jQuery('#link-cost-type').val(link.costType);
            jQuery('#link-cost-value').val(link.costValue);
            jQuery('input[name=aida-attention]').val(link.settings.aida.a);
            jQuery('input[name=aida-interest]').val(link.settings.aida.i);
            jQuery('input[name=aida-desire]').val(link.settings.aida.d);

            if (link.settings.fraudOptions) {
                jQuery('#bot-detection-type-select').val(link.settings.fraudOptions.mode).trigger('change');

                jQuery('#tab-link-bot-detection input[name=recaptchav3-site-key]').val(link.settings.fraudOptions.recaptcha3SiteKey).trigger('change');
                jQuery('#tab-link-bot-detection input[name=recaptchav3-secret-key]').val(link.settings.fraudOptions.recaptcha3SecretKey).trigger('change');
                if (link.settings.fraudOptions.recaptcha3HideBadge == 'yes') {
                    jQuery('#tab-link-bot-detection input[name=recaptchav3-hide-badge]').prop('checked', true);
                }
            }

            ClickerVoltLinkController.refreshLinkSlugNavSummary(true);

            if (link.settings.sequentialCounter !== undefined) {
                jQuery('#linkdist-sequential-counter').val(link.settings.sequentialCounter);
            }

            jQuery('#default-path').find('table.target-urls:not(#target-urls-template)').remove();
            var $urlsBlock = ClickerVoltLinkController.getNewTargetUrlsBlock('default-path');
            $urlsBlock.appendTo(jQuery('td#default-path'));

            for (var i = 0; i < link.settings.defaultUrls.length; i++) {
                var url = link.settings.defaultUrls[i];
                var weight = link.settings.defaultWeights[i];
                var affNetwork = "";
                if (link.settings.defaultAffNetworks) {
                    affNetwork = link.settings.defaultAffNetworks[i];
                }

                var $curUrlBlock;
                if (i == 0) {
                    $curUrlBlock = $urlsBlock;
                } else {
                    $curUrlBlock = ClickerVoltLinkController.addUrlToBlock($urlsBlock);
                }

                if (affNetwork) {
                    $curUrlBlock.find('span.aff-networks-tracking-ids select').val(affNetwork).trigger('change');
                }
                $curUrlBlock.find('input.url').val(url).trigger('change');
                $curUrlBlock.find('input.weight').val(weight);
            }

            if (link.settings.cloakingOptions) {
                jQuery('input[name=cloaking-option-meta-title]').val(link.settings.cloakingOptions.title);
                jQuery('input[name=cloaking-option-meta-description]').val(link.settings.cloakingOptions.desc);
                jQuery('input[name=cloaking-option-meta-keywords]').val(link.settings.cloakingOptions.keywords);
                jQuery('input[name=cloaking-option-no-index]').prop('checked', link.settings.cloakingOptions.noindex ? true : false);
                jQuery('input[name=cloaking-option-no-follow]').prop('checked', link.settings.cloakingOptions.nofollow ? true : false);
            } else {
                jQuery('input[name=cloaking-option-meta-title]').val('');
                jQuery('input[name=cloaking-option-meta-description]').val('');
                jQuery('input[name=cloaking-option-meta-keywords]').val('');
                jQuery('input[name=cloaking-option-no-index]').prop('checked', false);
                jQuery('input[name=cloaking-option-no-follow]').prop('checked', false);
            }

            jQuery('#voltify-options tr.link-replacement-row:not(.template)').remove();
            jQuery('#voltify-options tr.dynamic-content-replacement-row:not(.template)').remove();
            jQuery('#voltify-options tr.static-content-replacement-row:not(.template)').remove();
            if (link.settings.voltifyOptions) {
                jQuery('input[name=voltify-option-cache]').prop('checked', link.settings.voltifyOptions.cached);
                jQuery('input[name=voltify-inject-aida]').prop('checked', link.settings.voltifyOptions.injectAIDA);
                jQuery('input[name=voltify-internal-urls]').prop('checked', link.settings.voltifyOptions.voltifyInternalURLs);
                jQuery('input[name=voltify-option-disable-analytics]').prop('checked', link.settings.voltifyOptions.disableAnalytics);
                jQuery('input[name=voltify-option-disable-popups]').prop('checked', link.settings.voltifyOptions.disablePopups);

                if (link.settings.voltifyOptions.linkReplacements) {
                    for (var from in link.settings.voltifyOptions.linkReplacements) {
                        var to = link.settings.voltifyOptions.linkReplacements[from];
                        ClickerVoltLinkController.addLinkReplacement(from, to);
                    }
                }

                if (link.settings.voltifyOptions.dynamicContentReplacements) {
                    for (var from in link.settings.voltifyOptions.dynamicContentReplacements) {
                        var to = link.settings.voltifyOptions.dynamicContentReplacements[from];
                        ClickerVoltLinkController.addDynamicContentReplacement(from, to);
                    }
                }

                if (link.settings.voltifyOptions.staticContentReplacements) {
                    for (var from in link.settings.voltifyOptions.staticContentReplacements) {
                        var to = link.settings.voltifyOptions.staticContentReplacements[from];
                        ClickerVoltLinkController.addStaticContentReplacement(from, to);
                    }
                }
            } else {
                jQuery('input[name=voltify-option-cache]').prop('checked', true);
                jQuery('input[name=voltify-inject-aida]').prop('checked', true);
                jQuery('input[name=voltify-internal-urls]').prop('checked', false);
                jQuery('input[name=voltify-option-disable-analytics]').prop('checked', true);
                jQuery('input[name=voltify-option-disable-popups]').prop('checked', false);
            }

            jQuery('.rule-block').not('.template').remove();
            if (link.settings.redirectRules) {
                for (var i = 0; i < link.settings.redirectRules.length; i++) {

                    var rule = link.settings.redirectRules[i];
                    var $ruleBlock = ClickerVoltLinkController.addRedirectRule();

                    for (var c = 0; c < rule.conditions.length; c++) {
                        var condition = rule.conditions[c];
                        var $conditionBlock = ClickerVoltLinkController.addNewRedirectRuleConditionBlock($ruleBlock);

                        var conditionType = condition['type'];
                        var conditionOperator = condition['operator'];
                        var conditionValues = condition['values'];
                        if (!conditionValues) {
                            conditionValues = [];
                        }

                        $conditionBlock.find('select.rule-type').val(conditionType).trigger('change');
                        $conditionBlock.find('select.rule-operator').val(conditionOperator).trigger('change');

                        var $valueElem = $conditionBlock.find(`.rule-values .for-${conditionType} .rule-value`).not('span.select2');
                        if ($valueElem.is('select')) {
                            if ($valueElem.data('ajaxsource')) {
                                // This select is filled dynamically via an ajax source.
                                // Only way to initialize it is to add the options manually
                                for (var v = 0; v < conditionValues.length; v++) {
                                    ClickerVoltFunctions.addOptionToSelect($valueElem, ClickerVoltFunctions.htmlEntities(conditionValues[v]), conditionValues[v]);
                                }
                            }
                            $valueElem.val(conditionValues).trigger('change');
                        } else if ($valueElem.is('input')) {
                            $valueElem.val(conditionValues);

                            if ($valueElem.hasClass('daterange')) {
                                $valueElem.data('daterangepicker').setStartDate(conditionValues[0]);
                                $valueElem.data('daterangepicker').setEndDate(conditionValues[0]);
                            }
                        } else if ($valueElem.is('textarea')) {
                            $valueElem.val(conditionValues.join('\n'));
                        }
                    }

                    for (var u = 0; u < rule['urls'].length; u++) {
                        var url = rule['urls'][u];
                        var weight = rule['weights'][u];
                        var affNetwork = "";
                        if (rule['aff-networks']) {
                            affNetwork = rule['aff-networks'][u];
                        }

                        var $curUrlBlock;
                        if (u == 0) {
                            $curUrlBlock = $ruleBlock.find('.rule-path');
                        } else {
                            $curUrlBlock = ClickerVoltLinkController.addUrlToBlock($ruleBlock.find('.rule-path'));
                        }

                        if (affNetwork) {
                            $curUrlBlock.find('span.aff-networks-tracking-ids select').val(affNetwork).trigger('change');
                        }
                        $curUrlBlock.find('input.url').val(url).trigger('input');
                        $curUrlBlock.find('input.weight').val(weight);
                    }
                }
            }

            jQuery('#tab-link-hooks .hook-panel:not(.template)').remove();
            if (link.settings.hooks) {
                if (link.settings.hooks['redirects']) {
                    for (var redirectType in link.settings.hooks['redirects']) {
                        for (var i = 0; i < link.settings.hooks['redirects'][redirectType].length; i++) {
                            var redirectData = link.settings.hooks['redirects'][redirectType][i];
                            if (redirectType == 'html') {

                                var code = atob(redirectData['code']);
                                var when = redirectData['when'];
                                var duration = redirectData['duration'];
                                ClickerVoltLinkController.addOnRedirectHook({
                                    action: 'html',
                                    value: {
                                        code: code,
                                        when: when,
                                        duration: duration
                                    }
                                });

                            } else if (redirectType == 'php') {

                                var code = redirectData;
                                ClickerVoltLinkController.addOnRedirectHook({
                                    action: 'php',
                                    value: atob(code)
                                });
                            }
                        }
                    }
                }
            }

            jQuery('#slug-aliases-panel textarea').val('');
            if (link.settings.aliases) {
                jQuery('#slug-aliases-panel textarea').val(link.settings.aliases.join('\n'));
            }

            ClickerVoltLinkController.linkDistTypeUpdated();
            ClickerVoltLinkController.refreshTrackingURL();
            ClickerVoltLinkController.refreshLinksLists(function() {
                if (link.settings.funnelLinks && link.settings.funnelLinks.length > 0) {
                    var vals = [];
                    for (var i = 0; i < link.settings.funnelLinks.length; i++) {
                        vals.push(link.settings.funnelLinks[i]);
                    }
                    jQuery('#select-funnel-links').val(vals).trigger('change');

                } else {
                    jQuery('#select-funnel-links').val(null).trigger('change');
                }
            });
        }

        /**
         * 
         */
        static refreshSlugs($select, slugInfos, selectedSlug) {

            $select.find('option').each(function() {
                var $option = jQuery(this);
                if (!$option.attr('reserved')) {
                    $option.remove();
                }
            });

            for (var i = 0; i < slugInfos.length; i++) {
                var id = slugInfos[i]['id'];
                var slug = slugInfos[i]['slug'];

                $select.append(`<option value="${id}">${slug}</option>`);

                if (selectedSlug !== undefined && selectedSlug == slug) {
                    $select.val(id);
                }
            }
        }


        /**
         * 
         */
        static setupForm() {

            // Using https://jqueryvalidation.org/

            var $form = jQuery("#create-link-form");

            // We have multiple submit inputs in this form, and we need to know
            // which one triggers the submit... so we add a "clicked" attribute
            // whenever one of those submit buttons is clicked... since the click event
            // happens before the submit event, we can then, in the submit, get the 
            // the input with that attribute to find out which submit input was clicked
            $form.find('input[type=submit].save-link').click(function() {
                $form.find('input[type=submit].save-link').removeAttr("clicked");
                jQuery(this).attr("clicked", "true");
            });

            $form.validate({
                rules: {
                    linkslug: {
                        required: true,
                        maxlength: 128
                    }
                },

                submitHandler: function(form) {

                    var inputId = $form.find('input[type=submit][clicked=true].save-link').attr('id');

                    $form.find('input[type=submit].save-link').prop('disabled', true);

                    var redirectRules = ClickerVoltLinkController.redirectRulesToObjects();

                    ClickerVoltFunctions.ajax('wp_ajax_clickervolt_save_link', form, {
                        data: {
                            redirectRules: redirectRules
                        },
                        success: function(response) {

                            if (response['recaptcha']) {
                                // New default values for recaptcha have just been set while saving this link
                                clickerVoltVars.settings.recaptchaV3SiteKey = response['recaptcha']['recaptchaV3SiteKey'];
                                clickerVoltVars.settings.recaptchaV3SecretKey = response['recaptcha']['recaptchaV3SecretKey'];
                                clickerVoltVars.settings.recaptchaV3HideBadge = response['recaptcha']['recaptchaV3HideBadge'];
                            }

                            var link = response['link'];

                            jQuery('#linkid').val(link['id']);

                            if (ClickerVoltLinkController.linkSavedCallbacks) {
                                ClickerVoltLinkController.linkSavedCallbacks.forEach(function(callback) {
                                    callback();
                                });
                            }

                            if (inputId == 'submit-and-new') {
                                ClickerVoltLinkController.resetLinkFields();
                            }

                            ClickerVoltLinkController.refreshLinkSlugNavSummary(true);
                            ClickerVoltFunctions.showSavedConfirmation(jQuery('#saved-link-confirmation-message'));
                        },
                        complete: function() {

                            $form.find('input[type=submit].save-link').prop('disabled', false);
                        }
                    });
                }
            });
        }

        /**
         * @param string|null from
         * @param string|null to
         */
        static addStaticContentReplacement(from, to) {
            var $clone = jQuery('.static-content-replacement-row.template').clone();
            $clone.removeClass('template');

            $clone.find('.static-content-replacement-delete').on('click', function() {
                jQuery(this).closest('.static-content-replacement-row').remove();
            });

            if (from) {
                $clone.find('input.static-content-replacement-from').val(from);
            }

            if (to) {
                $clone.find('input.static-content-replacement-to').val(to);
            }

            $clone.find('input.static-content-replacement-from').attr('name', 'voltify-static-content-replacement-froms[]');
            $clone.find('input.static-content-replacement-to').attr('name', 'voltify-static-content-replacement-tos[]');

            $clone.insertBefore(jQuery('a.button.add-static-content-replacement').closest('tr'));
            $clone.show();
        }

        /**
         * @param string|null from
         * @param string|null to
         */
        static addDynamicContentReplacement(from, to) {
            var $clone = jQuery('.dynamic-content-replacement-row.template').clone();
            $clone.removeClass('template');

            $clone.find('.dynamic-content-replacement-delete').on('click', function() {
                jQuery(this).closest('.dynamic-content-replacement-row').remove();
            });

            if (from) {
                $clone.find('input.dynamic-content-replacement-from').val(from);
            }

            var $select = $clone.find('select.dynamic-content-replacement-to');
            for (var key in clickerVoltVars.const.DynamicTokens) {
                var token = clickerVoltVars.const.DynamicTokens[key];
                ClickerVoltFunctions.addOptionToSelect($select, token, token);
            }

            if (to) {
                $select.val(to);
            }

            ClickerVoltFunctions.initSelect2($select);

            $clone.find('input.dynamic-content-replacement-from').attr('name', 'voltify-dynamic-content-replacement-froms[]');
            $select.attr('name', 'voltify-dynamic-content-replacement-tos[]');

            $clone.insertBefore(jQuery('a.button.add-dynamic-content-replacement').closest('tr'));
            $clone.show();
        }

        /**
         * @param string|null from
         * @param string|null to
         */
        static addLinkReplacement(from, to) {
            var $clone = jQuery('.link-replacement-row.template').clone();
            $clone.removeClass('template');

            $clone.find('.link-replacement-delete').on('click', function() {
                jQuery(this).closest('.link-replacement-row').remove();
            });

            if (from) {
                $clone.find('input.link-replacement-from').val(from);
            }

            var $select = $clone.find('select.link-replacement-to');
            ClickerVoltLinkController.refreshSlugs($select, ClickerVoltLinkController.getLinksList(), '');

            if (to) {
                $select.val(to);
            }

            ClickerVoltFunctions.initSelect2($select);

            $clone.find('input.link-replacement-from').attr('name', 'voltify-link-replacement-urls[]');
            $select.attr('name', 'voltify-link-replacement-links[]');

            $clone.insertBefore(jQuery('a.button.add-link-replacement').closest('tr'));
            $clone.show();
        }

        /**
         * 
         */
        static addRedirectRule() {

            var nbBlocks = jQuery('.rule-block').not('.template').length;
            if (nbBlocks > 0) {

            }

            var $block = ClickerVoltLinkController.getNewRedirectRuleBlock();
            $block.insertBefore(jQuery('a.button.rule-add-block'));
            return $block;
        }

        /**
         * @return array of objects
         */
        static redirectRulesToObjects() {

            var objects = [];

            jQuery('.rule-block').not('.template').each(function() {
                var $block = jQuery(this);

                var ruleBlock = {
                    "name": $block.find('.rule-name').text(),
                    "conditions": [],
                    "urls": [],
                    "weights": [],
                    "aff-networks": [],
                };

                $block.find('.rule-path input.url').map(function() {
                    ruleBlock.urls.push(this.value);
                });

                $block.find('.rule-path input.weight').map(function() {
                    ruleBlock.weights.push(this.value);
                });

                $block.find('.rule-path span.aff-networks-tracking-ids select').map(function() {
                    ruleBlock["aff-networks"].push(jQuery(this).find('option:selected').val());
                });

                $block.find('.rule-condition').not('.template').each(function() {
                    var $condition = jQuery(this);
                    var conditionBlock = {
                        type: $condition.find('select.rule-type option:selected').val(),
                        operator: $condition.find('select.rule-operator option:selected').val(),
                        values: []
                    };

                    var $valueElem = $condition.find(`div.for-${conditionBlock.type} .rule-value`);
                    if ($valueElem.is('input')) {
                        conditionBlock.values.push($valueElem.val());

                    } else if ($valueElem.is('textarea')) {
                        var lines = $valueElem.val().split('\n');
                        for (var i = 0; i < lines.length; i++) {
                            var line = lines[i].trim();
                            if (line.length > 0) {
                                conditionBlock.values.push(line);
                            }
                        }
                    } else if ($valueElem.is('select')) {
                        $valueElem.find("option:selected").map(function() {
                            conditionBlock.values.push(this.value);
                        });
                    }

                    ruleBlock.conditions.push(conditionBlock);
                });

                objects.push(ruleBlock);
            });

            return objects;
        }

        /**
         * 
         */
        static getNewRedirectRuleBlock() {

            var $clone = jQuery('.rule-block.template').clone();
            $clone.removeClass('template');

            $clone.find('.button.rule-add-condition').on('click', function() {
                ClickerVoltLinkController.addNewRedirectRuleConditionBlock($clone);
            });

            $clone.find('.rule-block-delete').on('click', function() {
                $clone.remove();
            });

            var ruleBlockName = 'ruleURLs-' + ClickerVoltFunctions.uuid();
            ClickerVoltLinkController.getNewTargetUrlsBlock(ruleBlockName).appendTo($clone.find('div.rule-path'));

            $clone.show();

            return $clone;
        }

        /**
         * 
         */
        static addNewRedirectRuleConditionBlock($toRedirectRuleBlock) {

            var $template = $toRedirectRuleBlock.find('.rule-condition.template');
            var $clone = $template.clone();
            $clone.removeClass('template');

            var rules = {};

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_COUNTRY] = {
                name: "Country",
                operators: ["is", "is-not", "empty", "empty-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_REGION] = {
                name: "Region",
                operators: ["is", "is-not", "empty", "empty-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_CITY] = {
                name: "City",
                operators: ["is", "is-not", "empty", "empty-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_ISP] = {
                name: "ISP",
                operators: ["is", "is-not", "empty", "empty-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_IP] = {
                name: "IP",
                operators: ["is", "is-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_LANGUAGE] = {
                name: "Language",
                operators: ["is", "is-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_USER_AGENT] = {
                name: "User Agent",
                operators: ["contains", "contains-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_DEVICE_TYPE] = {
                name: "Device Type",
                operators: ["is", "is-not", "empty", "empty-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_DEVICE_BRAND] = {
                name: "Device Brand",
                operators: ["is", "is-not", "empty", "empty-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_DEVICE_NAME] = {
                name: "Device Name",
                operators: ["is", "is-not", "empty", "empty-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_OS] = {
                name: "Operating System",
                operators: ["is", "is-not", "empty", "empty-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_OS_VERSION] = {
                name: "Operating System Version",
                operators: ["is", "is-not", "gt", "lt", "gte", "lte"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_BROWSER] = {
                name: "Browser",
                operators: ["is", "is-not", "empty", "empty-not"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_BROWSER_VERSION] = {
                name: "Browser Version",
                operators: ["is", "is-not", "gt", "lt", "gte", "lte"],
            };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_DATE] = {
                name: "Date",
                operators: ["is", "is-not", "gt", "lt", "gte", "lte"],
            };

            // rules[ clickerVoltVars.const.RedirectRules.RULE_TYPE_DAY_OF_WEEK ] = {
            //     name: "Day of Week",
            //     operators: [ "is", "is-not" ],
            // };

            // rules[ clickerVoltVars.const.RedirectRules.RULE_TYPE_HOUR ] = {
            //     name: "Hour",
            //     operators: [ "between" ],
            // };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_URL] = {
                name: "URL",
                operators: ["contains", "contains-not"],
            };

            // rules[ clickerVoltVars.const.RedirectRules.RULE_TYPE_URL_VARIABLE ] = {
            //     name: "URL Variable",
            //     operators: [ "is", "is-not", "contains", "contains-not", "empty", "empty-not" ],
            // };

            rules[clickerVoltVars.const.RedirectRules.RULE_TYPE_REFERRER] = {
                name: "Referrer",
                operators: ["contains", "contains-not", "empty", "empty-not"],
            };


            var $ruleTypeSelect = $clone.find('select.rule-type');
            for (var id in rules) {
                var data = rules[id];
                ClickerVoltFunctions.addOptionToSelect($ruleTypeSelect, id, data.name);
            }

            $ruleTypeSelect.on('change', function() {
                var val = jQuery(this).find('option:selected').val();
                var $selectOperators = jQuery(this).closest('.rule-condition').find('select.rule-operator');
                $clone.find('.rule-values div').hide();

                if (!val) {
                    $selectOperators.hide();
                } else {

                    $selectOperators.find('option').prop('disabled', true);
                    var operators = rules[val].operators;
                    for (var i = 0; i < operators.length; i++) {
                        var operator = operators[i];
                        $selectOperators.find(`option[value=${operator}]`).prop('disabled', false);
                    }
                    $selectOperators.val($selectOperators.find('option:not([disabled])').first().val())
                    $selectOperators.show();

                    var valuesBlock = 'for-' + val;
                    $clone.find(`.rule-values div.${valuesBlock}`).show();
                }
            });

            var $selectOperators = $clone.find('select.rule-operator');
            $selectOperators.on('change', function() {
                if (jQuery(this).val() == "empty" || jQuery(this).val() == "empty-not") {
                    jQuery(this).closest('.rule-condition').find('.rule-values').hide();
                } else {
                    jQuery(this).closest('.rule-condition').find('.rule-values').show();
                }
            });

            $clone.find('.rule-value.daterange').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                },
                singleDatePicker: true,
                showDropdowns: true,
            });

            $clone.find('.rule-condition-delete').on('click', function() {
                $clone.remove();
                ClickerVoltLinkController.renameRedirectRuleBlockIFs($toRedirectRuleBlock);
            });

            $clone.insertBefore($toRedirectRuleBlock.find('.button.rule-add-condition'));
            $clone.show();

            ClickerVoltFunctions.initSelect2($clone.find('.rule-values select'));
            ClickerVoltLinkController.renameRedirectRuleBlockIFs($toRedirectRuleBlock);

            return $clone;
        }

        /**
         * 
         */
        static renameRedirectRuleBlockIFs($block) {
            $block.find('.rule-condition').not('.template').find('div.if').each(function(i) {
                if (i == 0) {
                    jQuery(this).text('IF');
                } else {
                    jQuery(this).text('AND IF');
                }
            });
        }

        /**
         * @return string
         */
        static getBlockName($blockElement) {
            if (!$blockElement.hasClass('target-urls')) {
                $blockElement = $blockElement.closest('table.target-urls');
            }
            return $blockElement.attr('name');
        }

        /**
         * @return $block - jQuery object
         */
        static getNewTargetUrlsBlock(blockName) {

            var $clone = jQuery('#target-urls-template').clone();
            $clone.removeAttr('id');
            $clone.attr('name', blockName);

            $clone.find('select.select2-hidden-accessible').each(function() {
                ClickerVoltFunctions.destroySelect2(jQuery(this));
            });

            var $firstRow = $clone.find('tbody tr:first');
            $firstRow.find('td input.url').on('input', ClickerVoltLinkController.refreshTrackingURL);
            ClickerVoltLinkController.initUrlVariables($firstRow);

            $clone.find('input.url').attr('name', `urls[${blockName}][]`);
            $clone.find('input.weight').attr('name', `weights[${blockName}][]`);

            $clone.find('input.add-url').on('click', function() {
                ClickerVoltLinkController.addUrlToBlock($clone);
            });

            $clone.show();
            return $clone;
        }

        /**
         * 
         */
        static closeAllUrlVariables() {

            jQuery('.urls-list .url-variables-open.opened').each(function() {
                ClickerVoltLinkController.toggleUrlVariables(jQuery(this));
            });
        }

        /**
         *
         */
        static toggleUrlVariables($toggleButton) {

            var $urlRow = $toggleButton.closest('.url-row');
            var $urlInput = $urlRow.find('input.url');

            if ($toggleButton.hasClass('opened')) {

                $urlInput.off('input');
                $urlRow.find('.url-variable-key').off('input');
                $urlRow.find('.url-variable-value').off('change');

                $toggleButton.removeClass('opened');
                $urlRow.find('.url-variables').hide();

            } else {

                // Handling of changes in url input (url to variables) 
                {
                    var updateVariableBlocksFromURL = function(url) {

                        var url = $urlInput.val();
                        var urlParams = ClickerVoltFunctions.getParamsFromURL(url);

                        var paramsFromVarBlocks = {};
                        $urlRow.find('.url-variables tbody tr:not(.variable-row-template)').each(function() {

                            var key = jQuery(this).find('.url-variable-key').val();
                            var val = jQuery(this).find('.url-variable-value').val();
                            paramsFromVarBlocks[key] = val;
                        });

                        if (jQuery.param(urlParams) != jQuery.param(paramsFromVarBlocks)) {

                            // Make sure we have as many variable blocks as params present in the url

                            var nbUrlParams = Object.keys(urlParams).length;
                            var nbVarBlocks = Object.keys(paramsFromVarBlocks).length;
                            if (nbVarBlocks > nbUrlParams) {

                                var indexTooMany = nbUrlParams - 1;
                                $urlRow.find(`.url-variables tbody tr:not(.variable-row-template):gt(${indexTooMany})`).remove();

                            } else if (nbVarBlocks < nbUrlParams) {

                                var nbMissing = nbUrlParams - nbVarBlocks;
                                var $addVariableButton = $urlRow.find('.button.add-url-variable');
                                for (var i = 0; i < nbMissing; i++) {
                                    ClickerVoltLinkController.addVariableToUrl($addVariableButton);
                                }
                            }

                            // Update variable blocks that are different from the URL ones

                            var iBlock = 0;
                            for (var key in urlParams) {

                                var $varBlock = $urlRow.find(`.url-variables tbody tr:not(.variable-row-template)`).eq(iBlock);
                                var varBlockKey = $varBlock.find('.url-variable-key').val();
                                var varBlockVal = $varBlock.find('.url-variable-value option:selected').val();

                                if (varBlockKey != key) {
                                    $varBlock.find('.url-variable-key').val(key).attr('updated', 'updated');
                                }

                                if (varBlockVal != urlParams[key]) {
                                    var $select = $varBlock.find('select.url-variable-value');
                                    ClickerVoltFunctions.addOptionToSelect($select, urlParams[key], urlParams[key]);
                                    $select.val(urlParams[key]).trigger('change.select2');
                                }

                                iBlock++;
                            }
                        }
                    }

                    $urlInput.on('input', function() {
                        updateVariableBlocksFromURL($urlInput.val());
                    });

                    updateVariableBlocksFromURL($urlInput.val());
                }

                // Handling of changes in variable fields (variables to url)
                {
                    var $currentVariableRows = $urlRow.find('.url-variables tbody tr:not(.variable-row-template)');
                    ClickerVoltLinkController.handleVariablesChanges($currentVariableRows, $urlInput);
                }

                $toggleButton.addClass('opened');
                $urlRow.find('.url-variables').show();
            }
        }

        /**
         * 
         */
        static updateURLFromVariableRows($variableRows) {

            var $urlInput = $variableRows.closest('.url-row').find('input.url');

            var queryParams = {};
            $variableRows.closest('.url-variables').find('tbody tr:not(.variable-row-template)').each(function() {
                var key = jQuery(this).find('.url-variable-key').val();
                var value = jQuery(this).find('.url-variable-value').val();
                queryParams[key] = value;
            });

            var currentQuery = jQuery.param(ClickerVoltFunctions.getParamsFromURL($urlInput.val()));
            var newQuery = jQuery.param(queryParams);

            if (currentQuery != newQuery) {
                $urlInput.val(ClickerVoltFunctions.setParamsOnURL($urlInput.val(), queryParams));
            }
        }

        /**
         * 
         */
        static handleVariablesChanges($variableRows, $urlInput) {

            $variableRows.each(function() {

                var $row = jQuery(this);

                $row.find('.url-variable-key').on('input', function() {
                    if (jQuery(this).val() != '') {
                        jQuery(this).attr('updated', 'updated');
                    } else {
                        jQuery(this).attr('updated', '');
                    }
                    ClickerVoltLinkController.updateURLFromVariableRows($variableRows);
                });

                $row.find('.url-variable-value').on('change', function() {
                    ClickerVoltLinkController.updateURLFromVariableRows($variableRows);
                });

                $row.find('div.url-variables .url-variable-delete').on('click', function() {
                    ClickerVoltLinkController.updateURLFromVariableRows($variableRows);
                });
            });
        }

        /**
         * 
         */
        static removeVariableFromUrl($removeVariableButton) {

            var $variableRow = $removeVariableButton.parent().closest('tr.variable-row');
            var $variablesTable = $variableRow.closest('tbody');
            $variableRow.remove();

            ClickerVoltLinkController.updateURLFromVariableRows($variablesTable);
        }

        /**
         * 
         */
        static addVariableToUrl($addVariableButton) {

            var $urlRow = $addVariableButton.parent().closest('td.url-row');
            var $variableRowTemplate = $urlRow.find('tr.variable-row-template');

            var $clone = $variableRowTemplate.clone();
            $clone.removeClass('variable-row-template');
            $clone.find('input[type=text]').val('');

            $clone.appendTo($variableRowTemplate.parent());
            $clone.show();

            var $select = $clone.find('select.url-variable-value');
            var uniqueSelectClass = ClickerVoltFunctions.uuid();
            $select.addClass(uniqueSelectClass);

            ClickerVoltFunctions.addOptionToSelect($select, '', '');
            for (var key in clickerVoltVars.const.DynamicTokens) {
                var token = clickerVoltVars.const.DynamicTokens[key];
                ClickerVoltFunctions.addOptionToSelect($select, token, token);
            }

            $select.on('change', function() {
                if ($clone.find('input.url-variable-key').attr('updated') != 'updated') {
                    var token = $select.find('option:selected').val();
                    token = token.replace('[', '').replace(']', '');
                    $clone.find('input.url-variable-key').val(token);
                }
            });

            ClickerVoltFunctions.initSelect2($select, {
                tags: true
            });

            $select.on('select2:open', function(e) {
                var $searchField = jQuery(document).find(`.select2-container.url-variable-value.${uniqueSelectClass} .select2-search__field`);
                $searchField.attr('placeholder', 'Search or create...');
            });

            $clone.find('.url-variable-delete').on('click', function() {
                ClickerVoltLinkController.removeVariableFromUrl(jQuery(this));
            });

            ClickerVoltLinkController.handleVariablesChanges($clone, $urlRow.find('input.url'));

            return $clone;
        }

        /**
         * Add a url to the specified block
         */
        static addUrlToBlock($block) {

            var $firstRow = $block.find('tbody.urls-list tr:first');
            var $newRow = $firstRow.clone();

            $newRow.find('input.url').val('');
            $newRow.find('input.weight').val('100');

            $newRow.find('input.url').val('').removeClass('error');
            $newRow.find('label.error').remove();

            $newRow.find('i.path-delete-url').on('click', function() {
                ClickerVoltLinkController.removeUrlFromBlock($newRow);
            });
            $newRow.find('i.path-delete-url').show();

            $newRow.appendTo($block.find('tbody.urls-list'));
            ClickerVoltLinkController.initUrlVariables($newRow);
            return $newRow;
        }

        static initAffiliateNetworks($urlBlock) {

            var $copyButton = $urlBlock.find('.aff-network-postback-url-copy');
            $copyButton.on('click', function() {
                var $elem = $urlBlock.find('input.aff-network-postback-url');
                ClickerVoltFunctions.copyToClipboard($elem.val(), null);

                var networkName = $urlBlock.find('.aff-networks-tracking-ids select option:selected').val();
                ClickerVoltModals.message('Copied to Clipboard!', `You can now paste this postback URL in your ${networkName} account`);
            });
            $copyButton.hide();

            var $span = $urlBlock.find('span.aff-networks-tracking-ids');
            $span.empty();
            $span.append("<select></select>");
            var $select = $span.find('select');

            var blockName = ClickerVoltLinkController.getBlockName($select);
            $select.attr('name', `aff-networks[${blockName}][]`);
            // $urlBlock.find('input.aff-network-postback-url').attr('name', `aff-network-postback-urls[${blockName}][]`);

            $select.append(`<option value="">Set Affiliate Network Tracking ID</option>`);
            AffiliateNetworkHelper.fillSelect($select);
            ClickerVoltFunctions.initSelect2($select);

            $select.on('change', function() {
                var cidToken = '[cid]';
                $urlBlock.find('.variable-row').each(function() {
                    if (jQuery(this).find('.url-variable-value option:selected').val() == cidToken) {
                        jQuery(this).remove();
                    }
                });

                var networkName = jQuery(this).find('option:selected').val();
                var network = AffiliateNetworkHelper.getNetwork(networkName);
                if (network) {
                    var $addVariableButton = $urlBlock.find('.button.add-url-variable');
                    var $variableRow = ClickerVoltLinkController.addVariableToUrl($addVariableButton);
                    $variableRow.find('.url-variable-value').val(cidToken).trigger('change');
                    $variableRow.find('.url-variable-key').val(network.getTID()).trigger('input');

                    $urlBlock.find('.aff-network-postback-url').val(network.getPostbackURL());

                    $copyButton.show();
                } else {
                    $copyButton.hide();
                }

                var $variableRows = $urlBlock.find('.url-variables');
                ClickerVoltLinkController.updateURLFromVariableRows($variableRows);
            });
        }

        /**
         * 
         */
        static initUrlVariables($urlBlock) {

            ClickerVoltLinkController.removeAllVariablesFromUrlBlock($urlBlock);

            $urlBlock.find('.url-variables-open').removeClass('opened');
            $urlBlock.find('.url-variables').hide();

            $urlBlock.find('.url-variables-open').on('click', function() {
                ClickerVoltLinkController.toggleUrlVariables(jQuery(this));
            });

            $urlBlock.find('.button.add-url-variable').on('click', function() {
                ClickerVoltLinkController.addVariableToUrl(jQuery(this));
            });

            ClickerVoltLinkController.initAffiliateNetworks($urlBlock);
        }

        /**
         * 
         */
        static removeAllVariablesFromUrlBlock($urlBlock) {

            $urlBlock.find('.url-variables tbody tr:not(.variable-row-template)').remove();
        }

        /**
         * 
         */
        static removeUrlFromBlock($urlRow) {

            $urlRow.remove();
        }

        /**
         * 
         */
        static linkDistTypeUpdated() {

            jQuery('label.linkdist-sequential-counter').hide();

            var sequentialCounter = parseInt(jQuery('input#linkdist-sequential-counter').val());
            if (isNaN(sequentialCounter)) {
                sequentialCounter = 1;
            }

            switch (jQuery('#linkdist').val()) {

                case clickerVoltVars.const.DistributionTypes.RANDOM:
                    jQuery('#description-for-linkdist').text('Random: Visitors will be redirected randomly to one of your target URLs (each according to its weight)');
                    jQuery('.weight-info').show();
                    break;

                case clickerVoltVars.const.DistributionTypes.SEQUENTIAL:
                    jQuery('#description-for-linkdist').text(`Sequential: Starting with URL 1, then every ${sequentialCounter} visitors, will switch to the next URL`);
                    jQuery('.weight-info').hide();
                    jQuery('label.linkdist-sequential-counter').show();
                    break;

                case clickerVoltVars.const.DistributionTypes.AUTO_OPTIM:
                    jQuery('#description-for-linkdist').text('Auto-Optimization: Visitors will be redirected to the best converting URL (the other URLs will still receive some traffic in case they start performing better)');
                    jQuery('.weight-info').hide();
                    break;
            }
        }

        static areAllParamsValid() {

            var allValid = true;

            jQuery('table.target-urls:visible tbody tr:first td input.url').each(function() {
                var url = jQuery(this).val();
                if (!url || !ClickerVoltFunctions.isValidURL(url)) {
                    allValid = false;
                }
            });

            if (allValid) {
                var slug = jQuery('#linkslug').val();
                if (!slug) {
                    allValid = false;
                }
            }

            return allValid;
        }

        /**
         *
         */
        static getSlugAliases() {
            var aliases = [];

            var $aliasesTextArea = jQuery('#slug-aliases-panel textarea');
            var l = $aliasesTextArea.val().split('\n');
            for (var i = 0; i < l.length; i++) {
                if (l[i].trim().length > 0) {
                    aliases.push(l[i]);
                }
            }

            return aliases;
        }

        /**
         * 
         */
        static refreshTrackingURL() {

            if (ClickerVoltLinkController.areAllParamsValid()) {

                ClickerVoltLinkController._trackingURLHtml.getDiv().show();
                jQuery('input[type=submit].save-link').prop('disabled', false);

            } else {

                ClickerVoltLinkController._trackingURLHtml.getDiv().hide();
                jQuery('input[type=submit].save-link').prop('disabled', true);
            }

            var slug = jQuery('#linkslug').val();
            ClickerVoltLinkController._trackingURLHtml.setSlug(slug);
            ClickerVoltLinkController._trackingURLHtml.setSlugAliases(ClickerVoltLinkController.getSlugAliases());
            ClickerVoltLinkController._trackingURLHtml.refreshTrackingURL();

            if (ClickerVoltLinkController.templateAIDAScript) {
                var aidaScript = ClickerVoltLinkController.templateAIDAScript.replace('#SLUG#', slug);
                jQuery('#aida-script textarea').val(aidaScript);
            }

            ClickerVoltLinkController.refreshConversionPixels();
        }
    }
</script>