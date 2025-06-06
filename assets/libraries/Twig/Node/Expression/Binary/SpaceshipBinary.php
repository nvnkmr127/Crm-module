<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\Node\Expression\Binary;

use Twig\Compiler;
use Twig\Node\Expression\ReturnNumberInterface;

class SpaceshipBinary extends AbstractBinary implements ReturnNumberInterface
{
    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('<=>');
    }
}
