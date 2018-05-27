# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://vagrantcloud.com/search.
  config.vm.box = "ubuntu/bionic64"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # NOTE: This will enable public access to the opened port
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine and only allow access
  # via 127.0.0.1 to disable public access
  # config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "172.28.128.3"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder ".", "/var/www/evetoolkit"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider "virtualbox" do |vb|
     # Display the VirtualBox GUI when booting the machine
     # vb.gui = true
     # Customize the amount of memory on the VM:
     vb.memory = "1024"
   end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # install requirements
  config.vm.provision "shell", inline: <<-SHELL
     apt-get upgrade -y
     apt-get update -y
     apt-get install -y nginx php-fpm php-mysql php7.2-xml mariadb-server-10.1

     # create db
     mysql -e "CREATE USER 'evetoolkit'@'%' IDENTIFIED BY 'master';" > /dev/null 2>&1
     mysql -e "GRANT ALL PRIVILEGES ON evetoolkit.* TO 'evetoolkit'@'%';"

     # create initial data
     mysql -e "SHOW DATABASES;" | grep "evetoolkit"
     if [ "$?" = "1" ]; then
       mysql -e "CREATE DATABASE evetoolkit;"
       mysql evetoolkit < /vagrant/res/db.sql
     fi

     mkdir -p /etc/ssl/evetoolkit
     cp /var/www/evetoolkit/resources/ssl/fullchain.pem /etc/ssl/evetoolkit/fullchain.pem
     cp /var/www/evetoolkit/resources/ssl/privkey.pem /etc/ssl/evetoolkit/privkey.pem
     cp /var/www/evetoolkit/resources/config/nginx_site.conf /etc/nginx/sites-enabled/evetoolkit

     chown -R www-data:www-data /var/www/evetoolkit/var

     /etc/init.d/nginx restart
     /etc/init.d/php7.2-fpm restart

     # if you need to retrieve a new certificate,
     # uncomment the following lines to install certbot
     # apt-get install -y software-properties-common
     # add-apt-repository ppa:certbot/certbot
     # apt-get install -y certbot

     # this command will retrieve the cert
     # certbot certonly  --manual --manual-public-ip-logging-ok --preferred-challenges dns -d <your-dns-name>
     # copy the certificate and privatekey to the apropriate location
     # cp /etc/letsencrypt/live/<your-dns-name>/fullchain.pem /etc/ssl/evetoolkit/fullchain.pem
     # cp /etc/letsencrypt/live/<your-dns-name>/privkey.pem /etc/ssl/evetoolkit/privkey.pem
     # do not forget to add the certs to your host machine in /res/ssl so
     # that the new certs are also available on vm reload
  SHELL
end
