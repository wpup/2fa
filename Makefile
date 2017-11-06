deps:
	composer install
	npm install -g wp-pot-cli

js:
	node_modules/.bin/webpack

lint:
	vendor/bin/phpcs -s --extensions=php --standard=phpcs.xml src/

pot:
	wp-pot --src 'src/**/*.php' --dest-file languages/2fa.pot --package 2fa