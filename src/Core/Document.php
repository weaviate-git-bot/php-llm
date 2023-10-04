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

class Document
{
    private const META_ID = 'id';
    private const META_DISTANCE = 'distance';
    private const META_CERTAINTY = 'certainty';
    private const META_SCORE = 'score';

    public function __construct(
        public readonly string $content,
        public array $metadata
    ) {
    }

    public static function withId(string $id, string $content): Document
    {
        return new self($content, [
            self::META_ID => $id,
        ]);
    }

    public function getId(): string
    {
        return $this->metadata[self::META_ID] ?? hash('sha1', $this->content);
    }

    public function withCertainty(float $certainty): Document
    {
        $this->metadata[self::META_CERTAINTY] = $certainty;

        return $this;
    }

    public function withDistance(float $distance): Document
    {
        $this->metadata[self::META_DISTANCE] = $distance;

        return $this;
    }

    public function withScore(float $score): Document
    {
        $this->metadata[self::META_SCORE] = $score;

        return $this;
    }

    public function getScore(): float
    {
        return $this->metadata[self::META_SCORE] ?? 0.0;
    }

    public function getDistance(): float
    {
        return $this->metadata[self::META_DISTANCE] ?? 0;
    }

    public function getCertainty(): float
    {
        return $this->metadata[self::META_CERTAINTY] ?? 0;
    }
}
