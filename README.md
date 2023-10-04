PHP LLM
=======

A toolkit to quickly build applications using Large Language Models in PHP

## "How do I get it?"

You can just type in your PHP project:

```bash
composer require mnavarrocarter/php-llm
```

## "Can you explain this to me, like I'm five?"

Sure! This is a library that provides a set of very general abstractions over common tools and vendors use to build
applications that use Large Language Models.

The abstractions are designed to be as much unaware of implementation details as possible, so you can swap implementations
or combining them as you see fit. Overall, they result in code that is easier to test and use, since this library
has all the complex implementation logic already built for you.

## "I came to see some code, fella!"

Inside the `.dev/examples` folder you can find a lot of examples that are ready to run. You just need
your own API token for OpenAI (or the provided of your choice). Please refer to the "Running the Examples" guide
to see how you can run them.

## "I actually need to understand how this works"

The best introductory write-up I've seen of this is [here][agile-monkeys-llm]. Is a really good summary by Javier
Toledo from the Agile Monkeys on how LLMs work and can be used for building cool stuff.

## "Why did you build this?"

Because many other programming languages have similar abstraction libraries, but there was nothing for PHP. This project
draws most of its inspiration from the [LangChain][langchain] library, available for Python, Javascript and Golang, among others.

[agile-monkeys-llm]: https://medium.com/the-theam-journey/llms-and-embeddings-101-unleash-the-power-of-gpt-4-with-unbounded-long-term-memory-edd77b83e536
[langchain]: https://www.langchain.com/