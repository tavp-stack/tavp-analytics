<?php

declare(strict_types=1);

use Tavp\Core\Database\Migrations\Migration;
use Tavp\Core\Database\Migrations\SchemaBuilder;

return new class extends Migration
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->createTable('analytics_sessions', function (SchemaBuilder\TableDefinition $table) {
            $table->add($schema->column('id', 'bigInteger', ['identity' => true, 'primary' => true]));
            $table->add($schema->column('session_id', 'string', ['size' => 100]));
            $table->add($schema->column('user_id', 'bigInteger', ['null' => true]));
            $table->add($schema->column('ip_address', 'string', ['size' => 45]));
            $table->add($schema->column('user_agent', 'string', ['size' => 500, 'null' => true]));
            $table->add($schema->column('device', 'string', ['size' => 50]));
            $table->add($schema->column('browser', 'string', ['size' => 50]));
            $table->add($schema->column('os', 'string', ['size' => 50]));
            $table->add($schema->column('platform', 'string', ['size' => 50]));
            $table->add($schema->column('country', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('city', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('referrer', 'string', ['size' => 500, 'null' => true]));
            $table->add($schema->column('landing_page', 'string', ['size' => 500]));
            $table->add($schema->column('exit_page', 'string', ['size' => 500, 'null' => true]));
            $table->add($schema->column('page_views', 'integer', ['default' => 1]));
            $table->add($schema->column('duration', 'integer', ['default' => 0]));
            $table->add($schema->column('is_bounce', 'boolean', ['default' => true]));
            $table->add($schema->column('is_bot', 'boolean', ['default' => false]));
            $table->add($schema->column('started_at', 'timestamp'));
            $table->add($schema->column('last_activity_at', 'timestamp'));
            $table->add($schema->column('created_at', 'timestamp'));
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->dropTable('analytics_sessions');
    }
};
