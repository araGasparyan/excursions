<?php
/**
 * Create table languages
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

use Phinx\Migration\AbstractMigration;

class CreateTableLanguages extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('languages', ['id' => 'language_id']);
        $table->addColumn('secure_id', 'string', ['comment' => 'id for communicating with external world', 'limit' => 100])
            ->addColumn('name', 'string', ['comment' => '', 'limit' => 255])
            ->addColumn('additional', 'text', ['comment' => 'additional information about the language, like language name with its letters or a list of similar languages.', 'limit' => 400, 'null' => true])
            ->addColumn('description', 'text', ['comment' => 'a short description of the language.', 'limit' => 400, 'null' => true])
            ->addColumn('type', 'integer', ['comment' => 'this is used for grouping languages, i. e. spanish and portuguese may have the same type namely iberian romance.', 'limit' => 255])
            ->addColumn('rank', 'integer', ['comment' => 'this will give us a simple comparison metric between the languages.', 'limit' => 255, 'null' => true])
            ->addColumn('status', 'integer', ['comment' => 'some possible values are active and deactivated. may store more statuses.', 'limit' => 255])
            ->addColumn('language_created_date', 'datetime')
            ->addColumn('language_updated_date', 'datetime')
            ->addIndex('secure_id', ['unique' => true])
            ->addIndex('name', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
