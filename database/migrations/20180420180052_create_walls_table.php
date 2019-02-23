<?php

use Phinx\Migration\AbstractMigration;

class CreateWallsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('walls')) {
            $table = $this->table('walls', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('author_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
