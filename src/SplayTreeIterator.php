<?php

declare(strict_types=1);

namespace SplayTree;

use Iterator;

/**
 * @implements Iterator<int, ?Node>
 */
final class SplayTreeIterator implements Iterator
{
    private ?Node $root;

    /**
     * @var array<Node|null>
     */
    private array $stack = [];

    private int $position = 0;

    public function __construct(?Node $root)
    {
        $this->root = $root;
        $this->rewind();
    }

    public function rewind(): void
    {
        $this->stack = [];
        $this->position = 0;
        $node = $this->root;
        while ($node !== null) {
            $this->stack[] = $node;
            $node = $node->left;
        }
    }

    public function current(): ?Node
    {
        if (empty($this->stack)) {
            return null;
        }

        return $this->stack[count($this->stack) - 1];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        if (! empty($this->stack)) {
            $node = array_pop($this->stack);
            $this->position++;
            $node = $node ? $node->right : null;
            while ($node !== null) {
                $this->stack[] = $node;
                $node = $node->left;
            }
        }
    }

    public function valid(): bool
    {
        return ! empty($this->stack);
    }
}
