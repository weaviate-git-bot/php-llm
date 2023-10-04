<?php

use MNC\LLM;
use MNC\LLM\Core\Chat\ArrayHistory;
use MNC\LLM\Core\Chat\Console;
use MNC\LLM\Core\Document;
use MNC\LLM\Core\Pipeline\ConversationalQALoop;
use MNC\LLM\Core\Prompt\StaticTemplateFactory;
use Ramsey\Uuid\Uuid;
use Weaviate\Weaviate;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

// First, we bootstrap all the things we need
$weaviate = new Weaviate('http://weaviate:8080/v1', '');
$templates = new StaticTemplateFactory(__DIR__.'/prompts/en');
$openAI = LLM\Backends\OpenAI::create(getenv('OPEN_AI_TOKEN'));
$weaviateBackend = new LLM\Backends\Weaviate($weaviate, $openAI, 'LiechFacts');

// The country of Liech is a fictitious country created by me. Here are some facts about it.
// This is all fictional with the aim to prove that the chat-bot can learn even the most spurious content.
// However, please note that with this kind of content, the models tend to hallucinate much more.
$knowledge = [
    "The country of Liech is located between the banks of the river Bongo, and the mighty Zarkath mountain range, in the
    region of Azara.",
    "Azara is famous for being a highly well-off region, with commerce and manufacturing being the primary activities.
    However, Liech is the exception to this.",
    "Liech is known for being a primarily an agricultural economy, with a very underdeveloped society.",
    "The reason for this may be due to Liech blessed location: the river Bongo's various arms bless all the land of Liech
    with abundant water, and the Zarkath mountains protect the country from unwanted trespassers.",
    "It has massive plains of land, which benefits a lot its primary economic activity, although they are scarcely populated.",
    "The population of Liech is small. We don't have exact numbers, but it probably borders a million. Most of them, farmers.",
    "The capital of Liech is called Prenath, but it would be a overstatement to call it a capital city; it's more like a
    big group of farms.",
    "Liech is ruled by a council of elders. All decisions are made collectively and there is no one sole ruler.",
    "The weather in Liech is quite mild in every respect: moderate rain and temperatures make it perfect to grow all sort
    of things with ease.",
    "Other countries in Azara are Karnath, Froyin and Thollen. They in fact have been in war with each other in the past.",
    "The reason that they are in war, is because Karnath is the primary line of defense against the League of the East, and 
    the other countries in Azara have never helped them with the weight of that war.",
    "This has made Karnath grow very resentful, but also very powerful, as it has developed a powerful military to deal
    with The League's ambitions over the whole of Azara.",
    "The other countries in Azara don't want to help Karnath because is convenient for them that the two superpowers
     weaken each other in this long and extenuating war. When the war is over, they will have to serve one of them."
];

$documents = [];
$ns = Uuid::fromString('d6a81051-2bd9-489e-9477-08fec679f2b8');
foreach ($knowledge as $i => $phrase) {
    $documents[] = Document::withId(Uuid::uuid3($ns, $i), $phrase);
}

$weaviateBackend->insert(...$documents);

$chatSource = Console::open();
$chatSourceWithMemory = new ArrayHistory($chatSource);

$qaPipeline = new ConversationalQALoop($chatSourceWithMemory, $openAI, $weaviateBackend, $templates);
$qaPipeline->run('Hi, ask me anything about the country of Liech.');