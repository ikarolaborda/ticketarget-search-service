# TicketArget — Search Service

Symfony 8.1 hexagonal (ports & adapters) service exposing fuzzy + stemming event
search and edge-ngram autocomplete over Elasticsearch: `GET /search`,
`GET /search/autocomplete`.

Part of the [TicketArget platform](https://github.com/ikarolaborda/ticketarget) —
run it from the aggregator repo, which provides the Docker topology and shared
`ticketarget/logging` package.
