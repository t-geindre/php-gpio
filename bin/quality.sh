#!/bin/bash
# phploc: https://github.com/sebastianbergmann/phploc
# phpmd: https://github.com/phpmd/phpmd
# phpcpd: https://github.com/sebastianbergmann/phpcpd
# pdepend: https://github.com/pdepend/pdepend
# php-cs-fixer: https://github.com/fabpot/PHP-CS-Fixer

phploc src/ > STATS
phpmd src/ text codesize,unusedcode,naming >> STATS
phpcpd src/ >> STATS
pdepend src/ >> STATS
php-cs-fixer fix src/ --dry-run >> STATS

