<?php

namespace ClickerVolt;

require_once __DIR__ . '/ajax.php';
require_once __DIR__ . '/../../db/tableSourceTemplates.php';
require_once __DIR__ . '/../../db/objects/sourceTemplateModels.php';

class AjaxSources extends Ajax
{
    /**
     * throws \Exception
     */
    static function saveSource($form)
    {
        $sourceId = empty($form['sourceId']) ? null : Sanitizer::sanitizeKey($form['sourceId']);
        $sourceName = Sanitizer::sanitizeTextField($form['sourcename']);
        $varValues = Sanitizer::sanitizeTextField($form['varvalues']);
        $varNames = Sanitizer::sanitizeTextField($form['varnames']);

        $sourceTemplate = new SourceTemplate($sourceId, $sourceName, $varValues, $varNames);
        $table = new TableSourceTemplates();
        $table->insert([$sourceTemplate]);

        return $sourceTemplate->toArray();
    }

    /**
     * 
     */
    static function getAllSources()
    {
        $table = new TableSourceTemplates();
        $sources = $table->loadAll();

        foreach ($sources as $k => $source) {
            unset($sources[$k]);
            $sources[$source->getSourceId()] = $source->toArray();
        }

        return [
            'sources' => $sources,
            'models' => SourceTemplateModels::getModels(),
        ];
    }

    /**
     * 
     */
    static function deleteSource()
    {
        $sourceId = Sanitizer::sanitizeKey($_POST['sourceId']);
        $table = new TableSourceTemplates();
        $table->delete($sourceId);
    }
};
