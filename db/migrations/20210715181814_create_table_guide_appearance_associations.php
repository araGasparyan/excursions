<?php
/**
 * Create table guide_appearance_associations
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

use Phinx\Migration\AbstractMigration;

class CreateTableGuideAppearanceAssociations extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('guide_appearance_associations', ['id' => 'guide_appearance_association_id']);
        $table->addColumn('guide_id', 'integer')
            ->addColumn('appearance_id', 'integer')
            ->addColumn('guide_appearance_associations_created_date', 'datetime')
            ->addColumn('guide_appearance_associations_updated_date', 'datetime')
            ->addIndex(['guide_id', 'appearance_id'], ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
