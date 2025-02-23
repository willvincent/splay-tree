<?php

namespace SplayTree;

use Iterator;
use ReturnTypeWillChange;
use SplayTree\Node;

class SplayTreeIterator implements Iterator {
    private $root;
    private $stack = [];
    private $position = 0;

    public function __construct(?Node $root) {
        $this->root = $root;
        $this->rewind();
    }

    public function rewind(): void {
        $this->stack = [];
        $this->position = 0;
        $node = $this->root;
        while ($node !== null) {
            $this->stack[] = $node;
            $node = $node->left;
        }
    }

    #[ReturnTypeWillChange]
    public function current() {
        if (empty($this->stack)) {
            return null;
        }
        return $this->stack[count($this->stack) - 1]->data;
    }

    public function key(): int {
        return $this->position;
    }

    public function next(): void {
        if (!empty($this->stack)) {
            $node = array_pop($this->stack);
            $this->position++;
            $node = $node->right;
            while ($node !== null) {
                $this->stack[] = $node;
                $node = $node->left;
            }
        }
    }

    public function valid(): bool {
        return !empty($this->stack);
    }
}