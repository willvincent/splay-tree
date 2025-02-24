<?php

declare(strict_types=1);

namespace SplayTree;

use Exception;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, ?Node>
 */
final class SplayTree implements IteratorAggregate
{
    private ?Node $root;

    private int $size = 0;

    private mixed $comparator;

    public function __construct(?callable $comparator = null)
    {
        $this->root = null;
        $this->size = 0;
        $this->comparator = $comparator ?? function ($a, $b) {
            return $a <=> $b; // Default comparison for numbers or strings
        };
    }

    private function rotateLeft(Node $node): Node
    {
        $temp = $node->right;
        if ($temp === null) {
            throw new Exception('Cannot rotate left: right child is null');
        }

        // Update the node's right child
        $node->right = $temp->left;
        if ($temp->left !== null) {
            $temp->left->parent = $node;
        }

        // Update parent pointers
        $temp->left = $node;
        $temp->parent = $node->parent;
        $node->parent = $temp;

        // Update the parent's child pointer if it exists
        if ($temp->parent !== null) {
            if ($temp->parent->left === $node) {
                $temp->parent->left = $temp;
            } else {
                $temp->parent->right = $temp;
            }
        }

        return $temp;
    }

    private function rotateRight(Node $node): Node
    {
        $temp = $node->left;
        if ($temp === null) {
            throw new Exception('Cannot rotate right: left child is null');
        }

        // Update the node's left child
        $node->left = $temp->right;
        if ($temp->right !== null) {
            $temp->right->parent = $node;
        }

        // Update parent pointers
        $temp->right = $node;
        $temp->parent = $node->parent;
        $node->parent = $temp;

        // Update the parent's child pointer if it exists
        if ($temp->parent !== null) {
            if ($temp->parent->left === $node) {
                $temp->parent->left = $temp;
            } else {
                $temp->parent->right = $temp;
            }
        }

        return $temp;
    }

    private function splay(Node $node): void
    {
        while ($node->parent !== null) {
            $parent = $node->parent;
            $grandparent = $parent->parent;

            if ($grandparent === null) {
                // Zig step: node is a direct child of the root
                if ($parent->left === $node) {
                    $this->root = $this->rotateRight($parent);
                } else {
                    $this->root = $this->rotateLeft($parent);
                }
            } elseif ($grandparent->left === $parent && $parent->left === $node) {
                // Zig-zig step (left-left)
                $grandparent = $this->rotateRight($grandparent);
                $this->root = $this->rotateRight($parent);
            } elseif ($grandparent->right === $parent && $parent->right === $node) {
                // Zig-zig step (right-right)
                $grandparent = $this->rotateLeft($grandparent);
                $this->root = $this->rotateLeft($parent);
            } elseif ($grandparent->left === $parent && $parent->right === $node) {
                // Zig-zag step (left-right)
                $parent = $this->rotateLeft($parent);
                $this->root = $this->rotateRight($grandparent);
            } else {
                // Zig-zag step (right-left)
                $parent = $this->rotateRight($parent);
                $this->root = $this->rotateLeft($grandparent);
            }
        }
        $this->root = $node;
    }

    public function insert(mixed $data): Node
    {
        $newNode = new Node($data);

        if ($this->root === null) {
            $this->root = $newNode;
            $this->size = 1;

            return $newNode;
        }

        $node = $this->root;
        while (true) {
            $cmp = call_user_func($this->comparator, $data, $node->data);
            if ($cmp < 0) {
                if ($node->left === null) {
                    $node->left = $newNode;
                    $node->left->parent = $node;
                    $this->splay($node->left);
                    $this->size++;
                    break;
                }
                $node = $node->left;
            } elseif ($cmp > 0) {
                if ($node->right === null) {
                    $node->right = $newNode;
                    $node->right->parent = $node;
                    $this->splay($node->right);
                    $this->size++;
                    break;
                }
                $node = $node->right;
            } else {
                // Duplicate data found; splay the existing node
                $this->splay($node);
                break;
            }
        }

        return $newNode;
    }

    public function search(mixed $data): mixed
    {
        if ($this->root === null) {
            return null;
        }

        $node = $this->root;
        $lastNode = null;

        while ($node !== null) {
            $lastNode = $node;
            $cmp = call_user_func($this->comparator, $data, $node->data);
            if ($cmp === 0) {
                $this->splay($node);

                return $this->root ? $this->root->data : null;
            } elseif ($cmp < 0) {
                $node = $node->left;
            } else {
                $node = $node->right;
            }
        }

        if ($lastNode !== null) {
            $this->splay($lastNode);
        }

        return null;
    }

    public function delete(mixed $data): void
    {
        $node = $this->searchNode($data);
        if ($node === null) {
            return; // Node not found, nothing to delete
        }

        $this->splay($node);
        $root = $this->root;

        if ($root && $root->left === null) {
            // Case 1: No left child
            $this->root = $root->right;
            if ($this->root !== null) {
                $this->root->parent = null;
            }
        } elseif ($root && $root->right === null) {
            // Case 2: No right child
            $this->root = $root->left;
            if ($this->root !== null) {
                $this->root->parent = null;
            }
        } else {
            // Case 3: Two children
            $leftSubtree = $root ? $root->left : null;
            $rightSubtree = $root ? $root->right : null;
            $leftSubtree->parent = null;
            $rightSubtree->parent = null;
            $this->root = $this->mergeSubtrees($leftSubtree, $rightSubtree);
        }
        $this->size--;
    }

    private function mergeSubtrees(Node $left, Node $right): Node
    {
        $maxLeft = $left;
        while ($maxLeft->right !== null) {
            $maxLeft = $maxLeft->right;
        }
        $this->splay($maxLeft);
        $maxLeft->right = $right;
        if ($right !== null) {
            $right->parent = $maxLeft;
        }

        return $maxLeft;
    }

    public function min(): mixed
    {
        if ($this->root === null) {
            return null;
        }

        $node = $this->root;
        while ($node->left !== null) {
            $node = $node->left;
        }
        $this->splay($node);

        return $this->root ? $this->root->data : null;
    }

    public function max(): mixed
    {
        if ($this->root === null) {
            return null;
        }

        $node = $this->root;
        while ($node->right !== null) {
            $node = $node->right;
        }
        $this->splay($node);

        return $this->root ? $this->root->data : null;
    }

    public function next(mixed $data): mixed
    {
        $node = $this->searchNode($data);
        if ($node === null) {
            return null;
        }
        $successor = $this->findSuccessor($node);
        if ($successor !== null) {
            $this->splay($successor);

            return $this->root ? $this->root->data : null;
        }

        return null;
    }

    public function prev(mixed $data): mixed
    {
        $node = $this->searchNode($data);
        if ($node === null) {
            return null;
        }
        $predecessor = $this->findPredecessor($node);
        if ($predecessor !== null) {
            $this->splay($predecessor);

            return $this->root ? $this->root->data : null;
        }

        return null;
    }

    private function findSuccessor(Node $node): ?Node
    {
        if ($node->right !== null) {
            $successor = $node->right;
            while ($successor->left !== null) {
                $successor = $successor->left;
            }

            return $successor;
        }

        $ancestor = $node->parent;
        while ($ancestor !== null && $node === $ancestor->right) {
            $node = $ancestor;
            $ancestor = $ancestor->parent;
        }

        return $ancestor;
    }

    private function findPredecessor(Node $node): ?Node
    {
        if ($node->left !== null) {
            $predecessor = $node->left;
            while ($predecessor->right !== null) {
                $predecessor = $predecessor->right;
            }

            return $predecessor;
        }

        $ancestor = $node->parent;
        while ($ancestor !== null && $node === $ancestor->left) {
            $node = $ancestor;
            $ancestor = $ancestor->parent;
        }

        return $ancestor;
    }

    private function searchNode(mixed $data): ?Node
    {
        $node = $this->root;
        while ($node !== null) {
            $cmp = call_user_func($this->comparator, $data, $node->data);
            if ($cmp === 0) {
                return $node;
            } elseif ($cmp < 0) {
                $node = $node->left;
            } else {
                $node = $node->right;
            }
        }

        return null;
    }

    public function contains(mixed $data): bool
    {
        $node = $this->root;
        while ($node !== null) {
            $cmp = call_user_func($this->comparator, $data, $node->data);
            if ($cmp === 0) {
                return true;
            } elseif ($cmp < 0) {
                $node = $node->left;
            } else {
                $node = $node->right;
            }
        }

        return false;
    }

    public function hasCycle(): bool
    {
        if ($this->root === null) {
            return false;
        }
        $visited = []; // Array to track visited nodes
        $stack = [$this->root];
        while (! empty($stack)) {
            $node = array_pop($stack);
            if (in_array($node, $visited, true)) { // Strict comparison
                return true; // Cycle detected
            }
            $visited[] = $node;
            if ($node->left !== null) {
                $stack[] = $node->left;
            }
            if ($node->right !== null) {
                $stack[] = $node->right;
            }
        }

        return false;
    }

    public function clear(): void
    {
        $this->root = null;
        $this->size = 0;
    }

    public function isEmpty(): bool
    {
        return $this->root === null;
    }

    public function getRoot(): mixed
    {
        return $this->root ? $this->root->data : null;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setRoot(Node $node): void
    {
        $this->root = $node;
    }

    public function getIterator(): SplayTreeIterator
    {
        return new SplayTreeIterator($this->root);
    }

    public function toString(int $maxDepth = 100): string
    {
        // Using an array as PHP doesn't have a built-in HashSet
        $visited = [];

        return $this->toStringHelper($this->root, 0, $maxDepth, $visited);
    }

    /**
     * @param  Node[]  $visited
     */
    private function toStringHelper(?Node $node, int $depth, int $maxDepth, array &$visited): string
    {
        // Base case: null node
        if ($node === null) {
            return '';
        }

        // Check for depth limit or cycle
        if ($depth > $maxDepth || in_array($node, $visited, true)) {
            return '[Cycle or Depth Limit Reached]';
        }

        // Add current node to visited array
        $visited[] = $node;

        // Recursively build string: left subtree + current value + right subtree
        $string = [];
        $string[] = $this->toStringHelper($node->left, $depth + 1, $maxDepth, $visited);
        $string[] = (string) $node->data;
        $string[] = $this->toStringHelper($node->right, $depth + 1, $maxDepth, $visited);

        return implode(', ', array_filter($string));
    }
}
