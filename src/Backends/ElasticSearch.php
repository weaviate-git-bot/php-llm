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

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use MNC\LLM\Core\Document;
use MNC\LLM\Core\Document\Inserter;
use MNC\LLM\Core\Document\KNNSearcher;
use MNC\LLM\Core\Document\Vector;
use MNC\LLM\Core\Vectorizer;

/**
 * Implements Document KNN Search and Insertion using ElasticSearch.
 *
 * Needs ElasticSearch 8.8+ in order to work with OpenAI embeddings,
 * because of the number of dimensions supported prior to that does not fit the OpenAI
 * dimension size of 1536.
 *
 * @see https://github.com/elastic/elasticsearch/issues/92458
 */
final class ElasticSearch implements KNNSearcher, Inserter
{
    public function __construct(
        private readonly Client $client,
        private readonly Vectorizer $vectorizer,
        private readonly int $dimensions,
        private readonly string $indexName = 'documents',
    ) {
    }

    public function init(): void
    {
        $indexExists = $this->client->indices()->exists([
            'index' => $this->indexName,
        ])->asBool();

        if ($indexExists) {
            return;
        }

        $this->client->indices()->create([
            'index' => $this->indexName,
            'body' => [
                'mappings' => [
                    'properties' => [
                        'vector' => [
                            'type' => 'dense_vector',
                            'dims' => $this->dimensions,
                            'index' => true,
                            'similarity' => 'cosine',
                        ],
                        'contents' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
        ])->asArray();
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
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
        ])->asArray();

        $errors = $res['errors'] ?? false;
        if ($errors) {
            throw new \LogicException('Error while bulk indexing');
        }
    }

    /**
     * @return array|Document[]
     *
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function searchKNN(Vector $vector, int $k): array
    {
        $results = $this->client->search([
            'index' => $this->indexName,
            'body' => [
                'knn' => [
                    'field' => 'vector',
                    'query_vector' => $vector->points,
                    'k' => $k,
                    'num_candidates' => 100,
                ],
                'fields' => ['contents'],
            ],
        ])->asArray();

        $documents = [];
        foreach ($results['hits']['hits'] ?? [] as $osDocument) {
            $documents[] = Document::withId($osDocument['_id'], $osDocument['_source']['contents'])
                ->withScore($osDocument['_score'])
            ;
        }

        return $documents;
    }
}
