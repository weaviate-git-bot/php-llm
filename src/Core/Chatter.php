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

namespace MNC\LLM\Core;

use MNC\LLM\Core\Chat\History;

/**
 * Chatter is implemented by LLMs that support chat completions.
 *
 * Chat completions differ from traditional instruction completions in that model better considers
 * the context of a conversation to provide a completion.
 */
interface Chatter
{
    /**
     * Performs a chat completion.
     */
    public function chat(string $system, History $history): string;
}
