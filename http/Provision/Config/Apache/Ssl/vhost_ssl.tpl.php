<?php $this->root = provision_auto_fix_platform_root($this->root); ?>

<?php if ($this->ssl_enabled && $this->ssl_key) : ?>

  <VirtualHost <?php print "{$ip_address}:{$http_ssl_port}"; ?>>
  <?php if ($this->site_mail) : ?>
    ServerAdmin <?php  print $this->site_mail; ?>
  <?php endif;?>

  <IfModule mod_http2.c>
    Protocols h2 http/1.1
  </IfModule>

<?php
$aegir_root = drush_get_option('aegir_root');
if (!$aegir_root && $server->aegir_root) {
  $aegir_root = $server->aegir_root;
}
?>

    DocumentRoot <?php print $this->root; ?>

    ServerName <?php print $this->uri; ?>

    SetEnv db_type  <?php print urlencode($db_type); ?>

    SetEnv db_name  <?php print urlencode($db_name); ?>

    SetEnv db_user  <?php print implode('@', array_map('urlencode', explode('@', $db_user))); ?>

    SetEnv db_passwd  <?php print urlencode($db_passwd); ?>

    SetEnv db_host  <?php print urlencode($db_host); ?>

    SetEnv db_port  <?php print urlencode($db_port); ?>

    # Enable SSL handling.

    SSLEngine on

    SSLCertificateFile <?php print $ssl_cert; ?>

    SSLCertificateKeyFile <?php print $ssl_cert_key; ?>

  <?php if (!empty($ssl_chain_cert)) : ?>
    SSLCertificateChainFile <?php print $ssl_chain_cert; ?>
  <?php endif; ?>

<?php
if (sizeof($this->aliases)) {
  foreach ($this->aliases as $alias) {
    print "  ServerAlias " . $alias . "\n";
  }
}
?>

<IfModule mod_rewrite.c>
  RewriteEngine on

  # Mitigation for https://www.drupal.org/SA-CORE-2018-002
  RewriteCond %{QUERY_STRING} (.*)(23value|23default_value|element_parents=%23)(.*) [NC]
  RewriteCond %{REQUEST_METHOD} POST [NC]
  RewriteRule ^.*$  - [R=403,L]

<?php
if ($this->redirection) {
  print " # Redirect all aliases to the selected alias.\n";
  print " RewriteCond %{HTTP_HOST} !^{$this->redirection}$ [NC]\n";
  print " RewriteRule ^/*(.*)$ https://{$this->redirection}/$1 [NE,L,R=301]\n";
}
?>
  RewriteRule ^/files/(.*)$ /sites/<?php print $this->uri; ?>/files/$1 [L]
  RewriteCond <?php print $this->site_path; ?>/files/robots.txt -f
  RewriteRule ^/robots.txt /sites/<?php print $this->uri; ?>/files/robots.txt [L]
</IfModule>

  <?php print $extra_config; ?>

      # Error handler for Drupal > 4.6.7
      <Directory ~ "sites/.*/files">
        <Files *>
          SetHandler This_is_a_Drupal_security_line_do_not_remove
        </Files>
        Options None
        Options +FollowSymLinks

        # If we know how to do it safely, disable the PHP engine entirely.
        <IfModule mod_php5.c>
          php_flag engine off
        </IfModule>
      </Directory>

    # Prevent direct reading of files in the private dir.
    # This is for Drupal7 compatibility, which would normally drop
    # a .htaccess in those directories, but we explicitly ignore those
    <Directory ~ "sites/.*/private">
      <Files *>
        SetHandler This_is_a_Drupal_security_line_do_not_remove
      </Files>
      Deny from all
      Options None
      Options +FollowSymLinks

      # If we know how to do it safely, disable the PHP engine entirely.
      <IfModule mod_php5.c>
        php_flag engine off
      </IfModule>
    </Directory>

  </VirtualHost>
<?php endif; ?>

<?php
  include(provision_class_directory('Provision_Config_Apache_Site') . '/vhost.tpl.php');
?>
