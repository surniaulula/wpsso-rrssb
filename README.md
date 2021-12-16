
Fatal error: Uncaught TypeError: Argument 1 passed to WpssoOptions::add_versions() must be of the type array, null given, called in /var/www/wpadm/wordpress/wp-content/plugins/wpsso/lib/options.php on line 845 and defined in /var/www/wpadm/wordpress/wp-content/plugins/wpsso/lib/options.php:963
Stack trace:
#0 /var/www/wpadm/wordpress/wp-content/plugins/wpsso/lib/options.php(845): WpssoOptions->add_versions(NULL)
#1 /var/www/wpadm/wordpress/wp-content/plugins/wpsso/lib/options.php(570): WpssoOptions->save_options('wpsso_options', Array, false)
#2 /var/www/wpadm/wordpress/wp-content/plugins/wpsso/wpsso.php(477): WpssoOptions->check_options('wpsso_options', Array, false)
#3 /var/www/wpadm/wordpress/wp-includes/class-wp-hook.php(303): Wpsso->set_objects('')
#4 /var/www/wpadm/wordpress/wp-includes/class-wp-hook.php(327): WP_Hook->apply_filters(NULL, Array)
#5 /var/www/wpadm/wordpress/wp-includes/plugin.php(470): WP_Hook->do_action(Array)
#6 /var/www/wpadm/wordpress/wp-settings.php(578): do_action('init')
#7 /var/www/wpadm/wp-c in /var/www/wpadm/wordpress/wp-content/plugins/wpsso/lib/options.php on line 963
