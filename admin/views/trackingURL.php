<div class="tracking-url-block" id="tracking-url-block-template" style="display: none;">

    <h2>Your Tracking Link</h2>
    <p class="logged-in-warning">Important: Your own clicks will not be recorded while you are logged into WordPress</p>
    <p>If you want to see your clicks appear in your stats, please use an Incognito/Private window, or another Browser.</p>

    <label class="tracking-url-type fast-url-block">
        <input type="url" readonly="true" class="regular-text your-fast-tracking-url">
        <a class="button your-fast-tracking-url-copy"><i class="material-icons for-button copy"></i>Copy</a>
        <a class="button your-fast-tracking-url-test"><i class="material-icons for-button go-to-url"></i>Test</a>
    </label>

    <label class="tracking-url-type pretty-url-block" style="display: none;">
        <input type="url" readonly="true" class="regular-text your-tracking-url">
        <a class="button your-tracking-url-copy"><i class="material-icons for-button copy"></i>Copy</a>
        <a class="button your-tracking-url-test"><i class="material-icons for-button go-to-url"></i>Test</a>
    </label>

    <fieldset class="tracking-url-types-radio">
        <label class="tracking-url-type-radio-group-fastest">
            <input type="radio" class="tracking-url-type-radio-group" value="fastest" checked="checked">
            <span>Use Fastest Redirect URL</span>
        </label>
        <label class="tracking-url-type-radio-group-pretty">
            <input type="radio" class="tracking-url-type-radio-group" value="pretty">
            <span>Use Pretty URL</span>
            <span class="pretty-url-disabled" style="display: none;">(Currently OFF: You must set your <a href="<?php echo admin_url("options-permalink.php") ?>">"Permalink Settings"</a> to anything else other than "Plain")</span>
        </label>
    </fieldset>

    <fieldset class="tracking-url-extra-options">
        <label class='tracking-url-option-slug-aliases' style='display: none;'>
            <input class="tracking-url-use-slug-aliases" type="checkbox">
            <span>Use one of the defined <strong>Slug Aliases</strong> each time I click 'Copy'</span>
        </label>
    </fieldset>

    <form class="sources-form" autocomplete="off">

        <label>
            <p>Add source to tracking link above:</p>
            <p>
                <select class="sources-select">
                    <option reserved="true" value="" selected="selected">None</option>
                    <option reserved="true" value="custom">Custom Entry</option>
                </select>
                <i class="source-delete material-icons inline-delete" style="display: none;"></i>
                <select class="source-models-select" style="display: none;">
                    <option reserved="true" value="" selected="selected">Create from Model...</option>
                </select>
            </p>
        </label>

        <table class="source-details-table">
            <tbody>
                <tr>
                    <td><label>Source Name:</label></td>
                    <td><input name="sourcename" placeholder=" " type="text" class="regular-text alphanum-and-dash"></td>
                    <td><input name="sourceId" type="text" readonly></td>
                </tr>
                <tr>
                    <td><label>V1:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <td><label>V2:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <td><label>V3:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <td><label>V4:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <td><label>V5:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <td><label>V6:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <td><label>V7:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <td><label>V8:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <td><label>V9:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <td><label>V10:</label></td>
                    <td><input name="varvalues[]" placeholder="Value" type="text" class="regular-text"></td>
                    <td><input name="varnames[]" placeholder="Naming in reports" type="text" class="regular-text"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td colspan="2">
                        <input type="submit" class="button save-source" value="Save as template">
                        <label class="confirmation-message"></label>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>

</div>

<script>
    class TrackingURLHtml {

        constructor(container, slug) {

            this._sources = {};
            this._models = {};
            this._divId = ClickerVoltFunctions.uuid();

            this._div = jQuery('#tracking-url-block-template').clone();
            this._div.attr('id', this._divId);
            this._div.find('input.tracking-url-type-radio-group').attr('name', ClickerVoltFunctions.uuid());

            if (!clickerVoltVars.settings.permalinkStructure) {
                this._div.find('input.tracking-url-type-radio-group[value=pretty]').prop('disabled', true);
                this._div.find('span.pretty-url-disabled').show();
            }

            if (container) {
                this._div.appendTo(container);
            }

            this._div.find('input[type=radio].tracking-url-type-radio-group').on('change', {
                cmInstance: this
            }, function(event) {
                event.data.cmInstance._urlTypeUpdated();
            });

            this._div.find('input.tracking-url-use-slug-aliases').on('change', {
                cmInstance: this
            }, function(event) {
                event.data.cmInstance.refreshTrackingURL();
            });

            ClickerVoltFunctions.initSelect2(this._div.find('.sources-select'));
            ClickerVoltFunctions.initSelect2(this._div.find('.source-models-select'));

            this._setupTrackingURLControls('');
            this._setupTrackingURLControls('fast');
            this._setupSourceTemplateForm();

            if (slug) {
                this.setSlug(slug);
            }

            this._div.show();
        }

        getDivId() {
            return this._divId;
        }

        getDiv() {
            return this._div;
        }

        /**
         * 
         */
        getPrettyTrackingURL() {

            var url = clickerVoltVars.urls.home + '/' + this.getActiveSlug();
            var source = encodeURIComponent(this.getCurrentSourceId());
            if (source) {
                url += `.${source}`;
            }

            var queryParams = this._getVarsQueryParams()
            if (Object.keys(queryParams).length > 0) {
                url += '?' + decodeURIComponent(jQuery.param(queryParams));
            }

            return url;
        }

        /**
         * 
         */
        getFastTrackingURL() {

            var baseUrl = clickerVoltVars.urls.plugin;

            var queryParams = {};
            queryParams[clickerVoltVars.const.Router.QUERY_KEY_SLUG] = this.getActiveSlug();

            var source = encodeURIComponent(this.getCurrentSourceId());
            if (source) {
                queryParams[clickerVoltVars.const.Router.QUERY_KEY_SOURCE] = source;
            }

            Object.assign(queryParams, this._getVarsQueryParams());

            return baseUrl + '/go.php?' + decodeURIComponent(jQuery.param(queryParams));
        }

        getCurrentSourceId() {

            var sourceName = this.getDiv().find('table.source-details-table input[name="sourcename"]').val();
            var sourceId = this.getDiv().find('table.source-details-table input[name="sourceId"]').val();

            return sourceId || sourceName;
        }

        /**
         * 
         */
        setSlug(slug) {
            this._slug = slug;
        }

        setSlugAliases(aliases) {
            this._slugAliases = aliases;

            if (aliases && aliases.length) {
                this.getDiv().find('.tracking-url-option-slug-aliases').show();
            } else {
                this.getDiv().find('.tracking-url-option-slug-aliases').hide();
            }
        }

        getActiveSlug() {
            var slug = this._slug;

            if (this._slugAliases && this._slugAliases.length > 0) {
                if (this.getDiv().find('input.tracking-url-use-slug-aliases:checked').length == 1) {
                    slug = ClickerVoltFunctions.getRandomFromArray(this._slugAliases);
                }
            }

            return slug;
        }

        allowFastestURLMode(allowed) {
            if (!allowed) {
                this._div.find('input.tracking-url-type-radio-group[value=fastest]').hide();
                this._div.find('label.tracking-url-type-radio-group-fastest').hide();

                this._div.find('input.tracking-url-type-radio-group[value=pretty]').prop('checked', true);
                this._div.find('input.tracking-url-type-radio-group[value=pretty]').trigger('change');
            } else {
                this._div.find('input.tracking-url-type-radio-group[value=fastest]').show();
                this._div.find('label.tracking-url-type-radio-group-fastest').show();
            }
        }

        /**
         * 
         */
        _setupSourceTemplateForm() {

            // Using https://jqueryvalidation.org/

            var cmInstance = this;
            var $form = this.getDiv().find('.sources-form');

            $form.find('i.source-delete').on('click', function() {
                ClickerVoltModals.confirm('Do you want to delete this source template?', function() {
                    ClickerVoltFunctions.ajax('wp_ajax_clickervolt_delete_source_template', null, {
                        data: {
                            sourceId: $form.find('select.sources-select option:selected').val()
                        },
                        success: function() {
                            var deletedSourceId = $form.find('select.sources-select option:selected').val();
                            delete cmInstance._sources[deletedSourceId];
                            cmInstance._populateSourcesSelect();
                            cmInstance._newSourceSelected();
                        },
                    });
                });
            });

            ClickerVoltFunctions.ajax('wp_ajax_clickervolt_get_sources', null, {
                success: function(response) {
                    cmInstance._sources = response.sources;
                    cmInstance._models = response.models;
                    cmInstance._populateSourcesSelect();
                    cmInstance._populateSourceModelsSelect();
                },
            });

            $form.find('select.sources-select').on('change', {
                cmInstance: this
            }, function(event) {
                event.data.cmInstance._newSourceSelected();
            });
            $form.find('select.sources-select').trigger('change');

            $form.find('select.source-models-select').on('change', {
                cmInstance: this
            }, function(event) {
                event.data.cmInstance._newSourceModelSelected();
            });

            $form.find('table.source-details-table td input[type="text"]').on('input', {
                cmInstance: this
            }, function(event) {
                event.data.cmInstance._sourceInputUpdated(jQuery(this));
            });
            $form.find('table.source-details-table td input[name="sourcename"]').trigger('input');

            jQuery.validator.addMethod('sourceNameValidator', function(value, element) {

                var selected = $form.find('select.sources-select option:selected').val();
                selected = cmInstance._sources[selected] !== undefined ? cmInstance._sources[selected].sourceName : '';

                var reservedNames = [];

                $form.find('select.sources-select option').each(function() {
                    if (jQuery(this).attr('reserved')) {
                        reservedNames.push(jQuery(this).text());
                    }
                });

                var sourceNames = [];
                for (var sourceId in cmInstance._sources) {
                    sourceNames.push(cmInstance._sources[sourceId].sourceName);
                }
                if (ClickerVoltFunctions.isStringInArrayi(value, sourceNames) && selected.toLowerCase() != value.toLowerCase()) {
                    // We are entering a name that is already an existing source name
                    reservedNames.push(value);
                }

                return this.optional(element) || !ClickerVoltFunctions.isStringInArrayi(value, reservedNames);

            }, 'This name is reserved or already used');

            $form.validate({

                rules: {
                    sourcename: {
                        required: true,
                        sourceNameValidator: true,
                        maxlength: clickerVoltVars.const.TableSourceTemplates.MAX_LENGTH_SOURCE_NAME
                    },
                    "varvalues[]": {
                        required: false,
                        maxlength: clickerVoltVars.const.TableSourceTemplates.MAX_LENGTH_VAR_VALUES
                    },
                    "varnames[]": {
                        required: false,
                        maxlength: clickerVoltVars.const.TableSourceTemplates.MAX_LENGTH_VAR_NAMES
                    },
                },

                submitHandler: function(form) {
                    cmInstance._saveSourceTemplate();
                }
            });
        }

        _getVarsQueryParams() {

            var values = [];
            this.getDiv().find('table.source-details-table input[name="varvalues[]"]').each(function() {
                values.push(jQuery(this).val());
            });

            var namesInReports = [];
            this.getDiv().find('table.source-details-table input[name="varnames[]"]').each(function() {
                namesInReports.push(jQuery(this).val());
            });

            var paramNames = [];
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_1);
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_2);
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_3);
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_4);
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_5);
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_6);
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_7);
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_8);
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_9);
            paramNames.push(clickerVoltVars.const.Router.QUERY_KEY_VAR_10);

            var emptyVarPlaceHolder = clickerVoltVars.const.TableSourceTemplates.EMPTY_VAR_PLACE_HOLDER;
            var params = {};
            values.forEach(function(value, i) {
                if (!ClickerVoltFunctions.isEmptyString(value)) {
                    params[paramNames[i]] = value;
                } else if (!ClickerVoltFunctions.isEmptyString(namesInReports[i])) {
                    params[paramNames[i]] = emptyVarPlaceHolder.replace('%s', namesInReports[i].toUpperCase().replace(/ /g, '-'));
                }
            });

            return params;
        }

        /**
         * 
         */
        refreshTrackingURL() {
            this.getDiv().find('input.your-tracking-url').val(this.getPrettyTrackingURL());
            this.getDiv().find('input.your-fast-tracking-url').val(this.getFastTrackingURL());
        }

        /**
         * 
         */
        _setupTrackingURLControls(which) {

            if (which) {
                which = `your-${which}-tracking-url`;
            } else {
                which = `your-tracking-url`;
            }

            jQuery(`#${this._divId} .${which}-copy`).on('click', {
                cmInstance: this
            }, function(event) {

                event.data.cmInstance.refreshTrackingURL();
                var $elem = jQuery(`#${event.data.cmInstance._divId} input.${which}`);
                ClickerVoltFunctions.copyToClipboard($elem.val(), $elem);
            });

            jQuery(`#${this._divId} .${which}-test`).on('click', {
                cmInstance: this
            }, function(event) {

                var url = jQuery(`#${event.data.cmInstance._divId} input.${which}`).val();
                window.open(url);
            });
        }

        /**
         * 
         */
        _sourceInputUpdated($input) {

            if ($input.attr('name') == 'sourcename') {
                if ($input.val()) {
                    this.getDiv().find('input.button.save-source').prop('disabled', false);
                } else {
                    this.getDiv().find('input.button.save-source').prop('disabled', true);
                }
            }

            this.refreshTrackingURL();
        }

        /**
         * 
         */
        _newSourceSelected() {
            this._emptySourceInfo();

            var $form = this.getDiv().find('form.sources-form');
            var $table = this.getDiv().find('table.source-details-table');

            $form.find('i.source-delete').hide();

            this.getDiv().find('.source-models-select').hide();

            var selected = this.getDiv().find('select.sources-select option:selected').val();
            if (!selected) {
                $table.hide();

            } else {
                if (this._sources[selected]) {
                    var source = this._sources[selected];
                    this._fillSourceInfo(source);
                    $form.find('i.source-delete').show();

                } else if (selected == "custom") {
                    this.getDiv().find('.source-models-select').show();
                }

                $table.show();
            }

            this._sourceInputUpdated($table.find('input[name="sourcename"]'));
        }

        /**
         *
         */
        _newSourceModelSelected() {
            var $select = this.getDiv().find('select.source-models-select');
            var selected = $select.find('option:selected').val();
            if (selected) {
                this._emptySourceInfo();

                if (this._models[selected]) {
                    var source = this._models[selected];
                    this._fillSourceInfo(source);
                    $select.val('').trigger('change');

                    var $table = this.getDiv().find('table.source-details-table');
                    $table.find('input[name="sourceId"]').val('');

                    this._sourceInputUpdated($table.find('input[name="sourcename"]'));
                }
            }
        }

        _emptySourceInfo() {
            var $table = this.getDiv().find('table.source-details-table');
            $table.find('tbody td input').val('');
            $table.find('input[type="text"]').val('').removeClass('error');
            $table.find('label.error').remove();
        }

        /**
         * 
         */
        _fillSourceInfo(source) {
            this._emptySourceInfo();

            var $table = this.getDiv().find('table.source-details-table');

            $table.find('input[name="sourcename"]').val(source.sourceName);
            $table.find('input[name="sourceId"]').val(source.sourceId);

            $table.find('input[name="varvalues[]"]').each(function(index) {
                var v = index + 1;
                var key = `v${v}`;
                jQuery(this).val(source[key] === null ? '' : source[key]);
            });

            $table.find('input[name="varnames[]"]').each(function(index) {
                var v = index + 1;
                var key = `v${v}Name`;
                jQuery(this).val(source[key] === null ? '' : source[key]);
            });
        }

        /**
         * 
         */
        _urlTypeUpdated() {

            this.getDiv().find('.tracking-url-type').hide();

            switch (this.getDiv().find('input[type=radio].tracking-url-type-radio-group:checked').val()) {

                case 'pretty':
                    this.getDiv().find('.pretty-url-block').show();
                    break;

                case 'fastest':
                    this.getDiv().find('.fast-url-block').show();
                    break;
            }
        }

        /**
         * 
         */
        _saveSourceTemplate() {

            var cmInstance = this;
            var $form = this.getDiv().find('form.sources-form');

            $form.find('input[type="submit"]').prop('disabled', true);

            ClickerVoltFunctions.ajax('wp_ajax_clickervolt_save_source_template', $form[0], {

                success: function(source) {

                    cmInstance._sources[source.sourceId] = source;
                    cmInstance._populateSourcesSelect(source.sourceId);

                    $form.find('input[name="sourceId"]').val(source.sourceId);
                    ClickerVoltFunctions.showSavedConfirmation($form.find('label.confirmation-message'));

                    if (ClickerVoltLinkController.sourceSavedCallbacks) {

                        ClickerVoltLinkController.sourceSavedCallbacks.forEach(function(callback) {
                            callback(source);
                        });
                    }
                },
                complete: function() {
                    $form.find('input[type="submit"]').prop('disabled', false);
                }
            });
        }

        /**
         * 
         */
        _populateSourcesSelect(selectedValue) {
            var $select = this.getDiv().find('select.sources-select');

            $select.find('option').each(function() {
                var $option = jQuery(this);
                if (!$option.attr('reserved')) {
                    $option.remove();
                }
            });

            for (var sourceId in this._sources) {
                var sourceName = this._sources[sourceId].sourceName;
                $select.append(`<option value="${sourceId}">${sourceName}</option>`);
            }

            if (selectedValue !== undefined) {
                $select.val(selectedValue).trigger('change');
            }
        }

        /**
         * 
         */
        _populateSourceModelsSelect() {
            var $select = this.getDiv().find('select.source-models-select');

            for (var sourceId in this._models) {
                var sourceName = this._models[sourceId].sourceName;
                $select.append(`<option value="${sourceId}">${sourceName}</option>`);
            }
        }
    }
</script>