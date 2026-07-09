<?php

declare(strict_types=1);

use Tavp\Core\Database\Migrations\Migration;
use Tavp\Core\Database\Migrations\SchemaBuilder;

return new class extends Migration
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->createTable('analytics_fraud_events', function (SchemaBuilder\TableDefinition $table) {
            $table->add($schema->column('id', 'bigInteger', ['identity' => true, 'primary' => true]));
            $table->add($schema->column('session_id', 'string', ['size' => 100, 'null' => true]));
            $table->add($schema->column('user_id', 'bigInteger', ['null' => true]));
            $table->add($schema->column('ip_address', 'string', ['size' => 45]));
            $table->add($schema->column('event_type', 'string', ['size' => 50]));
            $table->add($schema->column('rule_name', 'string', ['size' => 200]));
            $table->add($schema->column('score', 'decimal', ['size' => 5, 'scale' => 3]));
            $table->add($schema->column('details', 'json', ['null' => true]));
            $table->add($schema->column('action_taken', 'string', ['size' => 50]));
            $table->add($schema->column('resolved_at', 'timestamp', ['null' => true]));
            $table->add($schema->column('resolved_by', 'bigInteger', ['null' => true]));
            $table->add($schema->column('created_at', 'timestamp'));
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->dropTable('analytics_fraud_events');
    }
};
