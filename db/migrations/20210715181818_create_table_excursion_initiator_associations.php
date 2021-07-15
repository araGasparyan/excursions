<?php
/**
 * Create table excursion_initiator_associations
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

use Phinx\Migration\AbstractMigration;

class CreateTableExcursionInitiatorAssociations extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('excursion_initiator_associations', ['id' => 'excursion_initiator_association_id']);
        $table->addColumn('excursion_id', 'integer')
            ->addColumn('initiator_id', 'integer')
            ->addColumn('excursion_initiator_associations_created_date', 'datetime')
            ->addColumn('excursion_initiator_associations_updated_date', 'datetime')
            ->addIndex(['excursion_id', 'initiator_id'], ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
