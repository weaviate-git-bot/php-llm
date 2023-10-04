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

namespace MNC\LLM\Core\TextSplitter;

use MNC\LLM\Core\TextSplitter;

/**
 * TODO: Copy the text splitting algorithm from this Golang implementation.
 *
 * @see https://github.com/henomis/lingoose/blob/main/textsplitter/recursiveTextSplitter.go#L66
 */
final class Recursive implements TextSplitter
{
    private const DEFAULT_SEPARATORS = ["\n\n", "\n", ' ', ''];

    /**
     * @param string[] $separators
     */
    public function __construct(
        private readonly int $chunkSize,
        private readonly int $chunkOverlap,
        private readonly array $separators,
        private readonly Length $length
    ) {
    }

    public static function create(
        int $chunkSize,
        int $chunkOverlap,
        array $separators = self::DEFAULT_SEPARATORS,
        ?Length $length = null
    ): Recursive {
        return new self(
            $chunkSize,
            $chunkOverlap,
            $separators,
            $length ?? TextSplitter\Length\ByCharacters::global(),
        );
    }

    public function splitText(string $text): \Generator
    {
        throw new \LogicException('Not Implemented');
    }
}
