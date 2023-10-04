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

namespace MNC\LLM\Backends;

use MNC\LLM\Core\Document;
use MNC\LLM\Core\Document\Inserter;
use MNC\LLM\Core\Document\KNNSearcher;
use MNC\LLM\Core\Document\Vector;
use MNC\LLM\Core\Vectorizer;
use OpenSearch\Client;

/**
 * Implements Document KNN Search and Insertion using OpenSearch 1.3+.
 */
final class OpenSearch implements KNNSearcher, Inserter
{
    public function __construct(
        public readonly Client $client,
        private readonly Vectorizer $vectorizer,
        private readonly int $dimensions,
        private readonly string $indexName = 'documents',
    ) {
    }

    public function init(): void
    {
        $indexExists = $this->client->indices()->exists([
            'index' => $this->indexName,
        ]);

        if ($indexExists) {
            return;
        }

        $this->client->indices()->create([
            'index' => $this->indexName,
            'body' => [
                'settings' => [
                    'index.knn' => true,
                ],
                'mappings' => [
                    'properties' => [
                        'vector' => [
                            'type' => 'knn_vector',
                            'dimension' => $this->dimensions,
                            'index' => true,
                            'similarity' => 'cosine',
                        ],
                        'contents' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function insert(Document ...$documents): void
    {
        $body = [];

        foreach ($documents as $document) {
            $body[] = [
                'index' => [
                    '_index' => $this->indexName,
                    '_id' => $document->getId(),
                ],
            ];

            $body[] = [
                'vector' => $this->vectorizer->vectorize($document->content)->points,
                'contents' => $document->content,
            ];
        }

        $res = $this->client->bulk([
            'refresh' => true,
            'body' => $body,
        ]);

        $errors = $res['errors'] ?? false;
        if ($errors) {
            throw new \LogicException('Error while bulk indexing');
        }
    }

    /**
     * @return array|Document[]
     */
    public function searchKNN(Vector $vector, int $k): array
    {
        $results = $this->client->search([
            'index' => $this->indexName,
            'body' => [
                'size' => 2,
                'query' => [
                    'knn' => [
                        'vector' => [
                            'vector' => $vector->points,
                            'k' => $k,
                        ],
                    ],
                ],
                'fields' => ['contents'],
            ],
        ]);

        $documents = [];
        foreach ($results['hits']['hits'] ?? [] as $osDocument) {
            $documents[] = Document::withId($osDocument['_id'], $osDocument['_source']['contents'])
                ->withScore($osDocument['_score'])
            ;
        }

        return $documents;
    }
}
