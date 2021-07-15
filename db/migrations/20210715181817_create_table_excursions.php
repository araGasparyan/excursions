<?php
/**
 * Create table excursions
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

use Phinx\Migration\AbstractMigration;

class CreateTableExcursions extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('excursions', ['id' => 'excursion_id']);
        $table->addColumn('secure_id', 'string', ['comment' => 'id for communicating with external world', 'limit' => 100])
            ->addColumn('group_members_count', 'integer', ['comment' => 'the actual number of the group members', 'limit' => 65535, 'null' => true])
            ->addColumn('expected_excursion_start_date', 'date', ['comment' => 'when the excursion is registered initially, the excursion date should be fixed.'])
            ->addColumn('expected_excursion_start_time', 'time', ['comment' => 'when the excursion is registered initially, the excursion time can be fixed.', 'null' => true])
            ->addColumn('verify_start_time_in_hours', 'integer', ['comment' => 'in such cases when the excursion time is not registered initially, the person who registers the excursion may promise to fix the time of the excursion before n hours they will come.', 'limit' => 255, 'null' => true])
            ->addColumn('expected_duration_of_excursion', 'integer', ['comment' => 'in this field, we are storing the expected (from the group) duration of the excursion in minutes.', 'limit' => 65535, 'null' => true])
            ->addColumn('excursion_start_date', 'date', ['comment' => 'the date when the excursion is started actually.', 'null' => true])
            ->addColumn('excursion_start_time', 'time', ['comment' => 'the time when the excursion is started actually.', 'null' => true])
            ->addColumn('excursion_end_time', 'time', ['comment' => 'the time when the excursion is finished actually.', 'null' => true])
            ->addColumn('country', 'string', ['comment' => 'we are going to store country codes here, i. e. am, us, etc. if the excursion is strongly associated with one country this field can be filled.', 'limit' => 255, 'null' => true])
            ->addColumn('description', 'text', ['comment' => 'a short description of the excursion.', 'limit' => 400, 'null' => true])
            ->addColumn('expected_group_members_count', 'integer', ['comment' => 'the expected number of the group members', 'limit' => 65535])
            ->addColumn('radio_guide', 'integer', ['comment' => 'some excursions require radio guides. this field is for that information - 1 is no need for radio guides and 2 is there is a need for them. this field is like a boolean field.', 'limit' => 255, 'null' => true])
            ->addColumn('is_free', 'integer', ['comment' => 'some excursions are free according to the directors decision. this field is like a boolean field. the default value is not free. 1 - not free, 2 - free.', 'limit' => 255, 'null' => true])
            ->addColumn('additional_info', 'text', ['comment' => 'we can write here additional info, like the reason why the coordinator changes the guide of the excursion.', 'limit' => 500, 'null' => true])
            ->addColumn('type', 'integer', ['comment' => 'this is used for grouping excursions.', 'limit' => 255])
            ->addColumn('rank', 'integer', ['comment' => 'this will give us a simple comparison metric between excursions.', 'limit' => 255, 'null' => true])
            ->addColumn('status', 'integer', ['comment' => 'some possible values are registered, arrived, in progress, active and deactivated. may store more statuses.', 'limit' => 255])
            ->addColumn('excursion_created_date', 'datetime')
            ->addColumn('excursion_updated_date', 'datetime')
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
