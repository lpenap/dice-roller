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

namespace Bakame\DiceRoller;

final class TossContext implements Context, \JsonSerializable
{
    private function __construct(
        private string $notation,
        private string $source,
        private array $extensions
    ) {
    }

    public static function fromRolling(Rollable $rollable, string $source, array $extensions = []): self
    {
        unset(
            $extensions['notation'],
            $extensions['source'],
            $extensions['operation'],
            $extensions['value'],
        );

        return new self($rollable->notation(), $source, $extensions);
    }

    public function notation(): string
    {
        return $this->notation;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function extensions(): array
    {
        return $this->extensions;
    }

    public function asArray(): array
    {
        return ['source' => $this->source, 'notation' => $this->notation] + $this->extensions;
    }

    public function jsonSerialize(): array
    {
        return $this->asArray();
    }
}
