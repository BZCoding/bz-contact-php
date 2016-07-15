# -*- mode: ruby -*-
# vi: set ft=ruby :

# A name for your project
PROJECT = 'bzcontact'

VAGRANTFILE_API_VERSION = 2
Vagrant.require_version ">= 1.8.0"
Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  # Cache software packages
  if Vagrant.has_plugin?("vagrant-cachier")
     config.cache.enable :apt
     config.cache.enable :apt_lists
     config.cache.enable :composer
  end

  # Use custom key path, leave commented until after first provision
  # config.ssh.private_key_path = "#{ENV['HOME']}/.ssh/id_rsa"

  # VM definition
  config.vm.define "bzcontact" do |jessie|

    # Box details
    # Note: consider hosting you own boxes (ie http://virtualboxes.loc/boxes/jessie64.box)
    jessie.vm.box      = 'debian/jessie64'
    jessie.vm.box_url  = 'https://atlas.hashicorp.com/debian/boxes/jessie64'
    jessie.vm.hostname = PROJECT

    # Networking setup
    jessie.vm.network "private_network", type: "dhcp"

    # Port forwarding (MySQL, HTTP and HTTPS)
    jessie.vm.network :forwarded_port, guest: 80, host: 8000, auto_correct: true
    jessie.vm.network :forwarded_port, guest: 8080, host: 8080, auto_correct: true
    # jessie.vm.network :forwarded_port, guest: 8443, host: 8443, auto_correct: true
    # jessie.vm.network :forwarded_port, guest: 3306, host: 3306, auto_correct: true

    # Disable default Vagrant share, turn it on only when needed (ie copy large files)
    jessie.vm.synced_folder ".", "/app",
      disabled: false,
      owner: "vagrant",
      group: "www-data"
      # mount_options: ["dmode=775,fmode=774,umask=000"]

    # VM Provider specific settings for VirtualBox
    jessie.vm.provider "virtualbox" do |vb|

      # Share VPN connections
      vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]

      vb.name = PROJECT

      # Customize the amount of memory on the VM:
      vb.memory = "512"
    end

    # Add custom key
    jessie.vm.provision :file, :source => "#{ENV['HOME']}/.ssh/id_rsa.pub", :destination => "/tmp/vagrantfile-pubkey"
    jessie.vm.provision :shell, :privileged => false, :inline => <<-SHELL
      cat /tmp/vagrantfile-pubkey >> $HOME/.ssh/authorized_keys
      SHELL

    # Provisioning with Ansible
    jessie.vm.provision "ansible" do |ansible|
      # Vagrant auto generates the inventory file, uncomment below to use yours
      # ansible.inventory_path = "hosts"

      # Custom config
      # ENV['ANSIBLE_CONFIG'] = "/path/to/custom/ansible.cfg"
      # Example:
      # ENV['ANSIBLE_CONFIG'] = "#{ENV['HOME']}/Dev/Ansible/ansible.cfg"

      # Add the box to mysql group and use custom group vars
      ansible.groups = {
        "php" => "bzcontact",
        "php:vars" => {
          "variable1" => 9,
          "variable2" => "example"
        }
      }

      # Custom host vars
      ansible.host_vars = {
        "bzcontact" => {
        }
      }

      # ansible.verbose = "vvvv"

      # Use local playbook that has access to shared roles, with custom vars above
      ansible.playbook = "ansible/php-apache2.yml"
    end

  end
end
