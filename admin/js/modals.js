// Look at http://craftpip.github.io/jquery-confirm/ for plugin options

class ClickerVoltModals {

    static error(title, message) {
        ClickerVoltModals.message(title, message);
    }

    static message(title, message) {
        jQuery.confirm({
            boxWidth: '30%',
            useBootstrap: false,
            title: title,
            content: message,
            type: 'red',
            scrollToPreviousElement: false,
            // theme: 'supervan',
            buttons: {
                ok: {
                    text: 'OK',
                    btnClass: 'btn-blue btn-primary',
                },
            }
        });
    }

    /**
     * 
     * @param {*} message 
     * @param {*} onConfirmed 
     */
    static confirm(message, onConfirmed, onCancelled, options) {

        var defaultOptions = {
            title: 'Confirmation Required',
            okButtonText: 'OK',
            okButtonClass: 'btn-blue btn-primary',
            cancelButtonText: 'Cancel',
            cancelButtonClass: 'btn-default',
        };

        if (!options) {
            options = {};
        }
        options = jQuery.extend({}, defaultOptions, options);

        jQuery.confirm({
            boxWidth: '30%',
            useBootstrap: false,
            title: options.title,
            content: message,
            type: 'red',
            scrollToPreviousElement: false,
            // theme: 'supervan',
            buttons: {
                ok: {
                    text: options.okButtonText,
                    btnClass: options.okButtonClass,
                    action: function () {
                        if (onConfirmed) {
                            onConfirmed();
                        }
                    }
                },
                cancel: {
                    text: options.cancelButtonText,
                    btnClass: options.cancelButtonClass,
                    keys: ['esc'],
                    action: function () {
                        if (onCancelled) {
                            onCancelled();
                        }
                    }
                }
            }
        });
    }
}