<?php

namespace ClickerVolt;

require_once __DIR__ . '/ajax.php';
require_once __DIR__ . '/../../db/tableStatsWholePath.php';
require_once __DIR__ . '/../../db/tableLinks.php';
require_once __DIR__ . '/../../db/tableFunnelLinks.php';
require_once __DIR__ . '/../../db/objects/cvSettings.php';
require_once __DIR__ . '/../../redirect/distributions/distributionSequential.php';
require_once __DIR__ . '/../../utils/dataProxy.php';

class AjaxLinks extends Ajax
{
    const STATUS_SLUG_CHECKING = "cv-slug-checking";

    /**
     * throws \Exception
     */
    static function saveLink($form)
    {
        $response = [];

        $id = $form['linkid'] ?: null;
        $slug = $form['linkslug'];
        $slugAliases = $form['slug-aliases'];
        $distributionType = $form['linkdist'];
        $distributionSequentialCounter = $form['linkdist-sequential-counter'];
        $defaultUrls = $form['urls']['default-path'];
        $defaultWeights = $form['weights']['default-path'];
        $defaultAffNetworks = $form['aff-networks']['default-path'];
        $redirectMode = $form['redirect-mode'];
        $linkCostType = $form['link-cost-type'];
        $linkCostValue = $form['link-cost-value'];
        $aidaAttention = $form['aida-attention'];
        $aidaInterest = $form['aida-interest'];
        $aidaDesire = $form['aida-desire'];
        $cloakingTitle = empty($form['cloaking-option-meta-title']) ? '' : $form['cloaking-option-meta-title'];
        $cloakingDescription = empty($form['cloaking-option-meta-description']) ? '' : $form['cloaking-option-meta-description'];
        $cloakingKeywords = empty($form['cloaking-option-meta-keywords']) ? '' : $form['cloaking-option-meta-keywords'];
        $cloakingNoIndex = empty($form['cloaking-option-no-index']) ? false : true;
        $cloakingNoFollow = empty($form['cloaking-option-no-follow']) ? false : true;
        $voltifyCached = empty($form['voltify-option-cache']) ? false : true;
        $voltifyInjectAIDA = empty($form['voltify-inject-aida']) ? false : true;
        $voltifyInternalURLs = empty($form['voltify-internal-urls']) ? false : true;
        $voltifyDisableAnalytics = empty($form['voltify-option-disable-analytics']) ? false : true;
        $voltifyDisablePopups = empty($form['voltify-option-disable-popups']) ? false : true;
        $voltifyLinkReplacementsFrom = empty($form['voltify-link-replacement-urls']) ? [] : $form['voltify-link-replacement-urls'];
        $voltifyLinkReplacementsTo = empty($form['voltify-link-replacement-links']) ? [] : $form['voltify-link-replacement-links'];
        $voltifyDynamicContentReplacementsFrom = empty($form['voltify-dynamic-content-replacement-froms']) ? [] : $form['voltify-dynamic-content-replacement-froms'];
        $voltifyDynamicContentReplacementsTo = empty($form['voltify-dynamic-content-replacement-tos']) ? [] : $form['voltify-dynamic-content-replacement-tos'];
        $voltifyStaticContentReplacementsFrom = empty($form['voltify-static-content-replacement-froms']) ? [] : $form['voltify-static-content-replacement-froms'];
        $voltifyStaticContentReplacementsTo = empty($form['voltify-static-content-replacement-tos']) ? [] : $form['voltify-static-content-replacement-tos'];
        $selectFunnelLinkIds = empty($form['select-funnel-links']) ? [] : $form['select-funnel-links'];
        $hookRedirectHTMLCodes = empty($form['hook-redirect-html-code']) ? [] : $form['hook-redirect-html-code'];
        $hookRedirectHTMLWhens = empty($form['hook-redirect-html-when']) ? [] : $form['hook-redirect-html-when'];
        $hookRedirectHTMLDurations = empty($form['hook-redirect-html-duration']) ? [] : $form['hook-redirect-html-duration'];
        $hookRedirectPHPCodes = empty($form['hook-redirect-php']) ? [] : $form['hook-redirect-php'];
        $redirectRules = empty($_POST['redirectRules']) ? [] : $_POST['redirectRules'];
        $fraudDetectionMode = $form['bot-detection-type-mode'];
        $fraudDetectionRecaptcha3SiteKey = $form['recaptchav3-site-key'];
        $fraudDetectionRecaptcha3SecretKey = $form['recaptchav3-secret-key'];
        $fraudDetectionRecaptcha3HideBadge = empty($form['recaptchav3-hide-badge']) ? '' : 'yes';

        if ($fraudDetectionMode == Link::FRAUD_DETECTION_MODE_RECAPTCHA_V3 && !empty($fraudDetectionRecaptcha3SiteKey)) {
            // Set default settings for recaptcha v3
            CVSettings::set(CVSettings::RECAPTCHA3_SITE_KEY, $fraudDetectionRecaptcha3SiteKey);
            CVSettings::set(CVSettings::RECAPTCHA3_SECRET_KEY, $fraudDetectionRecaptcha3SecretKey);
            CVSettings::set(CVSettings::RECAPTCHA3_HIDE_BADGE, $fraudDetectionRecaptcha3HideBadge);
            CVSettings::update();

            $response['recaptcha'] = [
                'recaptchaV3SiteKey' => $fraudDetectionRecaptcha3SiteKey,
                'recaptchaV3SecretKey' => $fraudDetectionRecaptcha3SecretKey,
                'recaptchaV3HideBadge' => $fraudDetectionRecaptcha3SecretKey,
            ];
        }

        self::validateSlug($slug, $id);
        self::validateUrls($defaultUrls, $defaultWeights, $defaultAffNetworks);
        $voltifyLinkReplacements = self::pairsToKeyValues($voltifyLinkReplacementsFrom, $voltifyLinkReplacementsTo);
        $voltifyDynamicContentReplacements = self::pairsToKeyValues($voltifyDynamicContentReplacementsFrom, $voltifyDynamicContentReplacementsTo);
        $voltifyStaticContentReplacements = self::pairsToKeyValues($voltifyStaticContentReplacementsFrom, $voltifyStaticContentReplacementsTo);

        if (!empty($slugAliases)) {
            $slugAliases = str_replace("\r\n", "\n", $slugAliases);
            $slugAliases = explode("\n", $slugAliases);
            foreach ($slugAliases as $k => $alias) {
                if (strlen(trim($alias)) == 0) {
                    unset($slugAliases[$k]);
                }
            }
            $slugAliases = array_values($slugAliases);

            // Check that these aliases are not used for other links
            foreach ($slugAliases as $alias) {
                $path = DataProxy::getAliasFilePath($alias);
                if (file_exists($path)) {
                    $aliasSlug = file_get_contents($path);
                    if ($aliasSlug != $slug) {
                        $aliasLink = DataProxy::getLink($aliasSlug);
                        if ($aliasLink && $aliasLink->getId() != $id) {
                            throw new \Exception("Alias '{$alias}' is already used by link '{$aliasSlug}'");
                        }
                    }
                } else {
                    $otherLink = DataProxy::getLink($alias);
                    if ($otherLink && $otherLink->getId() != $id) {
                        throw new \Exception("Alias '{$alias}' already used by existing link's slug");
                    }
                }
                self::validateSlug($alias);
            }
        } else {
            $slugAliases = [];
        }

        // Make sure interest has a higher value than attention
        // and that desire has a higher value than interest
        $aidaInterest = max($aidaAttention, $aidaInterest);
        $aidaDesire = max($aidaInterest, $aidaDesire);

        $hooks = [];
        $hookRedirects = [];

        foreach ($hookRedirectHTMLCodes as $k => $code) {
            if ($k != 'x' && $code !== '') {
                $when = $hookRedirectHTMLWhens[$k];
                $duration = $hookRedirectHTMLDurations[$k];
                $hookRedirects[Link::SETTINGS_HOOKS_REDIRECTS_HTML][] = [
                    Link::SETTINGS_HOOKS_REDIRECTS_HTML_CODE => base64_encode($code),
                    Link::SETTINGS_HOOKS_REDIRECTS_HTML_WHEN => $when,
                    Link::SETTINGS_HOOKS_REDIRECTS_HTML_DURATION => $duration,
                ];
            }
        }

        foreach ($hookRedirectPHPCodes as $k => $code) {
            if ($k != 'x' && $code !== '<?php ') {
                $hookRedirects[Link::SETTINGS_HOOKS_REDIRECTS_PHP][] = base64_encode($code);
            }
        }

        if (!empty($hookRedirects)) {
            $hooks[Link::SETTINGS_HOOKS_REDIRECTS] = $hookRedirects;
        }

        foreach ($redirectRules as $k => $rule) {
            if (empty($rule['conditions'])) {
                unset($redirectRules[$k]);
                continue;
            }

            foreach ($rule['conditions'] as $i => $condition) {
                if (empty($condition['type'])) {
                    unset($redirectRules[$k]['conditions'][$i]);
                    continue;
                }
                if (
                    $condition['operator'] != Rules::OPERATOR_EMPTY
                    && $condition['operator'] != Rules::OPERATOR_EMPTY_NOT
                    && empty($condition['values'])
                ) {

                    unset($redirectRules[$k]['conditions'][$i]);
                    continue;
                }

                // switch( $condition['type'] ) {
                //     case Rules::RULE_TYPE_IP: {
                //         foreach( $condition['values'] as $k => $ip ) {

                //         }
                //     }
                //     break;
                // }
            }
            $redirectRules[$k]['conditions'] = array_values($redirectRules[$k]['conditions']);

            self::validateUrls($redirectRules[$k]['urls'], $redirectRules[$k]['weights'], $redirectRules[$k]['aff-networks']);
        }
        $redirectRules = array_values($redirectRules);

        $settings = [
            Link::SETTING_DISTRIBUTION_TYPE => $distributionType,
            Link::SETTING_DEFAULT_URLS => $defaultUrls,
            Link::SETTING_DEFAULT_WEIGHTS => $defaultWeights,
            Link::SETTING_DEFAULT_AFFILIATE_NETWORKS => $defaultAffNetworks,
            Link::SETTINGS_REDIRECT_RULES => $redirectRules,
            Link::SETTINGS_REDIRECT_MODE => $redirectMode,
            Link::SETTINGS_AIDA => [
                Link::SETTINGS_AIDA_ATTENTION => $aidaAttention ?: 0,
                Link::SETTINGS_AIDA_INTEREST => $aidaInterest ?: 0,
                Link::SETTINGS_AIDA_DESIRE => $aidaDesire ?: 0,
            ],
            Link::SETTINGS_FUNNEL_LINKS => $selectFunnelLinkIds,
            Link::SETTINGS_HOOKS => $hooks,
            Link::SETTINGS_SLUG_ALIASES => $slugAliases,
            Link::SETTINGS_CLOAKING_OPTIONS => [
                Link::SETTINGS_CLOAKING_OPTION_MODE => Link::CLOAKING_MODE_IFRAME,
                Link::SETTINGS_CLOAKING_OPTION_TITLE => trim($cloakingTitle),
                Link::SETTINGS_CLOAKING_OPTION_DESCRIPTION => trim($cloakingDescription),
                Link::SETTINGS_CLOAKING_OPTION_KEYWORDS => trim($cloakingKeywords),
                Link::SETTINGS_CLOAKING_OPTION_NOINDEX => $cloakingNoIndex,
                Link::SETTINGS_CLOAKING_OPTION_NOFOLLOW => $cloakingNoFollow,
            ],
            Link::SETTINGS_VOLTIFY_OPTIONS => [
                Link::SETTINGS_VOLTIFY_OPTION_CACHED => $voltifyCached,
                Link::SETTINGS_VOLTIFY_OPTION_INJECT_AIDA => $voltifyInjectAIDA,
                Link::SETTINGS_VOLTIFY_OPTION_VOLTIFY_INTERNAL_URLS => $voltifyInternalURLs,
                Link::SETTINGS_VOLTIFY_OPTION_DISABLE_ANALYTICS => $voltifyDisableAnalytics,
                Link::SETTINGS_VOLTIFY_OPTION_DISABLE_POPUPS => $voltifyDisablePopups,
                Link::SETTINGS_VOLTIFY_OPTION_LINK_REPLACEMENTS => $voltifyLinkReplacements,
                Link::SETTINGS_VOLTIFY_OPTION_DYNAMIC_CONTENT_REPLACEMENTS => $voltifyDynamicContentReplacements,
                Link::SETTINGS_VOLTIFY_OPTION_STATIC_CONTENT_REPLACEMENTS => $voltifyStaticContentReplacements,
            ],
            Link::SETTINGS_FRAUD_DETECTION_OPTIONS => [
                Link::SETTINGS_FRAUD_DETECTION_MODE => $fraudDetectionMode,
                Link::SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_SITE_KEY => $fraudDetectionRecaptcha3SiteKey,
                Link::SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_SECRET_KEY => $fraudDetectionRecaptcha3SecretKey,
                Link::SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_HIDE_BADGE => $fraudDetectionRecaptcha3HideBadge,
            ],
        ];

        if ($distributionType == DistributionSequential::TYPE) {
            $settings['sequentialCounter'] = $distributionSequentialCounter;
        }

        $link = new Link([
            'id' => $id,
            'slug' => $slug,
            'settings' => $settings,
            'costType' => $linkCostType,
            'costValue' => $linkCostValue,
        ]);

        if ($id != null) {
            // Updating existing link...
            $oldLink = DataProxy::getLink($slug);
            if ($oldLink == null) {
                // Old link with that name doesn't exist... it means we are changing the slug to something else.
                // Make sure to delete old proxied data for that old slug.
                $table = new TableLinks();
                $oldLink = $table->loadById($id);
                if ($oldLink) {
                    DataProxy::expireLink($oldLink->getSlug());
                }
            }
        }

        DB::singleton()->transactionStart();
        try {
            $table = new TableLinks();
            if ($id === null) {
                $table->insert($link);
            } else {
                $table->update($link);
            }

            $funnelLinks = [];
            if ($selectFunnelLinkIds && is_array($selectFunnelLinkIds)) {
                foreach ($selectFunnelLinkIds as $sfl) {
                    $funnelLinks[] = new FunnelLink($link->getId(), $sfl);
                }
            }

            $tableFunnelLinks = new TableFunnelLinks();
            $tableFunnelLinks->deleteByParentLinkId($link->getId());
            $tableFunnelLinks->insert($funnelLinks);

            DB::singleton()->transactionCommit();
        } catch (\Exception $ex) {
            DB::singleton()->transactionRollback();
            throw $ex;
        }

        $response['link'] = $link->toArray();
        return $response;
    }

    /**
     * Check that there is no post type using this slug
     * @throws \Exception if slug is already in use
     */
    static private function validateSlug($slug, $linkId = null)
    {
        $uniqueSlug = wp_unique_post_slug($slug, $linkId, self::STATUS_SLUG_CHECKING, 'attachment', 0);
        if ($uniqueSlug != $slug) {
            throw new \Exception("The slug '{$slug}' is already used by one of your posts or pages");
        }
    }

    /**
     * 
     */
    static function getAllSlugs()
    {
        $table = new TableLinks();
        $links = $table->loadAll(['id', 'slug']);

        $slugInfos = [];
        foreach ($links as $link) {
            $slugInfos[] = [
                'id' => $link->getId(),
                'slug' => $link->getSlug()
            ];
        }

        return $slugInfos;
    }

    /**
     * 
     */
    static function getLink()
    {
        $id = Sanitizer::sanitizeKey($_POST['id']);

        $table = new TableLinks();
        $link = $table->loadById($id);

        return $link ? $link->toArray() : [];
    }

    /**
     * 
     */
    static function getLinkBySlug()
    {
        $slug = Sanitizer::sanitizeKey($_POST['slug']);

        $table = new TableLinks();
        $link = $table->loadBySlug($slug);

        return $link ? $link->toArray() : [];
    }

    /**
     * 
     */
    static function deleteLinkBySlug()
    {
        $slug = Sanitizer::sanitizeKey($_POST['slug']);

        $table = new TableLinks();
        $link = $table->deleteBySlug($slug);
    }

    /**
     * 
     */
    static function getAIDAScriptTemplate()
    {
        return JSTracking::getRemoteTrackingScript('#SLUG#', ['minimize ' => true]);
    }

    /**
     * 
     */
    static private function validateUrls(&$urls, &$weights = null, $affNetworks = null)
    {
        foreach ($urls as $i => $url) {
            if (empty(trim($url))) {
                unset($urls[$i]);

                if ($weights !== null) {
                    unset($weights[$i]);
                }

                if ($affNetworks !== null) {
                    unset($affNetworks[$i]);
                }
            }
        }

        $urls = array_values($urls);
        if ($weights !== null) {
            $weights = array_values($weights);
        }
        if ($affNetworks !== null) {
            $affNetworks = array_values($affNetworks);
        }
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    static private function pairsToKeyValues(&$array1, &$array2)
    {
        $keyValues = [];

        foreach ($array1 as $i => $v1) {
            if ($v1 !== '') {
                if (array_key_exists($i, $array2) && $array2[$i] !== '') {
                    $keyValues[$v1] = $array2[$i];
                }
            }
        }

        return $keyValues;
    }
};
