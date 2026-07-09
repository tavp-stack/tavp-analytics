<?php

declare(strict_types=1);

use Tavp\Core\Database\Migrations\Migration;
use Tavp\Core\Database\Migrations\SchemaBuilder;

return new class extends Migration
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->createTable('analytics_experiments', function (SchemaBuilder\TableDefinition $table) {
            $table->add($schema->column('id', 'bigInteger', ['identity' => true, 'primary' => true]));
            $table->add($schema->column('name', 'string', ['size' => 255]));
            $table->add($schema->column('slug', 'string', ['size' => 255]));
            $table->add($schema->column('description', 'text', ['null' => true]));
            $table->add($schema->column('variants', 'json'));
            $table->add($schema->column('traffic_percentage', 'decimal', ['size' => 5, 'scale' => 2, 'default' => 100]));
            $table->add($schema->column('is_active', 'boolean', ['default' => true]));
            $table->add($schema->column('started_at', 'timestamp', ['null' => true]));
            $table->add($schema->column('ended_at', 'timestamp', ['null' => true]));
            $table->add($schema->column('created_at', 'timestamp'));
        });

        $schema->createTable('analytics_experiment_participations', function (SchemaBuilder\TableDefinition $table) {
            $table->add($schema->column('id', 'bigInteger', ['identity' => true, 'primary' => true]));
            $table->add($schema->column('experiment_id', 'bigInteger'));
            $table->add($schema->column('session_id', 'string', ['size' => 100]));
            $table->add($schema->column('user_id', 'bigInteger', ['null' => true]));
            $table->add($schema->column('variant', 'string', ['size' => 100]));
            $table->add($schema->column('converted', 'boolean', ['default' => false]));
            $table->add($schema->column('converted_at', 'timestamp', ['null' => true]));
            $table->add($schema->column('created_at', 'timestamp'));
        });

        $schema->createTable('analytics_funnels', function (SchemaBuilder\TableDefinition $table) {
            $table->add($schema->column('id', 'bigInteger', ['identity' => true, 'primary' => true]));
            $table->add($schema->column('name', 'string', ['size' => 255]));
            $table->add($schema->column('slug', 'string', ['size' => 255]));
            $table->add($schema->column('steps', 'json'));
            $table->add($schema->column('is_active', 'boolean', ['default' => true]));
            $table->add($schema->column('created_at', 'timestamp'));
        });

        $schema->createTable('analytics_funnel_events', function (SchemaBuilder\TableDefinition $table) {
            $table->add($schema->column('id', 'bigInteger', ['identity' => true, 'primary' => true]));
            $table->add($schema->column('funnel_id', 'bigInteger'));
            $table->add($schema->column('session_id', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('user_id', 'bigInteger', ['null' => true]));
            $table->add($schema->column('step_index', 'integer'));
            $table->add($schema->column('step_name', 'string', ['size' => 255]));
            $table->add($schema->column('metadata', 'json', ['null' => true]));
            $table->add($schema->column('created_at', 'timestamp'));
        });

        $schema->createTable('analytics_session_recordings', function (SchemaBuilder\TableDefinition $table) {
            $table->add($schema->column('id', 'bigInteger', ['identity' => true, 'primary' => true]));
            $table->add($schema->column('session_id', 'string', ['size' => 100]));
            $table->add($schema->column('user_id', 'bigInteger', ['null' => true]));
            $table->add($schema->column('events', 'json'));
            $table->add($schema->column('duration', 'integer', ['default' => 0]));
            $table->add($schema->column('viewport_width', 'integer', ['default' => 1920]));
            $table->add($schema->column('viewport_height', 'integer', ['default' => 1080]));
            $table->add($schema->column('started_at', 'timestamp'));
            $table->add($schema->column('created_at', 'timestamp'));
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->dropTable('analytics_session_recordings');
        $schema->dropTable('analytics_funnel_events');
        $schema->dropTable('analytics_funnels');
        $schema->dropTable('analytics_experiment_participations');
        $schema->dropTable('analytics_experiments');
    }
};
