<?php

namespace ClickerVolt;

require_once __DIR__ . '/ajax.php';
require_once __DIR__ . '/../../db/objects/cvSettings.php';

class AjaxCVSettings extends Ajax
{

    /**
     * throws \Exception
     */
    static function save($form)
    {

        $recaptchaSiteKey = $form['recaptchav3-site-key'];
        $recaptchaSecretKey = $form['recaptchav3-secret-key'];
        $recaptchaHideBadge = empty($form['recaptchav3-hide-badge']) ? '' : 'yes';
        $ipDetectionType = $form['ip-detection'];

        CVSettings::set(CVSettings::RECAPTCHA3_SITE_KEY, $recaptchaSiteKey);
        CVSettings::set(CVSettings::RECAPTCHA3_SECRET_KEY, $recaptchaSecretKey);
        CVSettings::set(CVSettings::RECAPTCHA3_HIDE_BADGE, $recaptchaHideBadge);
        CVSettings::set(CVSettings::IP_DETECTION_TYPE, $ipDetectionType);
        CVSettings::update();
    }
};
