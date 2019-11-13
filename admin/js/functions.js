
for (var key in clickerVoltVars.const) {
    clickerVoltVars.const[key] = JSON.parse(clickerVoltVars.const[key]);
}

jQuery(document).ready(function () {

    jQuery(document).on('input', 'input[type=text]', function () {
        if (jQuery(this).hasClass('input-as-change')) {
            jQuery(this).trigger('change');
        }
    });

    jQuery(document).on('input', '.numeric', function () {

        if (jQuery(this).hasClass('float')) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        } else {
            this.value = this.value.replace(/[^0-9]/g, '');
        }

        if (jQuery(this).hasClass('ge0') && this.value.indexOf('-') !== -1) {
            this.value = 0;
        } else if (jQuery(this).hasClass('positive') && this.value === '0') {
            this.value = 1;
        }
    });

    jQuery(document).on('input', '.alphanum-and-dash', function () {

        if (jQuery(this).hasClass('lowercase')) {
            this.value = this.value.toLowerCase();
        }

        this.value = this.value.replace(/[ \t]/g, '-');
        this.value = this.value.replace(/[^a-zA-Z0-9-\n]/g, '');

        var maxCharsPerLine = jQuery(this).data('max-chars-per-line');
        if (maxCharsPerLine) {
            var parts = this.value.split('\n');
            for (var i = 0; i < parts.length; i++) {
                parts[i] = parts[i].substring(0, maxCharsPerLine);
            }
            this.value = parts.join('\n');
        }
    });

    jQuery(document).on('input', '.english-text', function () {

        this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
    });

    jQuery(document).on('input, change', 'input.auto-resize', function () {
        var minSize = jQuery(this).data('min-size');
        if (!minSize) {
            minSize = 10;
        }
        jQuery(this).attr('size', Math.max(minSize, jQuery(this).val().length));
    });
    jQuery('input.auto-resize').trigger('change');

    ClickerVoltFunctions.initSelect2(jQuery('select.select2'));
});

class ClickerVoltFunctions {

    static destroySelect2($select) {
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy')
        }
    }

    static initSelect2($select, additionalOptions) {

        if (!additionalOptions) {
            additionalOptions = {};
        }

        $select.each(function () {
            var $current = jQuery(this);
            if (!$current.hasClass("select2-hidden-accessible")) {

                var options = jQuery.extend(true, {}, additionalOptions);

                if (!$current.attr('id')) {
                    $current.attr('id', ClickerVoltFunctions.uuid());
                }

                if (!options.placeholder && $current.attr('placeholder')) {
                    options.placeholder = $current.attr('placeholder');
                }

                if ($current.prop('multiple')) {
                    options.closeOnSelect = false;
                }

                var ajaxSource = $current.data('ajaxsource');
                if (ajaxSource) {
                    if (!options.minimumInputLength) {
                        options.minimumInputLength = 3;
                    }
                    options.dataCache = {};
                    options.data = function (params) {
                        return params;
                    },
                        options.delay = 250;
                    options.query = function (q) {
                        var obj = options;
                        var key = q.term;
                        var lastCachedKey = "__last_cached_entry__";
                        var timeKey = "__time__";
                        obj.dataCache[timeKey] = Date.now();

                        if (key && key.length >= options.minimumInputLength) {
                            if (obj.dataCache[key]) {
                                obj.dataCache[lastCachedKey] = obj.dataCache[key];
                                q.callback(obj.dataCache[key]);
                            } else {
                                ClickerVoltFunctions.ajax(ajaxSource, null, {
                                    data: {
                                        search: key,
                                        requestTime: obj.dataCache[timeKey],
                                    },
                                    success: function (data, ajaxOptions) {
                                        // We only take the results if they come from the very latest query
                                        if (ajaxOptions.requestTime == obj.dataCache[timeKey]) {
                                            var response = {
                                                results: []
                                            };

                                            for (var i = 0; i < data['results'].length; i++) {
                                                response.results.push({
                                                    id: ClickerVoltFunctions.htmlEntities(data['results'][i]),
                                                    text: data['results'][i]
                                                });
                                            }

                                            obj.dataCache[key] = response;
                                            obj.dataCache[lastCachedKey] = response;
                                            q.callback(response);
                                        }
                                    },
                                });
                            }
                        } else if (obj.dataCache[lastCachedKey]) {
                            q.callback(obj.dataCache[lastCachedKey]);
                        } else {
                            q.callback({ results: [] });
                        }
                    };
                }

                if ($current.attr('class')) {
                    options.theme = "default " + $current.attr('class');
                }

                $current.select2(ClickerVoltFunctions.getSelect2Options(options)).maximizeSelect2Height();
            }
        });
    }

    static getSelect2Options(additionalOptions) {

        var options = {
            dropdownAutoWidth: true,
            // sorter: function(data) {
            //     /* Sort data using lowercase comparison */
            //     return data.sort(function (a, b) {
            //         a = a.text.toLowerCase();
            //         b = b.text.toLowerCase();
            //         if (a > b) {
            //             return 1;
            //         } else if (a < b) {
            //             return -1;
            //         }
            //         return 0;
            //     });
            // }
        };

        if (additionalOptions) {
            options = jQuery.extend(true, {}, options, additionalOptions);
        }

        return options;
    }

    /**
     * 
     * @param {*} action 
     * @param {*} form 
     * @param {*} options 
     * @return jqXHR object (http://api.jquery.com/jQuery.ajax/#jqXHR)
     */
    static ajax(action, form, options) {

        action = action.replace('wp_ajax_', '');

        var defaultOptions = {
            type: "POST",
            url: clickerVoltVars.urls.ajax,
            data: {
                action: action,
                clickervolt_nonce: clickerVoltVars.clickervolt_nonce,
            },
        };

        var ajaxOptions = jQuery.extend(true, {}, defaultOptions, options);

        if (form) {
            ajaxOptions.data.form = jQuery(form).serialize();
        }

        var callerSuccess = ajaxOptions['success'];
        var callerError = ajaxOptions['error'];

        ajaxOptions.success = function (response) {
            if (!response.success) {
                if (!callerError) {
                    ClickerVoltModals.error("ERROR", response.data.error ? response.data.error : "Looks like something went wrong...");
                } else {
                    callerError.call(this, response.data.error ? response.data.error : "Looks like something went wrong...", ajaxOptions);
                }
            } else if (callerSuccess) {
                callerSuccess.call(this, response.data, ajaxOptions.data);
            }
        };

        return jQuery.ajax(ajaxOptions);
    }

    /**
     * 
     * @param {*} str 
     */
    static copyToClipboard(str, $element) {

        const el = document.createElement('textarea');  // Create a <textarea> element
        el.value = str;                                 // Set its value to the string that you want copied
        el.setAttribute('readonly', '');                // Make it readonly to be tamper-proof
        el.style.position = 'absolute';
        el.style.left = '-9999px';                      // Move outside the screen to make it invisible
        document.body.appendChild(el);                  // Append the <textarea> element to the HTML document
        const selected =
            document.getSelection().rangeCount > 0        // Check if there is any content selected previously
                ? document.getSelection().getRangeAt(0)     // Store selection if found
                : false;                                    // Mark as false to know no selection existed before
        el.select();                                    // Select the <textarea> content
        document.execCommand('copy');                   // Copy - only works as a result of a user action (e.g. click events)
        document.body.removeChild(el);                  // Remove the <textarea> element
        if (selected) {                                 // If a selection existed before copying
            document.getSelection().removeAllRanges();    // Unselect everything on the HTML document
            document.getSelection().addRange(selected);   // Restore the original selection
        }

        if ($element) {
            $element.hide();
            $element.fadeIn(250);
        }
    }

    /**
     * 
     */
    static shortId() {
        var root = Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);

        var id = '';
        for (var i = 0; i < root.length; i++) {
            var c = root.charAt(i);
            if (c >= 'a' && c <= 'z' && Math.random() >= 0.5) {
                c = c.toUpperCase();
            }
            id += c;
        }

        return id;
    }

    /**
     * 
     */
    static uuid() {

        var uuid = ClickerVoltFunctions.shortId()
            + ClickerVoltFunctions.shortId()
            + '-' + ClickerVoltFunctions.shortId()
            + '-' + ClickerVoltFunctions.shortId()
            + '-' + ClickerVoltFunctions.shortId()
            + '-' + ClickerVoltFunctions.shortId() + ClickerVoltFunctions.shortId() + ClickerVoltFunctions.shortId();

        return uuid.toLowerCase();
    }

    /**
     * 
     * @param {string} wrapperSelector 
     * @param {function} onTabChanged - if set, then this callback is called when a tab is changed
     */
    static initTabs(wrapperSelector, onTabChanged) {

        var optionsKey = `tabs-${wrapperSelector}`;
        var tabOptions = ClickerVoltFunctions.getOption(optionsKey);
        if (!tabOptions) {
            tabOptions = {
                lastSelectionId: jQuery(wrapperSelector).find('.tabs .tab-link.current').attr('data-tab')
            };
        }

        if (onTabChanged) {
            tabOptions.onTabChanged = onTabChanged;
        }

        ClickerVoltFunctions.setOption(optionsKey, tabOptions);

        var $wrapper = jQuery(wrapperSelector);
        $wrapper.find('ul.tabs li').click(function () {

            var tabOptions = ClickerVoltFunctions.getOption(optionsKey);
            var tab_id = jQuery(this).attr('data-tab');

            if (tabOptions.lastSelectionId != tab_id) {

                tabOptions.lastSelectionId = tab_id;

                $wrapper.find('ul.tabs li').removeClass('current');
                $wrapper.find('.tab-content').removeClass('current');

                jQuery(this).addClass('current');
                jQuery(`#${tab_id}`).addClass('current');

                if (tabOptions.onTabChanged) {
                    tabOptions.onTabChanged(tab_id);
                }

                ClickerVoltFunctions.setOption(optionsKey, tabOptions);
            }
        });

        $wrapper.show();
    }

    /**
     * 
     * @param {*} wrapperSelector 
     * @param {*} tabId 
     */
    static selectTab(wrapperSelector, tabId) {

        jQuery(wrapperSelector).find(`.tabs .tab-link[data-tab=${tabId}]`).trigger('click');
    }

    /**
     * 
     * @param {string} selector 
     */
    static initAccordionButton(selector, onOpen, onClose) {

        jQuery(selector).children('div').show();
        jQuery(selector).accordion({
            collapsible: true,
            active: false,
            heightStyle: "content",
            animate: {
                duration: 250
            },
            activate: function (event, ui) {
                if (ui.newHeader.length > 0) {
                    if (onOpen) {
                        onOpen();
                    }
                } else {
                    if (onClose) {
                        onClose();
                    }
                }
            }
        });
    }

    /**
     * 
     * @param {*} $select 
     * @param {*} optionValue 
     * @param {*} optionName 
     */
    static addOptionToSelect($select, optionValue, optionName) {

        if (!$select.find(`option[value="${optionValue}"]`).length) {
            $select.append(`<option value="${optionValue}">${optionName}</option>`);
        }
    }

    /**
     * 
     * @param {*} $container 
     */
    static showSavedConfirmation($container) {

        $container.text('Successfully saved...');
        $container.show();
        $container.fadeOut(3000);
    }

    /**
     * Find if the specified string is in the specified array. The search is case insensitive.
     * 
     * @param {*} string 
     * @param {*} array 
     */
    static isStringInArrayi(string, array) {

        return array.findIndex(function (item, index, array) {
            return string.toLowerCase() === item.toLowerCase();
        }) !== -1;
    }

    /**
     * 
     * @param {*} value 
     */
    static isString(value) {
        return typeof value === 'string' || value instanceof String;
    }

    /**
     * 
     * @param {*} string 
     */
    static isEmptyString(string) {
        return !string || string.trim().length == 0;
    }

    /**
     * 
     * @param {*} value 
     */
    static isValidURL(value) {
        if (!ClickerVoltFunctions.isString(value)) {
            return false;
        }
        if (value.indexOf('tel:') === 0) {
            return true;
        }
        return /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(value);
    }

    /**
     * 
     */
    static extendJQueryValidator() {
        var originalUrlMethod = jQuery.validator.methods.url;
        jQuery.validator.methods.url = function (value, element) {
            if (ClickerVoltFunctions.isValidURL(value)) {
                return true;
            }
            return originalUrlMethod.call(this, value, element);
        };
    }

    /**
     * 
     * The header for first column must use the class "treegrid" like the example below:
     * 
     *  <table class="stripe" id="links-list-table" style="display: none; ">
     *      <thead>
     *          <tr>
     *              <th class='treegrid'></th>
     *              <th class='grouping'>Link</th>
     *              <th class='metrics'>Clicks</th>
     *              <th class='metrics'>Uniques</th>
     *              <th class='metrics'>Revenue</th>
     *              <th class='metrics'>Cost</th>
     *              <th class='metrics'>Profit</th>
     *              <th class='metrics'>ROI</th>
     *          </tr>
     *      </thead>
     *  </table>
     * 
     */
    static initTreeGridDataTable(tableSelector, dataTableOptions, treeGridGetDataCallback) {

        var expandIcon = '<span class="tree-icon expand"></span>';
        var collapseIcon = '<span class="tree-icon collapse"></span>';

        var options = {};

        if (dataTableOptions) {
            options = jQuery.extend(true, {}, dataTableOptions);
        }

        if (options.stateSave === undefined) {
            options.stateSave = true;
        }

        if (options.stateSaveParams === undefined) {
            options.stateSaveParams = function (settings, data) {
                // do not save search query
                data.search.search = '';
            };
        }

        // if( options.order === undefined ) {
        //     options.order = [
        //         [ 0, 'asc' ]
        //     ];
        // }

        // for( var i = 0; i < options.order.length; i++ ) {
        //     options.order[i][0]++;
        // }

        options.ordering = false;

        if (options.columnDefs === undefined) {
            options.columnDefs = [];
        }

        options.columnDefs.push({
            targets: 'treegrid',
            className: 'treegrid-control',
            orderable: false,
            searchable: false,
            data: function (item) {
                if (item.children) {
                    return expandIcon;
                }
                return '';
            }
        });

        if (options.treeGrid === undefined) {
            options.treeGrid = {
                left: 20,
                expandIcon: expandIcon,
                collapseIcon: collapseIcon,
                dataCallback: treeGridGetDataCallback,
                dataRowID: 'treegrid-row-id',
            }
        }

        // Paging does not work with tree view
        options.paging = false;

        // 'Showing x of y entries' does not work with tree view
        options.info = false;

        if (options.createdRow !== undefined) {
            var oldCreatedRowCB = options.createdRow;
        }

        options.createdRow = function (row, data, dataIndex) {
            // TODO: Move this id assignation into datatables.treegrid.js
            var rowId = data[options.treeGrid.dataRowID];
            if (rowId === undefined) {
                throw new Error(`Data's rows do not contain a '${options.treeGrid.dataRowID}' column`);
            }
            jQuery(row).attr('id', rowId);

            if (oldCreatedRowCB !== undefined) {
                oldCreatedRowCB.call(this, row, data, dataIndex);
            }
        }

        return jQuery(tableSelector).DataTable(options);
    }

    /**
     * 
     * @param {array} array 
     * @return {Object}
     */
    static arrayToObject(array) {

        var obj = {};
        for (var i = 0; i < array.length; i++) {
            obj[i] = array[i];
        }

        return obj;
    }

    /**
     * 
     * @param {string} key 
     * @param {mixed} value 
     * @param {Object} $element - if undefined, options will be set on body element
     */
    static setOption(key, value, $element) {

        if (!$element) {
            $element = jQuery('body');
        }

        var clickerVoltOptions = $element.data('clickerVoltOptions');
        if (!clickerVoltOptions) {
            clickerVoltOptions = {};
        }
        clickerVoltOptions[key] = value;
        $element.data('clickerVoltOptions', clickerVoltOptions);
    }

    /**
     * 
     * @param {string} key 
     * @param {Object} $element - if undefined, options will be set on body element
     */
    static getOption(key, $element) {

        if (!$element) {
            $element = jQuery('body');
        }

        var clickerVoltOptions = $element.data('clickerVoltOptions');
        if (!clickerVoltOptions) {
            return undefined;
        }

        return clickerVoltOptions[key];
    }

    /**
     * 
     * @param {string} url 
     */
    static getParamsFromURL(url) {

        var searchQuery = url;
        var params = [];
        if (searchQuery.indexOf("?") != -1) {
            searchQuery = '?' + searchQuery.split('?')[1];
            for (var a = searchQuery.split("?"), t = a[1].split("&"), l = 0; l < t.length; l++) {
                var f = t[l].split("=");
                var key = f[0];
                var value = f[1];
                if (key !== undefined && value !== undefined) {
                    params.push(key + "=" + value);
                }
            }
        }

        var keyValues = {};
        for (var key in params) {
            var parts = params[key].split("=");
            keyValues[parts[0]] = parts[1];
        }

        return keyValues;
    }

    /**
     * Based on https://stackoverflow.com/a/19513428/3135599
     * 
     * @param {Object} $textArea 
     */
    static textAreaToAceEditor($textArea) {

        var mode = $textArea.data('editor');
        var width = $textArea.data('width');
        var height = $textArea.data('height');
        var gutter = $textArea.data('gutter');

        if (width === undefined) {
            width = $textArea.width();
            if (width <= 0) {
                width = "100%";
            }
        }

        if (height === undefined) {
            height = $textArea.height();
            if (height <= 0) {
                height = "100%";
            }
        }

        if (gutter === undefined) {
            gutter = 1;
        }

        var $editDiv = jQuery('<div>', {
            position: 'absolute',
            'class': $textArea.attr('class')
        }).css('width', width).css('height', height).insertBefore($textArea);

        $textArea.css('display', 'none');

        var editor = ace.edit($editDiv[0]);
        editor.renderer.setShowGutter(gutter);
        editor.getSession().setValue($textArea.val());
        editor.getSession().setMode("ace/mode/" + mode);
        editor.setTheme("ace/theme/monokai");

        editor.getSession().on('change', function () {
            $textArea.val(editor.getSession().getValue());
        });
    }

    /**
     * 
     * @param {string} url 
     * @param {Object} params 
     */
    static setParamsOnURL(url, params) {

        for (var key in params) {
            if (key === '') {
                delete params[key];
            }
        }

        var urlPath = url.split('?')[0];
        var query = decodeURIComponent(jQuery.param(params));
        if (query) {
            urlPath += '?' + query;
        }

        return urlPath;
    }

    static htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    static removeHTMLTags(str) {
        return ('' + str).replace(/<(.|\n)*?>/g, '');
    }

    static removeBetweenBrackets(str) {
        return ('' + str).replace(/ *\([^)]*\) */g, "");
    }

    static getRandomFromArray(array) {
        return array[Math.floor(Math.random() * array.length)];
    }

    /**
     * See https://stackoverflow.com/questions/11832914/round-to-at-most-2-decimal-places-only-if-necessary
     * @param {float} num
     * @param {int} places
     */
    static roundToDecimalPlaces(num, places) {
        return +(Math.round(num + "e+" + places) + "e-" + places);
    }
};
