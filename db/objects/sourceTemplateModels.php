<?php

namespace ClickerVolt;

require_once __DIR__ . '/../tableSourceTemplates.php';

class SourceTemplateModels
{
    const BING = "Microsoft Ads (Bing Ads)";
    const GOOGLE_SEARCH_AND_DISPLAY = "Google Ads (Search-Display)";
    const GOOGLE_YOUTUBE = "Google Ads (YouTube)";
    const FB = "FB Ads";
    const MEGAPUSH = "MegaPush";
    const RICH_PUSH = "RichPush";
    const PUSH_GROUND = "Pushground";
    const ZEROPARK = "Zeropark";
    const WIDGET_MEDIA = "Widget Media";
    const TRAFFIC_STARS = "Traffic Stars";
    const TABOOLA = "Taboola";
    const SELF_ADVERTISER = "Self Advertiser";
    const REVCONTENT = "RevContent";
    const PROPELLERADS = "PropellerAds";
    const POPCASH = "PopCash";
    const POPADS = "PopAds";
    const OUTBRAIN = "Outbrain";
    const MGID = "MGID";
    const AVAZU = "Avazu";
    const ADCASH = "AdCash";
    const ADMAVEN = "AdMaven";

    static function getModels()
    {
        $models = [];
        self::push($models, self::createTemplate(self::ADMAVEN, [
            "{pubfeed}" => "Publisher Feed ID",
            "{remfeed}" => "Remote Feed ID",
            "{keyword}" => "Keyword",
            "{query}" => "Search Query",
            "{banner}" => "Banner",
            "{referrer_domain}" => "Referrer Domain",
            "{country}" => "Country Code",
        ], "{subid}", "{bid}"));

        self::push($models, self::createTemplate(self::ADCASH, [
            "[zone]" => "Zone",
            "[campaign]" => "Campaign",
            "[country]" => "Country",
            "[lang]" => "Language",
        ], "[clickid]", null));

        self::push($models, self::createTemplate(self::AVAZU, [
            "{bundle_id}" => "Bundle ID",
            "{publisher_name}" => "Publisher Name",
            "{source_name}" => "Source Name",
            "{creative_id}" => "Creative ID",
            "{category}" => "Category",
            "{exchange}" => "Exchange",
            "{media}" => "Media",
            "{country}" => "Country",
            "{carrier}" => "Carrier",
        ], "{subid}", null));

        self::push($models, self::createTemplate(self::MGID, [
            "{widget_id}" => "Publisher",
            "{teaser_id}" => "Teaser ID",
            "{campaign_id}" => "Campaign ID",
        ], "{click_id}", null));

        self::push($models, self::createTemplate(self::OUTBRAIN, [
            "{{publisher_name}}" => "Publisher Name",
            "{{publisher_id}}" => "Publisher ID",
            "{{campaign_id}}" => "Campaign ID",
            "{{section_name}}" => "Section Name",
            "{{ad_id}}" => "Ad ID",
        ], "{{ob_click_id}}", null));

        self::push($models, self::createTemplate(self::POPADS, [
            "[WEBSITEID]" => "Website ID",
            "[CAMPAIGNNAME]" => "Campaign Name",
            "[CATEGORYNAME]" => "Category Name",
            "[DEVICENAME]" => "Device Name",
            "[FORMFACTORNAME]" => "Form Factor",
            "[OSNAME]" => "OS Name",
            "[ISPNAME]" => "ISP",
            "[COUNTRY]" => "Country",
            "[ADBLOCK]" => "AdBlock",
            "[QUALITY]" => "Quality Score",
        ], "[IMPRESSIONID]", "[BID]"));

        self::push($models, self::createTemplate(self::POPCASH, [
            "[siteid]" => "Publisher",
            "[campaignid]" => "Campaign ID",
            "[category]" => "Category",
            "[cc]" => "Country Code",
            "[operatingsystem]" => "OS",
            "[device]" => "Device Type",
            "[connection]" => "Connection Type",
            "[carrier]" => "Carrier",
        ], "[clickid]", "[bid]"));

        self::push($models, self::createTemplate(self::PROPELLERADS, [
            "{zoneid}" => "Zone",
            "{campaignid}" => "Campaign",
            "{bannerID}" => "Banner",
        ], "\${SUBID}", null));

        self::push($models, self::createTemplate(self::REVCONTENT, [
            "{widget_id}" => "Widget",
            "{adv_targets}" => "Target",
            "{boost_id}" => "Campaign",
            "{content_id}" => "Content",
        ], null, null));

        self::push($models, self::createTemplate(self::RICH_PUSH, [
            "[ADVERTISER_ID]" => "Advertiser ID",
            "[SSP_ID]" => "Segment ID",
            "[CAMPAIGN_ID]" => "Campaign ID",
            "[CREATIVE_ID]" => "Creative ID",
            "[SOURCE_ID]" => "Source ID",
            "[COUNTRY_CODE]" => "Country Code",
            "[OS]" => "OS",
        ], "[CLICK_ID]", null));

        self::push($models, self::createTemplate(self::PUSH_GROUND, [
            "{source}" => "Source",
            "{supply_id}" => "Supply ID",
            "{creativity_id}" => "Creative ID",
            "{campaign_id}" => "Campaign ID",
            "{userAge}" => "User Age",
            "{country}" => "Country",
            "{deviceName}" => "Device",
        ], "{click_id}", "{bid}"));

        self::push($models, self::createTemplate(self::SELF_ADVERTISER, [
            "@@SOURCE@@" => "Source",
            "@@CAMPAIGN-KEYWORD@@" => "Campaign Keyword",
        ], "@@CLICK-ID@@", null));

        self::push($models, self::createTemplate(self::BING, [
            "{keyword}" => "Keyword",
            "{QueryString}" => "Query",
            "{AdGroup}" => "Ad Group",
            "{AdId}" => "Ad ID",
            "{BidMatchType}" => "Bid Match Type",
            "{MatchType}" => "Match Type",
            "{Network}" => "Network",
            "{TargetId}" => "Target ID",
            "{lpurl}" => "LP URL",
        ], null, null));

        self::push($models, self::createTemplate(self::GOOGLE_SEARCH_AND_DISPLAY, [
            "{keyword}" => "Keyword",
            "{target}" => "Target",
            "{placement}" => "Placement",
            "{network}" => "Network",
            "{adgroupid}" => "Ad Group ID",
            "{creative}" => "Creative",
            "{adposition}" => "Ad Position",
            "{matchtype}" => "Match Type",
            "{lpurl}" => "LP URL",
        ], null, null));

        self::push($models, self::createTemplate(self::GOOGLE_YOUTUBE, [
            "{target}" => "Target",
            "{placement}" => "Placement",
            "{network}" => "Network",
            "{adgroupid}" => "Ad Group ID",
            "{creative}" => "Creative",
            "{matchtype}" => "Match Type",
            "{lpurl}" => "LP URL",
        ], null, null));

        self::push($models, self::createTemplate(self::FB, [
            "{{campaign.name}}" => "Campaign",
            "{{adset.name}}" => "Adset",
            "{{ad.name}}" => "Ad",
            "{{placement}}" => "Placement",
            "{{site_source_name}}" => "Site Source",
        ], null, null));

        self::push($models, self::createTemplate(self::MEGAPUSH, [
            "{feedid}" => "Feed ID",
            "{camp_id}" => "Camp ID"
        ], "{clickid}", "{bid}"));

        self::push($models, self::createTemplate(self::ZEROPARK, [
            "{source}" => "Source",
            "{target}" => "Target",
            "{keyword}" => "Keyword",
            "{match}" => "Match",
            "{traffic_type}" => "Traffic Type",
            "{visitor_type}" => "Visitor Type",
            "{campaign_id}" => "Campaign ID",
            "{ad_copy_name}" => "Ad Copy Name",
        ], "{cid}", "{visit_cost}"));

        self::push($models, self::createTemplate(self::WIDGET_MEDIA, [
            "{advertiserId}" => "Advertiser ID",
            "{pid}" => "Placement ID",
            "{adId}" => "Ad ID",
            "{campaignGroupId}" => "Campaign Group ID",
            "{campaignId}" => "Campaign ID",
            "{width}" => "Width",
            "{height}" => "Height",
            "{country}" => "Country",
            "{os}" => "OS",
        ], "{transactionId}", null));

        self::push($models, self::createTemplate(self::TRAFFIC_STARS, [
            "{keywords}" => "Keywords",
            "{site_id}" => "Site ID",
            "{site_host}" => "Site Host",
            "{adspot_id}" => "Adspot ID",
            "{campaign_id}" => "Campaign ID",
            "{category_id}" => "Category ID",
            "{creative_id}" => "Creative ID",
            "{referrer}" => "Referrer",
            "{carrier}" => "Carrier",
        ], "{click_id}", "{cost}"));

        self::push($models, self::createTemplate(self::TABOOLA, [
            "{site}" => "Publisher",
            "{title}" => "Ad",
            "{thumbnail}" => "Creative",
            "{campaign}" => "Campaign",
        ], null, null));

        uasort($models, function ($a, $b) {
            return strcasecmp($a['sourceName'], $b['sourceName']);
        });
        return $models;
    }

    static private function push(&$models, $source)
    {
        $models[$source['sourceId']] = $source;
    }

    static private function createTemplate($name, $vars, $externalID, $cost)
    {
        $varValues = array_keys($vars);
        $varNames = array_values($vars);
        $id = preg_replace("/[^a-z0-9]/", '', strtolower($name));
        $tpl = new SourceTemplate($id, $name, $varValues, $varNames);
        return $tpl->toArray();
    }
}
