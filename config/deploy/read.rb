server "47.93.88.111", user: "root", roles: %w{app web db queue}

set :deploy_to, "/var/www/WO"

set :ssh_options, {
  keys: %w(~/.ssh/id_rsa),
  forward_agent: true,
  auth_methods: %w(publickey)
}
