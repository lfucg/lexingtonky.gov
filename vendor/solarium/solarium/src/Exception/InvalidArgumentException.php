<?php

/*
 * This file is part of the Solarium package.
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code.
 */

namespace Solarium\Exception;

/**
 * InvalidArgument exception for Solarium classes.
 */
class InvalidArgumentException extends \InvalidArgumentException implements LogicExceptionInterface
{
}