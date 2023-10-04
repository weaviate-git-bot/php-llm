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

final class StaticTemplateFactory implements TemplateFactory
{
    public function __construct(
        private readonly string $directory
    ) {
    }

    public function create(string $name): Template
    {
        $filename = $this->directory.DIRECTORY_SEPARATOR.$name.'.txt';

        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException('Template does not exist or is not readable');
        }

        $contents = @file_get_contents($filename);
        if (!is_string($contents)) {
            throw new \RuntimeException('Could not read template');
        }

        return Template::parse($contents);
    }
}
