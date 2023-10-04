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

namespace MNC\LLM\Core\TextSplitter\Length;

use MNC\LLM\Core\TextSplitter\Length;

final class ByCharacters implements Length
{
    private static ?ByCharacters $global = null;

    public static function global(): ByCharacters
    {
        if (null === self::$global) {
            self::$global = new self();
        }

        return self::$global;
    }

    public function len(string $text): int
    {
        return \mb_strlen($text);
    }
}
