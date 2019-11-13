<?php

namespace ClickerVolt;

class LanguageCodes
{

    const MAP = [
        "AB" => "Abkhazian (AB)",
        "AZ" => "Azerbaijani (AZ)",
        "SQ" => "Albanian (SQ)",
        "AR" => "Arabic (AR)",
        "HY" => "Armenian (HY)",
        "AF" => "Afrikaans (AF)",
        "BA" => "Bashkir (BA)",
        "BE" => "Belarusian (BE)",
        "BN" => "Bengali (BN)",
        "BG" => "Bulgarian (BG)",
        "BS" => "Bosnian (BS)",
        "ZH" => "Chinese (ZH)",
        "HR" => "Croatian (HR)",
        "CS" => "Czech (CS)",
        "DA" => "Danish (DA)",
        "NL" => "Dutch (NL)",
        "EN" => "English (EN)",
        "FJ" => "Fiji (FJ)",
        "FI" => "Finnish (FI)",
        "FR" => "French (FR)",
        "FY" => "Frisian (FY)",
        "ET" => "Estonian (ET)",
        "KA" => "Georgian (KA)",
        "DE" => "German (DE)",
        "EL" => "Greek (EL)",
        "HE" => "Hebrew (HE)",
        "HI" => "Hindi (HI)",
        "HU" => "Hungarian (HU)",
        "IS" => "Icelandic (IS)",
        "ID" => "Indonesian (ID)",
        "GA" => "Irish (GA)",
        "IT" => "Italian (IT)",
        "JA" => "Japanese (JA)",
        "JW" => "Javanese (JW)",
        "KK" => "Kazakh (KK)",
        "KO" => "Korean (KO)",
        "KY" => "Kyrgyz (KY)",
        "KU" => "Kurdish (KU)",
        "LV" => "Latvian (LV)",
        "LT" => "Lithuanian (LT)",
        "MY" => "Malaysian (MY)",
        "MK" => "Macedonian (MK)",
        "MS" => "Malay (MS)",
        "MO" => "Moldovan (MO)",
        "MN" => "Mongolian (MN)",
        "NE" => "Nepali (NE)",
        "NO" => "Norwegian (NO)",
        "UR" => "Pakistani (UR)",
        "FA" => "Persian (FA)",
        "PL" => "Polish (PL)",
        "RO" => "Romanian (RO)",
        "RU" => "Russian (RU)",
        "SR" => "Serbian (SR)",
        "SK" => "Slovak (SK)",
        "SL" => "Slovenian (SL)",
        "SO" => "Somalia (SO)",
        "ES" => "Spanish (ES)",
        "SU" => "Sundanese (SU)",
        "SW" => "Swahili (SW)",
        "SV" => "Swedish (SV)",
        "TL" => "Tagalog (TL)",
        "TG" => "Tajik (TG)",
        "TH" => "Thai (TH)",
        "TT" => "Tatar (TT)",
        "TR" => "Turkish (TR)",
        "TK" => "Turkmen (TK)",
        "UK" => "Ukrainian (UK)",
        "UZ" => "Uzbek (UZ)",
        "VI" => "Vietnamese (VI)",
    ];

    /**
     * 
     */
    static function getBrowserLanguage()
    {
        $language = empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? '' : $_SERVER["HTTP_ACCEPT_LANGUAGE"];

        $parts = explode(',', $language);
        if (count($parts)) {
            $parts = explode(';', $parts[0]);
            $language = trim($parts[0]);
        }

        return $language;
    }
}
