<?php

namespace ClickerVolt;

require_once __DIR__ . '/../tools/sanitizer.php';

class Ajax
{

    /**
     * 
     */
    static function getAjaxForm()
    {

        check_ajax_referer('clickervolt', 'clickervolt_nonce');

        if (!empty($_POST['form'])) {

            $formData = [];
            parse_str($_POST['form'], $formData);
            return wp_unslash($formData);
        }

        return null;
    }

    /**
     * Check if trying to call an ajax variant of an existing static function.
     * Ajax variants of existing functions have Ajax as a suffix of the function name.
     * For example, if the function doThat() exists, its ajax variant will be doThatAjax().
     * In addition, it will add the ajax form's data to the arguments list (at the end)
     */
    static function __callStatic($name, $arguments)
    {
        $nameWithoutAjax = preg_replace('/Ajax$/', '', $name);
        if ($nameWithoutAjax != $name) {

            // This is an Ajax call...

            $class = get_called_class();
            if (method_exists($class, "{$nameWithoutAjax}")) {

                try {

                    $arguments = [];

                    $formData = self::getAjaxForm();
                    if ($formData) {
                        $arguments[] = $formData;
                    }

                    if (empty($arguments)) {
                        $response = call_user_func("{$class}::{$nameWithoutAjax}");
                    } else {
                        $response = call_user_func_array("{$class}::{$nameWithoutAjax}", $arguments);
                    }
                } catch (\Exception $ex) {

                    $response['error'] = $ex->getMessage();
                }

                if (!empty($response['error'])) {

                    wp_send_json_error($response);
                } else {

                    wp_send_json_success($response);
                }
            }
        }
    }
}
