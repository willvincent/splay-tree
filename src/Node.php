<?php

declare(strict_types=1);

namespace SplayTree;

final class Node
{
    public mixed $data;

    public mixed $left;

    public mixed $right;

    public mixed $parent;

    public function __construct(mixed $data)
    {
        $this->data = $data;
        $this->left = null;
        $this->right = null;
        $this->parent = null;
    }
}
