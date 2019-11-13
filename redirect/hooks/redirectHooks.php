<?php

namespace ClickerVolt;

require_once __DIR__ . '/../../db/tableLinks.php';

class RedirectHooks
{

    /**
     * 
     */
    static function executeHTML($html, $duration, $nextURL = '')
    {

        $script = <<<SCRIPT
{$html}
<script>
        if( '{$nextURL}' ) {
            setTimeout( function() {
                location.href = '{$nextURL}';
            }, {$duration} );
        }
</script>
SCRIPT;

        echo $script;
    }

    /**
     * 
     */
    static function executePHP($code)
    {
        $code = str_replace(['<?php', '?>', '<?='], '', $code);
        eval($code);
    }
}
