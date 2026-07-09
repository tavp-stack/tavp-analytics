<?php

declare(strict_types=1);

use Tavp\Core\Database\Migrations\Migration;
use Tavp\Core\Database\Migrations\SchemaBuilder;

return new class extends Migration
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->createTable('analytics_events', function (SchemaBuilder\TableDefinition $table) {
            $table->add($schema->column('id', 'bigInteger', ['identity' => true, 'primary' => true]));
            $table->add($schema->column('event_name', 'string', ['size' => 100]));
            $table->add($schema->column('event_category', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('event_label', 'string', ['size' => 255, 'null' => true]));
            $table->add($schema->column('event_value', 'string', ['size' => 255, 'null' => true]));
            $table->add($schema->column('path', 'string', ['size' => 500, 'null' => true]));
            $table->add($schema->column('ip_address', 'string', ['size' => 45, 'null' => true]));
            $table->add($schema->column('session_id', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('user_id', 'bigInteger', ['null' => true]));
            $table->add($schema->column('platform', 'string', ['size' => 50]));
            $table->add($schema->column('metadata', 'json', ['null' => true]));
            $table->add($schema->column('fraud_score', 'decimal', ['size' => 5, 'scale' => 3, 'default' => 0]));
            $table->add($schema->column('is_suspicious', 'boolean', ['default' => false]));
            $table->add($schema->column('created_at', 'timestamp'));
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->dropTable('analytics_events');
    }
};
