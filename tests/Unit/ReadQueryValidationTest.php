<?php

namespace Tests\Unit;

use Tests\TestCase;

class ReadQueryValidationTest extends TestCase
{
    /**
     * Ensure valid read-only queries are accepted.
     */
    public function test_it_allows_single_read_queries(): void
    {
        $this->assertTrue(isValidReadQuery('SELECT * FROM songs'));
        $this->assertTrue(isValidReadQuery('SHOW TABLES'));
        $this->assertTrue(isValidReadQuery('DESCRIBE songs'));
        $this->assertTrue(isValidReadQuery('EXPLAIN SELECT * FROM songs'));
    }

    /**
     * Ensure unsafe queries are rejected before execution.
     */
    public function test_it_blocks_unsafe_queries(): void
    {
        $this->assertFalse(isValidReadQuery('UPDATE songs SET title = "x"'));
        $this->assertFalse(isValidReadQuery('INSERT INTO songs (title) VALUES ("x")'));
        $this->assertFalse(isValidReadQuery('DELETE FROM songs'));
        $this->assertFalse(isValidReadQuery('DROP TABLE songs'));
        $this->assertFalse(isValidReadQuery('TRUNCATE TABLE songs'));
        $this->assertFalse(isValidReadQuery('SELECT * FROM songs; DELETE FROM songs'));
        $this->assertFalse(isValidReadQuery('SELECT * FROM songs -- comment'));
        $this->assertFalse(isValidReadQuery(''));
    }
}
