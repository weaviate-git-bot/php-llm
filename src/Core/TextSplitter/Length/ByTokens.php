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
use MNC\LLM\Core\Tokenizer;

final class ByTokens implements Length
{
    public function __construct(
        private readonly Tokenizer $tokenizer
    ) {
    }

    public function len(string $text): int
    {
        return $this->tokenizer->countTokens($text);
    }
}
