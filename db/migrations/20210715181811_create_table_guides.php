<?php
/**
 * Create table guides
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

use Phinx\Migration\AbstractMigration;

class CreateTableGuides extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('guides', ['id' => 'guide_id']);
        $table->addColumn('secure_id', 'string', ['comment' => 'id for communicating with external world', 'limit' => 100])
            ->addColumn('first_name', 'string', ['comment' => '', 'limit' => 255])
            ->addColumn('middle_name', 'string', ['comment' => '', 'limit' => 255, 'null' => true])
            ->addColumn('last_name', 'string', ['comment' => '', 'limit' => 255])
            ->addColumn('birth_date', 'date', ['comment' => '', 'null' => true])
            ->addColumn('email', 'string', ['comment' => '', 'limit' => 255])
            ->addColumn('address', 'string', ['comment' => '', 'limit' => 255, 'null' => true])
            ->addColumn('affiliation', 'string', ['comment' => '', 'limit' => 255, 'null' => true])
            ->addColumn('job_title', 'string', ['comment' => '', 'limit' => 255, 'null' => true])
            ->addColumn('country', 'string', ['comment' => 'we are going to store country codes here, i. e. am, us, etc.', 'limit' => 255, 'null' => true])
            ->addColumn('education', 'string', ['comment' => 'we are going to store education short info here, i. e. master, ph.d., etc.', 'limit' => 255, 'null' => true])
            ->addColumn('phone', 'string', ['comment' => '', 'limit' => 255, 'null' => true])
            ->addColumn('image_path', 'string', ['comment' => '', 'limit' => 255, 'null' => true])
            ->addColumn('additional_info', 'text', ['comment' => 'info about the current work progress of the guide can be stored in a json format.', 'limit' => 400, 'null' => true])
            ->addColumn('position', 'integer', ['comment' => 'the field is for druyq, 1/4 is 1, 1/2 is 2, 1 is 4, etc.', 'limit' => 255])
            ->addColumn('description', 'text', ['comment' => 'a short description of the guide.', 'limit' => 400, 'null' => true])
            ->addColumn('type', 'integer', ['comment' => 'type of the guide', 'limit' => 255])
            ->addColumn('rank', 'integer', ['comment' => 'this will give us a simple comparison metric between the guides.', 'limit' => 255, 'null' => true])
            ->addColumn('status', 'integer', ['comment' => 'some possible values are active, deactivated, pending. may store more statuses.', 'limit' => 255])
            ->addColumn('guide_created_date', 'datetime')
            ->addColumn('guide_updated_date', 'datetime')
            ->addIndex('secure_id', ['unique' => true])
            ->addIndex('email', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
