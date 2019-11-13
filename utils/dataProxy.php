<?php

namespace ClickerVolt;

require_once __DIR__ . '/fileTools.php';
require_once __DIR__ . '/arraySerializer.php';

interface Proxyable extends ArraySerializer
{ }

class DataProxy
{
    /**
     * 
     */
    static function expireLink($slug, $linkId = null)
    {
        $cacheFile = self::getLinkFilePath($slug);
        self::expireFile($cacheFile);

        $fakePath = self::getFakeSlugsPath($slug);
        self::expireFile($fakePath);

        if ($linkId) {
            $tableLinks = new TableLinks();
            $link = $tableLinks->loadById($linkId);
            if ($link) {
                // Expire voltified data if any...
                self::expireVoltifiedLink($linkId);

                // Expire aliases too...
                $settings = $link->getSettings();
                if (!empty($settings[Link::SETTINGS_SLUG_ALIASES])) {
                    foreach ($settings[Link::SETTINGS_SLUG_ALIASES] as $alias) {
                        $aliasPath = self::getAliasFilePath($alias);
                        self::expireFile($aliasPath);

                        $fakePath = self::getFakeSlugsPath($alias);
                        self::expireFile($fakePath);
                    }
                }
            }
        }
    }

    /**
     * 
     * @param string $slug
     * @return \ClickerVolt\Link
     */
    static function getLink($slug, $tryAliasIfSlugNotFound = true)
    {
        $cacheFile = self::getLinkFilePath($slug);
        if (!file_exists($cacheFile)) {

            if ($tryAliasIfSlugNotFound) {
                $link = self::getLinkByAlias($slug);
            }

            if (empty($link)) {
                $fakePath = self::getFakeSlugsPath($slug);
                if (!file_exists($fakePath)) {
                    require_once __DIR__ . '/../db/tableLinks.php';

                    $tableLinks = new TableLinks();
                    $link = $tableLinks->loadBySlug($slug);
                    if ($link) {
                        FileTools::atomicSave($cacheFile, json_encode($link->toArray()));

                        self::saveLinkId($link);

                        $settings = $link->getSettings();
                        if (!empty($settings[Link::SETTINGS_SLUG_ALIASES])) {
                            foreach ($settings[Link::SETTINGS_SLUG_ALIASES] as $alias) {
                                $aliasPath = self::getAliasFilePath($alias);
                                FileTools::atomicSave($aliasPath, $slug);
                            }
                        }
                    } else {
                        FileTools::atomicSave($fakePath, $slug);
                    }
                }
            }
        } else {

            $fileContent = FileTools::atomicLoad($cacheFile);
            if ($fileContent) {
                $array = json_decode($fileContent, true);
                if ($array) {
                    $link = new Link($array);
                }
            }
        }

        return empty($link) ? null : $link;
    }

    /**
     * @param Link $link
     */
    static function saveLinkId($link)
    {
        FileTools::atomicSave(self::getLinkIdFilePath($link->getId()), $link->getSlug());
    }

    /**
     * 
     */
    static function getLinkById($id)
    {
        $link = null;
        $path = self::getLinkIdFilePath($id);
        if (file_exists($path)) {
            $slug = FileTools::atomicLoad($path);
            if ($slug) {
                $link = self::getLink($slug, false);
            }
        }

        return $link;
    }

    /**
     * 
     */
    static function getLinkByAlias($alias)
    {
        $link = null;
        $path = self::getAliasFilePath($alias);
        if (file_exists($path)) {
            $slug = FileTools::atomicLoad($path);
            if ($slug) {
                $link = self::getLink($slug, false);
            }
        }

        return $link;
    }

    static function getPublicCVSettings()
    {
        $settings = [];
        $path = self::getPublicCVSettingsFilePath();
        if (file_exists($path)) {
            $content = FileTools::atomicLoad($path);
            if ($content) {
                $decoded = json_decode($content, true);
                if ($decoded) {
                    $settings = $decoded;
                }
            }
        } else {
            require_once __DIR__ . '/../db/objects/cvSettings.php';

            $settings = [
                CVSettings::IP_DETECTION_TYPE => CVSettings::get(CVSettings::IP_DETECTION_TYPE)
            ];

            FileTools::atomicSave($path, json_encode($settings));
        }

        return $settings;
    }

    static function expirePublicCVSettings()
    {
        self::expireFile(self::getPublicCVSettingsFilePath());
    }

    static function getPublicCVSettingsFilePath()
    {
        return FileTools::getDataFolderPath('misc') . "/settings";
    }

    static function getVoltifiedPage($linkId, $url)
    {
        $path = self::getVoltifiedPageFilePath($linkId, $url);
        if (file_exists($path)) {
            return FileTools::atomicLoad($path);
        }
        return null;
    }

    static function setVoltifiedPage($linkId, $url, $page)
    {
        $path = self::getVoltifiedPageFilePath($linkId, $url);
        FileTools::atomicSave($path, $page);
    }

    static function expireVoltifiedLink($linkId)
    {
        $path = self::getVoltifiedLinkPath($linkId);
        array_map('unlink', array_filter((array) glob("{$path}/*")));
    }

    static function getVoltifiedLinkPath($linkId)
    {
        return FileTools::getDataFolderPath("voltified/{$linkId}");
    }

    static function getVoltifiedPageFilePath($linkId, $url)
    {
        $parts = parse_url($url);
        $host = $parts['host'];
        $path = !empty($parts['path']) ? $parts['path'] : '';
        $query = !empty($parts['query']) ? $parts['query'] : '';
        $fileKey = md5($host . $path . $query);
        return self::getVoltifiedLinkPath($linkId) . "/{$fileKey}";
    }

    static function expireFile($path)
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    static function getLinkFilePath($slug)
    {
        return FileTools::getDataFolderPath('links') . "/{$slug}";
    }

    static function getAliasFilePath($alias)
    {
        return FileTools::getDataFolderPath('links/aliases') . "/{$alias}";
    }

    static function getLinkIdFilePath($id)
    {
        return FileTools::getDataFolderPath('links/ids') . "/{$id}";
    }

    static function getFakeSlugsPath($slug)
    {
        return FileTools::getDataFolderPath('links/fake') . "/{$slug}";
    }
}
