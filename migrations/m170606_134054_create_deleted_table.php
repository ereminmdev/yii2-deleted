<?php

use yii\db\Migration;

/**
 * Handles the creation of table `deleted`.
 */
class m170606_134054_create_deleted_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%deleted}}', [
            'id' => $this->primaryKey(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'comment' => $this->string()->notNull()->defaultValue(''),

            'class_name' => $this->string()->notNull(),
            'model_data' => $this->text(),

            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%deleted}}');
    }
}
