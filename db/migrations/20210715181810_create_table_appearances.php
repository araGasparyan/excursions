<?php
/**
 * Create table appearances
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

use Phinx\Migration\AbstractMigration;

class CreateTableAppearances extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('appearances', ['id' => 'appearance_id']);
        $table->addColumn('secure_id', 'string', ['comment' => 'id for communicating with external world', 'limit' => 100])
            ->addColumn('mode', 'integer', ['comment' => 'defines appearances mode, i. e. present or absent appearance. the default value should be absent.', 'limit' => 255])
            ->addColumn('reason', 'text', ['comment' => 'a reason for the appearance.', 'limit' => 400, 'null' => true])
            ->addColumn('appearance_start_datetime', 'datetime', ['comment' => ''])
            ->addColumn('appearance_end_datetime', 'datetime', ['comment' => ''])
            ->addColumn('type', 'integer', ['comment' => 'this is used for grouping appearance. possible examples - vacation, pregnancy, disease, etc.', 'limit' => 255])
            ->addColumn('rank', 'integer', ['comment' => 'this will give us a simple comparison metric between appearances.', 'limit' => 255, 'null' => true])
            ->addColumn('status', 'integer', ['comment' => 'some possible values are active and deactivated. may store more statuses.', 'limit' => 255])
            ->addColumn('appearance_created_date', 'datetime')
            ->addColumn('appearance_updated_date', 'datetime')
            ->addIndex('secure_id', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
