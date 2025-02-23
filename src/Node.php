<?php

namespace SplayTree;

class Node {
    public $data;
    public $left;
    public $right;
    public $parent;

    public function __construct($data) {
        $this->data = $data;
        $this->left = null;
        $this->right = null;
        $this->parent = null;
    }
}