<?php
// Fix wp-config.php to use dynamic WP_SITEURL and WP_HOME

$config = file_get_contents('/var/www/html/wp-config.php');

// Code to insert after <?php
$insert = '
// Dynamisk WP_SITEURL og WP_HOME basert på HTTP_HOST
if (isset($_SERVER[\'HTTP_HOST\'])) {
    $host = $_SERVER[\'HTTP_HOST\'];
    $protocol = (!empty($_SERVER[\'HTTPS\']) && $_SERVER[\'HTTPS\'] !== \'off\') ? \'https\' : \'http\';
    define(\'WP_SITEURL\', $protocol . \'://\' . $host);
    define(\'WP_HOME\', $protocol . \'://\' . $host);
}
';

// Only add if not already present
if (strpos($config, 'WP_SITEURL') === false) {
    $config = str_replace('<?php', '<?php' . $insert, $config);
    file_put_contents('/var/www/html/wp-config.php', $config);
    echo "✅ Updated wp-config.php with dynamic URLs\n";
} else {
    echo "⚠️  WP_SITEURL already defined in wp-config.php\n";
}
