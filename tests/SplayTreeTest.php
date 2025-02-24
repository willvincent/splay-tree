<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use SplayTree\Node;
use SplayTree\SplayTree;

class SplayTreeTest extends TestCase
{
    /**
     * Test basic insertion and search functionality, including splaying.
     */
    public function test_basic_insert_and_search(): void
    {
        $tree = new SplayTree;
        $tree->insert(5);
        $tree->insert(3);
        $tree->insert(7);

        // Verify elements are found
        $this->assertEquals(5, $tree->search(5));
        $this->assertEquals(3, $tree->search(3));
        $this->assertEquals(7, $tree->search(7));
        $this->assertNull($tree->search(4)); // Non-existent element

        // After searching for 3, it should be splayed to the root
        $tree->search(3);
        $this->assertEquals(3, $tree->getRoot());
    }

    /**
     * Test that inserting duplicates does not increase the tree size.
     */
    public function test_insert_duplicates(): void
    {
        $tree = new SplayTree;
        $tree->insert(5);
        $tree->insert(5); // Duplicate

        $this->assertEquals(1, $tree->getSize());
        $this->assertEquals(5, $tree->getRoot());
    }

    /**
     * Test deletion of nodes and tree consistency.
     */
    public function test_delete_operations(): void
    {
        $tree = new SplayTree;
        $tree->insert(5);
        $tree->insert(3);
        $tree->insert(7);

        $tree->delete(3);
        $this->assertNull($tree->search(3));
        $this->assertEquals(5, $tree->search(5));
        $this->assertEquals(7, $tree->search(7));

        $tree->delete(5);
        $this->assertNull($tree->search(5));
        $this->assertEquals(7, $tree->search(7));

        $tree->delete(7);
        $this->assertNull($tree->search(7));
        $this->assertTrue($tree->isEmpty());
    }

    /**
     * Test edge cases like empty tree operations.
     */
    public function test_edge_cases(): void
    {
        $tree = new SplayTree;

        // Insert into empty tree
        $tree->insert(1);
        $this->assertEquals(1, $tree->getRoot());
        $this->assertEquals(1, $tree->getSize());

        // Delete from single-node tree
        $tree->delete(1);
        $this->assertTrue($tree->isEmpty());

        // Search in empty tree
        $this->assertNull($tree->search(1));
    }

    /**
     * Test min and max operations, including splaying.
     */
    public function test_min_and_max(): void
    {
        $tree = new SplayTree;
        $tree->insert(5);
        $tree->insert(3);
        $tree->insert(7);
        $tree->insert(2);
        $tree->insert(4);
        $tree->insert(6);
        $tree->insert(8);

        $this->assertEquals(2, $tree->min());
        $this->assertEquals(2, $tree->getRoot()); // Min should be splayed to root

        $this->assertEquals(8, $tree->max());
        $this->assertEquals(8, $tree->getRoot()); // Max should be splayed to root
    }

    /**
     * Test next and prev operations for finding successors and predecessors.
     */
    public function test_next_and_prev(): void
    {
        $tree = new SplayTree;
        for ($i = 1; $i <= 5; $i++) {
            $tree->insert($i);
        }

        $this->assertEquals(4, $tree->next(3));
        $this->assertEquals(2, $tree->prev(3));
        $this->assertNull($tree->next(5)); // No next for max
        $this->assertNull($tree->prev(1)); // No prev for min
    }

    /**
     * Test size tracking after insertions and deletions.
     */
    public function test_size_tracking(): void
    {
        $tree = new SplayTree;
        $this->assertEquals(0, $tree->getSize());

        $tree->insert(1);
        $this->assertEquals(1, $tree->getSize());

        $tree->insert(2);
        $this->assertEquals(2, $tree->getSize());

        $tree->delete(1);
        $this->assertEquals(1, $tree->getSize());

        $tree->clear();
        $this->assertEquals(0, $tree->getSize());
    }

    /**
     * Test clearing the tree.
     */
    public function test_clear_operation(): void
    {
        $tree = new SplayTree;
        $tree->insert(1);
        $tree->insert(2);
        $tree->clear();

        $this->assertTrue($tree->isEmpty());
        $this->assertNull($tree->getRoot());
        $this->assertEquals(0, $tree->getSize());
    }

    /**
     * Test contains method for checking existence without splaying.
     */
    public function test_contains_method(): void
    {
        $tree = new SplayTree;
        $tree->insert(5);
        $tree->insert(3);
        $tree->insert(7);

        $this->assertTrue($tree->contains(5));
        $this->assertTrue($tree->contains(3));
        $this->assertFalse($tree->contains(4));
    }

    /**
     * Test toString method for string representation.
     */
    public function test_to_string(): void
    {
        $tree = new SplayTree;
        $tree->insert(5);
        $tree->insert(3);
        $tree->insert(7);

        $this->assertFalse($tree->hasCycle(), 'Cycle detected in the tree');

        $str = $tree->toString();

        $this->assertEquals('3, 5, 7', $str); // In-order traversal
    }

    /**
     * Test iterator for in-order traversal.
     */
    public function test_iterator(): void
    {
        $tree = new SplayTree;
        $tree->insert(5);
        $tree->insert(3);
        $tree->insert(7);
        $tree->insert(2);
        $tree->insert(4);
        $tree->insert(6);
        $tree->insert(8);

        $this->assertFalse($tree->hasCycle(), 'Cycle detected in the tree');

        $elements = [];
        foreach ($tree as $node) {
            $elements[] = $node ? $node->data : null;
        }

        $this->assertEquals([2, 3, 4, 5, 6, 7, 8], $elements);
    }

    /**
     * Test that insert() returns the inserted node.
     */
    public function test_insert_returns_node(): void
    {
        $tree = new SplayTree;

        $obj = new TestObject(1);

        $node = $tree->insert($obj);
        $this->assertInstanceOf(Node::class, $node);
        $this->assertInstanceOf(TestObject::class, $node->data);
        $this->assertEquals($node->data, $obj);
    }

    /**
     * Test custom comparator with objects.
     */
    public function test_object_comparator(): void
    {
        $comparator = function ($a, $b) {
            return $a->value <=> $b->value;
        };
        $tree = new SplayTree($comparator);

        $obj1 = new TestObject(5);
        $obj2 = new TestObject(3);
        $obj3 = new TestObject(7);

        $tree->insert($obj1);
        $tree->insert($obj2);
        $tree->insert($obj3);

        // Search should return the exact object inserted
        $this->assertSame($obj1, $tree->search(new TestObject(5)));
        $this->assertSame($obj2, $tree->search(new TestObject(3)));
        $this->assertSame($obj3, $tree->search(new TestObject(7)));
        $this->assertNull($tree->search(new TestObject(4)));
    }
}

/**
 * Helper class for testing with objects.
 */
class TestObject
{
    public mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }
}
