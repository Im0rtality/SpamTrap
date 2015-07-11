Vagrant.require_version ">= 1.5"

Vagrant.configure("2") do |config|

  config.vm.box = "puppetlabs/debian-7.8-32-puppet"
  config.vm.network :private_network, ip: "192.168.59.101"
  config.vm.network :forwarded_port, host:5051, guest: 5051
  config.ssh.forward_agent = true

  #Host information
  config.vm.hostname = "spamtrap"

  config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--memory", 512]
    v.customize ["modifyvm", :id, "--cpus", 1]
    v.customize ["modifyvm", :id, "--name", "SpamTrap"]
  end

  if RUBY_PLATFORM =~ /darwin/ || RUBY_PLATFORM =~ /linux/
    config.vm.synced_folder "./", "/var/www", :nfs => true
  end

  config.vm.provision :shell, path: ".provision/bootstrap"
  config.vm.provision :puppet do |puppet|
    puppet.module_path    = ".provision/puppet/modules"
    puppet.manifests_path = ".provision/puppet/manifests"
    puppet.options        = ["--verbose"]
  end

end
