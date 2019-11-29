<?php

/**
 * PHP Dice Roller (https://github.com/bakame-php/dice-roller/)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bakame\DiceRoller\Contract;

interface RollContext extends \JsonSerializable
{
    /**
     * The Rollable object from which the context originate from.
     */
    public function rollable(): Rollable;

    /**
     * The action from which the context originate from.
     */
    public function source(): string;

    /**
     * Optional associated information.
     */
    public function extensions(): array;

    /**
     * Array representation of the context.
     */
    public function asArray(): array;
}