<?php

declare(strict_types=1);

/**
 * @project PHP LLM
 * @link https://github.com/mnavarrocarter/php-llm
 * @project mnavarrocarter/php-llm
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license BSD-3-Clause
 * @copyright 2023 Castor Labs Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MNC\LLM;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class FFITest extends TestCase
{
    public function testFFI(): void
    {
        $ffi = \FFI::cdef(
            'int printf(const char *format, ...);',
            'libc.so.6'
        );

        $n = $ffi->printf("Hello %s!\n", 'world');
        $this->assertSame(13, $n);
    }
}
