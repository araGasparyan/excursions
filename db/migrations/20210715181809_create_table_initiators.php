<?php
/**
 * Create table initiators
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

use Phinx\Migration\AbstractMigration;

class CreateTableInitiators extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('initiators', ['id' => 'initiator_id']);
        $table->addColumn('secure_id', 'string', ['comment' => 'id for communicating with external world', 'limit' => 100])
            ->addColumn('name', 'string', ['comment' => 'the name of the initiator - a person or organization who organizes the excursion. possible values are guests from germany, hyur service, the guests of hovik, etc.', 'limit' => 1000])
            ->addColumn('address', 'string', ['comment' => '', 'limit' => 255, 'null' => true])
            ->addColumn('email', 'string', ['comment' => 'it can have multiple values that should be separated by a comma.', 'limit' => 255, 'null' => true])
            ->addColumn('phone', 'string', ['comment' => 'it can have multiple values that should be separated by a comma.', 'limit' => 255, 'null' => true])
            ->addColumn('website', 'string', ['comment' => '', 'limit' => 255, 'null' => true])
            ->addColumn('additional_info', 'text', ['comment' => 'a short description of the initiator.', 'limit' => 400, 'null' => true])
            ->addColumn('identifier', 'integer', ['comment' => 'this is used for identifying initiators. sometimes the initiators with the same name are different and the ones with different names are the same. it can have a unique index together with the name.', 'limit' => 65535, 'null' => true])
            ->addColumn('type', 'integer', ['comment' => 'this is used for grouping the initiators. examples: tour agency, hotel, person, other, etc.', 'limit' => 255])
            ->addColumn('rank', 'integer', ['comment' => 'this will give us a simple comparison metric between the initiators.', 'limit' => 255, 'null' => true])
            ->addColumn('status', 'integer', ['comment' => 'some possible values for the initiators are active and deactivated. may store more statuses.', 'limit' => 255])
            ->addColumn('initiator_created_date', 'datetime')
            ->addColumn('initiator_updated_date', 'datetime')
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
