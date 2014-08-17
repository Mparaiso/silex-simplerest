test:
	@phpunit
commit:
	@git add .
	@git commit -am"$(message) `date`" | :
push:
	@git push origin master --tags | :

.PHONY: commit push test