<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\Node\Expression;

use Twig\Compiler;

/**
 * @final
 */
class ConstantExpression extends AbstractExpression implements SupportDefinedTestInterface, ReturnPrimitiveTypeInterface
{
    use SupportDefinedTestTrait;

    public function __construct($value, int $lineno)
    {
        parent::__construct([], ['value' => $value], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->repr($this->definedTest ? true : $this->getAttribute('value'));
    }
}
