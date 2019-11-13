<?php

namespace ClickerVolt;

require_once __DIR__ . '/redirector.php';
require_once __DIR__ . '/../jsTracking/jsTracking.php';

class RedirectorCloaked implements Redirector
{
    /**
     * 
     */
    function redirectTo($url, $options = [])
    {
        list($headOptions, $htmlContent) = $this->getHTMLData($url, $options);

        echo "<head>" . implode(PHP_EOL, $headOptions) . "</head>";
        echo "<body style='margin:0px;padding:0px;overflow:hidden'>";
        echo "<iframe id='main-iframe' src='{$url}' frameborder='0' style='overflow:hidden;overflow-x:hidden;overflow-y:hidden;height:100%;width:100%;position:absolute;top:0px;left:0px;right:0px;bottom:0px' height='100%' width='100%'></iframe>";
        echo $htmlContent;
        echo "</body>";
        exit;
    }

    /**
     * 
     * @return [$headOptions, $htmlContent]
     */
    protected function getHTMLData($url, $options = [])
    {
        $slug = empty($options[self::OPTION_SLUG]) ? null : $options[self::OPTION_SLUG];

        $headOptions = [
            '<meta charset="UTF-8">',
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
        ];
        $script = '';
        $htmlContent = '';

        if ($slug) {
            $link = DataProxy::getLink($slug);
            if ($link) {
                $htmlContentFromBeforeHook = $link->getHTMLHooks(Link::HTML_HOOK_WHEN_BEFORE);
                if (!empty($htmlContentFromBeforeHook)) {
                    $htmlContent .= implode('<br>', $htmlContentFromBeforeHook);
                }

                $cloakingOptions = $link->getCloakingOptions();
                if (!empty($cloakingOptions)) {
                    if (!empty($cloakingOptions[Link::SETTINGS_CLOAKING_OPTION_TITLE])) {
                        $headOptions[] = '<title>' . $cloakingOptions[Link::SETTINGS_CLOAKING_OPTION_TITLE] . '</title>';
                    }
                    if (!empty($cloakingOptions[Link::SETTINGS_CLOAKING_OPTION_DESCRIPTION])) {
                        $headOptions[] = '<meta name="description" content="' . htmlentities($cloakingOptions[Link::SETTINGS_CLOAKING_OPTION_DESCRIPTION], ENT_COMPAT) . '">';
                    }
                    if (!empty($cloakingOptions[Link::SETTINGS_CLOAKING_OPTION_KEYWORDS])) {
                        $headOptions[] = '<meta name="keywords" content="' . htmlentities($cloakingOptions[Link::SETTINGS_CLOAKING_OPTION_KEYWORDS], ENT_COMPAT) . '">';
                    }

                    $robotsOptions = [];
                    if (!empty($cloakingOptions[Link::SETTINGS_CLOAKING_OPTION_NOINDEX])) {
                        $robotsOptions[] = 'noindex';
                    }
                    if (!empty($cloakingOptions[Link::SETTINGS_CLOAKING_OPTION_NOFOLLOW])) {
                        $robotsOptions[] = 'nofollow';
                    }
                    if (!empty($robotsOptions)) {
                        $headOptions[] = '<meta name="robots" content="' . implode(',', $robotsOptions) . '"';
                    }
                }
            }

            $script = JSTracking::getRemoteTrackingScript($slug, ['cloaked' => true, 'minimize' => false]);
        }

        return [$headOptions, $script . $htmlContent];
    }
}
