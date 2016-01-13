class Homestead
  def Homestead.configure(config, settings)
    ENV["VAGRANT_DEFAULT_PROVIDER"] = settings["type"]
    config.vm.hostname = "homestead"
    if settings.has_key?("ports")
        forwardedPorts = settings["ports"]
    else
        forwardedPorts = ["80:80", "443:443"]
    end

    if (settings.has_key?("type") && settings["type"] == 'docker')
      dir = File.dirname(File.expand_path(__FILE__))
			config.vm.provider "docker" do |d|
				d.build_dir = "#{dir}/docker"
        d.cmd = ["/sbin/my_init", "--enable-insecure-key"]
        d.has_ssh = true
        d.ports = forwardedPorts
      end
      config.ssh.username = "root"
      config.ssh.private_key_path = [
        "#{dir}/docker/files/insecure_key",
        "#{dir}/docker/files/id_rsa",
      ]
    else
      # Configure The Box
      config.vm.box = "laravel/homestead"
      # Configure A Private Network IP
      config.vm.network :private_network, ip: settings["ip"] ||= "192.168.10.10"
      # Configure A Few VirtualBox Settings
      config.vm.provider "virtualbox" do |vb|
        vb.customize ["modifyvm", :id, "--memory", settings["memory"] ||= "2048"]
        vb.customize ["modifyvm", :id, "--cpus", settings["cpus"] ||= "1"]
        vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
        vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
      end

      # Configure Port Forwarding To The Box
      config.vm.network "forwarded_port", guest: 80, host: 8000
      config.vm.network "forwarded_port", guest: 5000, host: 5000
      config.vm.network "forwarded_port", guest: 3306, host: 33060
      config.vm.network "forwarded_port", guest: 5432, host: 54320
    end

    # Copy The Bash Aliases
    config.vm.provision "shell" do |s|
      s.inline = "cp /vagrant/aliases /home/vagrant/.bash_aliases"
      s.inline = "sudo apt-get update"
      s.inline = "sudo composer self-update"
    end

    # Register All Of The Configured Shared Folders
    settings["folders"].each do |folder|
      config.vm.synced_folder folder["map"], folder["to"], type: folder["type"] ||= nil
    end

    # Install All The Configured Nginx Sites
    settings["sites"].each do |site|
      config.vm.provision "shell" do |s|
          custom = site.has_key?("custom") && site["custom"]
          hhvm = site.has_key?("hhvm") && site["hhvm"]
          ssl = site.has_key?("ssl") && site["ssl"]
          html = site.has_key?("html") && site["html"]

          if custom
            s.inline = "bash /vagrant/#{custom} $1 $2"
            s.args = [site["map"], site["to"]]
          elsif hhvm and ssl
            s.inline = "bash /vagrant/scripts/provision/serve/hhvm-ssl.sh $1 $2"
            s.args = [site["map"], site["to"]]
          elsif hhvm
            s.inline = "bash /vagrant/scripts/provision/serve/hhvm.sh $1 $2"
            s.args = [site["map"], site["to"]]
          elsif html and ssl
            s.inline = "bash /vagrant/scripts/provision/serve/html-ssl.sh $1 $2"
            s.args = [site["map"], site["to"]]
          elsif html
            s.inline = "bash /vagrant/scripts/provision/serve/html.sh $1 $2"
            s.args = [site["map"], site["to"]]
          elsif ssl
            s.inline = "bash /vagrant/scripts/provision/serve/ssl.sh $1 $2"
            s.args = [site["map"], site["to"]]
          else
            s.inline = "bash /vagrant/scripts/provision/serve/standard.sh $1 $2"
            s.args = [site["map"], site["to"]]
          end
      end
    end

    # Configure All Of The Server Environment Variables
    if settings.has_key?("variables")
      settings["variables"].each do |var|
        config.vm.provision "shell" do |s|
            s.inline = "echo \"\nenv[$1] = '$2'\" >> /etc/php5/fpm/php-fpm.conf"
            s.args = [var["key"], var["value"]]
        end
      end
    end

		# Restart server and PHP
    if (settings.has_key?("type") && settings["type"] == 'docker')
      config.vm.provision "shell" do |s|
        s.inline = "bash /vagrant/scripts/provision/restart-docker.sh"
			end
    else
      config.vm.provision "shell" do |s|
        s.inline = "bash /vagrant/scripts/provision/restart.sh"
			end
    end

    # Install all databases
    settings["databases"].each do |database|
      config.vm.provision "shell" do |s|
				if (database.has_key?("type") && database["type"] == 'postgresql')
	        s.inline = "bash /vagrant/scripts/provision/database/create-postgresql.sh $1 $2 $3"
				else
	        s.inline = "bash /vagrant/scripts/provision/database/create-mysql.sh $1 $2 $3"
				end
        s.args = [database["name"], database["user"], database["password"]]
      end
    end
  end
end
