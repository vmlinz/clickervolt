<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';
require_once __DIR__ . '/../utils/dataProxy.php';
require_once __DIR__ . '/../utils/arraySerializer.php';

class Link implements Proxyable
{
    use ArraySerializerImpl {
        toArray as protected _ArraySerializerImplToArray;
        fromArray as protected _ArraySerializerImplFromArray;
    }

    const SETTING_DISTRIBUTION_TYPE = 'type';
    const SETTING_DEFAULT_URLS = 'defaultUrls';
    const SETTING_DEFAULT_WEIGHTS = 'defaultWeights';
    const SETTING_DEFAULT_AFFILIATE_NETWORKS = 'defaultAffNetworks';
    const SETTINGS_REDIRECT_RULES = 'redirectRules';
    const SETTINGS_REDIRECT_MODE = 'redirectMode';
    const SETTINGS_AIDA = 'aida';
    const SETTINGS_AIDA_ATTENTION = 'a';
    const SETTINGS_AIDA_INTEREST = 'i';
    const SETTINGS_AIDA_DESIRE = 'd';
    const SETTINGS_FUNNEL_LINKS = 'funnelLinks';
    const SETTINGS_HOOKS = 'hooks';
    const SETTINGS_HOOKS_REDIRECTS = 'redirects';
    const SETTINGS_HOOKS_REDIRECTS_HTML = 'html';
    const SETTINGS_HOOKS_REDIRECTS_HTML_WHEN = 'when';  // see self::HTML_HOOK_WHEN_xxx
    const SETTINGS_HOOKS_REDIRECTS_HTML_CODE = 'code';
    const SETTINGS_HOOKS_REDIRECTS_HTML_DURATION = 'duration';
    const SETTINGS_HOOKS_REDIRECTS_PHP = 'php';
    const SETTINGS_SLUG_ALIASES = 'aliases';
    const SETTINGS_CLOAKING_OPTIONS = 'cloakingOptions';
    const SETTINGS_CLOAKING_OPTION_MODE = 'mode';       // see self::CLOAKING_MODE_xxx
    const SETTINGS_CLOAKING_OPTION_TITLE = 'title';
    const SETTINGS_CLOAKING_OPTION_DESCRIPTION = 'desc';
    const SETTINGS_CLOAKING_OPTION_KEYWORDS = 'keywords';
    const SETTINGS_CLOAKING_OPTION_NOINDEX = 'noindex';
    const SETTINGS_CLOAKING_OPTION_NOFOLLOW = 'nofollow';
    const SETTINGS_VOLTIFY_OPTIONS = 'voltifyOptions';
    const SETTINGS_VOLTIFY_OPTION_CACHED = 'cached';
    const SETTINGS_VOLTIFY_OPTION_INJECT_AIDA = 'injectAIDA';
    const SETTINGS_VOLTIFY_OPTION_VOLTIFY_INTERNAL_URLS = 'voltifyInternalURLs';
    const SETTINGS_VOLTIFY_OPTION_DISABLE_ANALYTICS = 'disableAnalytics';
    const SETTINGS_VOLTIFY_OPTION_DISABLE_POPUPS = 'disablePopups';
    const SETTINGS_VOLTIFY_OPTION_LINK_REPLACEMENTS = 'linkReplacements';
    const SETTINGS_VOLTIFY_OPTION_DYNAMIC_CONTENT_REPLACEMENTS = 'dynamicContentReplacements';
    const SETTINGS_VOLTIFY_OPTION_STATIC_CONTENT_REPLACEMENTS = 'staticContentReplacements';
    const SETTINGS_FRAUD_DETECTION_OPTIONS = 'fraudOptions';
    const SETTINGS_FRAUD_DETECTION_MODE = 'mode';
    const SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_SITE_KEY =   'recaptcha3SiteKey';
    const SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_SECRET_KEY = 'recaptcha3SecretKey';
    const SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_HIDE_BADGE = 'recaptcha3HideBadge';

    const HTML_HOOK_WHEN_BEFORE = 'before-redirect';
    const HTML_HOOK_WHEN_AFTER = 'after-redirect';

    const REDIRECT_MODE_PERMANENT = 301;
    const REDIRECT_MODE_TEMPORARY = 302;
    const REDIRECT_MODE_DMR = 'meta-refresh';
    const REDIRECT_MODE_CLOAKING = 'cloaked';
    const REDIRECT_MODE_VOLTIFY = 'voltify';

    const CLOAKING_MODE_IFRAME = 'iframe';
    const CLOAKING_MODE_DOWNLOAD = 'download';

    const FRAUD_DETECTION_MODE_NONE = '';
    const FRAUD_DETECTION_MODE_RECAPTCHA_V3 = 'recaptcha3';
    const FRAUD_DETECTION_MODE_HUMAN = 'human';

    const COST_TYPE_CPC = 0;
    const COST_TYPE_CPA = 1;
    const COST_TYPE_TOTAL = 2;

    private $id;
    private $slug;
    private $settings;
    private $costType;
    private $costValue;

    function __construct($properties)
    {
        $this->fromArray($properties);
        $this->openSettings($this->settings);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function getCostType()
    {
        return $this->costType;
    }

    public function getCostValue()
    {
        return $this->costValue;
    }

    public function getHTMLHooks($hookWhen = null)
    {
        $htmlContent = [];

        $linkSettings = $this->getSettings();
        if (isset($linkSettings[self::SETTINGS_HOOKS][self::SETTINGS_HOOKS_REDIRECTS][self::SETTINGS_HOOKS_REDIRECTS_HTML])) {
            $htmlRedirectHooks = $linkSettings[self::SETTINGS_HOOKS][self::SETTINGS_HOOKS_REDIRECTS][self::SETTINGS_HOOKS_REDIRECTS_HTML];
            foreach ($htmlRedirectHooks as $htmlHook) {
                $when = empty($htmlHook[self::SETTINGS_HOOKS_REDIRECTS_HTML_WHEN])
                    ? self::HTML_HOOK_WHEN_BEFORE
                    : $htmlHook[self::SETTINGS_HOOKS_REDIRECTS_HTML_WHEN];

                if ($hookWhen === null || $hookWhen === $when) {
                    $htmlContent[] = base64_decode($htmlHook[self::SETTINGS_HOOKS_REDIRECTS_HTML_CODE]);
                }
            }
        }
        return $htmlContent;
    }

    public function getCloakingOptions()
    {
        $linkSettings = $this->getSettings();
        if (isset($linkSettings[self::SETTINGS_CLOAKING_OPTIONS])) {
            return $linkSettings[self::SETTINGS_CLOAKING_OPTIONS];
        }
        return [];
    }

    public function getVoltifyOptions()
    {
        $linkSettings = $this->getSettings();
        if (isset($linkSettings[self::SETTINGS_VOLTIFY_OPTIONS])) {
            return $linkSettings[self::SETTINGS_VOLTIFY_OPTIONS];
        }
        return [];
    }

    public function getAllURLs()
    {
        $linkSettings = $this->getSettings();
        $allURLs = $linkSettings[Link::SETTING_DEFAULT_URLS];

        if (!empty($linkSettings[Link::SETTINGS_REDIRECT_RULES])) {
            foreach ($linkSettings[Link::SETTINGS_REDIRECT_RULES] as $rule) {
                $allURLs = array_merge($allURLs, $rule['urls']);
            }
        }

        return $allURLs;
    }

    /**
     * Get a list of all funnel links for this link.
     * 
     * @param bool $includeIndirect - if true, also get indirect funnel links, like those specified in voltify link replacements
     * @return array
     */
    public function getFunnelLinks($includeIndirect = false)
    {
        $funnelLinks = [];

        $settings = $this->getSettings();
        if ($settings) {
            if (isset($settings[self::SETTINGS_FUNNEL_LINKS])) {
                $funnelLinks = $settings[self::SETTINGS_FUNNEL_LINKS];
            }

            if ($includeIndirect) {
                if (isset($settings[self::SETTINGS_VOLTIFY_OPTIONS][self::SETTINGS_VOLTIFY_OPTION_LINK_REPLACEMENTS])) {
                    $linkReplacementIds = array_values($settings[self::SETTINGS_VOLTIFY_OPTIONS][self::SETTINGS_VOLTIFY_OPTION_LINK_REPLACEMENTS]);
                    $funnelLinks = array_merge($funnelLinks, $linkReplacementIds);
                }
            }
        }
        return array_unique($funnelLinks);
    }

    public function toArray()
    {
        $array = $this->_ArraySerializerImplToArray();
        $this->openSettings($array['settings']);
        return $array;
    }

    public function fromArray($array)
    {
        $this->_ArraySerializerImplFromArray($array);
        $this->openSettings($this->settings);
        return $this;
    }

    protected function openSettings(&$settings)
    {
        if (is_string($settings)) {
            $settings = json_decode($settings, true);
        }
    }
}

class TableLinks extends Table
{
    /**
     * 
     */
    public function getName()
    {
        return $this->wpTableName('clickervolt_links');
    }

    /**
     * 
     */
    public function setup($oldVersion, $newVersion)
    {
        global $wpdb;

        $tableName = $this->getName();

        if (!$this->doesTableExist()) {

            $sql = "CREATE TABLE {$tableName} (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                        `slug` varchar(128) NOT NULL,
                        `settings` TEXT NOT NULL,
                        `costType` tinyint unsigned NOT NULL,
                        `costValue` double NOT NULL default 0,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `slug_idx` (`slug`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) {

            if ($oldVersion < 0.138 && !$this->doesColumnExist('costType')) {
                $sql = "ALTER TABLE {$tableName} 
                        ADD `costType` tinyint unsigned NOT NULL,
                        ADD `costValue` double NOT NULL default 0";

                $res = $wpdb->query($sql);
                if ($res === false) {
                    throw new \Exception("Cannot update table {$tableName}: {$wpdb->last_error}");
                }
            }

            if ($oldVersion < 1.117) {
                $sql = "ALTER TABLE {$tableName}
                        MODIFY `slug` varchar(128) NOT NULL";

                $res = $wpdb->query($sql);
                if ($res === false) {
                    throw new \Exception("Cannot update table {$tableName}: {$wpdb->last_error}");
                }
            }

            if ($oldVersion < 1.124) {
                // Add fraud options to link settings
                require_once __DIR__ . '/objects/cvSettings.php';
                $siteKey = CVSettings::get(CVSettings::RECAPTCHA3_SITE_KEY);
                $secretKey = CVSettings::get(CVSettings::RECAPTCHA3_SECRET_KEY);
                if ($siteKey && $secretKey) {
                    $hide = CVSettings::get(CVSettings::RECAPTCHA3_HIDE_BADGE);
                    $links = self::loadAll();
                    foreach ($links as $link) {
                        $settings = $link->getSettings();
                        $settings[Link::SETTINGS_FRAUD_DETECTION_OPTIONS] = [
                            Link::SETTINGS_FRAUD_DETECTION_MODE => Link::FRAUD_DETECTION_MODE_RECAPTCHA_V3,
                            Link::SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_SITE_KEY => $siteKey,
                            Link::SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_SECRET_KEY => $secretKey,
                            Link::SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_HIDE_BADGE => $hide,
                        ];
                        $link->setSettings($settings);
                        try {
                            $this->update($link);
                        } catch (\Exception $ex) { }
                    }
                }
            }

            if ($oldVersion < 1.138) {
                $links = self::loadAll();
                foreach ($links as $link) {
                    DataProxy::saveLinkId($link);
                }
            }
        }
    }

    /**
     * @return \ClickerVolt\Link[]
     */
    public function loadAll($properties = ['id', 'slug', 'settings', 'costType', 'costValue'])
    {
        global $wpdb;

        $columns = implode(',', $properties);

        $tableName = $this->getName();
        $rows = $wpdb->get_results("select {$columns} from {$tableName}", ARRAY_A);

        $links = [];
        foreach ($rows as $row) {
            $link = $this->rowToLink($row);

            if (in_array('slug', $properties)) {
                $links[$link->getSlug()] = $link;
            } else {
                $links[] = $link;
            }
        }

        return $links;
    }

    /**
     * @return \ClickerVolt\Link
     */
    public function loadBySlug($slug)
    {
        global $wpdb;

        $tableName = $this->getName();
        $rows = $wpdb->get_results(
            $wpdb->prepare("select * from {$tableName} where slug = %s", $slug),
            ARRAY_A
        );

        if (!empty($rows)) {
            $link = $this->rowToLink($rows[0]);
        } else {
            $link = null;
        }

        return $link;
    }

    /**
     * 
     */
    public function loadById($id)
    {
        global $wpdb;

        $tableName = $this->getName();
        $rows = $wpdb->get_results(
            $wpdb->prepare("select * from {$tableName} where id = %d", $id),
            ARRAY_A
        );

        if (!empty($rows)) {
            $link = $this->rowToLink($rows[0]);
        } else {
            $link = null;
        }

        return $link;
    }

    /**
     * 
     * @return ClickerVolt\Link
     */
    private function rowToLink($row)
    {
        return new Link($row);
    }

    /**
     * 
     */
    public function deleteBySlug($slug)
    {
        global $wpdb;

        $link = $this->loadBySlug($slug);
        if ($link) {

            require_once __DIR__ . '/tableFunnelLinks.php';

            DB::singleton()->transactionStart();
            try {

                $tableFunnelLinks = new TableFunnelLinks();
                $tableFunnelLinks->deleteByLinkId($link->getId());

                $wpdb->delete($this->getName(), ['slug' => $slug]);
                DB::singleton()->transactionCommit();
            } catch (\Exception $ex) {
                DB::singleton()->transactionRollback();
                throw $ex;
            }
        }

        DataProxy::expireLink($slug, $link ? $link->getId() : null);
    }

    /**
     * @param \ClickerVolt\Link $link
     * @return id of the inserted link
     * @throws \Exception
     */
    public function insert($link)
    {
        global $wpdb;

        if (!$wpdb->insert($this->getName(), [
            'slug' => $link->getSlug(),
            'settings' => json_encode($link->getSettings()),
            'costType' => $link->getCostType(),
            'costValue' => $link->getCostValue()
        ])) {

            throw new \Exception("Link could not be saved into DB: " . $wpdb->last_error);
        }

        $link->setId($wpdb->insert_id);

        DataProxy::expireLink($link->getSlug(), $link->getId());
        DataProxy::getLink($link->getSlug());

        return $link->getId();
    }

    /**
     * @param \ClickerVolt\Link $link
     * @throws \Exception
     */
    public function update($link)
    {
        global $wpdb;

        DataProxy::expireLink($link->getSlug(), $link->getId());

        if (
            false === $wpdb->update(
                $this->getName(),
                [
                    'slug' => $link->getSlug(),
                    'settings' => json_encode($link->getSettings()),
                    'costType' => $link->getCostType(),
                    'costValue' => $link->getCostValue()
                ],
                [
                    'id' => $link->getId()
                ]
            )
        ) {
            throw new \Exception("Link could not be updated in DB: " . $wpdb->last_error);
        }

        DataProxy::getLink($link->getSlug());
    }
}
