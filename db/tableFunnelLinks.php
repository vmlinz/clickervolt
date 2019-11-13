<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';
require_once __DIR__ . '/../utils/arraySerializer.php';

class FunnelLink implements Proxyable
{
    use ArraySerializerImpl;

    private $parentLinkId;
    private $funnelLinkId;

    function __construct($parentLinkId, $funnelLinkId)
    {
        $this->parentLinkId = $parentLinkId;
        $this->funnelLinkId = $funnelLinkId;
    }

    function getParentLinkId()
    {
        return $this->parentLinkId;
    }

    function getFunnelLinkId()
    {
        return $this->funnelLinkId;
    }
}

class TableFunnelLinks extends Table
{

    /**
     * 
     */
    public function getName()
    {

        return $this->wpTableName('clickervolt_funnel_links');
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
                        `parentLinkId` INT UNSIGNED NOT NULL,
                        `funnelLinkId` INT UNSIGNED NOT NULL,
                        PRIMARY KEY (`parentLinkId`, `funnelLinkId`),
                        KEY `funnelLinkId_idx` (`funnelLinkId`)
                    ) ENGINE=InnoDB";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }

    /**
     * @return array
     */
    public function loadByParentLinkId($id)
    {

        global $wpdb;

        $funnelLinks = [];

        $tableName = $this->getName();
        $rows = $wpdb->get_results(
            $wpdb->prepare("select * from {$tableName} where parentLinkId = %d", $id),
            ARRAY_A
        );

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $funnelLinks[] = $this->rowToFunnelLink($row);
            }
        }

        return $funnelLinks;
    }

    /**
     * 
     * @return ClickerVolt\FunnelLink
     */
    private function rowToFunnelLink($row)
    {

        return new FunnelLink($row['parentLinkId'], $row['funnelLinkId']);
    }

    /**
     * 
     */
    public function deleteByLinkId($id)
    {

        DB::singleton()->transactionStart();
        try {

            $this->deleteByParentLinkId($id);
            $this->deleteByFunnelLinkId($id);
            DB::singleton()->transactionCommit();
        } catch (\Exception $ex) {
            DB::singleton()->transactionRollback();
            throw $ex;
        }
    }

    /**
     * 
     */
    public function deleteByParentLinkId($id)
    {

        global $wpdb;
        $wpdb->delete($this->getName(), ['parentLinkId' => $id]);
    }

    /**
     * 
     */
    public function deleteByFunnelLinkId($id)
    {

        global $wpdb;
        $wpdb->delete($this->getName(), ['funnelLinkId' => $id]);
    }

    /**
     * @param \ClickerVolt\FunnelLink[] $funnelLinks
     * @throws \Exception
     */
    public function insert($funnelLinks)
    {

        $mapper = [
            'parentLinkId' => ['type' => '%d'],
            'funnelLinkId' => ['type' => '%d'],
        ];

        parent::insertBulk($funnelLinks, $mapper, ['insertModifiers' => ['ignore']]);
    }
}
