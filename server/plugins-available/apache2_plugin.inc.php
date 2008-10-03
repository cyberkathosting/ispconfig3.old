
<VirtualHost <tmpl_var name='ip_address'>:80>
<tmpl_if name='php' op='==' value='suphp'>
  DocumentRoot <tmpl_var name='web_document_root'>
</tmpl_else>
  DocumentRoot <tmpl_var name='web_document_root_www'>
</tmpl_if>

  ServerName <tmpl_var name='domain'>
<tmpl_if name='alias'>
  ServerAlias <tmpl_var name='alias'>
</tmpl_if>
  ServerAdmin webmaster@<tmpl_var name='domain'>

  ErrorLog <tmpl_var name='document_root'>/log/error.log
<tmpl_if name='errordocs' op='==' value='y'>

	ErrorDocument 400 /error/invalidSyntax.html
	ErrorDocument 401 /error/authorizationRequired.html
	ErrorDocument 403 /error/forbidden.html
	ErrorDocument 404 /error/fileNotFound.html
	ErrorDocument 405 /error/methodNotAllowed.html
	ErrorDocument 500 /error/internalServerError.html
	ErrorDocument 503 /error/overloaded.html
</tmpl_if>

  <Directory {tmpl_var name='web_document_root_www'}>
      Options None
      AllowOverride Indexes AuthConfig Limit FileInfo
      Order allow,deny
      Allow from all
  </Directory>

<tmpl_if name='cgi' op='==' value='y'>
  # cgi enabled
  ScriptAlias  /cgi-bin/ <tmpl_var name='document_root'>/cgi-bin/
  AddHandler cgi-script .cgi
  AddHandler cgi-script .pl
</tmpl_if>
<tmpl_if name='ssi' op='==' value='y'>
  # ssi enabled
  AddType text/html .shtml
  AddOutputFilter INCLUDES .shtml
</tmpl_if>
<tmpl_if name='suexec' op='==' value='y'>
  # suexec enabled
  SuexecUserGroup <tmpl_var name='system_user'> <tmpl_var name='system_group'>
</tmpl_if>
<tmpl_if name='php' op='==' value='mod'>
  # mod_php enabled
  AddType application/x-httpd-php .php .php3 .php4 .php5
</tmpl_if>
<tmpl_if name='php' op='==' value='suphp'>
  # suphp enabled
  <Directory {tmpl_var name='web_document_root'}>
      suPHP_Engine on
      # suPHP_UserGroup <tmpl_var name='system_user'> <tmpl_var name='system_group'>
      AddHandler x-httpd-suphp .php .php3 .php4 .php5
      suPHP_AddHandler x-httpd-suphp
  </Directory>
</tmpl_if>
<tmpl_if name='php' op='==' value='cgi'>
  # php as cgi enabled
  AddType application/x-httpd-php .php .php3 .php4 .php5
</tmpl_if>
<tmpl_if name='php' op='==' value='fast-cgi'>
  # php as fast-cgi enabled
  <Directory {tmpl_var name='web_document_root_www'}>
      AddHandler fcgid-script .php .php3 .php4 .php5
      FCGIWrapper <tmpl_var name='fastcgi_starter_path'><tmpl_var name='fastcgi_starter_script'> .php
      Options FollowSymLinks +ExecCGI Indexes
      AllowOverride None
      Order allow,deny
      Allow from all
  </Directory>
</tmpl_if>
<tmpl_if name="rewrite_enabled">

  RewriteEngine on
<tmpl_loop name="redirects">
  RewriteCond %{HTTP_HOST}   ^<tmpl_var name='rewrite_domain'> [NC]
  RewriteRule   ^/(.*)$ <tmpl_var name='rewrite_target'>$1  [<tmpl_var name='rewrite_type'>]
</tmpl_loop>
</tmpl_if>
<tmpl_if name='php' op='!=' value=''>

  php_admin_value sendmail_path "/usr/sbin/sendmail -t -i -fwebmaster@<tmpl_var name='domain'>"	
  #php_admin_value open_basedir <tmpl_var name='document_root'>:/usr/share/php5
  php_admin_value upload_tmp_dir <tmpl_var name='document_root'>/tmp
  php_admin_value session.save_path <tmpl_var name='document_root'>/tmp
</tmpl_if>
<tmpl_var name='apache_directives'>
</VirtualHost>



<tmpl_if name='ssl_enabled'>
<IfModule mod_ssl.c>
###########################################################
# SSL Vhost
###########################################################

<VirtualHost <tmpl_var name='ip_address'>:443>
  DocumentRoot <tmpl_var name='web_document_root'>
  ServerName <tmpl_var name='domain'>
<tmpl_if name='alias'>
  ServerAlias <tmpl_var name='alias'>
</tmpl_if>
  ServerAdmin webmaster@<tmpl_var name='domain'>
  
  ErrorLog <tmpl_var name='document_root'>/log/error.log

<tmpl_if name='errordocs' op='==' value='y'>
	ErrorDocument 400 /error/invalidSyntax.html
	ErrorDocument 401 /error/authorizationRequired.html
	ErrorDocument 403 /error/forbidden.html
	ErrorDocument 404 /error/fileNotFound.html
	ErrorDocument 405 /error/methodNotAllowed.html
	ErrorDocument 500 /error/internalServerError.html
	ErrorDocument 503 /error/overloaded.html

</tmpl_if>
	SSLEngine on
	SSLCertificateFile <tmpl_var name='document_root'>/ssl/<tmpl_var name='domain'>.crt
	SSLCertificateKeyFile <tmpl_var name='document_root'>/ssl/<tmpl_var name='domain'>.key
<tmpl_if name='has_bundle_cert'>
	SSLCACertificateFile <tmpl_var name='document_root'>/ssl/<tmpl_var name='domain'>.bundle
</tmpl_if>

<tmpl_if name='cgi'>
  # cgi enabled
  ScriptAlias  /cgi-bin/ <tmpl_var name='document_root'>/cgi-bin/
  AddHandler cgi-script .cgi
  AddHandler cgi-script .pl
</tmpl_if>
<tmpl_if name='ssi'>
  # ssi enabled
  AddType text/html .shtml
  AddOutputFilter INCLUDES .shtml
</tmpl_if>
<tmpl_if name='suexec'>
  # suexec enabled
  SuexecUserGroup <tmpl_var name='system_user'> <tmpl_var name='system_group'>
</tmpl_if>
<tmpl_if name='php' op='==' value='mod'>
  # mod_php enabled
  AddType application/x-httpd-php .php .php3 .php4 .php5
</tmpl_if>
<tmpl_if name='php' op='==' value='suphp'>
  # suphp enabled
  suPHP_Engine on
  suPHP_UserGroup <tmpl_var name='system_user'> <tmpl_var name='system_group'>
  AddHandler x-httpd-php .php .php3 .php4 .php5
  suPHP_AddHandler x-httpd-php
</tmpl_if>
<tmpl_if name='php' op='==' value='cgi'>
  # php as cgi enabled
  AddType application/x-httpd-php .php .php3 .php4 .php5
</tmpl_if>

<tmpl_if name="rewrite_enabled">

  RewriteEngine on
<tmpl_loop name="redirects">

  RewriteCond %{HTTP_HOST}   ^<tmpl_var name='rewrite_domain'> [NC]
  RewriteRule   ^/(.*)$ <tmpl_var name='rewrite_target'>$1  [<tmpl_var name='rewrite_type'>]
</tmpl_loop>
</tmpl_if>
<tmpl_if name='php' op='!=' value=''>

  php_admin_value sendmail_path "/usr/sbin/sendmail -t -i -fwebmaster@<tmpl_var name='domain'>"	
  #php_admin_value open_basedir <tmpl_var name='document_root'>:/usr/share/php5
  php_admin_value upload_tmp_dir <tmpl_var name='document_root'>/tmp
  php_admin_value session.save_path <tmpl_var name='document_root'>/tmp
</tmpl_if>

</VirtualHost>
</IfModule>

</tmpl_if>