<?php
/**
 * Create table guide_language_associations
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

use Phinx\Migration\AbstractMigration;

class CreateTableGuideLanguageAssociations extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('guide_language_associations', ['id' => 'guide_language_association_id']);
        $table->addColumn('guide_id', 'integer')
            ->addColumn('language_id', 'integer')
            ->addColumn('guide_language_associations_created_date', 'datetime')
            ->addColumn('guide_language_associations_updated_date', 'datetime')
            ->addIndex(['guide_id', 'language_id'], ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
