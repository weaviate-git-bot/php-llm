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

namespace MNC\LLM\Core\Chat\History;

class Message
{
    public function __construct(
        public readonly Author $author,
        public readonly string $contents
    ) {
    }

    /**
     * @param Message[] $messages
     */
    public static function format(array $messages): string
    {
        $lines = [];
        foreach ($messages as $message) {
            $lines[] = sprintf('%s: %s', $message->author->value, $message->contents);
        }

        return implode(PHP_EOL, $lines);
    }
}
