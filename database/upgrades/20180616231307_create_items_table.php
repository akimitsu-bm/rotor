<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateItemsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
            $table = $this->table('items', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('board_id', 'integer')
                ->addColumn('title', 'string', ['limit' => 100])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('user_id', 'integer')
                ->addColumn('price', 'integer', ['default' => 0])
                ->addColumn('created_at', 'integer')
                ->addColumn('updated_at', 'integer')
                ->addColumn('expires_at', 'integer')
                ->addIndex('board_id')
                ->addIndex('expires_at')
                ->addIndex('created_at')
                ->create();
    }
}