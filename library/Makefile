build:
	docker-compose build
dependencies:
	docker-compose run -e XDEBUG_MODE=off -u host:host --entrypoint=composer php install --prefer-dist --no-progress -n
up:
	docker-compose up -d
down:
	docker-compose down -v
sh:
	docker-compose exec -e XDEBUG_MODE=off -u host:host php sh
format-preview:
	docker-compose exec -e XDEBUG_MODE=off -u host:host php composer f:p
format:
	docker-compose exec -e XDEBUG_MODE=off -u host:host php composer format
test:
	docker-compose exec -e XDEBUG_MODE=off -u host:host php composer test
analyse:
	docker-compose exec -e XDEBUG_MODE=off -u host:host php composer analyse
check:
	docker-compose exec -e XDEBUG_MODE=off -u host:host php composer all
status:
	docker-compose ps
