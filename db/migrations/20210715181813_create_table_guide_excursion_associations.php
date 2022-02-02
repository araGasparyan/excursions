<?php
/**
 * Create table guide_excursion_associations
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

use Phinx\Migration\AbstractMigration;

class CreateTableGuideExcursionAssociations extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('guide_excursion_associations', ['id' => 'guide_excursion_association_id']);
        $table->addColumn('guide_id', 'integer')
            ->addColumn('excursion_id', 'integer')
            ->addColumn('guide_excursion_associations_created_date', 'datetime')
            ->addColumn('guide_excursion_associations_updated_date', 'datetime')
            ->addIndex(['guide_id', 'excursion_id'], ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
