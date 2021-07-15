<?php
/**
 * Create table language_excursion_associations
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

use Phinx\Migration\AbstractMigration;

class CreateTableLanguageExcursionAssociations extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('language_excursion_associations', ['id' => 'language_excursion_association_id']);
        $table->addColumn('language_id', 'integer')
            ->addColumn('excursion_id', 'integer')
            ->addColumn('language_excursion_associations_created_date', 'datetime')
            ->addColumn('language_excursion_associations_updated_date', 'datetime')
            ->addIndex(['language_id', 'excursion_id'], ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
