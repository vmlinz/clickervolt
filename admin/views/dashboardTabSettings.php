<div id="tab-settings" class="tab-content">

    <form id="settings-form" method="post" novalidate="novalidate">

        <div class="settings-section">
            <h2>IP Detection</h2>
            <p class="description">For the majority of servers out there, we recommend to keep "Auto" selected to let ClickerVolt decide which header to use to determine visitor IPs.</p>
            <p class="description">On Hostgator, you must select the REMOTE_ADDR option, as Hostgator doesn't pass the X-Forwarded-For header to any PHP software, including WordPress. Some other hosting companies may have the same restriction in place, so if you notice all your traffic is reported as coming from the same IP, try using that setting too.</p>
            <select name="ip-detection">
                <option value="auto">Auto (Recommended)</option>
                <option value="REMOTE_ADDR">REMOTE_ADDR (Hostgator and similar)</option>
            </select>
        </div>

        <div>
            <input type="submit" name="submit" class="save-settings button button-primary" value="Save Settings">
            <label id="saved-settings-confirmation-message" class="confirmation-message"></label>
        </div>

    </form>

</div>

<script>
    jQuery(document).ready(function() {
        setupForm();

        var $select = jQuery('select[name=ip-detection]');
        ClickerVoltFunctions.addOptionToSelect($select, clickerVoltVars.const.CVSettings.VALUE_IP_DETECTION_TYPE_AUTO, 'Auto (Recommended)');
        ClickerVoltFunctions.addOptionToSelect($select, clickerVoltVars.const.CVSettings.VALUE_IP_DETECTION_TYPE_REMOTE_ADDR, 'REMOTE_ADDR (Hostgator and similar)');
        if (clickerVoltVars.settings.ipDetectionType) {
            $select.val(clickerVoltVars.settings.ipDetectionType);
        }
    });

    /**
     * 
     */
    function setupForm() {

        // Using https://jqueryvalidation.org/

        var $form = jQuery("#settings-form");

        $form.validate({
            rules: {},

            submitHandler: function(form) {

                $form.find('input[type=submit].save-settings').prop('disabled', true);

                ClickerVoltFunctions.ajax('wp_ajax_clickervolt_save_settings', form, {
                    data: {},
                    success: function() {
                        ClickerVoltFunctions.showSavedConfirmation(jQuery('#saved-settings-confirmation-message'));
                        location.reload();
                    },
                    complete: function() {
                        $form.find('input[type=submit].save-settings').prop('disabled', false);
                    }
                });
            }
        });
    }
</script>