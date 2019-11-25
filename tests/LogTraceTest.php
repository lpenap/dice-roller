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

namespace Bakame\DiceRoller\Test;

use Bakame\DiceRoller\Contract\Trace;
use Bakame\DiceRoller\Cup;
use Bakame\DiceRoller\Dice\CustomDie;
use Bakame\DiceRoller\Dice\SidedDie;
use Bakame\DiceRoller\LogTrace;
use Bakame\DiceRoller\LogTracer;
use Bakame\DiceRoller\MemoryLogger;
use Bakame\DiceRoller\Modifier\Explode;
use PHPUnit\Framework\TestCase;
use function get_class;

class LogTraceTest extends TestCase
{
    /**
     * @var LogTracer
     */
    private $tracer;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracer = new LogTracer(new MemoryLogger());
    }

    public function testItCanBeInstantiated(): void
    {
        $rollable = Cup::fromRollable(new SidedDie(6), 4);
        $rollable->setTracer($this->tracer);
        $roll = $rollable->roll();
        $trace = $rollable->lastTrace();
        self::assertInstanceOf(Trace::class, $trace);
        self::assertInstanceOf(LogTrace::class, $trace);
        self::assertSame($rollable, $trace->subject());
        self::assertSame($roll, $trace->result());
        self::assertSame(get_class($rollable).'::roll', $trace->source());
        self::assertEmpty($trace->extensions());
        self::assertStringContainsString('+', $trace->operation());
        $expectedContext = [
            'source' => get_class($rollable).'::roll',
            'subject' => $trace->subject()->toString(),
            'operation' => $trace->operation(),
            'result' => $trace->result(),
        ];

        self::assertSame($expectedContext, $trace->context());
    }

    public function testTraceCanHaveOptionalsValue(): void
    {
        $rollable = new Explode(new CustomDie(-1, -1, -2), Explode::EQ, -1);
        $rollable->setTracer($this->tracer);
        $rollable->roll();
        $trace = $rollable->lastTrace();
        self::assertInstanceOf(LogTrace::class, $trace);
        self::assertArrayHasKey('totalRollsCount', $trace->context());
        self::assertIsInt($trace->context()['totalRollsCount']);
    }
}