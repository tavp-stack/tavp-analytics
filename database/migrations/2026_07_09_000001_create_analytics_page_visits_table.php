<?php

declare(strict_types=1);

use Tavp\Core\Database\Migrations\Migration;
use Tavp\Core\Database\Migrations\SchemaBuilder;

return new class extends Migration
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->createTable('analytics_page_visits', function (SchemaBuilder\TableDefinition $table) {
            $table->add($schema->column('id', 'bigInteger', ['identity' => true, 'primary' => true]));
            $table->add($schema->column('path', 'string', ['size' => 500]));
            $table->add($schema->column('title', 'string', ['size' => 500, 'null' => true]));
            $table->add($schema->column('ip_address', 'string', ['size' => 45, 'null' => true]));
            $table->add($schema->column('user_agent', 'string', ['size' => 500, 'null' => true]));
            $table->add($schema->column('referrer', 'string', ['size' => 500, 'null' => true]));
            $table->add($schema->column('country', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('city', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('region', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('latitude', 'decimal', ['size' => 10, 'scale' => 7, 'null' => true]));
            $table->add($schema->column('longitude', 'decimal', ['size' => 10, 'scale' => 7, 'null' => true]));
            $table->add($schema->column('timezone', 'string', ['size' => 50, 'null' => true]));
            $table->add($schema->column('isp', 'string', ['size' => 200, 'null' => true]));
            $table->add($schema->column('device', 'string', ['size' => 50]));
            $table->add($schema->column('browser', 'string', ['size' => 50]));
            $table->add($schema->column('os', 'string', ['size' => 50]));
            $table->add($schema->column('platform', 'string', ['size' => 50]));
            $table->add($schema->column('screen_resolution', 'string', ['size' => 20, 'null' => true]));
            $table->add($schema->column('session_id', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('user_id', 'bigInteger', ['null' => true]));
            $table->add($schema->column('duration', 'integer', ['default' => 0]));
            $table->add($schema->column('is_bounce', 'boolean', ['default' => false]));
            $table->add($schema->column('is_bot', 'boolean', ['default' => false]));
            $table->add($schema->column('bot_name', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('is_authenticated', 'boolean', ['default' => false]));
            $table->add($schema->column('metadata', 'json', ['null' => true]));
            $table->add($schema->column('visited_at', 'timestamp'));
            $table->add($schema->column('created_at', 'timestamp'));
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->dropTable('analytics_page_visits');
    }
};
