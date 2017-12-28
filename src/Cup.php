<?php
/**
* This file is part of the League.csv library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/bakame-php/dice-roller/
* @version 1.0.0
* @package bakame-php/dice-roller
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
declare(strict_types=1);

namespace Bakame\DiceRoller;

use Countable;
use IteratorAggregate;

final class Cup implements Countable, IteratorAggregate, Rollable
{
    /**
     * @var Rollable[]
     */
    private $items = [];

    /**
     * @var string
     */
    private $trace;

    /**
     * Create a new Cup containing only on type of Rollable object
     *
     * @param int      $quantity
     * @param Rollable $rollable
     *
     * @throws Exception if the quantity is lesser than 0
     *
     * @return self
     */
    public static function createFromRollable(int $quantity, Rollable $rollable): self
    {
        if ($quantity < 1) {
            throw new Exception(sprintf('The quantity of dice `%s` is not valid', $quantity));
        }

        $items = [$rollable];
        for ($i = 0; $i < $quantity - 1; ++$i) {
            $items[] = clone $rollable;
        }

        return new self(...$items);
    }

    /**
     * New instance
     *
     * @param mixed $items a list of Rollable objects (iterable array or Traversable object)
     */
    public function __construct(Rollable ...$items)
    {
        $this->trace = '';
        $this->items = array_filter($items, [$this, 'filterEmptyCup']);
    }

    /**
     * Filter Out empty Cup object
     *
     * @param Rollable $rollable
     *
     * @return bool
     */
    private function filterEmptyCup(Rollable $rollable): bool
    {
        return !$rollable instanceof self || count($rollable) > 0;
    }

    /**
     * Return an instance with the added Rollable object.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified Rollable object.
     *
     * @param Rollable $rollable
     *
     * @return self
     */
    public function withRollable(Rollable $rollable): self
    {
        $items = array_filter(array_merge($this->items, [$rollable]), [$this, 'filterEmptyCup']);
        if ($items === $this->items) {
            return $this;
        }

        $cup = new self();
        $cup->items = $items;

        return $cup;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $this->trace = '';
        if (0 == count($this->items)) {
            return '0';
        }

        $parts = array_map(function (Rollable $rollable) {
            return (string) $rollable;
        }, $this->items);

        $pool = array_count_values($parts);
        array_walk($pool, function (&$value, $offset) {
            $value = $value > 1 ? $value.$offset : $offset;
        });

        return implode('+', $pool);
    }

    /**
     * Returns the number of Rollable objects.
     */
    public function count()
    {
        $this->trace = '';

        return count($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $this->trace = '';

        foreach ($this->items as $rollable) {
            yield $rollable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMinimum(): int
    {
        $this->trace = '';

        return array_reduce($this->items, [$this, 'minimum'], 0);
    }

    /**
     * Add the result of the Rollable::getMinimum method to the submitted sum.
     *
     * @param int      $sum      initial sum
     * @param Rollable $rollable
     *
     * @return int
     */
    private function minimum(int $sum, Rollable $rollable): int
    {
        return $sum + $rollable->getMinimum();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximum(): int
    {
        $this->trace = '';

        return array_reduce($this->items, [$this, 'maximum'], 0);
    }

    /**
     * Add the result of the Rollable::getMaximum method to the submitted sum.
     *
     * @param int      $sum      initial sum
     * @param Rollable $rollable
     *
     * @return int
     */
    private function maximum(int $sum, Rollable $rollable): int
    {
        return $sum + $rollable->getMaximum();
    }

    /**
     * {@inheritdoc}
     */
    public function getTrace(): string
    {
        return $this->trace;
    }

    /**
     * {@inheritdoc}
     */
    public function roll(): int
    {
        if (0 === count($this->items)) {
            $this->trace = '0';

            return 0;
        }

        $res = array_reduce($this->items, [$this, 'calculate'], []);
        $roll = array_sum(array_column($res, 'roll'));
        $this->trace = implode(' + ', array_map(function (string $value) {
            if (false !== strpos($value, '+')) {
                return '('.$value.')';
            }

            return $value;
        }, array_column($res, 'trace')));

        return $roll;
    }

    /**
     * Add the result of the Rollable::roll method to the submitted sum.
     *
     * @param array    $res
     * @param Rollable $rollable
     *
     * @return array
     */
    private function calculate(array $res, Rollable $rollable): array
    {
        $res[] = [
            'roll' => $rollable->roll(),
            'trace' => $rollable->getTrace(),
        ];

        return $res;
    }
}
