# PHP Splay Tree Implementation

![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)
![Latest Version](https://img.shields.io/packagist/v/willvincent/splaytree.svg)

A robust and efficient [Splay Tree](https://en.wikipedia.org/wiki/Splay_tree) implementation in PHP,
designed for scenarios where frequently accessed elements should be quickly retrievable.
This library provides a self-balancing binary search tree that automatically adjusts to prioritize recently
accessed nodes, making it ideal for use cases like caching, priority queues, or any application where recent
access patterns matter.



## Table of Contents

- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [API Documentation](#api-documentation)
- [Advanced Usage](#advanced-usage)
- [Performance Considerations](#performance-considerations)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Installation

To install the SplayTree library, use Composer:

```bash
composer require willvincent/splay-tree
```

## Basic Usage

### Creating a SplayTree

You can instantiate a `SplayTree` with or without a custom comparator. By default, it uses PHP’s
[spaceship operator](https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.spaceship-op)
(`<=>`) for comparisons.

```php
use SplayTree\SplayTree;

// Default comparator (spaceship operator)
$tree = new SplayTree();

// Custom comparator for integers
$tree = new SplayTree(function ($a, $b) {
    return $a <=> $b;
});
```

### Inserting Elements

Add elements to the tree using the `insert` method:

```php
$tree->insert(5);
$tree->insert(3);
$tree->insert(7);
```

### Searching for Elements

Search for an element with the `search` method. If found, it splays the element to the root and returns it;
otherwise, it returns `null`.

```php
$found = $tree->search(3);
if ($found !== null) {
    echo "Found: " . $found . "\n"; // Found: 3
}
```

### Deleting Elements

Remove an element from the tree with the `delete` method:

```php
$tree->delete(3);
```

## API Documentation

Here’s a detailed breakdown of the `SplayTree` class’s public methods, including descriptions, parameters,
return types, and examples.

### `insert($data): void`

Inserts a new element into the tree and splays it to the root.

#### Parameters:
- `$data`: The data to insert (mixed type).
#### Example:
```php
  $tree->insert(10);
```

---

### `search($data): mixed|null`

Searches for an element. If found, it splays the element to the root and returns it; otherwise, returns `null`.

#### Parameters:
- `$data`: The data to search for (mixed type).

#### Returns:
- The found data or `null`.

#### Example:
```php
  $result = $tree->search(10);
  echo $result !== null ? "Found: $result" : "Not found";
```

---

### `delete($data): void`

Deletes an element from the tree if it exists.

#### Parameters:
- `$data`: The data to delete (mixed type).

#### Example:
```php
  $tree->delete(10);
```

---

### `min(): mixed|null`

Finds the minimum element, splays it to the root, and returns its data.

#### Returns:
- The minimum data or `null` if the tree is empty.

#### Example:
```php
  $min = $tree->min();
  echo $min !== null ? "Min: $min" : "Tree is empty";
```

---

### `max(): mixed|null`

Finds the maximum element, splays it to the root, and returns its data.

#### Returns:
- The maximum data or `null` if the tree is empty.

#### Example:
```php
  $max = $tree->max();
  echo $max !== null ? "Max: $max" : "Tree is empty";
```

---

### `next($data): mixed|null`

Finds the successor of the given data, splays it to the root, and returns its data.

#### Parameters:
- `$data`: The data to find the successor of (mixed type).

#### Returns:
- The successor’s data or `null` if no successor exists.

#### Example:
```php
  $next = $tree->next(5);
  echo $next !== null ? "Next: $next" : "No successor";
```

---

### `prev($data): mixed|null`

Finds the predecessor of the given data, splays it to the root, and returns its data.

#### Parameters:
- `$data`: The data to find the predecessor of (mixed type).

#### Returns:
- The predecessor’s data or `null` if no predecessor exists.

#### Example:
```php
  $prev = $tree->prev(5);
  echo $prev !== null ? "Prev: $prev" : "No predecessor";
```

---

### `getSize(): int`

Returns the number of elements in the tree.

#### Returns:
- The size of the tree (integer).

#### Example:
```php
  $size = $tree->getSize();
  echo "Tree size: $size";
```

---

### `isEmpty(): bool`

Checks if the tree is empty.

#### Returns:
- `true` if empty, `false` otherwise.

#### Example:
```php
  if ($tree->isEmpty()) {
      echo "Tree is empty\n";
  }
```

---

### `clear(): void`

Removes all elements from the tree.

#### Example:
```php
  $tree->clear();
```

---

### `contains($data): bool`

Checks if the tree contains the specified data without modifying the tree structure.

#### Parameters:
- `$data`: The data to check for (mixed type).

#### Returns:
- `true` if the data exists, `false` otherwise.

#### Example:
```php
  if ($tree->contains(5)) {
      echo "Tree contains 5\n";
  }
```

---

### `toString(callable $printNode): string`

Converts the tree to a string representation using a provided callback to format each node’s data.

#### Parameters:
- `$printNode`: A callable that takes a node’s data and returns its string representation.

#### Returns**:
- A string representation of the tree (e.g., in-order traversal).

#### Example**:
```php
  echo $tree->toString(function ($data) {
      return (string)$data;
  });
```

---

### Iteration

The tree implements `IteratorAggregate`, allowing iteration over elements in order.

#### Example:
```php
  foreach ($tree as $data) {
      echo $data . "\n";
  }
```

## Advanced Usage

### Using a Custom Comparator

For complex data types like objects, define a custom comparator:

```php
class Person {
    public $age;
    public function __construct($age) {
        $this->age = $age;
    }
}

$comparator = function ($a, $b) {
    return $a->age <=> $b->age;
};

$tree = new SplayTree($comparator);
$tree->insert(new Person(25));
$tree->insert(new Person(30));
$tree->insert(new Person(20));

$minPerson = $tree->min();
echo $minPerson->age; // 20
```

### Splaying Behavior

Operations like `search`, `insert`, or `delete` splay the accessed or modified node to the root, optimizing future access:

```php
$tree->insert(3);
$tree->insert(5);
$tree->search(3); // Splays 3 to the root
// Assuming getRoot() exists (for illustration):
// echo $tree->getRoot(); // 3
```

## Performance Considerations

- **Time Complexity**: Operations (`insert`, `delete`, `search`) have an amortized time complexity of O(log n).
- **Splaying Advantage**: Frequent access to the same elements reduces access time, making this structure efficient for caching or similar use cases.

## Testing

The library is thoroughly tested with PHPUnit. To run the tests:

1. Install dependencies:
   ```bash
   composer install
   ```
2. Run PHPUnit:
   ```bash
   vendor/bin/phpunit
   ```

Good news—**all tests are passing**! You’re ready to use this library with confidence.

## Contributing

Contributions are welcome! Please submit issues or pull requests to
the [GitHub repository](https://github.com/willvincent/splay-tree). Follow standard guidelines for code style
and include tests with your submissions.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE.md) file for details.
