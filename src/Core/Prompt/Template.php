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

namespace MNC\LLM\Core\Prompt;

class Template
{
    private const PARSE_PATTERN = '/\{(.*?)}/';

    private function __construct(
        public readonly string $contents,
        public readonly array $vars
    ) {
    }

    /**
     * @throws \InvalidArgumentException if the template is invalid
     */
    public static function define(string $contents, string ...$vars): Template
    {
        if ([] === $vars) {
            throw new \InvalidArgumentException('Template does not contain any variables');
        }

        return new self($contents, $vars);
    }

    /**
     * @throws \InvalidArgumentException if the template is invalid
     */
    public static function parse(string $template): Template
    {
        $matches = [];
        $count = preg_match_all(self::PARSE_PATTERN, $template, $matches);
        if (!is_int($count)) {
            // This should never happen because regex is provided by us
            throw new \InvalidArgumentException('Invalid Regular Expression');
        }

        return new self($template, $matches[1] ?? []);
    }

    /**
     * @param array<string,mixed> $data
     *
     * @throws \LogicException
     */
    public function render(array $data): string
    {
        $search = [];
        $replace = [];
        foreach ($this->vars as $var) {
            $val = $data[$var] ?? null;
            if (null === $val) {
                throw new \LogicException("Missing required value for variable '{$var}'");
            }
            $search[] = '{'.$var.'}';
            $replace[] = $val;
        }

        return str_replace(
            $search,
            $replace,
            $this->contents,
        );
    }
}
