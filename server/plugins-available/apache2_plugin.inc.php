<?php

/*
Copyright (c) 2007, Till Brehm, projektfarm Gmbh
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
      this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.
    * Neither the name of ISPConfig nor the names of its contributors
      may be used to endorse or promote products derived from this software without
      specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

class apache2_plugin {
	
	var $plugin_name = 'apache2_plugin';
	var $class_name = 'apache2_plugin';
	
	// private variables
	var $action = '';
	
		
	/*
	 	This function is called when the plugin is loaded
	*/
	
	function onLoad() {
		global $app;
		
		/*
		Register for the events
		*/
		
		
		
		$app->plugins->registerEvent('web_domain_insert',$this->plugin_name,'ssl');
		$app->plugins->registerEvent('web_domain_update',$this->plugin_name,'ssl');
		$app->plugins->registerEvent('web_domain_delete',$this->plugin_name,'ssl');
		
		$app->plugins->registerEvent('web_domain_insert',$this->plugin_name,'insert');
		$app->plugins->registerEvent('web_domain_update',$this->plugin_name,'update');
		$app->plugins->registerEvent('web_domain_delete',$this->plugin_name,'delete');
		
		$app->plugins->registerEvent('server_ip_insert',$this->plugin_name,'server_ip');
		$app->plugins->registerEvent('server_ip_update',$this->plugin_name,'server_ip');
		$app->plugins->registerEvent('server_ip_delete',$this->plugin_name,'server_ip');
		
	}
	
	// Handle the creation of SSL certificates
	function ssl($event_name,$data) {
		global $app, $conf;
		
		if(!is_dir($data["new"]["document_root"]."/ssl")) exec("mkdir -p ".$data["new"]["document_root"]."/ssl");
		$ssl_dir = $data["new"]["document_root"]."/ssl";
		$domain = $data["new"]["domain"];
		$key_file = $ssl_dir.'/'.$domain.".key.org";
  		$key_file2 = $ssl_dir.'/'.$domain.".key";
  		$csr_file = $ssl_dir.'/'.$domain.".csr";
  		$crt_file = $ssl_dir.'/'.$domain.".crt";
		
		//* Create a SSL Certificate
		if($data["new"]["ssl_action"] == 'create') {
			$rand_file = $ssl_dir."/random_file";
    		$rand_data = md5(uniqid(microtime(),1));
    		for($i=0; $i<1000; $i++){
    			$rand_data .= md5(uniqid(microtime(),1));
    			$rand_data .= md5(uniqid(microtime(),1));
    			$rand_data .= md5(uniqid(microtime(),1));
    			$rand_data .= md5(uniqid(microtime(),1));
    		}
    		file_put_contents($rand_file, $rand_data);

    		$ssl_password = substr(md5(uniqid(microtime(),1)), 0, 15);
			
			$ssl_cnf = "        RANDFILE               = $rand_file

        [ req ]
        default_bits           = 1024
        default_keyfile        = keyfile.pem
        distinguished_name     = req_distinguished_name
        attributes             = req_attributes
        prompt                 = no
        output_password        = $ssl_password

        [ req_distinguished_name ]
        C                      = ".$data['new']['ssl_country']."
        ST                     = ".$data['new']['ssl_state']."
        L                      = ".$data['new']['ssl_locality']."
        O                      = ".$data['new']['ssl_organisation']."
        OU                     = ".$data['new']['ssl_organisation_unit']."
        CN                     = $domain
        emailAddress           = webmatser@".$data['new']['domain']."

        [ req_attributes ]
        challengePassword              = A challenge password";
			
			$ssl_cnf_file = $ssl_dir."/openssl.conf";
			file_put_contents($ssl_cnf_file,$ssl_cnf);
			
			$rand_file = escapeshellcmd($rand_file);
			$key_file = escapeshellcmd($key_file);
			$key_file2 = escapeshellcmd($key_file2);
			$ssl_days = 3650;
			$csr_file = escapeshellcmd($csr_file);
			$config_file = escapeshellcmd($ssl_cnf_file);
			$crt_file = escapeshellcmd($crt_file);

        	if(is_file($ssl_cnf_file)){
          		exec("openssl genrsa -des3 -rand $rand_file -passout pass:$ssl_password -out $key_file 1024 && openssl req -new -passin pass:$ssl_password -passout pass:$ssl_password -key $key_file -out $csr_file -days $ssl_days -config $config_file && openssl req -x509 -passin pass:$ssl_password -passout pass:$ssl_password -key $key_file -in $csr_file -out $crt_file -days $ssl_days -config $config_file && openssl rsa -passin pass:$ssl_password -in $key_file -out $key_file2");
				$app->log("Creating SSL Cert for: $domain",LOGLEVEL_DEBUG);
        	}

    		exec("chmod 400 $key_file2");
    		@unlink($config_file);
    		@unlink($rand_file);
    		$ssl_request = file_get_contents($csr_file);
    		$ssl_cert = file_get_contents($crt_file);
    		$app->db->query("UPDATE web_domain SET ssl_request = '$ssl_request', ssl_cert = '$ssl_cert' WHERE domain = '".$data["new"]["domain"]."'");
			$app->db->query("UPDATE web_domain SET ssl_action = '' WHERE domain = '".$data["new"]["domain"]."'");
		}
		
		//* Save a SSL certificate to disk
		if($data["new"]["ssl_action"] == 'save') {
			$ssl_dir = $data["new"]["document_root"]."/ssl";
			$domain = $data["new"]["domain"];
  			$csr_file = $ssl_dir.'/'.$domain.".csr";
  			$crt_file = $ssl_dir.'/'.$domain.".crt";
			$bundle_file = $ssl_dir.'/'.$domain.".bundle";
			file_put_contents($csr_file,$data["new"]["ssl_request"]);
			file_put_contents($crt_file,$data["new"]["ssl_cert"]);
			if(trim($data["new"]["ssl_bundle"]) != '') file_put_contents($bundle_file,$data["new"]["ssl_bundle"]);
			$app->db->query("UPDATE web_domain SET ssl_action = '' WHERE domain = '".$data["new"]["domain"]."'");
			$app->log("Saving SSL Cert for: $domain",LOGLEVEL_DEBUG);
		}
		
		//* Delete a SSL certificate
		if($data["new"]["ssl_action"] == 'del') {
			$ssl_dir = $data["new"]["document_root"]."/ssl";
			$domain = $data["new"]["domain"];
  			$csr_file = $ssl_dir.'/'.$domain.".csr";
  			$crt_file = $ssl_dir.'/'.$domain.".crt";
			$bundle_file = $ssl_dir.'/'.$domain.".bundle";
			unlink($csr_file);
			unlink($crt_file);
			unlink($bundle_file);
			$app->db->query("UPDATE web_domain SET ssl_action = '' WHERE domain = '".$data["new"]["domain"]."'");
			$app->log("Deleting SSL Cert for: $domain",LOGLEVEL_DEBUG);
		}
		
		
	}
	
	
	function insert($event_name,$data) {
		global $app, $conf;
		
		$this->action = 'insert';
		// just run the update function
		$this->update($event_name,$data);
		
		
	}
	
	
	function update($event_name,$data) {
		global $app, $conf;
		
		if($this->action != 'insert') $this->action = 'update';
		
		if($data["new"]["type"] != "vhost" && $data["new"]["parent_domain_id"] > 0) {
			// This is not a vhost, so we need to update the parent record instead.
			$parent_domain_id = intval($data["new"]["parent_domain_id"]);
			$tmp = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ".$parent_domain_id." AND active = 'y'");
			$data["new"] = $tmp;
			$data["old"] = $tmp;
		}
		
		
		// load the server configuration options
		$app->uses("getconf");
		$web_config = $app->getconf->get_server_config($conf["server_id"], 'web');
		
		if($data["new"]["document_root"] == '') {
			$app->log("document_root not set",LOGLEVEL_WARN);
			return 0;
		}
		if($data["new"]["system_user"] == 'root' or $data["new"]["system_group"] == 'root') {
			$app->log("Websites can not be owned by the root user or group.",LOGLEVEL_WARN);
			return 0;
		}
		
		//print_r($data);
		
		// Check if the directories are there and create them if nescessary.
		if(!is_dir($data["new"]["document_root"]."/web")) exec("mkdir -p ".$data["new"]["document_root"]."/web");
		if(!is_dir($data["new"]["document_root"]."/web/error")) exec("mkdir -p ".$data["new"]["document_root"]."/web/error");
		//if(!is_dir($data["new"]["document_root"]."/log")) exec("mkdir -p ".$data["new"]["document_root"]."/log");
		if(!is_dir($data["new"]["document_root"]."/ssl")) exec("mkdir -p ".$data["new"]["document_root"]."/ssl");
		if(!is_dir($data["new"]["document_root"]."/cgi-bin")) exec("mkdir -p ".$data["new"]["document_root"]."/cgi-bin");
		
		// Remove the symlink for the site, if site is renamed
		if($this->action == 'update' && $data["old"]["domain"] != '' && $data["new"]["domain"] != $data["old"]["domain"]) {
			if(is_dir('/var/log/ispconfig/httpd/'.$data["old"]["domain"])) exec('rm -rf /var/log/ispconfig/httpd/'.$data["old"]["domain"]);
			if(is_link($data["old"]["document_root"]."/log")) unlink($data["old"]["document_root"]."/log");
		}
		
		// Create the symlink for the logfiles
		if(!is_dir('/var/log/ispconfig/httpd/'.$data["new"]["domain"])) exec('mkdir -p /var/log/ispconfig/httpd/'.$data["new"]["domain"]);
		if(!is_link($data["new"]["document_root"]."/log")) {
			exec("ln -s /var/log/ispconfig/httpd/".$data["new"]["domain"]." ".$data["new"]["document_root"]."/log");
			$app->log("Creating Symlink: ln -s /var/log/ispconfig/httpd/".$data["new"]["domain"]." ".$data["new"]["document_root"]."/log",LOGLEVEL_DEBUG);
		}
		/*
		// Create the symlink for the logfiles
		// This does not work as vlogger can not log trogh symlinks.
		if($this->action == 'update' && $data["old"]["domain"] != '' && $data["new"]["domain"] != $data["old"]["domain"]) {
			if(is_dir($data["old"]["document_root"]."/log")) exec('rm -rf '.$data["old"]["document_root"]."/log");
			if(is_link('/var/log/ispconfig/httpd/'.$data["old"]["domain"])) unlink('/var/log/ispconfig/httpd/'.$data["old"]["domain"]);
		}
		
		// Create the symlink for the logfiles
		if(!is_dir($data["new"]["document_root"]."/log")) exec('mkdir -p '.$data["new"]["document_root"]."/log");
		if(!is_link('/var/log/ispconfig/httpd/'.$data["new"]["domain"])) {
			exec("ln -s ".$data["new"]["document_root"]."/log /var/log/ispconfig/httpd/".$data["new"]["domain"]);
			$app->log("Creating Symlink: ln -s ".$data["new"]["document_root"]."/log /var/log/ispconfig/httpd/".$data["new"]["domain"],LOGLEVEL_DEBUG);
		}
		*/
	
		// Get the client ID
		$client = $app->db->queryOneRecord("SELECT client_id FROM sys_group WHERE sys_group.groupid = ".intval($data["new"]["sys_groupid"]));
		$client_id = intval($client["client_id"]);
		unset($client);
		
		// Remove old symlinks, if site is renamed
		if($this->action == 'update' && $data["old"]["domain"] != '' && $data["new"]["domain"] != $data["old"]["domain"]) {
			$tmp_symlinks_array = explode(':',$web_config["website_symlinks"]);
			if(is_array($tmp_symlinks_array)) {
				foreach($tmp_symlinks_array as $tmp_symlink) {
					$tmp_symlink = str_replace("[client_id]",$client_id,$tmp_symlink);
					$tmp_symlink = str_replace("[website_domain]",$data["old"]["domain"],$tmp_symlink);
					// Remove trailing slash
					if(substr($tmp_symlink, -1, 1) == '/') $tmp_symlink = substr($tmp_symlink, 0, -1);
					// create the symlinks, if not exist
					if(!is_link($tmp_symlink)) {
						exec("rm -f ".escapeshellcmd($tmp_symlink));
						$app->log("Removed Symlink: rm -f ".$tmp_symlink,LOGLEVEL_DEBUG);
					}
				}
			}
		}
		
		// Create the symlinks for the sites
		$tmp_symlinks_array = explode(':',$web_config["website_symlinks"]);
		if(is_array($tmp_symlinks_array)) {
			foreach($tmp_symlinks_array as $tmp_symlink) {
				$tmp_symlink = str_replace("[client_id]",$client_id,$tmp_symlink);
				$tmp_symlink = str_replace("[website_domain]",$data["new"]["domain"],$tmp_symlink);
				// Remove trailing slash
				if(substr($tmp_symlink, -1, 1) == '/') $tmp_symlink = substr($tmp_symlink, 0, -1);
				// create the symlinks, if not exist
				if(!is_link($tmp_symlink)) {
					exec("ln -s ".escapeshellcmd($data["new"]["document_root"])."/ ".escapeshellcmd($tmp_symlink));
					$app->log("Creating Symlink: ln -s ".$data["new"]["document_root"]."/ ".$tmp_symlink,LOGLEVEL_DEBUG);
				}
			}
		}
		
		
		if($this->action == 'insert') {
			// Copy the error pages
			$error_page_path = escapeshellcmd($data["new"]["document_root"])."/web/error/";
			exec("cp /usr/local/ispconfig/server/conf/error/".substr(escapeshellcmd($conf["language"]),0,2)."/* ".$error_page_path);
			exec("chmod -R +r ".$error_page_path);
		
			// copy the standard index page
			exec("cp /usr/local/ispconfig/server/conf/index/standard_index.html_".substr(escapeshellcmd($conf["language"]),0,2)." ".escapeshellcmd($data["new"]["document_root"])."/web/index.html");
			exec("chmod +r ".escapeshellcmd($data["new"]["document_root"])."/web/index.html");
		}
		
		// Create group and user, if not exist
		$app->uses("system");
		
		$groupname = escapeshellcmd($data["new"]["system_group"]);
		if($data["new"]["system_group"] != '' && !$app->system->is_group($data["new"]["system_group"])) {
			exec("groupadd $groupname");
			$app->log("Adding the group: $groupname",LOGLEVEL_DEBUG);
		}
		
		$username = escapeshellcmd($data["new"]["system_user"]);
		if($data["new"]["system_user"] != '' && !$app->system->is_user($data["new"]["system_user"])) {
			exec("useradd -d ".escapeshellcmd($data["new"]["document_root"])." -g $groupname $username -s /bin/false");
			$app->log("Adding the user: $username",LOGLEVEL_DEBUG);
		}
		
		// Set the quota for the user
		if($username != '' && $app->system->is_user($username)) {
			if($data["new"]["hd_quota"] > 0){
    			$blocks_soft = $data["new"]["hd_quota"] * 1024;
    			$blocks_hard = $blocks_soft + 1024;
  			} else {
    			$blocks_soft = $blocks_hard = 0;
  			}
			exec("setquota -u $username $blocks_soft $blocks_hard 0 0 -a &> /dev/null");
			exec("setquota -T -u $username 604800 604800 -a &> /dev/null");
		}
		
		
		
		// Chown and chmod the directories
		exec("chown -R $username:$groupname ".escapeshellcmd($data["new"]["document_root"]));
		
		
		// Create the vhost config file
		$app->load('tpl');
		
		$tpl = new tpl();
		$tpl->newTemplate("vhost.conf.master");
		
		$vhost_data = $data["new"];
		$vhost_data["web_document_root"] = $data["new"]["document_root"]."/web";
		
		// Check if a SSL cert exists
		$ssl_dir = $data["new"]["document_root"]."/ssl";
		$domain = $data["new"]["domain"];
  		$key_file = $ssl_dir.'/'.$domain.".key";
  		$crt_file = $ssl_dir.'/'.$domain.".crt";
		$bundle_file = $ssl_dir.'/'.$domain.".bundle";
		
		if($data["new"]["ssl"] == 'y' && @is_file($crt_file) && @is_file($key_file)) {
			$vhost_data["ssl_enabled"] = 1;
			$app->log("Enable SSL for: $domain",LOGLEVEL_DEBUG);
		} else {
			$vhost_data["ssl_enabled"] = 0;
			$app->log("Disable SSL for: $domain",LOGLEVEL_DEBUG);
		}
		
		if(@is_file($bundle_file)) $vhost_data['has_bundle_cert'] = 1;
		
		//$vhost_data["document_root"] = $data["new"]["document_root"]."/web";
		$tpl->setVar($vhost_data);
		
		// Rewrite rules
		$rewrite_rules = array();
		if($data["new"]["redirect_type"] != '') {
			$rewrite_rules[] = array(	'rewrite_domain' 	=> $data["new"]["domain"],
										'rewrite_type' 		=> $data["new"]["redirect_type"],
										'rewrite_target' 	=> $data["new"]["redirect_path"]);
		}
		
		// get alias domains (co-domains and subdomains)
		$aliases = $app->db->queryAllRecords("SELECT * FROM web_domain WHERE parent_domain_id = ".$data["new"]["domain_id"]." AND active = 'y'");
		$server_alias = '';
		if(is_array($aliases)) {
			foreach($aliases as $alias) {
				$server_alias .= $alias["domain"].' ';
				$app->log("Add server alias: $alias[domain]",LOGLEVEL_DEBUG);
				// Rewriting
				if($alias["redirect_type"] != '') {
					$rewrite_rules[] = array(	'rewrite_domain' 	=> $alias["domain"],
												'rewrite_type' 		=> $alias["redirect_type"],
												'rewrite_target' 	=> $alias["redirect_path"]);
				}
			}
		}
		$tpl->setVar('alias',trim($server_alias));
		if(count($rewrite_rules) > 0) {
			$tpl->setVar('rewrite_enabled',1);
		} else {
			$tpl->setVar('rewrite_enabled',0);
		}
		$tpl->setLoop('redirects',$rewrite_rules);
		
		/** 
		 * install fast-cgi starter script and add script aliasd config 
		 * first we create the script directory if not already created, then copy over the starter script
		 * settings are copied over from the server ini config for now
		 * TODO: Create form for fastcgi configs per site.
		 */
		
		if ($data["new"]["php"] == "fast-cgi")
		{
			$fastcgi_config = $app->getconf->get_server_config($conf["server_id"], 'fastcgi');
			
			$fastcgi_starter_path = str_replace("[system_user]",$data["new"]["system_user"],$fastcgi_config["fastcgi_starter_path"]);
			$fastcgi_starter_path = str_replace("[client_id]",$client_id,$fastcgi_starter_path);
			
			if (!is_dir($fastcgi_starter_path))
			{
				exec("mkdir -p ".escapeshellcmd($fastcgi_starter_path));
				exec("chown ".$data["new"]["system_user"].":".$data["new"]["system_group"]." ".escapeshellcmd($fastcgi_starter_path));
				
				
				$app->log("Creating fastcgi starter script directory: $fastcgi_starter_path",LOGLEVEL_DEBUG);
			}
			
			$fcgi_tpl = new tpl();
			$fcgi_tpl->newTemplate("php-fcgi-starter.master");
				
			$fcgi_tpl->setVar('php_ini_path',$fastcgi_config["fastcgi_phpini_path"]);
			$fcgi_tpl->setVar('document_root',$data["new"]["document_root"]);
			$fcgi_tpl->setVar('php_fcgi_children',$fastcgi_config["fastcgi_children"]);
			$fcgi_tpl->setVar('php_fcgi_max_requests',$fastcgi_config["fastcgi_max_requests"]);
			$fcgi_tpl->setVar('php_fcgi_bin',$fastcgi_config["fastcgi_bin"]);
				
			$fcgi_starter_script = escapeshellcmd($fastcgi_starter_path.$fastcgi_config["fastcgi_starter_script"]);
			file_put_contents($fcgi_starter_script,$fcgi_tpl->grab());
			unset($fcgi_tpl);
			
			$app->log("Creating fastcgi starter script: $fcgi_starter_script",LOGLEVEL_DEBUG);
			
			
			exec("chmod 755 $fcgi_starter_script");
			exec("chown ".$data["new"]["system_user"].":".$data["new"]["system_group"]." $fcgi_starter_script");

			$tpl->setVar('fastcgi_alias',$fastcgi_config["fastcgi_alias"]);
			$tpl->setVar('fastcgi_starter_path',$fastcgi_starter_path);
			
		}
		
		$vhost_file = escapeshellcmd($web_config["vhost_conf_dir"].'/'.$data["new"]["domain"].'.vhost');
		file_put_contents($vhost_file,$tpl->grab());
		$app->log("Writing the vhost file: $vhost_file",LOGLEVEL_DEBUG);
		unset($tpl);
		
		// Set the symlink to enable the vhost
		$vhost_symlink = escapeshellcmd($web_config["vhost_conf_enabled_dir"].'/'.$data["new"]["domain"].'.vhost');
		if($data["new"]["active"] == 'y' && !is_link($vhost_symlink)) {
			symlink($vhost_file,$vhost_symlink);
			$app->log("Creating the symlink: $vhost_symlink => $vhost_file",LOGLEVEL_DEBUG);
		}
		
		// Remove the symlink, if site is inactive
		if($data["new"]["active"] == 'n' && is_link($vhost_symlink)) {
			unlink($vhost_symlink);
			$app->log("Removing symlink: $vhost_symlink => $vhost_file",LOGLEVEL_DEBUG);
		}
		
		// remove old symlink and vhost file, if domain name of the site has changed
		if($this->action == 'update' && $data["old"]["domain"] != '' && $data["new"]["domain"] != $data["old"]["domain"]) {
			$vhost_symlink = escapeshellcmd($web_config["vhost_conf_enabled_dir"].'/'.$data["old"]["domain"].'.vhost');
			unlink($vhost_symlink);
			$app->log("Removing symlink: $vhost_symlink => $vhost_file",LOGLEVEL_DEBUG);
			$vhost_file = escapeshellcmd($web_config["vhost_conf_dir"].'/'.$data["old"]["domain"].'.vhost');
			unlink($vhost_file);
			$app->log("Removing File $vhost_file",LOGLEVEL_DEBUG);
		}
		
		// request a httpd reload when all records have been processed
		$app->services->restartServiceDelayed('httpd','reload');
		
	}
	
	function delete($event_name,$data) {
		global $app, $conf;
		
		// load the server configuration options
		$app->uses("getconf");
		$web_config = $app->getconf->get_server_config($conf["server_id"], 'web');
		
		// Deleting the vhost file, symlink and the data directory
		$vhost_symlink = escapeshellcmd($web_config["vhost_conf_enabled_dir"].'/'.$data["old"]["domain"].'.vhost');
		unlink($vhost_symlink);
		$app->log("Removing symlink: $vhost_symlink => $vhost_file",LOGLEVEL_DEBUG);
		
		$vhost_file = escapeshellcmd($web_config["vhost_conf_dir"].'/'.$data["old"]["domain"].'.vhost');
		unlink($vhost_file);
		$app->log("Removing vhost file: $vhost_file",LOGLEVEL_DEBUG);
		
		$docroot = escapeshellcmd($data["old"]["document_root"]);
		if($docroot != '' && !stristr($docroot,'..')) exec("rm -rf $docroot");
		
		
		//remove the php fastgi starter script if available
		if ($data["old"]["php"] == "fast-cgi")
		{
			$fastcgi_starter_path = str_replace("[system_user]",$data["old"]["system_user"],$web_config["fastcgi_starter_path"]);
			if (is_dir($fastcgi_starter_path))
			{
					exec("rm -rf $fastcgi_starter_path");
			}
		}
		
		$app->log("Removing website: $docroot",LOGLEVEL_DEBUG);
		
		// Delete the symlinks for the sites
		$client = $app->db->queryOneRecord("SELECT client_id FROM sys_group WHERE sys_group.groupid = ".intval($data["old"]["sys_groupid"]));
		$client_id = intval($client["client_id"]);
		unset($client);
		$tmp_symlinks_array = explode(':',$web_config["website_symlinks"]);
		if(is_array($tmp_symlinks_array)) {
			foreach($tmp_symlinks_array as $tmp_symlink) {
				$tmp_symlink = str_replace("[client_id]",$client_id,$tmp_symlink);
				$tmp_symlink = str_replace("[website_domain]",$data["old"]["domain"],$tmp_symlink);
				// Remove trailing slash
				if(substr($tmp_symlink, -1, 1) == '/') $tmp_symlink = substr($tmp_symlink, 0, -1);
				// create the symlinks, if not exist
				if(is_link($tmp_symlink)) {
					unlink($tmp_symlink);
					$app->log("Removing symlink: ".$tmp_symlink,LOGLEVEL_DEBUG);
				}
			}
		}
		// end removing symlinks
		
		// Delete the log file directory
		$vhost_logfile_dir = escapeshellcmd('/var/log/ispconfig/httpd/'.$data["old"]["domain"]);
		if($data["old"]["domain"] != '' && !stristr($vhost_logfile_dir,'..')) exec("rm -rf $vhost_logfile_dir");
		$app->log("Removing website logfile directory: $vhost_logfile_dir",LOGLEVEL_DEBUG);
		
		//delete the web user
		$command = 'userdel';
		$command .= ' '.$data["old"]["system_user"];			
		exec($command);
	}
	
	//* This function is called when a IP on the server is inserted, updated or deleted
	function server_ip($event_name,$data) {
		global $app, $conf;
		
		// Here we write the name virtualhost directives
		// NameVirtualHost IP:80
		// NameVirtualHost IP:443
		
	}
	

} // end class

?>