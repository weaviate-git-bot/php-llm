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

namespace MNC\LLM\Core\Chat;

use MNC\LLM\Core\Chat;

/**
 * TODO: Check if chat can be implemented as a generator, where you can send and yield values.
 */
final class Console implements Chat
{
    /**
     * @param resource $input
     * @param resource $output
     */
    public function __construct(
        private $input,
        private $output
    ) {
    }

    public function __destruct()
    {
        fclose($this->input);
        fclose($this->output);
    }

    public static function open(): Console
    {
        return new self(STDIN, STDOUT);
    }

    public function receive(): string
    {
        $text = fgets($this->input);
        if (!is_string($text)) {
            throw new \RuntimeException('Could not read from console');
        }

        return trim($text);
    }

    public function send(string $message): void
    {
        $res = fwrite($this->output, $message.PHP_EOL.PHP_EOL);
        if (!is_int($res)) {
            throw new \RuntimeException('Could not write to console');
        }
    }
}
