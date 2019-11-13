<?php

namespace ClickerVolt;

require_once __DIR__ . '/fileTools.php';

class SingleProcess
{
    /**
     * 
     * @param string $group
     * @throws \Exception
     */
    public function __construct($group = '')
    {
        $this->group = preg_replace('[^a-zA-Z0-9]', '_', $group);

        $lockFilePath = $this->getLockFilePath();
        if (!file_exists($lockFilePath)) {

            file_put_contents($lockFilePath, '', LOCK_EX);
            chmod($lockFilePath, 0666);
        }
    }

    /**
     * 
     * Execute the specified function ONLY if no other thread/instance runs it now.
     * Otherwise, don't execute it.
     * 
     * @param callable $function
     * @return true if it was executed
     * @throws \Exception
     */
    function executeIfNone($function)
    {
        $executed = false;

        try {
            $lockHandle = fopen($this->getLockFilePath(), "w+");
            if ($lockHandle) {
                flock($lockHandle, LOCK_EX | LOCK_NB, $wouldblock);
                if (!$wouldblock) {
                    call_user_func($function);
                    $executed = true;

                    $this->unlockHandle($lockHandle);
                }
            }
        } catch (\Exception $ex) {
            if (isset($lockHandle)) {
                $this->unlockHandle($lockHandle);
            }

            throw $ex;
        }

        return $executed;
    }

    /**
     * 
     * @param type $handle
     */
    private function unlockHandle($handle)
    {
        if ($handle) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * 
     * @return string
     */
    private function getLockFilePath()
    {
        $path = FileTools::getAdminTmpFolderPath();
        return $path . "/singleprocess-{$this->group}.lck";
    }
}
