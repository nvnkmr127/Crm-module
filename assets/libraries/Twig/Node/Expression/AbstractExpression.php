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

use Twig\Node\Node;

/**
 * Abstract class for all nodes that represents an expression.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class AbstractExpression extends Node
{
    public function isGenerator(): bool
    {
        return $this->hasAttribute('is_generator') && $this->getAttribute('is_generator');
    }

    /**
     * @return static
     */
    public function setExplicitParentheses(): self
    {
        $this->setAttribute('with_parentheses', true);

        return $this;
    }

    public function hasExplicitParentheses(): bool
    {
        return $this->hasAttribute('with_parentheses') && $this->getAttribute('with_parentheses');
    }
}
