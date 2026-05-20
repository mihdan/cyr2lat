Set-PSDebug -Trace 1

Set-Location C:\laragon\www\Projects\cyr2lat

php C:\laragon\www\test\woocommerce-local-attribute-upgrade-repro.php cleanup --wp-load=C:\laragon\www\test\wp-load.php
git checkout 6.8.0
php C:\laragon\www\test\woocommerce-local-attribute-upgrade-repro.php seed --wp-load=C:\laragon\www\test\wp-load.php
git checkout v7.0.1
php C:\laragon\www\test\woocommerce-local-attribute-upgrade-repro.php probe --wp-load=C:\laragon\www\test\wp-load.php

php C:\laragon\www\test\woocommerce-local-attribute-upgrade-repro.php cleanup --wp-load=C:\laragon\www\test\wp-load.php
git checkout 6.8.0
php C:\laragon\www\test\woocommerce-local-attribute-upgrade-repro.php seed-any --wp-load=C:\laragon\www\test\wp-load.php
git checkout v7.0.1
php C:\laragon\www\test\woocommerce-local-attribute-upgrade-repro.php probe-any --wp-load=C:\laragon\www\test\wp-load.php

Set-Location C:\laragon\www\test
